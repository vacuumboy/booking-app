from __future__ import annotations

import os
from contextlib import contextmanager
from typing import Iterator

from sources.common import is_blocked

# Stores that need (or benefit from) a real browser behind Cloudflare.
BROWSER_STORE_IDS = frozenset({"220", "dateks", "aio", "balticdata"})

STEALTH_INIT = """
Object.defineProperty(navigator, 'webdriver', {get: () => undefined});
window.chrome = { runtime: {} };
Object.defineProperty(navigator, 'languages', {get: () => ['lv-LV', 'lv', 'en-US', 'en']});
Object.defineProperty(navigator, 'plugins', {get: () => [1, 2, 3]});
"""


def browser_available() -> bool:
    if os.environ.get("LAPTOP_WATCHER_DISABLE_BROWSER", "").strip() in {"1", "true", "yes"}:
        return False
    try:
        import playwright  # noqa: F401
    except ImportError:
        return False
    return True


@contextmanager
def browser_page(wait_ms_default: int = 3500):
    """Yield a Playwright page with stealth-ish settings."""
    if not browser_available():
        yield None
        return

    from playwright.sync_api import sync_playwright

    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(
            headless=True,
            args=[
                "--disable-blink-features=AutomationControlled",
                "--no-sandbox",
                "--disable-dev-shm-usage",
            ],
        )
        context = browser.new_context(
            locale="lv-LV",
            user_agent=(
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
            ),
            viewport={"width": 1365, "height": 900},
            extra_http_headers={
                "Accept-Language": "lv-LV,lv;q=0.9,ru;q=0.8,en;q=0.7",
            },
        )
        context.add_init_script(STEALTH_INIT)
        page = context.new_page()
        page.set_default_timeout(60000)
        try:
            yield page
        finally:
            browser.close()


def fetch_page_browser(url: str, wait_ms: int = 4000) -> tuple[int, str] | None:
    """Fetch HTML via headless Chromium. Returns None if blocked/unavailable."""
    try:
        with browser_page() as page:
            if page is None:
                return None
            response = page.goto(url, wait_until="domcontentloaded", timeout=60000)
            status = response.status if response is not None else 0
            page.wait_for_timeout(wait_ms)
            html = page.content()
    except Exception as exc:  # noqa: BLE001
        print(f"  ⚠ browser fetch failed: {exc}")
        return None

    if is_blocked(html, status or 403):
        return None
    if status and status >= 400:
        return None
    return status or 200, html


def fetch_pages_browser(urls: list[str], wait_ms: int = 3000) -> dict[str, str]:
    """Fetch several pages in one browser session (keeps cookies)."""
    if not browser_available() or not urls:
        return {}

    results: dict[str, str] = {}
    try:
        with browser_page() as page:
            if page is None:
                return {}
            for url in urls:
                try:
                    response = page.goto(url, wait_until="domcontentloaded", timeout=60000)
                    status = response.status if response is not None else 0
                    page.wait_for_timeout(wait_ms)
                    html = page.content()
                    if response is not None and not is_blocked(html, status) and status < 400:
                        results[url] = html
                except Exception as exc:  # noqa: BLE001
                    print(f"  ⚠ browser: {url}: {exc}")
    except Exception as exc:  # noqa: BLE001
        print(f"  ⚠ browser session failed: {exc}")
        return results

    return results
