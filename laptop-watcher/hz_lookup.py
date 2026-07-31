from __future__ import annotations

import html
import re
import sqlite3
import time
from collections import Counter
from dataclasses import dataclass
from pathlib import Path
from urllib.parse import quote_plus, unquote

import httpx

from specs_db import ModelSpec, SpecsDB, extract_product_codes


ROOT = Path(__file__).resolve().parent

USER_AGENT = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
)

# Common laptop panel refresh rates
KNOWN_HZ = frozenset({48, 50, 60, 75, 90, 100, 120, 144, 165, 180, 240, 300, 360})

BRAND_HINTS = (
    "asus",
    "acer",
    "hp",
    "hewlett",
    "msi",
    "samsung",
    "microsoft",
    "lenovo",
    "dell",
    "apple",
    "huawei",
    "honor",
)


@dataclass
class HzLookupResult:
    refresh_hz: int
    code: str
    source: str  # web_search / web_page
    evidence: str = ""


class HzWebLookup:
    """
    When a product card has no refresh rate, search the web by MPN/model
    and vote on Hz mentions (boosted when the exact code is in the snippet).
    Results are cached in SQLite so we don't hammer search every scan.
    """

    def __init__(
        self,
        specs: SpecsDB,
        *,
        enabled: bool = True,
        max_lookups: int = 25,
        delay_sec: float = 1.0,
        cache_path: Path | None = None,
    ) -> None:
        self.specs = specs
        self.enabled = enabled
        self.max_lookups = max(0, max_lookups)
        self.delay_sec = max(0.2, delay_sec)
        self.cache_path = cache_path or (ROOT / "data" / "hz_web_cache.db")
        self.lookups_done = 0
        self.hits = 0
        self.misses = 0
        self._init_cache()

    def _init_cache(self) -> None:
        self.cache_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.cache_path) as conn:
            conn.execute(
                """
                CREATE TABLE IF NOT EXISTS hz_web_cache (
                    query_key TEXT PRIMARY KEY,
                    refresh_hz INTEGER,
                    code TEXT NOT NULL DEFAULT '',
                    evidence TEXT NOT NULL DEFAULT '',
                    status TEXT NOT NULL,
                    checked_at REAL NOT NULL
                )
                """
            )

    def lookup(
        self,
        *,
        title: str,
        url: str = "",
        text_blob: str = "",
    ) -> HzLookupResult | None:
        if not self.enabled or self.lookups_done >= self.max_lookups:
            return None

        codes = _best_codes(title, url, text_blob)
        brand = _guess_brand(title)
        query_key = (codes[0] if codes else _title_key(title)).lower()

        cached = self._cache_get(query_key)
        if cached is not None:
            status, hz, code, evidence = cached
            if status == "miss":
                self.misses += 1
                return None
            if status == "hit" and hz:
                self.hits += 1
                return HzLookupResult(refresh_hz=hz, code=code or query_key, source="cache", evidence=evidence)

        self.lookups_done += 1
        result, had_signal = self._search_web(codes=codes, title=title, brand=brand)
        time.sleep(self.delay_sec)

        if result is None:
            self.misses += 1
            if had_signal:
                # Real search results but no usable Hz → remember miss
                self._cache_set(query_key, None, codes[0] if codes else "", "", "miss")
                print(f"  🌐 Hz web: miss «{query_key}»", flush=True)
            else:
                print(f"  🌐 Hz web: пустой поиск «{query_key}» (не кэширую)", flush=True)
            return None

        self.hits += 1
        self._cache_set(query_key, result.refresh_hz, result.code, result.evidence, "hit")
        # Persist into specs DB under the concrete code for next runs
        code = result.code or query_key
        if re.search(r"\d", code) and len(code) >= 5:
            self.specs.upsert(
                ModelSpec(
                    code=code,
                    brand=brand or "unknown",
                    model=title[:80],
                    refresh_hz=result.refresh_hz,
                    screen_inch=None,
                    weight_kg=None,
                    is_gaming=False,
                    notes=f"web:{result.source} {result.evidence[:120]}",
                )
            )
        print(
            f"  🌐 Hz web: {result.refresh_hz} Hz для «{code}» "
            f"({result.evidence[:80]})",
            flush=True,
        )
        return result

    def _search_web(
        self,
        *,
        codes: list[str],
        title: str,
        brand: str,
    ) -> tuple[HzLookupResult | None, bool]:
        queries: list[str] = []
        primary = codes[0] if codes else ""
        if primary:
            queries.append(f"{primary} refresh rate Hz")
            if brand:
                queries.append(f"{brand} {primary} Hz OLED")
        short = _short_title(title)
        if short and short.lower() not in {q.lower() for q in queries}:
            queries.append(f"{short} refresh rate Hz")
        queries = queries[:2]

        all_snippets: list[str] = []
        page_links: list[str] = []
        with httpx.Client(timeout=20, follow_redirects=True, headers={"User-Agent": USER_AGENT}) as client:
            for query in queries:
                snippets, links = _ddg_search(client, query)
                if not snippets:
                    # one retry after a short pause (DDG sometimes rate-limits)
                    time.sleep(0.8)
                    snippets, links = _ddg_search(client, query)
                all_snippets.extend(snippets)
                page_links.extend(links)

            snippet_vote = _vote_hz(all_snippets, codes)

            page_hz = None
            page_url = ""
            for link in page_links[:6]:
                if not _is_maker_spec_url(link):
                    continue
                page_hz = _hz_from_page(client, link, prefer_code=primary)
                if page_hz is not None:
                    page_url = link
                    break

        had_signal = bool(all_snippets) or page_hz is not None

        if snippet_vote and page_hz is not None:
            snip_hz, snip_ev = snippet_vote
            if primary and primary.lower() in snip_ev.lower() and snip_hz != page_hz:
                return (
                    HzLookupResult(
                        refresh_hz=snip_hz,
                        code=primary or _title_key(title),
                        source="web_search",
                        evidence=snip_ev[:160],
                    ),
                    True,
                )
            if snip_hz == page_hz:
                return (
                    HzLookupResult(
                        refresh_hz=page_hz,
                        code=primary or _title_key(title),
                        source="web_page",
                        evidence=page_url[:120],
                    ),
                    True,
                )
            return (
                HzLookupResult(
                    refresh_hz=snip_hz,
                    code=primary or _title_key(title),
                    source="web_search",
                    evidence=snip_ev[:160],
                ),
                True,
            )

        if snippet_vote is not None:
            hz, evidence = snippet_vote
            return (
                HzLookupResult(
                    refresh_hz=hz,
                    code=primary or _title_key(title),
                    source="web_search",
                    evidence=evidence[:160],
                ),
                True,
            )

        if page_hz is not None:
            return (
                HzLookupResult(
                    refresh_hz=page_hz,
                    code=primary or _title_key(title),
                    source="web_page",
                    evidence=page_url[:120],
                ),
                True,
            )
        return None, had_signal

    def _cache_get(self, key: str) -> tuple[str, int | None, str, str] | None:
        with sqlite3.connect(self.cache_path) as conn:
            row = conn.execute(
                "SELECT status, refresh_hz, code, evidence FROM hz_web_cache WHERE query_key=?",
                (key,),
            ).fetchone()
        if not row:
            return None
        return str(row[0]), (int(row[1]) if row[1] is not None else None), str(row[2] or ""), str(row[3] or "")

    def _cache_set(
        self,
        key: str,
        hz: int | None,
        code: str,
        evidence: str,
        status: str,
    ) -> None:
        with sqlite3.connect(self.cache_path) as conn:
            conn.execute(
                """
                INSERT INTO hz_web_cache (query_key, refresh_hz, code, evidence, status, checked_at)
                VALUES (?, ?, ?, ?, ?, ?)
                ON CONFLICT(query_key) DO UPDATE SET
                    refresh_hz=excluded.refresh_hz,
                    code=excluded.code,
                    evidence=excluded.evidence,
                    status=excluded.status,
                    checked_at=excluded.checked_at
                """,
                (key, hz, code.lower(), evidence, status, time.time()),
            )


