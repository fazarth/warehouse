#!/usr/bin/env python3
"""Ad-hoc W2 smoke: PO → GR → QC → Put Away (not full suite)."""
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


def options_in_select(html: str, name: str) -> list[tuple[str, str]]:
    m = re.search(rf'<select[^>]*name="{re.escape(name)}"[^>]*>([\s\S]*?)</select>', html)
    if not m:
        return []
    return re.findall(r'<option value="(\d+)"[^>]*>([^<]+)</option>', m.group(1))


def main() -> int:
    s = requests.Session()
    s.headers.update({"User-Agent": "hermes-w2-smoke"})

    r = s.get(f"{BASE}/login", timeout=15)
    ok("login page", r.status_code == 200, f"http={r.status_code}")
    token = csrf(r.text)
    ok("login csrf", bool(token))
    r = s.post(
        f"{BASE}/login",
        data={"_token": token, "email": EMAIL, "password": PASSWORD},
        timeout=15,
        allow_redirects=True,
    )
    ok("login post", "dashboard" in r.url or r.status_code == 200, f"url={r.url}")

    for path, name in [
        ("/purchase-orders", "po index"),
        ("/purchase-orders/create", "po create"),
        ("/putaways", "putaway index"),
        ("/goods-receipts", "gr index"),
        ("/goods-receipts/create", "gr create"),
    ]:
        rr = s.get(f"{BASE}{path}", timeout=15)
        ok(name, rr.status_code == 200, f"http={rr.status_code}")

    r = s.get(f"{BASE}/purchase-orders/create", timeout=15)
    token = csrf(r.text)
    suppliers = options_in_select(r.text, "supplier_id")
    # Alpine product select uses :name; options still present as static HTML
    products = re.findall(
        r'<option value="(\d+)">((?:SKU|W-)[^<]+)</option>',
        r.text,
    )
    ok("po form options", bool(suppliers) and bool(products), f"sup={len(suppliers)} prod={len(products)}")
    if not suppliers or not products:
        return 1

    supplier_id, _ = suppliers[0]
    product_id, _ = products[0]

    r = s.post(
        f"{BASE}/purchase-orders",
        data={
            "_token": token,
            "supplier_id": supplier_id,
            "order_date": "2026-07-19",
            "expected_date": "2026-07-25",
            "notes": "W2 smoke",
            "items[0][product_id]": product_id,
            "items[0][quantity_ordered]": "10",
            "items[0][unit_price]": "1000",
        },
        timeout=20,
        allow_redirects=True,
    )
    m = re.search(r"purchase-orders/(\d+)", r.url)
    po_id = m.group(1) if m else None
    ok("po store", bool(po_id) and r.status_code == 200, f"url={r.url}")
    ok("po id", bool(po_id), f"id={po_id}")
    po_number = re.search(r"(PO-\d{8}\d+)", r.text)
    ok("po number shown", bool(po_number), po_number.group(1) if po_number else "")
    if not po_id:
        return 1

    # GR from PO
    r = s.get(f"{BASE}/goods-receipts/create?purchase_order_id={po_id}", timeout=15)
    ok("gr create from po", r.status_code == 200 and (po_id in r.text or (po_number and po_number.group(1) in r.text)), f"http={r.status_code}")
    token = csrf(r.text)
    # warehouse options (selected WH-MAIN preferred)
    wh = re.findall(r'<option value="(\d+)"[^>]*selected[^>]*>\s*WH-', r.text)
    if not wh:
        wh = re.findall(r'<option value="(\d+)"[^>]*>\s*WH-', r.text)
    ok("warehouse option", bool(wh), f"wh={wh[:3]}")
    warehouse_id = wh[0] if wh else "1"

    poi = re.search(r'"purchase_order_item_id"\s*:\s*(\d+)', r.text)
    if not poi:
        poi = re.search(r"purchase_order_item_id[^\d]*(\d+)", r.text)
    poi_id = poi.group(1) if poi else ""
    ok("poi linked", bool(poi_id), f"poi={poi_id}")

    r = s.post(
        f"{BASE}/goods-receipts",
        data={
            "_token": token,
            "supplier_id": supplier_id,
            "purchase_order_id": po_id,
            "receipt_date": "2026-07-19",
            "document_number": "SJ-W2-SMOKE",
            "notes": "W2 smoke GR",
            "items[0][product_id]": product_id,
            "items[0][warehouse_id]": warehouse_id,
            "items[0][quantity]": "10",
            "items[0][batch_number]": "BATCH-W2",
            "items[0][lot_number]": "LOT-W2",
            "items[0][purchase_order_item_id]": poi_id,
        },
        timeout=20,
        allow_redirects=True,
    )
    m = re.search(r"goods-receipts/(\d+)", r.url)
    gr_id = m.group(1) if m else None
    ok("gr store", bool(gr_id), f"url={r.url}")
    ok("gr id", bool(gr_id), f"id={gr_id}")
    if not gr_id:
        # show flash/errors briefly
        print("DEBUG gr body snippet:", re.sub(r"\s+", " ", r.text)[:400])
        return 1

    r = s.get(f"{BASE}/goods-receipts/{gr_id}", timeout=15)
    token = csrf(r.text)
    item_id_m = re.search(r'name="items\[0\]\[id\]"\s+value="(\d+)"', r.text)
    item_id = item_id_m.group(1) if item_id_m else None
    ok("gr item id", bool(item_id), f"item={item_id}")
    r = s.post(
        f"{BASE}/goods-receipts/{gr_id}/verify",
        data={
            "_token": token,
            "items[0][id]": item_id,
            "items[0][qty_pass]": "10",
            "items[0][qty_fail]": "0",
            "items[0][qc_notes]": "ok",
        },
        timeout=20,
        allow_redirects=True,
    )
    ok(
        "qc verify",
        r.status_code == 200 and ("verified" in r.text.lower() or "putaway" in r.text.lower() or "QC" in r.text),
        f"url={r.url}",
    )
    task_m = re.search(r"putaways/(\d+)", r.text)
    ok("putaway spawned", bool(task_m), f"task={task_m.group(1) if task_m else None}")
    task_id = task_m.group(1) if task_m else None

    if task_id:
        r = s.get(f"{BASE}/putaways/{task_id}", timeout=15)
        ok("putaway show", r.status_code == 200, f"http={r.status_code}")
        token = csrf(r.text)
        # multiline options: <option value="3" >\n  A-01-01 — ...
        bins = re.findall(
            r'<option value="(\d+)"[^>]*>\s*([A-Z0-9][A-Z0-9\-]+)',
            r.text,
        )
        selected = re.search(
            r'<option value="(\d+)"[^>]*selected[^>]*>\s*([A-Z0-9][A-Z0-9\-]+)',
            r.text,
        )
        bin_id = selected.group(1) if selected else (bins[0][0] if bins else None)
        ok("bin option", bool(bin_id), f"bin={bin_id} opts={len(bins)} selected={bool(selected)}")
        r = s.post(
            f"{BASE}/putaways/{task_id}/complete",
            data={
                "_token": token,
                "location_id": bin_id,
                "quantity": "10",
                "notes": "W2 smoke putaway",
            },
            timeout=20,
            allow_redirects=True,
        )
        # success redirects to index with flash; avoid false positive on form page
        complete_ok = (
            r.status_code == 200
            and (
                "selesai" in r.text.lower()
                or re.search(r"putaways(?:\?|$)", r.url) is not None
                and f"putaways/{task_id}" not in r.url
            )
        )
        ok("putaway complete", complete_ok, f"url={r.url}")
        r = s.get(f"{BASE}/putaways/{task_id}", timeout=15)
        done_ok = r.status_code == 200 and (
            "selesai" in r.text.lower()
            or "Task selesai" in r.text
            or 'status">done' in r.text.lower()
            or ">done<" in r.text.lower()
            or "status</span><span>done" in r.text.lower()
            or re.search(r">\s*done\s*<", r.text, re.I) is not None
        )
        # also accept absence of complete form
        if not done_ok and r.status_code == 200 and 'name="location_id"' not in r.text:
            done_ok = True
        ok("putaway done page", done_ok, "status check")
    else:
        ok("putaway complete", False, "no task id")
        ok("putaway done page", False, "skipped")

    passed = sum(1 for _, c, _ in results if c)
    total = len(results)
    print(f"\nSUMMARY: {passed}/{total} PASS")
    Path(__file__).with_name("smoke_w2_result.txt").write_text(
        f"{passed}/{total}\n" + "\n".join(f"{'PASS' if c else 'FAIL'} {n} {d}" for n, c, d in results),
        encoding="utf-8",
    )
    return 0 if passed == total else 1


if __name__ == "__main__":
    sys.exit(main())
