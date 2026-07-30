import json
import sqlite3
from dataclasses import dataclass
from pathlib import Path


@dataclass
class ListingRecord:
    url: str
    title: str
    price: float | None
    source: str
    store: str


class ListingStore:
    def __init__(self, db_path: Path) -> None:
        self.db_path = db_path
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        self._init_db()

    def _init_db(self) -> None:
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                CREATE TABLE IF NOT EXISTS listings (
                    url TEXT PRIMARY KEY,
                    title TEXT NOT NULL,
                    price REAL,
                    source TEXT NOT NULL,
                    store TEXT NOT NULL,
                    first_seen_at TEXT NOT NULL,
                    last_seen_at TEXT NOT NULL
                )
                """
            )

    def upsert(self, listing: ListingRecord) -> tuple[bool, bool]:
        """Returns (is_new, price_dropped)."""
        with sqlite3.connect(self.db_path) as conn:
            row = conn.execute(
                "SELECT price FROM listings WHERE url = ?", (listing.url,)
            ).fetchone()

            now = _utc_now()
            if row is None:
                conn.execute(
                    """
                    INSERT INTO listings (url, title, price, source, store, first_seen_at, last_seen_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    """,
                    (
                        listing.url,
                        listing.title,
                        listing.price,
                        listing.source,
                        listing.store,
                        now,
                        now,
                    ),
                )
                return True, False

            old_price = row[0]
            price_dropped = (
                listing.price is not None
                and old_price is not None
                and listing.price < old_price
            )
            conn.execute(
                """
                UPDATE listings
                SET title = ?, price = ?, source = ?, store = ?, last_seen_at = ?
                WHERE url = ?
                """,
                (
                    listing.title,
                    listing.price,
                    listing.source,
                    listing.store,
                    now,
                    listing.url,
                ),
            )
            return False, price_dropped


def _utc_now() -> str:
    from datetime import datetime, timezone

    return datetime.now(timezone.utc).isoformat()
