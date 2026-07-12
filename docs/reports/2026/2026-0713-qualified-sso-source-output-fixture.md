# Qualified SSO Source Output Fixture

## Outcome

Plan #863E closes the first generated server runtime slice. A SQLite config project with normal table, DataClass, DBAccess, key/FK, and enabled SSO policy metadata now passes through the real runtime preparation path and emits the gated resolver file set.

The qualified path proves:

- resolver contract and executable support files exist under `_support/sso-app-user/`;
- the rewritten runtime autoload includes resolver support;
- the runtime generation manifest records `status=emitted`, exact files, and `emitted=true`;
- disabling the policy and regenerating returns `status=disabled` and removes the prior resolver files because staging is rebuilt cleanly.

## Corrections found by the fixture

The first runs intentionally retained two integration findings:

1. the fixture initially wrote SQLite's `IsNull` identifier without dialect quoting;
2. canonical schema validation recognized physical DBAccess names but not the normal generated PascalCase source names;
3. the schema gate required separate profile insert/update actions while the selected runtime contract correctly uses one `UpsertAppUserProfile` operation.

The fixture now uses dialect quoting, schema validation resolves generated-name aliases consistently with the exact operation gate, and profile readiness matches the upsert contract.

## Evidence

The final full integration suite passed with 544 tests and 14883 assertions; one existing test is skipped. Together with #863C, this proves generated-compatible DBAccess execution for first login, repeat login, credential exclusion, full rollback, canonical actor output, and real Source Output artifact placement for the SQLite-qualified path.

## Next boundary

Plan #864 carries the server-resolved `app_user_id` into the safe App-local/sync identity contract while keeping server revalidation authoritative. Cross-driver qualification remains #865.
