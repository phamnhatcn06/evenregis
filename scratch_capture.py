import os
from playwright.sync_api import sync_playwright

def capture_screenshot():
    url = "http://event.mt:8080/"
    dest_dir = r"e:\eventregis\docs\images"
    os.makedirs(dest_dir, exist_ok=True)
    dest_path = os.path.join(dest_dir, "actual_homepage.png")
    
    print(f"Starting browser to capture {url}...")
    with sync_playwright() as p:
        # Launch headless browser
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": 1280, "height": 800})
        
        try:
            # Navigate to the page
            print(f"Navigating to {url}...")
            page.goto(url, timeout=30000, wait_until="load")
            # Wait additional 2 seconds for any animations or dynamic rendering
            page.wait_for_timeout(2000)
            
            # Capture screenshot
            page.screenshot(path=dest_path)
            print(f"Screenshot successfully captured and saved to {dest_path}")
        except Exception as e:
            print(f"An error occurred during capture: {e}")
        finally:
            browser.close()

if __name__ == "__main__":
    capture_screenshot()
