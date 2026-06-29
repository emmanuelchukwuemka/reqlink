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

    remote_dir  = str(Path(remote_path).parent)
    remote_name = Path(remote_path).name

    url  = f"https://{CPANEL_HOST}:{CPANEL_PORT}/{token}/execute/Fileman/save_file_content"
    body = urllib.parse.urlencode({
        "dir":      remote_dir,
        "filename": remote_name,
        "content":  content,
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

    print(f"\n[4/4] Finalizing…")
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
