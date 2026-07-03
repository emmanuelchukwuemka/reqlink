#!/usr/bin/env python3
"""
ResQLink cPanel Deployment Script
Usage: python deploy.py
Uploads all files changed since the last deploy to the live server via cPanel UAPI.
"""

import os
import json
import subprocess
import urllib.request
import urllib.parse
import ssl
import base64
import sys
from pathlib import Path

# ── CONFIG ──────────────────────────────────────────────────────────────────
CPANEL_HOST     = "163.61.188.9"
CPANEL_PORT     = 2083
CPANEL_USER     = "resqlink"
CPANEL_PASS     = ""            # Set via env: CPANEL_PASS=xxx python deploy.py
                                # or fill in here (not recommended for git commits)
SERVER_APP_ROOT = "/home/resqlink/resqlink_app"  # Laravel root on server
LOCAL_ROOT      = Path(__file__).parent           # This project's local root

# File to track last deployed commit
DEPLOY_MARKER   = LOCAL_ROOT / ".last_deploy"
# ─────────────────────────────────────────────────────────────────────────────


def get_password():
    pwd = CPANEL_PASS or os.environ.get("CPANEL_PASS", "")
    if not pwd:
        import getpass
        pwd = getpass.getpass("cPanel password: ")
    return pwd


def cpanel_login(password):
    """Log in to cPanel, return security_token string."""
    url = f"https://{CPANEL_HOST}:{CPANEL_PORT}/login?login_only=1"
    body = urllib.parse.urlencode({"user": CPANEL_USER, "pass": password}).encode()
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    req = urllib.request.Request(url, data=body, method="POST")
    req.add_header("Content-Type", "application/x-www-form-urlencoded")
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        data = json.loads(resp.read())
    if not data.get("security_token"):
        print(f"  Login failed: {data.get('message', 'unknown error')}")
        sys.exit(1)
    return data["security_token"]


def cpanel_uapi(token, module, func, params=None, password=None):
    """Call cPanel UAPI and return the data field."""
    base = f"https://{CPANEL_HOST}:{CPANEL_PORT}/{token}/execute/{module}/{func}"
    if params:
        base += "?" + urllib.parse.urlencode(params)
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    auth = base64.b64encode(f"{CPANEL_USER}:{password}".encode()).decode()
    req = urllib.request.Request(base)
    req.add_header("Authorization", f"Basic {auth}")
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        return json.loads(resp.read())


def upload_file(token, password, local_path, remote_path):
    """Upload a single file via cPanel Fileman save_file_content."""
    try:
        content = local_path.read_text(encoding="utf-8")
    except UnicodeDecodeError:
        content = local_path.read_bytes().decode("latin-1")

    # Use posixpath (not Path) so Linux remote paths keep forward slashes on Windows
    import posixpath
    remote_dir  = posixpath.dirname(remote_path)
    remote_name = posixpath.basename(remote_path)

    url  = f"https://{CPANEL_HOST}:{CPANEL_PORT}/{token}/execute/Fileman/save_file_content"
    body = urllib.parse.urlencode({
        "dir":     remote_dir,
        "file":    remote_name,
        "content": content,
    }).encode()

    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    auth = base64.b64encode(f"{CPANEL_USER}:{password}".encode()).decode()
    req = urllib.request.Request(url, data=body, method="POST")
    req.add_header("Authorization", f"Basic {auth}")
    req.add_header("Content-Type", "application/x-www-form-urlencoded")

    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        result = json.loads(resp.read())

    return result.get("status") == 1


def clear_route_cache(token, password):
    """Delete bootstrap/cache/routes-*.php so stale cached routes never shadow web.php.

    Laravel route caching writes bootstrap/cache/routes-v7.php and, if present, it takes
    priority over routes/web.php for named-route lookups -- a deploy that adds/renames a
    route but doesn't bust this cache causes RouteNotFoundException (500) on any view
    using route(). exec() is disabled on this host so `artisan route:clear` isn't an
    option; UAPI's Fileman/delete_files doesn't exist on this account's cPanel version,
    so we fall back to the legacy API2 Fileman/fileop op=unlink, which does.
    """
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    auth = base64.b64encode(f"{CPANEL_USER}:{password}".encode()).decode()

    for name in ("routes-v7.php", "routes.php"):
        remote = f"{SERVER_APP_ROOT}/bootstrap/cache/{name}"
        url = f"https://{CPANEL_HOST}:{CPANEL_PORT}/{token}/json-api/cpanel"
        body = urllib.parse.urlencode({
            "cpanel_jsonapi_apiversion": "2",
            "cpanel_jsonapi_module":     "Fileman",
            "cpanel_jsonapi_func":       "fileop",
            "op":                        "unlink",
            "sourcefiles":               remote,
            "doubledecode":              "0",
        }).encode()
        req = urllib.request.Request(url, data=body, method="POST")
        req.add_header("Authorization", f"Basic {auth}")
        req.add_header("Content-Type", "application/x-www-form-urlencoded")
        try:
            with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
                json.loads(resp.read())  # file may not exist; errors here are fine to ignore
        except urllib.error.HTTPError:
            pass


