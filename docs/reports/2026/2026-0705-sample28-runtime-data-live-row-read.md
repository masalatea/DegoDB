# Sample28 Runtime Data Live Row Read

Status: DONE
Date: 2026-07-05

## Summary

This slice upgrades the #206 runtime data route contract from fail-closed DB connection errors to successful sample28 live row reads in the public web request path.

## Implemented

- Added runtime DB environment capture/restore helpers for public no-code runtime data reads.
- Bound generated DBAccess runtime env (`MTOOL_RUNTIME_DB_*` / SQLite equivalent) from the existing app `config_db` during `runtime-data.json` reads.
- Reset the legacy `$mtooldb` object around temporary runtime env changes so generated DBAccess reconnects with the intended request-local binding.
- Kept the endpoint read-only, `GET` only, auth-required, `no-store`, and current/alias scoped.
- Kept static `runtime-preview.json` out of the fresh-data success path.

## Verified Behavior

`make sample28-no-code-public-runtime-browser-smoke` now proves:

- current `runtime-data.json` returns HTTP 200
- alias `runtime-data.json` returns HTTP 200
- both responses use `contract_version: no-code-runtime-data-v0`
- both responses include three screens
- both responses read seeded row key `1001`
- existing current/alias submit enqueue checks still pass
- existing outbox processing smoke still passes

## Verification

- `php -l mtool/app/no_code_public_runtime_page.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test`: 337 tests, 11079 assertions, skipped 1

## Remaining Next Slice

Promote the same successful runtime-data read smoke to sample29 and sample31, then close the fresh runtime data endpoint first milestone.
