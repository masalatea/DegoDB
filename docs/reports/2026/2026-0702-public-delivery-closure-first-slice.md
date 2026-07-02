# Public Delivery Closure First Slice / public delivery closure first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

The no-code public runtime delivery lane is complete for the current minimum slice. Approved `NO-CODE-RUNTIME` candidates can be exposed through immutable artifact-key URLs, a no-store `current` URL, an explicitly selected current revision, stable custom alias URLs, and operator/admin alias deletion.

no-code public runtime delivery lane は current minimum slice として完了。approved `NO-CODE-RUNTIME` candidate は immutable artifact-key URL、no-store `current` URL、明示選択された current revision、stable custom alias URL、operator/admin による alias deletion まで扱える。

## Completed Capability / 完了した機能

- Approved candidate package exposure keeps artifact detail/download access guarded by approval state.
- Artifact-key public runtime preview serves approved `runtime-preview.html` with immutable public cache semantics.
- `current` public runtime preview serves the selected current approved candidate with `no-store`.
- Explicit current public revision selection lets operator/admin choose an older approved candidate, which is the first rollback path.
- Custom public alias storage serves approved candidates through `/runs/no-code/{project_key}/alias/{alias_key}/runtime-preview.html`.
- Public alias delete workflow lets operator/admin withdraw stale aliases by removing the alias row.

## Remaining Follow-Up Candidates / 残り候補

- Broader rollback workflow polish: clearer rollback wording/history around current and alias changes.
- Alias lifecycle audit trail: append-only alias create/update/delete events if operational accountability becomes necessary.
- Public delivery hardening: custom domains/CDN/static package copy when deployment requirements are concrete.
- Public delivery browser smoke: end-to-end browser route verification once stable sample fixtures for public URLs are promoted.

## Boundary / 境界

- In scope: docs closure, acceptance boundary, remaining follow-up candidates.
- Out of scope: new code, new routes, custom domain/CDN configuration, package copy/static hosting, push.

## Verification / 検証

- `git diff --check` passed.

## Next / 次

Replan the next no-code product goal. The strongest remaining candidate is broader rollback workflow polish, but it should be chosen explicitly against any higher-priority product lane.
