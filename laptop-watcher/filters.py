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


def matches(candidate: Candidate, cfg: FilterConfig) -> tuple[bool, str]:
    text = f"{candidate.title} {candidate.text_blob}".lower()

    if candidate.is_gaming_known is True:
        return False, "игровой (из базы спеков)"

    for brand in cfg.exclude_brands:
        if brand.lower() in text:
            return False, f"исключённый бренд: {brand}"

    for keyword in cfg.exclude_keywords:
        if keyword.lower() in text:
            return False, f"ключевое слово: {keyword}"

    if candidate.price is not None and candidate.price > cfg.max_price_eur:
        return False, f"цена {candidate.price:.0f}€ > лимита {cfg.max_price_eur:.0f}€"

    if cfg.require_windows and "mac" in text and "macbook" in text:
        return False, "macOS"

    has_refresh = _has_refresh(text, cfg)
    if not has_refresh:
        policy = (cfg.unknown_refresh_policy or "reject").lower()
        if policy == "accept":
            pass
        elif policy == "specs_only" and candidate.specs_known:
            return False, f"нет {cfg.min_refresh_hz} Hz в базе спеков"
        else:
            return False, f"нет {cfg.min_refresh_hz} Hz в данных"

    inches = _extract_screen_inches(candidate.title)
    if inches is None:
        inches = _extract_screen_inches(text)
    if inches is not None:
        if inches < cfg.min_screen_inch or inches > cfg.max_screen_inch:
            return False, f"диагональ {inches}\" вне {cfg.min_screen_inch}-{cfg.max_screen_inch}\""

    if cfg.max_weight_kg is not None:
        weight = _extract_weight_kg(candidate.text_blob)
        if weight is None:
            weight = _extract_weight_kg(text)
        if weight is not None and weight > cfg.max_weight_kg:
            return False, f"вес {weight} кг > {cfg.max_weight_kg} кг"

    return True, "ok"


def _has_refresh(text: str, cfg: FilterConfig) -> bool:
    for kw in cfg.refresh_keywords:
        if kw.lower() in text:
            return True

    for match in re.finditer(r"(?<![\dx])(\d{2,3})\s*hz\b", text, re.IGNORECASE):
        if int(match.group(1)) >= cfg.min_refresh_hz:
            return True
    return False


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
