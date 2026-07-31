from __future__ import annotations

import re
from dataclasses import dataclass


@dataclass
class FilterConfig:
    max_price_eur: float
    notify_on_price_drop: bool
    min_refresh_hz: int
    min_screen_inch: float
    max_screen_inch: float
    max_weight_kg: float | None
    require_windows: bool
    exclude_brands: list[str]
    exclude_keywords: list[str]
    refresh_keywords: list[str]
    # reject = нет Hz → отбросить; accept = нет Hz → пропустить
    # specs_db = нет Hz в карточке, но если в базе спеков есть — используем её
    unknown_refresh_policy: str = "reject"
    # Для топа «ближайшие»: насколько можно вылезти за бюджет
    closest_max_over_eur: float = 250.0


@dataclass
class Candidate:
    url: str
    title: str
    price: float | None
    source: str
    store: str
    text_blob: str = ""
    specs_known: bool = False
    is_gaming_known: bool | None = None
    specs_refresh_hz: int | None = None
    specs_screen_inch: float | None = None


@dataclass
class Closeness:
    """How close a listing is to the target filters (lower score = better)."""

    candidate: Candidate
    score: float
    perfect: bool
    gaps: list[str]
    ok_bits: list[str]
    hz: int | None
    inches: float | None


def matches(candidate: Candidate, cfg: FilterConfig) -> tuple[bool, str]:
    hard = _hard_reject(candidate, cfg)
    if hard:
        return False, hard

    text = f"{candidate.title} {candidate.text_blob}".lower()

    if candidate.price is not None and candidate.price > cfg.max_price_eur:
        return False, f"цена {candidate.price:.0f}€ > лимита {cfg.max_price_eur:.0f}€"

    listing_only = candidate.text_blob
    if "specs_db" in listing_only:
        listing_only = listing_only.split("specs_db", 1)[0]
    listing_hz = _extract_refresh_hz(f"{candidate.title} {listing_only}".lower(), cfg)
    effective_hz = listing_hz if listing_hz is not None else candidate.specs_refresh_hz

    if effective_hz is None or effective_hz < cfg.min_refresh_hz:
        policy = (cfg.unknown_refresh_policy or "reject").lower()
        if effective_hz is None and policy == "accept":
            pass
        elif effective_hz is None and policy == "specs_only" and candidate.specs_known:
            return False, f"нет {cfg.min_refresh_hz} Hz в базе спеков"
        elif effective_hz is None:
            return False, f"нет {cfg.min_refresh_hz} Hz в данных"
        else:
            return False, f"{effective_hz} Hz < {cfg.min_refresh_hz} Hz"

    inches = _screen_inches(candidate)
    if inches is not None:
        if inches < cfg.min_screen_inch or inches > cfg.max_screen_inch:
            return False, f"диагональ {inches}\" вне {cfg.min_screen_inch}-{cfg.max_screen_inch}\""

    if cfg.max_weight_kg is not None:
        weight = _weight_kg(candidate)
        if weight is not None and weight > cfg.max_weight_kg:
            return False, f"вес {weight} кг > {cfg.max_weight_kg} кг"

    return True, "ok"


