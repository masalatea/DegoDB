# 2026-0521 Original Codes Sensitive Literal Scrub

## Summary

- Added host-side sanitizer: `mtool/scripts/sanitize_original_codes_reference.php`
- Ran `php mtool/scripts/sanitize_original_codes_reference.php --write`
- Scrubbed sensitive literals under `original-codes/` without changing runtime / generator inputs

## Scrub Scope

- Real email addresses were replaced with `*.example.invalid`
- Personal identifiers were replaced with stable placeholders
  - legacy person identifier A -> `legacy-user-a`
  - legacy person identifier B -> `legacy-user-b`
  - legacy person identifier C -> `legacy-user-c`
- Fixed legacy password literal was replaced with `legacy-password-redacted`
- Opaque external ids in legacy SQL metadata were redacted
  - `dbid:...` -> `dbid:legacy-account-redacted`
  - `id:...` -> `id:legacy-object-redacted`
  - long single-quoted opaque ids -> `legacy-opaque-id-redacted`
  - legacy `HashKey` row values -> `legacy-hash-redacted`
- User-specific workspace / path suffixes were redacted
  - `legacy-user-a_<opaque>` -> `legacy-user-a_legacy-id-redacted`
  - `legacy-user-b_<opaque>` -> `legacy-user-b_legacy-id-redacted`
  - `legacy-user-c_<opaque>` -> `legacy-user-c_legacy-id-redacted`
- Example API tokens in legacy SQL payload examples were replaced with `legacy-token-redacted`
  - standard JSON / PHP payload examples
  - `__DELIMITER_FOR_CUSTOM_PROXY__` custom proxy examples

## Verification

- `php -l mtool/scripts/sanitize_original_codes_reference.php`
- Re-running `php mtool/scripts/sanitize_original_codes_reference.php` reported `would sanitize 0 file(s)`
- Email scan residuals are placeholder-only (`xxx@xxx.xxx` language-resource messages)
- Personal name scan residuals are `0`
- Non-placeholder `dbid:` / `id:` residuals are `0`
- `legacy-user-[abc]_<opaque>` residuals are `0`
- Non-redacted custom proxy `TOKEN` example residuals are `0`
- Remaining password-like search hits were inspected and were non-secret labels / URLs such as `change_password`

## Notes

- This scrub is intentionally limited to `original-codes/` content only.
- Runtime, Docker, artifact, sample, and current promoted reference inputs were not changed by this work.
- If a future legacy dump is re-imported, rerun the sanitizer before treating `original-codes/` as a reference snapshot.
- The sanitizer's replacement stats can still count already-redacted placeholder literals; use `would sanitize 0 file(s)` as the idempotency signal.