def soft_eligible_for_web_hz(
    *,
    title: str,
    price: float | None,
    text_blob: str,
    max_price: float,
    max_over: float,
    min_inch: float,
    max_inch: float,
    exclude_brands: list[str],
    exclude_keywords: list[str],
) -> bool:
    """True when listing looks like a real candidate except missing Hz."""
    text = f"{title} {text_blob}".lower()
    for brand in exclude_brands:
        if brand.lower() in text:
            return False
    for keyword in exclude_keywords:
        if keyword.lower() in text:
            return False
    if price is None or price < 250:
        return False
    if price > max_price + max_over:
        return False
    inches = None
    for pattern in (
        r'(\d{2}(?:[.,]\d)?)\s*"',
        r"\b[sSxX](\d{2}(?:[.,]\d)?)\b",
        r"\b(\d{2}(?:[.,]\d)?)\s*(?:oled|ips|inch|collas)\b",
    ):
        match = re.search(pattern, title, flags=re.I)
        if match:
            inches = float(match.group(1).replace(",", "."))
            break
    if inches is not None and (inches < min_inch or inches > max_inch):
        return False
    return True


def _best_codes(title: str, url: str, text_blob: str) -> list[str]:
    junk = (
        "product",
        "portativ",
        "laptop",
        "html",
        "http",
        "https",
        "www",
        "cenas",
        "veikals",
        "klepjdatori",
    )

    def usable(code: str) -> bool:
        if not re.search(r"\d", code):
            return False
        if len(code) < 5:
            return False
        if re.fullmatch(r"\d{4}", code):
            return False
        lowered = code.lower()
        if lowered in {"16gb", "32gb", "512gb", "1tb", "8gb"}:
            return False
        if any(token in lowered for token in junk):
            return False
        # need at least one letter (SKU-like), not pure numeric path ids
        if not re.search(r"[a-zA-Z]", code):
            return False
        return True

    scored: list[tuple[int, str]] = []
    for source_boost, text in ((200, title), (80, text_blob), (40, url)):
        for code in extract_product_codes(text):
            if not usable(code):
                continue
            scored.append((source_boost + len(code), code))

    scored.sort(reverse=True)
    out: list[str] = []
    seen: set[str] = set()
    for _, code in scored:
        key = code.lower()
        if key in seen:
            continue
        # Prefer keeping shorter clean MPN if we already have it as prefix
        if any(key.startswith(prev) or prev.startswith(key) for prev in seen):
            # keep the longer only if already not present; skip weaker shorter later
            longer = max([key] + [p for p in seen if key.startswith(p) or p.startswith(key)], key=len)
            if longer != key and key in {p for p in seen}:
                continue
        seen.add(key)
        out.append(code)
    return out[:4]


