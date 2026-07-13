# Source Output SSO Resolver Integration First Slice

## Outcome

Plan #863D adds the conditional SSO resolver lane to the real runtime staging function. The lane runs after canonical DBAccess/DataClass overlays and before the runtime manifest and autoload file are finalized.

For an enabled policy it reads ordinary project table, DataClass, DBAccess, key/FK, and policy metadata; runs the canonical schema gate; hydrates exact DBAccess functions; and runs the resolver operation gate. Only a fully ready contract writes files beneath `_support/sso-app-user/`.

The emitted set contains the deterministic resolver contract plus isolated executable resolver support and its non-secret normalization dependencies. The generation manifest records status, emitted flag, and exact file paths.

## Compatibility and blocking behavior

- No policy: status `not_configured`, no SSO files.
- Disabled policy: status `disabled`, no SSO files.
- Metadata read failure: warning and no SSO files.
- Schema or exact-operation gate failure: explicit warning and no SSO files.
- Existing non-SSO runtime generation continues unchanged apart from the additive manifest status object.
- Canonical `SELECTSINGLE` and `SELECTLIST` are normalized to the resolver's logical `select` operation family.

## Evidence and honest boundary

The full existing integration suite passed with 542 tests and 14868 assertions; one existing test is skipped. This proves non-SSO compatibility and the previously qualified resolver contract/execution tests.

This slice does not yet claim an enabled, generation-ready project has passed through the complete real Source Output preparation path. Plan #863E adds that qualified fixture and verifies emitted files, rewritten autoload visibility, manifest evidence, and disabled/blocked negative controls before #863 is closed.
