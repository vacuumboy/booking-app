from __future__ import annotations

import sqlite3
from pathlib import Path


class CatalogSeen:
    """Track which product URLs were already shown in dumps / scans."""

    def __init__(self, db_path: Path) -> None:
        self.db_path = db_path
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                CREATE TABLE IF NOT EXISTS catalog_seen (
                    url TEXT PRIMARY KEY,
                    title TEXT NOT NULL DEFAULT '',
                    price REAL,
                    first_seen_at TEXT NOT NULL
                )
                """
            )

    def is_new(self, url: str) -> bool:
        with sqlite3.connect(self.db_path) as conn:
            row = conn.execute(
                "SELECT 1 FROM catalog_seen WHERE url = ?", (url,)
            ).fetchone()
        return row is None

    def mark(
        self,
        url: str,
        *,
        title: str = "",
        price: float | None = None,
    ) -> None:
        from datetime import datetime, timezone

        now = datetime.now(timezone.utc).isoformat()
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                INSERT INTO catalog_seen (url, title, price, first_seen_at)
                VALUES (?, ?, ?, ?)
                ON CONFLICT(url) DO UPDATE SET
                    title=excluded.title,
                    price=excluded.price
                """,
                (url, title, price, now),
            )

    def mark_many(self, rows: list[tuple[str, str, float | None]]) -> None:
        for url, title, price in rows:
            self.mark(url, title=title, price=price)
