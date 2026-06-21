#!/usr/bin/env bash
set -euo pipefail

cat <<'MSG'
Baseline behavior to preserve:

- Public ticket detail for TICKET-1001 includes:
  - ticket_key: TICKET-1001
  - subject: Cannot access billing page
  - customer display name: Example Customer
  - public comment: I cannot access the billing page.
  - public comment: We are checking your account access.

- Public ticket detail must not include:
  - internal comment: Ask billing team to check account status.

This script is currently a documentation baseline.
Replace it with an executable MySQL check when the runnable legacy fixture is added.
MSG
