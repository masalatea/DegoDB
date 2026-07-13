# SSO App User Metadata Bundle Integration

Status: `DONE`

This report completes plan #861.

## Implemented behavior

Project metadata bundles now support an optional `app_user_policy` section stored as `app-user-policy.json`.

- Projects with a stored policy export the normalized non-secret policy.
- Projects without a policy export exactly the existing section/summary shape.
- Import validates the versioned policy before apply.
- Unknown policy fields are rejected instead of silently ignored.
- Credential/secret/raw-claim fields and email-auto-link remain invalid.
- Apply writes the policy inside the existing project-core import transaction.
- A bundle without the optional section preserves an existing target policy.
- A bundle with an enabled policy creates or replaces it.
- A bundle with a disabled policy explicitly disables it without dropping application user data.

## Preview diagnostics

When a source section or target policy exists, import preview reports:

- `create`
- `replace`
- `disable`
- `preserve`

When neither exists, no new policy summary keys are emitted. This preserves strict sample/golden compatibility for projects that have not opted in.

## Evidence

- Project metadata bundle focused class: `5 tests`, `300 assertions`.
- Policy hardening focused test: `3 tests`, `27 assertions`.
- Existing sample15 metadata bundle test: `1 test`, `8 assertions`.
- Existing sample26 capstone test: `1 test`, `11 assertions`.
- `make test`: `528 tests`, `14725 assertions`, `1 skipped`; exit code 0.
- PHP lint and `git diff --check` passed.

## Compatibility finding

The first cross-project test attempt exposed an existing project-bundle target override limitation: importing a source project into a second existing project can conflict on the source slug unique key. That behavior predates this policy section and is not required for policy preserve/replace semantics. The final policy test uses one project's time-ordered exports and does not broaden the unrelated slug migration scope.

## Next boundary

Plan #862 should resolve the enabled policy against ordinary canonical Table/DataClass/DBAccess metadata. It must report missing schema roles and identity invariants without creating hidden tables or modifying the user DB.