def score_closeness(candidate: Candidate, cfg: FilterConfig) -> Closeness | None:
    """
    Soft rank vs filters. Returns None for hard rejects (gaming / banned brand).
    Score 0 ≈ full match; higher = farther from the brief.

    Hz is the dominant signal: unknown refresh is a huge penalty so random
    office laptops without 120 Hz never outrank a real near-miss.
    """
    hard = _hard_reject(candidate, cfg)
    if hard:
        return None

    gaps: list[str] = []
    ok_bits: list[str] = []
    score = 0.0

    listing_only = candidate.text_blob
    if "specs_db" in listing_only:
        listing_only = listing_only.split("specs_db", 1)[0]
    listing_hz = _extract_refresh_hz(
        f"{candidate.title} {listing_only}".lower(),
        cfg,
    )
    if listing_hz is not None:
        hz = listing_hz
        hz_source = "карточка"
    elif candidate.specs_refresh_hz is not None:
        hz = candidate.specs_refresh_hz
        hz_source = "база"
    else:
        hz = None
        hz_source = ""

    inches = _screen_inches(candidate)

    # --- refresh Hz (dominant) ---
    if hz is not None and hz >= cfg.min_refresh_hz:
        label = f"{hz} Hz"
        if hz_source:
            label += f" ({hz_source})"
        ok_bits.append(label)
        # tiny nudge: prefer exactly-at-or-above without huge panels
        score += max(0, hz - cfg.min_refresh_hz) * 0.02
    elif hz is not None:
        # Known but below target — still useful near-miss (90–119…)
        deficit = cfg.min_refresh_hz - hz
        score += 80.0 + deficit * 2.5
        src = f" ({hz_source})" if hz_source else ""
        gaps.append(f"{hz} Hz{src} < {cfg.min_refresh_hz} Hz")
    else:
        # No confirmed Hz → almost never "closest"
        score += 400.0
        gaps.append(f"нет {cfg.min_refresh_hz} Hz в данных")

    # --- screen (must be known & near range to matter) ---
    if inches is None:
        score += 120.0
        gaps.append("диагональ неизвестна")
    elif cfg.min_screen_inch <= inches <= cfg.max_screen_inch:
        ok_bits.append(f'{inches}"')
    elif inches < cfg.min_screen_inch:
        delta = cfg.min_screen_inch - inches
        score += 60.0 + delta * 40.0
        gaps.append(f'{inches}" < {cfg.min_screen_inch}"')
    else:
        delta = inches - cfg.max_screen_inch
        score += 60.0 + delta * 40.0
        gaps.append(f'{inches}" > {cfg.max_screen_inch}"')

    # --- price ---
    min_plausible = 250.0
    if candidate.price is None:
        score += 50.0
        gaps.append("цена неизвестна")
    elif candidate.price < min_plausible:
        score += 200.0
        gaps.append(f"цена {candidate.price:.0f}€ похожа на лизинг/ошибку")
    elif candidate.price <= cfg.max_price_eur:
        # Under budget is good; prefer cheaper only slightly among equals
        score += (candidate.price / cfg.max_price_eur) * 3.0
        ok_bits.append(f"цена {candidate.price:.0f}€ ≤ {cfg.max_price_eur:.0f}€")
    else:
        over = candidate.price - cfg.max_price_eur
        # Mild over-budget with real 120Hz should still beat no-Hz junk
        score += 15.0 + (over / cfg.max_price_eur) * 50.0
        gaps.append(f"цена {candidate.price:.0f}€ (+{over:.0f}€ над лимитом)")

    if cfg.max_weight_kg is not None:
        weight = _weight_kg(candidate)
        if weight is None:
            score += 8.0
            gaps.append("вес неизвестен")
        elif weight <= cfg.max_weight_kg:
            ok_bits.append(f"{weight} кг")
        else:
            score += 25.0 + (weight - cfg.max_weight_kg) * 35.0
            gaps.append(f"вес {weight} кг > {cfg.max_weight_kg} кг")

    perfect = not gaps
    if perfect:
        score = min(score, 5.0)

    return Closeness(
        candidate=candidate,
        score=score,
        perfect=perfect,
        gaps=gaps,
        ok_bits=ok_bits,
        hz=hz,
        inches=inches,
    )


def rank_closest(
    candidates: list[Candidate],
    cfg: FilterConfig,
    *,
    top: int = 5,
) -> list[Closeness]:
    """
    Prefer real near-misses:
      1) confirmed ≥ min_hz + screen in range (maybe over budget)
      2) confirmed Hz not far below min + screen in range
    Skip unknown-Hz office laptops unless nothing better exists.
    """
    scored: list[Closeness] = []
    for candidate in candidates:
        item = score_closeness(candidate, cfg)
        if item is not None:
            scored.append(item)

    def screen_ok(item: Closeness) -> bool:
        if item.inches is None:
            return False
        return cfg.min_screen_inch <= item.inches <= cfg.max_screen_inch

    def sort_key(item: Closeness) -> tuple:
        price = item.candidate.price if item.candidate.price is not None else 1e9
        return (item.score, price)

    # How far over budget still counts as "close" (default +250€)
    max_over = float(getattr(cfg, "closest_max_over_eur", 250) or 250)

    def price_near(item: Closeness) -> bool:
        price = item.candidate.price
        if price is None:
            return False
        if price < 250:
            return False
        return price <= cfg.max_price_eur + max_over

    # Tier A: confirmed target Hz + screen in range + price not insane
    tier_a = [
        item
        for item in scored
        if item.hz is not None
        and item.hz >= cfg.min_refresh_hz
        and screen_ok(item)
        and price_near(item)
    ]
    tier_a.sort(key=sort_key)
    if len(tier_a) >= top:
        return tier_a[:top]

    tier_a_ids = {id(item) for item in tier_a}
    # Tier B: known Hz within 30 of target (e.g. 90+) + screen ok
    floor_hz = max(60, cfg.min_refresh_hz - 30)
    tier_b = [
        item
        for item in scored
        if id(item) not in tier_a_ids
        and item.hz is not None
        and item.hz >= floor_hz
        and screen_ok(item)
        and price_near(item)
    ]
    tier_b.sort(key=sort_key)

    merged = tier_a + tier_b
    if merged:
        return merged[:top]

    # Last resort: still don't dump unknown-Hz noise — return empty
    return []


