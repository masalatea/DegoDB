# Public Delivery Hardening Closure Report / public delivery hardening closure report

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

The no-code public runtime delivery lane is complete for the current minimum product-facing milestone after post-closure hardening. Public delivery now has browser-verified artifact-key, current, and alias preview URLs; immutable/no-store cache policy; explicit current rollback selection; custom alias storage and deletion; rollback wording; and append-only alias lifecycle audit visibility.

no-code public runtime delivery lane は、post-closure hardening 後の current minimum product-facing milestone として完了。public delivery は artifact-key / current / alias preview URL の browser verification、immutable / no-store cache policy、明示的な current rollback selection、custom alias storage / deletion、rollback wording、append-only alias lifecycle audit visibility まで揃った。

## Completed Capability / 完了した機能

- Approved candidate package exposure guards artifact detail/download links by approval state.
- Artifact-key public runtime preview serves approved `runtime-preview.html` with immutable public cache semantics.
- `current` public runtime preview serves the selected approved candidate with `no-store`.
- Explicit current public revision selection supports the first rollback path.
- Custom public aliases serve approved candidates through stable alias URLs.
- Operator/admin alias deletion can withdraw stale aliases.
- Rollback wording explains current selection and alias non-follow behavior.
- Browser smoke verifies artifact-key, current, and alias public preview URLs on sample28.
- Alias lifecycle events record create, update, and delete operations and show recent events in operator/admin UI.

## Remaining Parked Candidates / 残り保留候補

- Custom domain/CDN/static package copy: reopen when deployment requirements are concrete.
- Broader audit search/export: reopen when operational reporting needs exceed the recent-events panel.
- Automatic alias follow-current mode: reopen only if product semantics require aliases to track current rollback.
- New product-facing no-code continuation outside public delivery: replan after the accumulated worktree is organized into reviewable commits.

## Boundary / 境界

- In scope: closure record, accepted capability boundary, remaining parked candidates.
- Out of scope: new code, new routes, deployment infrastructure, commit, push.

## Verification / 検証

No new code was added in this closure report. The immediately preceding implementation slice passed:

- `make sample28-no-code-public-runtime-browser-smoke`
- `git diff --check`
- `make test`
  - `326 tests, 10699 assertions, skipped 1`

## Next / 次

Proceed to commit cleanup / review grouping for the accumulated public delivery and no-code product worktree. Push remains out of scope until explicitly requested.