def _guess_brand(title: str) -> str:
    lowered = title.lower()
    for brand in BRAND_HINTS:
        if brand in lowered:
            if brand == "hewlett":
                return "HP"
            return brand.upper() if brand != "hp" else "HP"
    return ""


def _title_key(title: str) -> str:
    cleaned = re.sub(r"[^a-zA-Z0-9]+", "-", title.lower()).strip("-")
    return cleaned[:60] or "unknown"


def _short_title(title: str) -> str:
    # Drop store fluff after dash
    base = re.split(r"\s[-–—]\s", title, maxsplit=1)[0]
    base = re.sub(r"\b(ENG|LV|RU|USB|Wi-?Fi|Windows\s*11)\b", "", base, flags=re.I)
    return re.sub(r"\s+", " ", base).strip()[:90]


def _ddg_search(client: httpx.Client, query: str) -> tuple[list[str], list[str]]:
    url = f"https://html.duckduckgo.com/html/?q={quote_plus(query)}"
    try:
        response = client.get(url)
    except httpx.HTTPError:
        return [], []
    if response.status_code >= 400:
        return [], []
    page = response.text
    snippets = [
        _strip_tags(chunk)
        for chunk in re.findall(
            r'class="result__snippet[^"]*"[^>]*>(.*?)</(?:a|td|div)',
            page,
            flags=re.I | re.S,
        )
    ]
    titles = [
        _strip_tags(chunk)
        for chunk in re.findall(r'class="result__a[^"]*"[^>]*>(.*?)</a>', page, flags=re.I | re.S)
    ]
    links: list[str] = []
    for raw in re.findall(r"uddg=([^&\"]+)", page):
        links.append(unquote(raw))
    # also plain hrefs
    for href in re.findall(r'class="result__a"[^>]*href="([^"]+)"', page, flags=re.I):
        if href.startswith("http"):
            links.append(href)
    return snippets + titles, links


