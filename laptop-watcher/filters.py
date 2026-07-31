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

    has_refresh = _has_refresh(text, cfg)
    if not has_refresh:
        policy = (cfg.unknown_refresh_policy or "reject").lower()
        if policy == "accept":
            pass
        elif policy == "specs_only" and candidate.specs_known:
            return False, f"нет {cfg.min_refresh_hz} Hz в базе спеков"
        else:
            return False, f"нет {cfg.min_refresh_hz} Hz в данных"

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
    """
    hard = _hard_reject(candidate, cfg)
    if hard:
        return None

    text = f"{candidate.title} {candidate.text_blob}".lower()
    gaps: list[str] = []
    ok_bits: list[str] = []
    score = 0.0

    # --- price (budget ceiling) ---
    # Tiny "from 1€/mo" leasing teasers shouldn't win the ranking.
    min_plausible = 250.0
    if candidate.price is None:
        score += 35.0
        gaps.append("цена неизвестна")
    elif candidate.price < min_plausible:
        score += 70.0
        gaps.append(f"цена {candidate.price:.0f}€ похожа на лизинг/ошибку")
    elif candidate.price <= cfg.max_price_eur:
        # Meets budget; tiny nudge so cheaper ranks above pricier equals.
        score += (candidate.price / cfg.max_price_eur) * 2.0
        ok_bits.append(f"цена {candidate.price:.0f}€ ≤ {cfg.max_price_eur:.0f}€")
    else:
        over = candidate.price - cfg.max_price_eur
        score += 40.0 + (over / cfg.max_price_eur) * 80.0
        gaps.append(f"цена {candidate.price:.0f}€ (+{over:.0f}€ над лимитом)")

    # --- refresh Hz ---
    hz = _extract_refresh_hz(text, cfg)
    if hz is not None and hz >= cfg.min_refresh_hz:
        ok_bits.append(f"{hz} Hz")
    elif hz is not None:
        score += 45.0 + ((cfg.min_refresh_hz - hz) / cfg.min_refresh_hz) * 40.0
        gaps.append(f"{hz} Hz < {cfg.min_refresh_hz} Hz")
    else:
        score += 55.0
        gaps.append(f"нет {cfg.min_refresh_hz} Hz в данных")

    # --- screen ---
    inches = _screen_inches(candidate)
    if inches is None:
        score += 12.0
        gaps.append("диагональ неизвестна")
    elif cfg.min_screen_inch <= inches <= cfg.max_screen_inch:
        ok_bits.append(f'{inches}"')
    elif inches < cfg.min_screen_inch:
        delta = cfg.min_screen_inch - inches
        score += 20.0 + delta * 18.0
        gaps.append(f'{inches}" < {cfg.min_screen_inch}"')
    else:
        delta = inches - cfg.max_screen_inch
        score += 20.0 + delta * 18.0
        gaps.append(f'{inches}" > {cfg.max_screen_inch}"')

    # --- weight (only if user set a limit) ---
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
        score = min(score, 3.0)  # keep slight price preference among perfects

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
    ranked: list[Closeness] = []
    for candidate in candidates:
        item = score_closeness(candidate, cfg)
        if item is not None:
            ranked.append(item)
    ranked.sort(key=lambda item: (item.score, item.candidate.price or 1e9))
    return ranked[: max(0, top)]


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
    return _extract_screen_inches(f"{candidate.title} {candidate.text_blob}".lower())


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
    ]
    for pattern in patterns:
        match = re.search(pattern, text)
        if match:
            return float(match.group(1).replace(",", "."))
    return None


def _extract_weight_kg(text: str) -> float | None:
    match = re.search(r"(\d(?:[.,]\d+)?)\s*kg", text)
    if match:
        return float(match.group(1).replace(",", "."))
    match = re.search(r"(\d(?:[.,]\d+)?)\s*кг", text)
    if match:
        return float(match.group(1).replace(",", "."))
    return None
