# 2026-07-08 Mtool no-code dogfooding metadata lane closure

Status: `DONE`

## Summary

#440 closes the first Mtool no-code dogfooding metadata lane.

This lane deliberately stayed below a full Mtool rewrite. It used Mtool's own Source Output review surface as a small self-dogfooding probe, then added the metadata layers needed to explain how generated no-code UI can sit on top of the existing database/tooling foundation.

## Accepted Capability

- A concrete `MTOOL` Source Output review fixture exists as no-code screen-definition metadata.
- The no-code custom extension boundary is documented as layered:
  - standard generated UI
  - configured presentation
  - custom UI slots
  - custom operations / Custom Proxy endpoints
  - full custom app handoff
- The Mtool probe flows through the normal no-code runtime artifact shape.
- Configured presentation metadata supports compact review density, emphasis, primary/secondary fields, and field groups.
- Custom UI slot metadata supports related settings, artifact status, and operator action slots.
- The inspection summary confirms metadata flows through `screen-definition.json` and `runtime-preview.json`.
- Generated HTML still treats custom slot metadata as embedded preview data; visible slot rendering is intentionally deferred.

## Commit Stack Review

Current unpushed stack from `origin/develop`:

| Commit | Meaning |
| --- | --- |
| `184af06b` | Mtool probe metadata helper |
| `a31e9faf` | Custom extension boundary report |
| `72ddd3e7` | Normal no-code artifact-shape proof |
| `b81b3a5d` | First dogfooding probe closure and stack review |
| `5daf9a27` | Configured presentation metadata |
| `31941a4d` | Custom UI slot metadata |
| `972e9198` | Mtool dogfooding inspection summary |

The stack is readable as-is. No squash or history rewrite is recommended before an explicit push.

## Verification Baseline

Latest code verification remains #439:

- focused PHPUnit: `OK (8 tests, 102 assertions)`
- `git diff --check`
- full `make test`: `345 tests`, `11254 assertions`, `Skipped: 1`

This closure is docs-only; no additional full test run is required beyond `git diff --check`.

## Remaining Candidates

1. Visible custom slot placeholder rendering.
   - First candidate: related settings panel or artifact status panel.
   - Keep operator actions deferred until action semantics are clearer.

2. React bridge slot mapping.
   - Map `extension_slots` into React composition points without making React the source of truth.

3. Persistent/admin edit UI for presentation and slots.
   - Useful after the metadata contract has one visible consumer.

4. Broader Mtool self-replacement inventory.
   - Treat as a later program, not the next automatic step.

## Recommended Next Step

This is a good push checkpoint if the user wants to publish the current metadata lane.

If continuing before push, the smallest implementation candidate is visible placeholder rendering for one safe slot, likely `related_settings_panel` or `artifact_status_panel`.
