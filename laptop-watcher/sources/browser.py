from __future__ import annotations

import os

from sources.common import is_blocked

# Stores we try with a real browser when plain HTTP is blocked.
BROWSER_STORE_IDS = frozenset({"220", "dateks", "aio"})


def browser_available() -> bool:
    if os.environ.get("LAPTOP_WATCHER_DISABLE_BROWSER", "").strip() in {"1", "true", "yes"}:
        return False
    try:
        import playwright  # noqa: F401
    except ImportError:
        return False
    return True


def fetch_page_browser(url: str, wait_ms: int = 4000) -> tuple[int, str] | None:
    """Fetch HTML via headless Chromium. Returns None if blocked/unavailable."""
    if not browser_available():
        return None

    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        return None

    try:
        with sync_playwright() as playwright:
            browser = playwright.chromium.launch(
                headless=True,
                args=["--disable-blink-features=AutomationControlled"],
            )
            context = browser.new_context(
                locale="lv-LV",
                user_agent=(
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                    "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
                ),
                viewport={"width": 1365, "height": 900},
            )
            page = context.new_page()
            response = page.goto(url, wait_until="domcontentloaded", timeout=60000)
            status = response.status if response is not None else 0
            # Give Cloudflare / SPA a moment to settle.
            page.wait_for_timeout(wait_ms)
            html = page.content()
            browser.close()
    except Exception as exc:  # noqa: BLE001 — scrape resilience
        print(f"  ⚠ browser fetch failed: {exc}")
        return None

    if is_blocked(html, status or 403):
        return None
    if status and status >= 400:
        return None
    return status or 200, html


def fetch_pages_browser(urls: list[str], wait_ms: int = 3500) -> dict[str, str]:
    """Fetch several pages in one browser session."""
    if not browser_available() or not urls:
        return {}

    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        return {}

    results: dict[str, str] = {}
    try:
        with sync_playwright() as playwright:
            browser = playwright.chromium.launch(
                headless=True,
                args=["--disable-blink-features=AutomationControlled"],
            )
            context = browser.new_context(
                locale="lv-LV",
                user_agent=(
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                    "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
                ),
                viewport={"width": 1365, "height": 900},
            )
            page = context.new_page()
            for url in urls:
                try:
                    response = page.goto(url, wait_until="domcontentloaded", timeout=60000)
                    status = response.status if response is not None else 0
                    page.wait_for_timeout(wait_ms)
                    html = page.content()
                    if response is not None and not is_blocked(html, status):
                        results[url] = html
                except Exception as exc:  # noqa: BLE001
                    print(f"  ⚠ browser: {url}: {exc}")
            browser.close()
    except Exception as exc:  # noqa: BLE001
        print(f"  ⚠ browser session failed: {exc}")
        return results

    return results
