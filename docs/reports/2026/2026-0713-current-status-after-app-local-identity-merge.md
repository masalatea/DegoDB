# Current Status After App-local Identity Merge

Status: `RECORDED`

This report records the current status after the App-local sync identity / SSO auto-restore first slice was merged to `develop`.

## Current baseline

The active plan source is `docs/current-plans.md`.

As of this checkpoint:

- AI workspace onboarding is complete and merged.
- App-local sync identity / SSO auto-restore first slice is complete and merged.
- Local `develop` is synchronized with `origin/develop`.
- The feature branch for App-local identity was cleaned after merge.
- The next active plan is #853: choose the next demand-driven product lane.

## Recent outcomes

### 1. AI workspace onboarding

The AI workspace onboarding lane completed the safe, AI-facing workspace setup flow:

- workspace layout contract and profile handling were defined;
- `mtool-project` was selected as the Mtool-owned output area;
- initialization preflight/apply behavior was implemented;
- `mtool/scripts/init_ai_workspace.php` was added;
- an AI/user-facing command guide was added;
- the stack was merged through `develop` and then into `master`.

The accepted boundary is intentionally not a broad adoption workflow. The CLI and guide exist so a user or AI can initialize a safe Mtool workspace when there is a concrete adoption need.

### 2. App-local sync identity / SSO auto-restore

The App-local identity lane completed the first product slice:

- `app-local-user-identity-v0` was introduced.
- SSO/stub principals can be normalized into a safe App-local identity snapshot.
- App-local SQLite can save and restore the identity snapshot.
- Credentials and broad raw claim fields are excluded from persisted snapshots.
- Managed-operation sync intents can carry actor metadata.
- The no-code managed-operation bridge can pass actor metadata to sync intents.
- Sample30 proves SSO-shaped principal normalization, identity save/restore, credential exclusion, sync intent actor propagation, and server handoff visibility.
- OIDC principal mapping now preserves safe `issuer`, `subject`, and `email` fields so it can feed the App-local identity contract without storing tokens.

Verification recorded for the implementation:

- `make sample30-pack-runtime-test`: `OK (1 test, 29 assertions)`
- focused OIDC contract test via sample16 pack: `OK (2 tests, 23 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 514, Assertions: 14596, Skipped: 1.`
- `git diff --check`

## Current active plan

The next active plan is:

`#853 Next demand-driven product lane inventory`

The purpose is to choose the next concrete lane rather than automatically expanding the completed identity slice into a broad rollout.

Candidate lanes:

| Candidate | Why it matters | Current recommendation |
| --- | --- | --- |
| Browser-local IndexedDB proof | Makes the client/server story visible in a browser-local DB instead of only SQLite. | Best next implementation candidate if we want another concrete sample. |
| Promoted outbox actor columns | Makes actor filtering/audit easier without decoding `intent_json`. | Wait until query/audit UX needs it. |
| Real IdP UI wiring | Proves browser login against an IdP end-to-end. | Too broad unless there is a concrete adoption scenario. |
| Another client/server sample | Can prove the same pattern in a different domain. | Useful only if it adds a new sync shape. |
| Park broader rollout | Avoids expanding beyond the supported-contract evidence. | Good default if no concrete adoption need is selected. |

## Recommendation

If continuing implementation immediately, the smallest useful next lane is:

`Browser-local IndexedDB App-local identity proof`

Suggested boundary:

- use a generated or sample runtime page;
- use a deterministic SSO-shaped principal fixture, not a real IdP yet;
- save/restore the same safe identity fields in browser-local storage;
- keep token/secret non-persistence as a hard rule;
- hand off actor metadata to the existing sync intent contract;
- keep the test mostly fast and headless, with a small browser smoke only if needed.

This would extend the already-merged SQLite proof into a more user-visible browser-local proof without taking on real IdP UI, SCIM, membership lifecycle, or broad authorization hardening.

## Non-goals for the next step

- Do not automatically add real IdP administration or lifecycle management.
- Do not store access tokens, refresh tokens, ID tokens, passwords, or client secrets.
- Do not promote outbox actor columns unless there is a query/audit need.
- Do not treat identity sync completion as a reason to broaden every sample or Mtool workflow.
