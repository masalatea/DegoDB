# SSO Schema Constraint Gate Completion

## Outcome

Plan #862E closes the canonical SSO app-user schema constraint gap. The validator now consumes the canonical ordered key/foreign-key snapshot in addition to normal table, DataClass, and DBAccess metadata.

A project reaches `status=generation_ready` and `ready_for_generation=true` only when all earlier schema requirements pass and canonical evidence proves:

- an ordered unique key on `app_user_external_identity(issuer, subject)`;
- `app_user_external_identity.app_user_id` references `app_user.app_user_id`;
- `app_user_profile.app_user_id` references `app_user.app_user_id`.

## Fail-closed behavior

Missing, incorrectly ordered, incorrectly targeted, or PID-unresolvable constraint metadata does not become implicit evidence. Expressible metadata may remain valid, but the validator returns `metadata_valid_constraint_gap`, lists the missing evidence, and keeps generation disabled.

Projects with disabled SSO app-user policy remain `not_applicable`. Existing callers that do not provide a constraint snapshot retain the previous blocked behavior.

## Evidence

The qualified fixture includes normal canonical table/DataClass/DBAccess metadata plus ordered unique and FK metadata and reaches `generation_ready`. The full integration suite passed with 539 tests and 14841 assertions; one existing test is skipped.

## Next boundary

Plan #863 may now generate the first bounded server runtime only after this gate succeeds. It must preserve the existing transactional JIT/restore/rollback semantics and return the server-owned canonical `app_user_id`; client/App-local propagation remains plan #864.
