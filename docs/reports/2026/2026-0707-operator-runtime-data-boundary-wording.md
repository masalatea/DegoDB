# Operator Runtime Data Boundary Wording

Date: 2026-07-07

## Summary

#407 adds operator/admin wording for the live runtime-data selection boundary on the `NO-CODE-RUNTIME` Source Output detail page.

The runtime-data product surface now has substantial current/alias behavior. Operators also need the publish/approval page to describe that boundary without requiring them to infer it from the generated preview itself.

## Accepted Capability

- The no-code workflow note says artifact-key preview URLs stay static for immutable artifact inspection.
- The same note says current and alias preview URLs can fetch authenticated read-only live runtime data through `runtime-data.json`.
- The note preserves the mutation boundary: submit/outbox processing remains separate from read-only runtime-data refresh.
- Approved package exposure repeats the static artifact-key vs live current/alias distinction near the artifact/current/alias public runtime links.

## Preserved Boundaries

- No public runtime route behavior changed.
- No `runtime-data.json` endpoint behavior changed.
- No generated runtime UI behavior changed.
- No publish candidate transition behavior changed.
- No alias/current selection mutation behavior changed.

## Verification

- `php -l mtool/app/project_source_output_detail_page.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `git diff --check`
- `make test`

`make test` passed with 339 tests, 11166 assertions, and 1 skipped test. Local `./vendor/bin/phpunit` / `phpunit` were not available in this worktree, so the repo-standard Docker-backed make target was used for the focused code-change verification.

## Remaining Candidates

- Broader operator copy review only if tryout users still confuse artifact-key, current, and alias routes.
- Future runtime-data behavior lanes should stay separate from this wording-only boundary slice.
