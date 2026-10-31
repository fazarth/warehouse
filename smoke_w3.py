#!/usr/bin/env python3
"""Ad-hoc W3 smoke: GI → FEFO allocate → pick → pack → ship → deliver."""
from __future__ import annotations

import re
import sys
from pathlib import Path

try:
    import requests
except ImportError:
    print("FAIL: requests missing")
    sys.exit(2)

import os
BASE = os.environ.get("WMS_BASE_URL", f"http://127.0.0.1:{os.environ.get('APP_PORT', os.environ.get('PORT', '8100'))}")
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
    s.headers.update({"User-Agent": "hermes-w3-smoke"})

    r = s.get(f"{BASE}/login", timeout=15)
    ok("login page", r.status_code == 200)
    token = csrf(r.text)
    r = s.post(
        f"{BASE}/login",
        data={"_token": token, "email": EMAIL, "password": PASSWORD},
        timeout=15,
        allow_redirects=True,
    )
    ok("login post", "dashboard" in r.url, f"url={r.url}")

    for path, name in [
        ("/goods-issues", "gi index"),
        ("/goods-issues/create", "gi create"),
    ]:
        rr = s.get(f"{BASE}{path}", timeout=15)
        ok(name, rr.status_code == 200, f"http={rr.status_code}")

    r = s.get(f"{BASE}/goods-issues/create", timeout=15)
    token = csrf(r.text)
    # Prefer SKU-WATER-600 (id often 7) — has multi-batch for FEFO
    products = re.findall(r'<option value="(\d+)">((?:SKU|W-)[^<]+)</option>', r.text)
    water = next((pid for pid, lab in products if "WATER" in lab or "SKU-WATER" in lab), None)
    product_id = water or (products[0][0] if products else None)
    wh = re.findall(r'<option value="(\d+)"[^>]*selected[^>]*>\s*WH-', r.text)
    if not wh:
        wh = re.findall(r'<option value="(\d+)"[^>]*>\s*WH-MAIN', r.text)
    warehouse_id = wh[0] if wh else "1"
    ok("form product/wh", bool(product_id) and bool(warehouse_id), f"p={product_id} wh={warehouse_id}")

    r = s.post(
        f"{BASE}/goods-issues",
        data={
            "_token": token,
            "destination": "Toko W3 Smoke",
            "issue_date": "2026-07-19",
            "delivery_note": "SJ-W3",
            "notes": "W3 FEFO smoke",
            "items[0][product_id]": product_id,
            "items[0][warehouse_id]": warehouse_id,
            "items[0][quantity]": "5",
            "items[0][batch_number]": "",
        },
        timeout=20,
        allow_redirects=True,
    )
    m = re.search(r"goods-issues/(\d+)", r.url)
    gi_id = m.group(1) if m else None
    ok("gi store", bool(gi_id), f"url={r.url}")
    if not gi_id:
        return 1

    r = s.get(f"{BASE}/goods-issues/{gi_id}", timeout=15)
    ok("gi show pending", r.status_code == 200 and "pending" in r.text.lower(), f"http={r.status_code}")
    token = csrf(r.text)

    r = s.post(
        f"{BASE}/goods-issues/{gi_id}/allocate",
        data={"_token": token},
        timeout=20,
        allow_redirects=True,
    )
    ok(
        "allocate fefo",
        r.status_code == 200 and ("pick" in r.text.lower() or "picking" in r.text.lower()),
        f"url={r.url}",
    )
    ok("pick lines shown", "Pick Lines" in r.text or "pick line" in r.text.lower() or re.search(r"bin|A-0|B-0|E-0", r.text, re.I) is not None)

    token = csrf(r.text)
    r = s.post(
        f"{BASE}/goods-issues/{gi_id}/pick",
        data={"_token": token},
        timeout=20,
        allow_redirects=True,
    )
    ok("complete pick", r.status_code == 200 and "packing" in r.text.lower(), f"url={r.url}")

    token = csrf(r.text)
    r = s.post(
        f"{BASE}/goods-issues/{gi_id}/pack",
        data={"_token": token, "notes": "carton-w3"},
        timeout=20,
        allow_redirects=True,
    )
    ok(
        "pack",
        r.status_code == 200 and ("packed" in r.text.lower() or "PKG-" in r.text),
        f"url={r.url}",
    )
    ok("package number", bool(re.search(r"PKG-\d+", r.text)))

    token = csrf(r.text)
    r = s.post(
        f"{BASE}/goods-issues/{gi_id}/ship",
        data={
            "_token": token,
            "carrier": "JNE",
            "tracking_number": "W3TRACK001",
        },
        timeout=20,
        allow_redirects=True,
    )
    ok(
        "ship",
        r.status_code == 200 and "shipped" in r.text.lower(),
        f"url={r.url}",
    )
    ok("tracking shown", "W3TRACK001" in r.text or "JNE" in r.text)

    token = csrf(r.text)
    r = s.post(
        f"{BASE}/goods-issues/{gi_id}/deliver",
        data={"_token": token},
        timeout=20,
        allow_redirects=True,
    )
    ok(
        "deliver",
        r.status_code == 200 and "delivered" in r.text.lower(),
        f"url={r.url}",
    )

    # FEFO evidence: pick lines table still on show
    r = s.get(f"{BASE}/goods-issues/{gi_id}", timeout=15)
    ok("final show", r.status_code == 200 and "delivered" in r.text.lower())
    ok("fefo strategy", "fefo" in r.text.lower())

    passed = sum(1 for _, c, _ in results if c)
    total = len(results)
    print(f"\nSUMMARY: {passed}/{total} PASS")
    Path(__file__).with_name("smoke_w3_result.txt").write_text(
        f"{passed}/{total}\n" + "\n".join(f"{'PASS' if c else 'FAIL'} {n} {d}" for n, c, d in results),
        encoding="utf-8",
    )
    return 0 if passed == total else 1


if __name__ == "__main__":
    sys.exit(main())
