# Mtool Source Output Inspection Entry-Point Preflight

## Status

`DONE`

## Decision

Add the first productized entry point as a feature-flagged link from the canonical MTOOL Source Outputs page to the no-code inspection route.

This should be implemented as a small canonical-page affordance, not as a replacement.

## Placement

Place the link inside the existing `No-Code Runtime Inspection` summary card on `project_source_outputs_page.php`.

Reasons:

- the card already explains no-code runtime inspection status;
- it is already on the canonical Source Outputs page;
- it avoids global navigation changes;
- it keeps the generated inspection route visibly subordinate to the canonical page.

## Gate

Use the existing `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED` behavior.

The link must be:

- hidden by default;
- visible only when the feature flag is enabled;
- visible only for project key `MTOOL`;
- admin-route only through the existing canonical Source Outputs page;
- a GET link only.

## Link target

Target:

```text
/projects/MTOOL/source-outputs/no-code-inspection
```

No selector is required for the first link. The inspection route already defaults to the first row when no selector is provided, and exact selector behavior is covered separately.

## Copy

The copy should make the boundary obvious:

- "Open read-only no-code inspection"
- "Generated inspection is read-only and does not replace canonical Source Outputs."

## Required tests

#802 should add focused fast coverage that:

- default-off HTML does not contain the link;
- enabled MTOOL HTML contains the link and stable marker;
- non-MTOOL HTML does not contain the link even if the flag is enabled;
- existing create/artifact POST forms remain unchanged;
- no generated execution binding is introduced.

Browser evidence is not required for the first link implementation if fast HTML coverage is precise; browser evidence can be added afterward if the visible affordance needs end-to-end confirmation.

## Non-goals

#802 must not add:

- new route;
- mutation;
- Source Output create/edit/delete/reorder/build/publish replacement;
- review request persistence;
- public/lab/current/alias exposure;
- broad navigation redesign.

Rollback remains unsetting `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED`.
