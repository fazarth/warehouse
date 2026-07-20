#!/usr/bin/env python3
"""Ad-hoc W4–W6 smoke: returns, cycle count, aging, mobile scan, ERP API."""
from __future__ import annotations

import re
import sys
from pathlib import Path

try:
    import requests
except ImportError:
    print("FAIL: requests missing")
    sys.exit(2)

BASE = "http://127.0.0.1:8000"
EMAIL = "superadmin@fazarth.my.id"
PASSWORD = "superadmin123!"
results = []


def ok(name: str, cond: bool, detail: str = ""):
    results.append((name, cond, detail))
    print(f"[{'PASS' if cond else 'FAIL'}] {name}" + (f" — {detail}" if detail else ""))


def csrf(html: str) -> str | None:
    m = re.search(r'name="_token"\s+value="([^"]+)"', html)
    return m.group(1) if m else None


def main() -> int:
    s = requests.Session()
    s.headers.update({"User-Agent": "hermes-w4w6-smoke"})

    r = s.get(f"{BASE}/login", timeout=15)
    ok("login page", r.status_code == 200)
    token = csrf(r.text)
    r = s.post(
        f"{BASE}/login",
        data={"_token": token, "email": EMAIL, "password": PASSWORD},
        timeout=15,
        allow_redirects=True,
    )
    ok("login", "dashboard" in r.url, r.url)

    # pages
    for path, name in [
        ("/returns", "returns index"),
        ("/returns/create", "returns create"),
        ("/cycle-counts", "cycle index"),
        ("/cycle-counts/create", "cycle create"),
        ("/reports/aging", "aging report"),
        ("/m/scan", "mobile scan"),
        ("/m/scan?code=LOC-A-01-01", "mobile scan loc"),
    ]:
        rr = s.get(f"{BASE}{path}", timeout=15)
        ok(name, rr.status_code == 200, f"http={rr.status_code}")

    # --- Return flow ---
    r = s.get(f"{BASE}/returns/create", timeout=15)
    token = csrf(r.text)
    products = re.findall(r'<option value="(\d+)">((?:SKU)[^<]*)</option>', r.text)
    locs = re.findall(r'<option value="(\d+)">([A-Z0-9][A-Z0-9\-]+)</option>', r.text)
    product_id = next((p for p, lab in products if "RICE" in lab or "WATER" in lab or "OIL" in lab), products[0][0] if products else None)
    # pick a bin option (not empty)
    location_id = None
    for lid, code in locs:
        if re.match(r"^[A-Z]-\d", code):
            location_id = lid
            break
    wh = re.findall(r'<option value="(\d+)"[^>]*>\s*WH-MAIN', r.text)
    warehouse_id = wh[0] if wh else "1"
    ok("return form ids", bool(product_id) and bool(location_id), f"p={product_id} loc={location_id} wh={warehouse_id}")

    r = s.post(
        f"{BASE}/returns",
        data={
            "_token": token,
            "source": "customer",
            "warehouse_id": warehouse_id,
            "location_id": location_id,
            "return_date": "2026-07-19",
            "reason": "W4 smoke return",
            "notes": "smoke",
            "items[0][product_id]": product_id,
            "items[0][location_id]": location_id,
            "items[0][batch_number]": "BATCH-RTN",
            "items[0][expired_date]": "2026-12-31",
            "items[0][quantity]": "3",
            "items[0][condition]": "good",
        },
        timeout=20,
        allow_redirects=True,
    )
    m = re.search(r"returns/(\d+)", r.url)
    rtn_id = m.group(1) if m else None
    ok("return store", bool(rtn_id), r.url)
    if rtn_id:
        token = csrf(r.text)
        r = s.post(
            f"{BASE}/returns/{rtn_id}/receive",
            data={"_token": token},
            timeout=20,
            allow_redirects=True,
        )
        ok("return receive", r.status_code == 200 and "received" in r.text.lower(), r.url)

    # --- Cycle count ---
    r = s.get(f"{BASE}/cycle-counts/create", timeout=15)
    token = csrf(r.text)
    r = s.post(
        f"{BASE}/cycle-counts",
        data={
            "_token": token,
            "warehouse_id": warehouse_id,
            "location_id": location_id or "",
            "stockopname_date": "2026-07-19",
            "notes": "W4 cycle smoke",
        },
        timeout=20,
        allow_redirects=True,
    )
    m = re.search(r"cycle-counts/(\d+)", r.url)
    cc_id = m.group(1) if m else None
    ok("cycle store", bool(cc_id), r.url)
    if cc_id:
        ok("cycle lines", "System" in r.text or "physical" in r.text.lower() or "SKU" in r.text)
        token = csrf(r.text)
        # fill first physical if present
        item_ids = re.findall(r'name="items\[(\d+)\]\[id\]"\s+value="(\d+)"', r.text)
        data = {"_token": token, "_method": "PUT"}
        for idx, iid in item_ids[:5]:
            data[f"items[{idx}][id]"] = iid
            data[f"items[{idx}][physical_qty]"] = "1"
        if item_ids:
            r = s.post(f"{BASE}/cycle-counts/{cc_id}", data=data, timeout=20, allow_redirects=True)
            ok("cycle update", r.status_code == 200, r.url)
            token = csrf(r.text)
            r = s.post(
                f"{BASE}/cycle-counts/{cc_id}/submit",
                data={"_token": token},
                timeout=20,
                allow_redirects=True,
            )
            ok("cycle submit", r.status_code == 200 and ("pending" in r.text.lower() or "submitted" in r.text.lower()), r.url)
        else:
            ok("cycle update", True, "no lines (empty bin) — skip")
            ok("cycle submit", True, "skip")

    # --- ERP API ---
    r = requests.post(
        f"{BASE}/api/auth/login",
        json={"email": EMAIL, "password": PASSWORD, "device_name": "smoke"},
        timeout=15,
    )
    ok("api login", r.status_code == 200 and "token" in r.text, f"http={r.status_code}")
    token_api = None
    try:
        token_api = r.json().get("token")
    except Exception:
        pass
    headers = {"Authorization": f"Bearer {token_api}", "Accept": "application/json"} if token_api else {}

    if token_api:
        for path, name in [
            ("/api/me", "api me"),
            ("/api/products", "api products"),
            ("/api/stock", "api stock"),
            ("/api/purchase-orders", "api po"),
            ("/api/goods-issues", "api gi"),
            ("/api/mutations", "api mutations"),
            ("/api/aging", "api aging"),
        ]:
            rr = requests.get(f"{BASE}{path}", headers=headers, timeout=15)
            ok(name, rr.status_code == 200, f"http={rr.status_code}")
        rr = requests.post(
            f"{BASE}/api/scan",
            headers=headers,
            json={"code": "LOC-A-01-01"},
            timeout=15,
        )
        ok("api scan", rr.status_code == 200, f"http={rr.status_code} body={rr.text[:120]}")
    else:
        for name in ["api me", "api products", "api stock", "api po", "api gi", "api mutations", "api aging", "api scan"]:
            ok(name, False, "no token")

    passed = sum(1 for _, c, _ in results if c)
    total = len(results)
    print(f"\nSUMMARY: {passed}/{total} PASS")
    Path(__file__).with_name("smoke_w4w6_result.txt").write_text(
        f"{passed}/{total}\n" + "\n".join(f"{'PASS' if c else 'FAIL'} {n} {d}" for n, c, d in results),
        encoding="utf-8",
    )
    return 0 if passed == total else 1


if __name__ == "__main__":
    sys.exit(main())
