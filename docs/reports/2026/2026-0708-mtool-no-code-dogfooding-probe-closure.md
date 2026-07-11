# 2026-07-08 Mtool no-code dogfooding probe closure

Status: `DONE`

## Summary

#435 closes the first Mtool no-code dogfooding probe.

The first probe is intentionally small: represent Mtool's Source Output review surface as a no-code screen-definition / runtime artifact shape, then classify what standard generation covers and where customization should enter later.

## Accepted Findings

| Area | Finding | Next treatment |
| --- | --- | --- |
| Standard generated UI | Good enough for the first Source Output review list/detail/form shape and artifact proof | Keep as the default base |
| Configured presentation | Review surfaces need density, grouping, and review-list defaults more than arbitrary custom code | Add as metadata-driven `view_profile` / presentation settings later |
| Custom UI slots | Related settings, artifact/revision status cards, and contextual action panels do not belong as hand edits in generated HTML | Define declared slots in screen-definition / manifest later |
| Custom operations | Build, publish, review request, approval, rollback, and other side-effecting workflows should remain operation-bound | Route through Managed Operation / Custom Proxy / existing operator routes |
| Full custom app handoff | Useful for rich bespoke workflows, but too broad for the first Mtool probe | Keep as later bridge / external app path |

## What The Probe Proved

- A Mtool-owned review surface can be described by no-code metadata without replacing the Mtool admin UI.
- The same no-code runtime artifact shape can carry the Mtool probe.
- React composition is a good implementation model for custom UI, but the durable source of truth should stay in no-code metadata / manifests.
- Customization needs to be explicit and traceable; generated artifact hand edits should not become the main path.

## Remaining Candidates

The next implementation lane should choose one of these:

1. Configured presentation metadata for review surfaces.
   - First candidate: `view_profile` or a small extension of `view_variant_preference`.
   - Useful for density, grouping, visible field set, and review-list defaults.

2. Custom UI slot manifest first slice.
   - First candidate slots: `related_settings_panel`, `artifact_status_panel`, `operator_actions_panel`.
   - React bridge can map these to component props; HTML runtime can render placeholders or links.

3. Mtool dogfooding operator inspection.
   - Generate or materialize the probe artifact in a local work directory and inspect the actual HTML/JSON output.
   - Record concrete UI findings before adding new slot metadata.

## Verification Baseline

Latest code verification remains #434:

- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- focused PHPUnit: `OK (7 tests, 69 assertions)`
- `git diff --check`
- full `make test`: `344 tests`, `11221 assertions`, `Skipped: 1`

This closure is docs-only; no additional test run is required beyond `git diff --check`.

## Recommended Next Step

Before a push, do a local commit stack review for #432-#435. The commits are already meaningful, but the review should confirm whether to keep four commits or squash the docs-only boundary/closure records into nearby implementation commits.