def clear_blade_cache(token, password):
    """Wipe Laravel's compiled Blade view cache so updated templates take effect immediately.

    Uploads a one-shot PHP script to public_html, fires it over HTTPS to delete all
    *.php files in storage/framework/views/, then removes the script. exec() is disabled
    on this host so we can't run `artisan view:clear` — this achieves the same result.
    """
    import uuid as _uuid
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    auth = base64.b64encode(f"{CPANEL_USER}:{password}".encode()).decode()

    script_name = f"_vc_{_uuid.uuid4().hex[:10]}.php"
    web_root    = "/home/resqlink/public_html"
    remote_path = f"{web_root}/{script_name}"

    php = (
        "<?php "
        "$d = dirname(__DIR__) . '/resqlink_app/storage/framework/views/';"
        "$files = glob($d . '*.php') ?: [];"
        "array_map('unlink', $files);"
        "echo 'OK:' . count($files);"
        " ?>"
    )

    # Upload the script
    url  = f"https://{CPANEL_HOST}:{CPANEL_PORT}/{token}/execute/Fileman/save_file_content"
    body = urllib.parse.urlencode({
        "dir":     web_root,
        "file":    script_name,
        "content": php,
    }).encode()
    req = urllib.request.Request(url, data=body, method="POST")
    req.add_header("Authorization", f"Basic {auth}")
    req.add_header("Content-Type", "application/x-www-form-urlencoded")
    try:
        with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
            json.loads(resp.read())
    except Exception:
        return

    # Execute it
    try:
        run_req = urllib.request.Request(f"https://resqlink.org.ng/{script_name}")
        with urllib.request.urlopen(run_req, context=ctx, timeout=15) as resp:
            result = resp.read().decode().strip()
            print(f"      Blade cache: {result} compiled view(s) removed")
    except Exception:
        pass

    # Delete the script
    del_url  = f"https://{CPANEL_HOST}:{CPANEL_PORT}/{token}/json-api/cpanel"
    del_body = urllib.parse.urlencode({
        "cpanel_jsonapi_apiversion": "2",
        "cpanel_jsonapi_module":     "Fileman",
        "cpanel_jsonapi_func":       "fileop",
        "op":                        "unlink",
        "sourcefiles":               remote_path,
        "doubledecode":              "0",
    }).encode()
    del_req = urllib.request.Request(del_url, data=del_body, method="POST")
    del_req.add_header("Authorization", f"Basic {auth}")
    del_req.add_header("Content-Type", "application/x-www-form-urlencoded")
    try:
        with urllib.request.urlopen(del_req, context=ctx, timeout=30) as resp:
            json.loads(resp.read())
    except Exception:
        pass


def get_changed_files():
    """Return list of changed file paths relative to project root since last deploy."""
    if DEPLOY_MARKER.exists():
        last_commit = DEPLOY_MARKER.read_text().strip()
        cmd = ["git", "diff", "--name-only", last_commit, "HEAD"]
    else:
        # First deploy: upload all tracked files
        print("  No previous deploy marker found. Uploading all tracked files.")
        cmd = ["git", "ls-files"]

    result = subprocess.run(cmd, capture_output=True, text=True, cwd=LOCAL_ROOT)
    files = [f.strip() for f in result.stdout.splitlines() if f.strip()]

    # Filter to only files that exist locally (skip deleted files)
    return [f for f in files if (LOCAL_ROOT / f).is_file()]


def get_current_commit():
    result = subprocess.run(["git", "rev-parse", "HEAD"], capture_output=True, text=True, cwd=LOCAL_ROOT)
    return result.stdout.strip()


# Files/dirs to never upload
SKIP_PATTERNS = [
    "node_modules/", ".git/", "vendor/", "storage/logs/",
    "storage/framework/cache/", "storage/framework/sessions/",
    ".env", "deploy.py", ".last_deploy",
]


def should_skip(filepath):
    for pat in SKIP_PATTERNS:
        if filepath.startswith(pat) or pat in filepath:
            return True
    return False


def main():
    print("╔═══════════════════════════════════════╗")
    print("║   ResQLink → cPanel Deployment         ║")
    print("╚═══════════════════════════════════════╝")

    password = get_password()
    print(f"\n[1/4] Authenticating with cPanel ({CPANEL_HOST})…")
    token = cpanel_login(password)
    print(f"      ✓ Logged in  (token: {token[:12]}…)")

    print("\n[2/4] Detecting changed files…")
    changed = get_changed_files()
    changed = [f for f in changed if not should_skip(f)]

    if not changed:
        print("      No files to deploy. Already up to date.")
        return

    print(f"      {len(changed)} file(s) to upload:")
    for f in changed:
        print(f"        · {f}")

    print(f"\n[3/4] Uploading to {SERVER_APP_ROOT} …")
    ok = 0
    fail = 0
    for rel_path in changed:
        local  = LOCAL_ROOT / rel_path
        remote = f"{SERVER_APP_ROOT}/{rel_path}"
        success = upload_file(token, password, local, remote)
        icon = "✓" if success else "✗"
        print(f"      {icon} {rel_path}")
        if success:
            ok += 1
        else:
            fail += 1

    extra_steps = sum([
        any(f.startswith("routes/") for f in changed),
        any(f.endswith(".blade.php") for f in changed),
    ])
    total = 3 + extra_steps + 1
    step_n = 4

    if any(f.startswith("routes/") for f in changed):
        print(f"\n[{step_n}/{total}] Clearing stale route cache (routes/ changed)…")
        clear_route_cache(token, password)
        print(f"      ✓ Cleared")
        step_n += 1

    if any(f.endswith(".blade.php") for f in changed):
        print(f"\n[{step_n}/{total}] Clearing Blade view cache (blade files changed)…")
        clear_blade_cache(token, password)
        step_n += 1

    print(f"\n[{step_n}/{total}] Finalizing…")
    current_commit = get_current_commit()
    DEPLOY_MARKER.write_text(current_commit)
    print(f"      Saved deploy marker: {current_commit[:10]}")

    print(f"\n{'─'*42}")
    print(f"  ✓ Uploaded: {ok}   ✗ Failed: {fail}")
    print(f"  Site: http://resqlink.org.ng/")
    print(f"{'─'*42}")

    if fail > 0:
        sys.exit(1)


if __name__ == "__main__":
    main()
