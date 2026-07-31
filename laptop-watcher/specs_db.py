from __future__ import annotations

import re
import sqlite3
from dataclasses import dataclass
from pathlib import Path

import yaml


ROOT = Path(__file__).resolve().parent


@dataclass
class ModelSpec:
    code: str
    brand: str
    model: str
    refresh_hz: int | None
    screen_inch: float | None
    weight_kg: float | None
    is_gaming: bool
    notes: str = ""

    def as_text_blob(self) -> str:
        parts = [
            f"specs_db brand={self.brand}",
            f"model={self.model}",
            f"matched_code={self.code}",
        ]
        if self.refresh_hz:
            parts.append(f"{self.refresh_hz} Hz")
        if self.screen_inch:
            parts.append(f'{self.screen_inch}"')
        if self.weight_kg:
            parts.append(f"{self.weight_kg} kg")
        if self.is_gaming:
            parts.append("gaming")
        if self.notes:
            parts.append(self.notes)
        return " ".join(parts)


class SpecsDB:
    def __init__(self, db_path: Path | None = None, seed_path: Path | None = None) -> None:
        self.db_path = db_path or (ROOT / "data" / "specs.db")
        self.seed_path = seed_path or (ROOT / "specs" / "seed.yaml")
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        self._init_db()
        self.load_seed()

    def _init_db(self) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                CREATE TABLE IF NOT EXISTS model_specs (
                    code TEXT PRIMARY KEY,
                    brand TEXT NOT NULL,
                    model TEXT NOT NULL,
                    refresh_hz INTEGER,
                    screen_inch REAL,
                    weight_kg REAL,
                    is_gaming INTEGER NOT NULL DEFAULT 0,
                    notes TEXT NOT NULL DEFAULT ''
                )
                """
            )

    def load_seed(self) -> int:
        """Reload seed from scratch so removed vague aliases disappear."""
        if not self.seed_path.exists():
            return 0
        with self.seed_path.open("r", encoding="utf-8") as handle:
            payload = yaml.safe_load(handle) or {}

        with sqlite3.connect(self.db_path) as conn:
            conn.execute("DELETE FROM model_specs")

        count = 0
        for entry in payload.get("models", []):
            codes = entry.get("codes") or []
            for code in codes:
                self.upsert(
                    ModelSpec(
                        code=str(code).strip(),
                        brand=str(entry.get("brand", "")),
                        model=str(entry.get("model", "")),
                        refresh_hz=_as_int(entry.get("refresh_hz")),
                        screen_inch=_as_float(entry.get("screen_inch")),
                        weight_kg=_as_float(entry.get("weight_kg")),
                        is_gaming=bool(entry.get("is_gaming", False)),
                        notes=str(entry.get("notes", "")),
                    )
                )
                count += 1
        return count

    def upsert(self, spec: ModelSpec) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                INSERT INTO model_specs
                    (code, brand, model, refresh_hz, screen_inch, weight_kg, is_gaming, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT(code) DO UPDATE SET
                    brand=excluded.brand,
                    model=excluded.model,
                    refresh_hz=excluded.refresh_hz,
                    screen_inch=excluded.screen_inch,
                    weight_kg=excluded.weight_kg,
                    is_gaming=excluded.is_gaming,
                    notes=excluded.notes
                """,
                (
                    spec.code.lower(),
                    spec.brand,
                    spec.model,
                    spec.refresh_hz,
                    spec.screen_inch,
                    spec.weight_kg,
                    1 if spec.is_gaming else 0,
                    spec.notes,
                ),
            )

    def count(self) -> int:
        with sqlite3.connect(self.db_path) as conn:
            row = conn.execute("SELECT COUNT(*) FROM model_specs").fetchone()
            return int(row[0]) if row else 0

    def lookup(self, text: str) -> ModelSpec | None:
        """
        Match only concrete MPN/SKU-style codes (must contain a digit),
        preferring the longest hit. Vague marketing names are ignored.
        Short gaming family markers (LOQ, TUF…) match as whole words only.
        """
        hay = text.lower()
        with sqlite3.connect(self.db_path) as conn:
            rows = conn.execute(
                """
                SELECT code, brand, model, refresh_hz, screen_inch, weight_kg, is_gaming, notes
                FROM model_specs
                ORDER BY LENGTH(code) DESC
                """
            ).fetchall()

        def to_spec(row: tuple) -> ModelSpec:
            return ModelSpec(
                code=row[0],
                brand=row[1],
                model=row[2],
                refresh_hz=row[3],
                screen_inch=row[4],
                weight_kg=row[5],
                is_gaming=bool(row[6]),
                notes=row[7] or "",
            )

        # 1) Longest digit-containing code with token boundaries
        #    (so UX3405 does not steal UX3405CA-QL219W, and longest wins)
        best: ModelSpec | None = None
        best_len = 0
        for row in rows:
            code = row[0]
            is_gaming = bool(row[6])
            if not re.search(r"\d", code):
                continue
            if not _code_in_text(code, hay):
                continue
            if len(code) > best_len:
                best = to_spec(row)
                best_len = len(code)
        if best is not None:
            return best

        # 2) Gaming family markers as whole words (no digits)
        for row in rows:
            code = row[0]
            if not bool(row[6]):
                continue
            if re.search(r"\d", code):
                continue
            if re.search(rf"\b{re.escape(code)}\b", hay, re.IGNORECASE):
                return to_spec(row)

        return None

    def enrich_text(
        self,
        title: str,
        text_blob: str,
        url: str = "",
    ) -> tuple[str, ModelSpec | None]:
        combined = f"{title}\n{text_blob}\n{url}"
        spec = self.lookup(combined)
        if spec is None:
            return text_blob, None
        extra = spec.as_text_blob()
        return f"{text_blob} {extra}".strip(), spec


def _code_in_text(code: str, hay: str) -> bool:
    """Match code as its own token; '-' / '_' / '/' count as boundaries."""
    code = code.lower()
    if code not in hay:
        return False
    # Boundary: not preceded/followed by alphanumeric (hyphen is ok → full SKU wins longer)
    pattern = rf"(?<![a-z0-9]){re.escape(code)}(?![a-z0-9])"
    return re.search(pattern, hay) is not None


def extract_product_codes(text: str) -> list[str]:
    patterns = [
        r"\b([A-Z]{1,3}\d{2,4}[A-Z0-9-]{2,})\b",  # UX3405CA, M5406WA, UX3405CA-QL219W
        r"\b(\d{2}-[a-z]{2}\d{3,4}[a-z]{0,4})\b",  # 14-ew1000
        r"\b([A-Z0-9]{5,12})\b",  # 8A670EA / 82XQ013ALT
        r"MPN[:\s]+([A-Z0-9-]{4,})",
        r"SKU[:\s]+([A-Z0-9-]{4,})",
    ]
    found: list[str] = []
    seen: set[str] = set()
    for pattern in patterns:
        for match in re.finditer(pattern, text, re.IGNORECASE):
            token = match.group(1).strip()
            key = token.lower()
            if key in seen or len(token) < 4:
                continue
            seen.add(key)
            found.append(token)
    return found


def _as_int(value: object) -> int | None:
    if value is None or value == "":
        return None
    return int(value)


def _as_float(value: object) -> float | None:
    if value is None or value == "":
        return None
    return float(value)