def _hard_reject(candidate: Candidate, cfg: FilterConfig) -> str | None:
    text = f"{candidate.title} {candidate.text_blob}".lower()

    if candidate.is_gaming_known is True:
        return "игровой (из базы спеков)"

    for brand in cfg.exclude_brands:
        if brand.lower() in text:
            return f"исключённый бренд: {brand}"

    for keyword in cfg.exclude_keywords:
        if keyword.lower() in text:
            return f"ключевое слово: {keyword}"

    if cfg.require_windows and "mac" in text and "macbook" in text:
        return "macOS"

    return None


def _has_refresh(text: str, cfg: FilterConfig) -> bool:
    hz = _extract_refresh_hz(text, cfg)
    return hz is not None and hz >= cfg.min_refresh_hz


def _extract_refresh_hz(text: str, cfg: FilterConfig) -> int | None:
    found: list[int] = []
    for kw in cfg.refresh_keywords:
        if kw.lower() in text:
            digits = re.search(r"(\d{2,3})", kw)
            if digits:
                found.append(int(digits.group(1)))
            else:
                found.append(cfg.min_refresh_hz)

    for match in re.finditer(r"(?<![\dx])(\d{2,3})\s*hz\b", text, re.IGNORECASE):
        found.append(int(match.group(1)))
    for match in re.finditer(r"(?<![\dx])(\d{2,3})\s*гц\b", text, re.IGNORECASE):
        found.append(int(match.group(1)))

    if not found:
        return None
    return max(found)


def _screen_inches(candidate: Candidate) -> float | None:
    inches = _extract_screen_inches(candidate.title)
    if inches is not None:
        return inches
    inches = _extract_screen_inches(f"{candidate.title} {candidate.text_blob}".lower())
    if inches is not None:
        return inches
    return candidate.specs_screen_inch


def _weight_kg(candidate: Candidate) -> float | None:
    weight = _extract_weight_kg(candidate.text_blob)
    if weight is not None:
        return weight
    return _extract_weight_kg(f"{candidate.title} {candidate.text_blob}".lower())


def _extract_screen_inches(text: str) -> float | None:
    patterns = [
        r'(\d{2}(?:[.,]\d)?)\s*"',
        r"(\d{2}(?:[.,]\d)?)\s*(?:inch|collas|collu|дюйм)",
        r"(\d{2}(?:[.,]\d)?)\s*''",
        r"\b[sSxX](\d{2}(?:[.,]\d)?)\b",  # S14, X15
        r"\b(\d{2}(?:[.,]\d)?)\s*(?:oled|ips|wuxga|uhd|fhd|qhd|3k)\b",
        r"\b(?:go|flip|pro|air)\s+(\d{2}(?:[.,]\d)?)\b",
        r"\b(\d{2}(?:[.,]\d)?)-?(?:inch|in)\b",
    ]
    for pattern in patterns:
        match = re.search(pattern, text, re.IGNORECASE)
        if match:
            value = float(match.group(1).replace(",", "."))
            if 11.0 <= value <= 18.0:
                return value
    return None


def _extract_weight_kg(text: str) -> float | None:
    match = re.search(r"(\d(?:[.,]\d+)?)\s*kg", text)
    if match:
        return float(match.group(1).replace(",", "."))
    match = re.search(r"(\d(?:[.,]\d+)?)\s*кг", text)
    if match:
        return float(match.group(1).replace(",", "."))
    return None