def _strip_tags(value: str) -> str:
    cleaned = re.sub(r"<[^>]+>", " ", html.unescape(value))
    return re.sub(r"\s+", " ", cleaned).strip()


def _is_maker_spec_url(url: str) -> bool:
    lowered = url.lower()
    makers = (
        "asus.com",
        "hp.com",
        "acer.com",
        "msi.com",
        "samsung.com",
        "lenovo.com",
        "dell.com",
    )
    if not any(m in lowered for m in makers):
        return False
    return any(token in lowered for token in ("techspec", "specs", "specifications", "/product/", "notebook"))


def _hz_from_page(client: httpx.Client, url: str, prefer_code: str = "") -> int | None:
    try:
        response = client.get(url, timeout=25)
    except httpx.HTTPError:
        return None
    if response.status_code >= 400:
        return None
    text = response.text
    lowered = text.lower()
    code_l = prefer_code.lower()

    votes: Counter[int] = Counter()
    for match in re.finditer(
        r"(.{0,80})(?:refresh(?:\s*rate)?|частота)[:\s]{0,12}(\d{2,3})\s*(?:hz|гц)(.{0,80})",
        lowered,
        flags=re.I,
    ):
        left, raw, right = match.group(1), match.group(2), match.group(3)
        try:
            hz = int(raw)
        except ValueError:
            continue
        if hz not in KNOWN_HZ:
            continue
        window = f"{left} {right}"
        weight = 1
        if code_l and code_l in window:
            weight += 8
        elif code_l and code_l in lowered:
            weight += 1
        votes[hz] += weight

    if not votes:
        return None

    ranked = votes.most_common()
    best_hz, best_score = ranked[0]
    # Ambiguous series page (60 and 120 both strong, no MPN proximity) → abstain
    if len(ranked) > 1:
        second_hz, second_score = ranked[1]
        if best_score < 8 and second_score >= best_score - 1:
            return None
        if {best_hz, second_hz} >= {60, 120} and best_score < 8:
            return None
    return best_hz


def _vote_hz(snippets: list[str], codes: list[str]) -> tuple[int, str] | None:
    if not snippets:
        return None
    codes_l = [c.lower() for c in codes]
    primary = codes_l[0] if codes_l else ""
    family = primary.split("-")[0] if primary else ""
    scores: Counter[int] = Counter()
    evidence: dict[int, str] = {}

    for snip in snippets:
        s = snip.lower()
        # Skip obvious non-laptop monitor chatter without a model code
        if "monitor refresh" in s and (not primary or primary not in s):
            continue
        for match in re.finditer(r"(\d{2,3})\s*hz", s):
            hz = int(match.group(1))
            if hz not in KNOWN_HZ:
                continue
            start = max(0, match.start() - 100)
            end = min(len(s), match.end() + 100)
            window = s[start:end]
            weight = 1
            code_in_snip = bool(primary and primary in s)
            if code_in_snip:
                weight += 5
            elif family and len(family) >= 5 and family in window:
                weight += 2
            elif any(code in s for code in codes_l[1:]):
                weight += 3
                code_in_snip = True
            # High gaming rates need the model code in the same snippet
            if hz >= 144 and not code_in_snip:
                continue
            if re.search(r"refresh(?:\s*rate)?\s*[:=]?\s*" + str(hz), s):
                weight += 2
            scores[hz] += weight
            if hz not in evidence or weight >= 5:
                evidence[hz] = snip.strip()[:160]

    if not scores:
        return None

    ranked = sorted(
        scores.items(),
        key=lambda kv: (kv[1], kv[0] in {60, 120, 144}),
        reverse=True,
    )
    best_hz, best_score = ranked[0]
    if primary:
        for hz, score in ranked:
            if hz == 60 and score >= best_score - 2:
                ev = evidence.get(60, "").lower()
                if primary in ev:
                    return 60, evidence.get(60, "")
    return best_hz, evidence.get(best_hz, "")
