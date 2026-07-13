# SSO App User Cross-driver Checkpoint

## Qualification result

| Capability | SQLite | MySQL/MariaDB | PostgreSQL |
| --- | --- | --- | --- |
| Canonical key/FK config metadata | Qualified | Qualified | Qualified at config-metadata layer |
| Live key/FK schema import | Not implemented | Qualified | Not implemented |
| General generated DBAccess contract | Qualified | Qualified | Existing general contract evidence |
| Shared transaction wrapper | Qualified PDO | Qualified mysqli/MariaDB | General PDO support exists; SSO path not qualified |
| SSO resolver first/repeat login | Qualified | Not qualified | Not qualified |
| SSO full rollback | Qualified | Not qualified | Not qualified |
| Real SSO Source Output emission | Qualified | Metadata emission is driver-neutral, runtime E2E not qualified | Metadata emission is driver-neutral, runtime E2E not qualified |
| App-local identity handoff | Qualified SQLite cache contract | Client cache contract is server-driver independent; server E2E not qualified | Same boundary; server E2E not qualified |

The supported first generated SSO app-user path is therefore SQLite E2E. MySQL/MariaDB is a strong partial foundation, not a completed SSO claim. PostgreSQL remains explicit follow-up.

## Current evidence

- Full integration suite: 546 tests, 14898 assertions, one existing skip.
- SQLite: JIT creation, repeat-login restore, allowlisted profile update, credential exclusion, full rollback, canonical actor result, real Source Output files/autoload/manifest, disabled regeneration cleanup, App-local save/restore and server revalidation.
- MySQL/MariaDB: ordered live primary/unique/FK import, managed-table filtering, canonical transactional apply, and previously qualified generated DBAccess Transaction Full foundation.
- PostgreSQL: existing general user DB contract infrastructure; no SSO-specific resolver or live constraint reader qualification in this lane.

## Contract rerun note

An additional `make user-db-contract-test` attempt was made during this checkpoint. The first run collided with the user's existing `33062` container port. Temporary environment port overrides were then tried without stopping existing containers, but the sample run script reloaded `.env` and continued to bind the fixed ports (`33062`, then `8081`). No user container was stopped or changed. Because the rerun did not execute its assertions, it is not counted as new evidence.

## Demand-driven follow-up

Do not broaden support merely to make the table symmetrical. Add work only when the corresponding deployment path is needed:

1. MySQL/MariaDB SSO resolver fixture using generated DBAccess and real transaction rollback.
2. PostgreSQL live ordered key/FK reader and SSO resolver fixture.
3. SQLite live constraint reader only when importing an existing SQLite application schema is a real workflow.

## Integration state

The product lane is complete for its declared first supported path. Plan #866 should inspect the semantic commit stack, clean tree, origin divergence, and PR scope before integration into `develop`.

## Stack checkpoint result

After a fresh `git fetch --prune origin`, the feature branch is clean, zero commits behind `origin/develop`, and fifteen commits ahead. The commits correspond to independently reviewable semantic units: policy, bundle support, constraint model/bundle/import, generation gate, resolver contract/execution/staging, qualified Source Output, App-local handoff, and driver checkpoint. They are retained without squash. The branch is ready to push and open a feature-to-`develop` PR when requested.
