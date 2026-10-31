#!/usr/bin/env python3
"""Ad-hoc W1 smoke: locations + QR scan resolve (not full suite)."""
from __future__ import annotations

import re
import sys
from pathlib import Path

try:
    import requests
except ImportError:
    import subprocess
    subprocess.check_call([sys.executable, "-m", "pip", "install", "requests", "-q"])
    import requests

import os
BASE = os.environ.get("WMS_BASE_URL", f"http://127.0.0.1:{os.environ.get('APP_PORT', os.environ.get('PORT', '8100'))}")
EMAIL = "superadmin@fazarth.my.id"
PASSWORD = "superadmin123!"
OUT = Path(__file__).with_name("smoke_w1_result.json")

results = []


def ok(name: str, cond: bool, detail: str = ""):
    results.append({"name": name, "ok": bool(cond), "detail": detail})
    mark = "PASS" if cond else "FAIL"
    print(f"[{mark}] {name}" + (f" — {detail}" if detail else ""))


def main() -> int:
    s = requests.Session()
    s.headers.update({"User-Agent": "hermes-w1-smoke/1.0"})

    # Landing / login page
    r = s.get(f"{BASE}/login", timeout=15)
    ok("login_page", r.status_code == 200, f"http={r.status_code}")
    token = None
    m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
    if m:
        token = m.group(1)
    ok("csrf_token", bool(token), "found" if token else "missing")

    if not token:
        OUT.write_text(__import__("json").dumps({"ok": False, "results": results}, indent=2), encoding="utf-8")
        return 1

    r = s.post(
        f"{BASE}/login",
        data={"_token": token, "email": EMAIL, "password": PASSWORD},
        timeout=15,
        allow_redirects=True,
    )
    logged_in = r.status_code == 200 and ("dashboard" in r.url.lower() or "Logout" in r.text or "Keluar" in r.text or "Scan QR" in r.text or "Zone" in r.text)
    # fallback: any non-login page with session cookie
    if not logged_in:
        logged_in = "login" not in r.url.lower() and r.status_code == 200
    ok("login", logged_in, f"url={r.url} http={r.status_code}")

    # Locations index
    r = s.get(f"{BASE}/locations", timeout=15)
    ok("locations_index", r.status_code == 200 and ("A-01-03" in r.text or "Zone" in r.text or "Lokasi" in r.text), f"http={r.status_code}")

    # Scan page
    r = s.get(f"{BASE}/scan", timeout=15)
    ok("scan_page", r.status_code == 200, f"http={r.status_code}")
    m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
    scan_token = m.group(1) if m else token

    codes = ["LOC-A-01-03", "LOC-A-01-01", "ITEM-SKU-RICE-25", "SUP-001", "WH-MAIN"]
    for code in codes:
        r = s.post(
            f"{BASE}/scan",
            data={"_token": scan_token, "code": code},
            timeout=15,
            allow_redirects=True,
        )
        body = r.text
        # success: either redirect to entity show, or page shows resolved entity / code
        hit = r.status_code == 200 and (
            code in body
            or "Tidak ditemukan" not in body
            and ("Lokasi" in body or "Produk" in body or "Supplier" in body or "Gudang" in body or "entity" in body.lower() or "Buka" in body or "Detail" in body)
        )
        # stricter: fail only if explicit not-found flash/message
        not_found = "tidak ditemukan" in body.lower() or "not found" in body.lower()
        ok(f"scan_{code}", hit and not not_found, f"http={r.status_code} not_found={not_found}")

    # Label page: resolve via scan result link or direct known demo id from locations index links
    r = s.get(f"{BASE}/locations", timeout=15)
    label_ok = False
    m = re.search(r'/locations/(\d+)(?:/label)?"', r.text)
    if not m:
        m = re.search(r'locations/(\d+)', r.text)
    if m:
        loc_id = m.group(1)
        r2 = s.get(f"{BASE}/locations/{loc_id}/label", timeout=15)
        label_ok = r2.status_code == 200 and (
            "LOC-" in r2.text or "Label" in r2.text or "qr" in r2.text.lower() or "A-01" in r2.text
        )
        ok("location_label", label_ok, f"id={loc_id} http={r2.status_code}")
    else:
        # Fallback: open scan result action for LOC-A-01-03 then follow label if present
        r = s.get(f"{BASE}/scan", params={"code": "LOC-A-01-03"}, timeout=15)
        m = re.search(r'/locations/(\d+)/label', r.text) or re.search(r'/locations/(\d+)', r.text)
        if m:
            loc_id = m.group(1)
            r2 = s.get(f"{BASE}/locations/{loc_id}/label", timeout=15)
            label_ok = r2.status_code == 200
            ok("location_label", label_ok, f"via_scan id={loc_id} http={r2.status_code}")
        else:
            ok("location_label", False, "no location id in index/scan html")

    # Unknown code should fail gracefully
    r = s.post(
        f"{BASE}/scan",
        data={"_token": scan_token, "code": "ZZZ-NOPE-999"},
        timeout=15,
        allow_redirects=True,
    )
    soft_fail = r.status_code in (200, 302, 422) and r.status_code != 500
    ok("scan_unknown_soft_fail", soft_fail, f"http={r.status_code}")

    passed = sum(1 for x in results if x["ok"])
    total = len(results)
    summary = {"ok": passed == total, "passed": passed, "total": total, "results": results}
    OUT.write_text(__import__("json").dumps(summary, indent=2), encoding="utf-8")
    print(f"\nRESULT {passed}/{total} ok={summary['ok']}")
    return 0 if summary["ok"] else 1


if __name__ == "__main__":
    raise SystemExit(main())
