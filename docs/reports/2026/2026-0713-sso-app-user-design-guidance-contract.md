# SSO App User Design Guidance Contract

Status: `DONE`

Plan #856 turns the permanent SSO application-user design standard into a deterministic, side-effect-free Mtool/AI contract.

## Added contract

`mtool/app/sso_app_user_design_guidance.php` provides:

- `app_sso_app_user_design_guidance()`
  - applies only to `oidc` or `sso` application contexts;
  - returns the standard `(issuer, subject)` and `app_user_id` recommendation;
  - asks only material decisions that remain absent from the supplied context;
  - distinguishes blocking provisioning/tenant choices from non-blocking profile and ownership questions;
  - warns about profile ownership overlap and forbidden credential/raw-claim fields;
  - performs no filesystem or database writes.
- `app_sso_app_user_validate_design()`
  - fails a design that omits issuer/subject or `app_user_id`;
  - rejects mutable profile fields as identity keys;
  - requires transactional JIT and server-side authorization;
  - rejects domain ownership through email and credential/raw-claim persistence;
  - rejects email-based automatic identity linking;
  - records a warning when the custom lifecycle boundary is missing.

The contract version is `sso-app-user-standard-v1`.

## Focused evidence

`tests/Integration/SsoAppUserDesignGuidanceTest.php` covers:

- non-SSO projects are not applicable;
- incomplete SSO context asks only the missing material question;
- a complete standard context is ready without questions;
- overlap and forbidden-field warnings;
- a valid design passes;
- an unsafe email/token/client-authority design fails closed.

## Verification

- PHP lint passed for the contract and test.
- `git diff --check` passed before the full test.
- `make test`: `520 tests`, `14621 assertions`, `1 skipped`; exit code 0.

## Next boundary

Plan #857 should use the standard and guidance result as input to one bounded runtime proof. It should not add a real IdP UI. A deterministic verified principal fixture and SQLite user database are sufficient to prove transactional JIT, repeat-login restore, safe profile refresh, invitation-only denial, and application-owned data keyed by `app_user_id`.
