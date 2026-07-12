# Mtool Source Output Browser Evidence Lane Closure

## Status

`DONE`

## Accepted result

#799 is accepted.

The MTOOL Source Output hybrid contract now has repeatable browser evidence:

- default-off route hides the marker;
- enabled route renders exactly one hybrid contract marker;
- enabled marker JSON exposes `no-code-mtool-source-output-inspection-hybrid-v0`;
- no inspection POST is issued;
- no Source Output operation POST is issued;
- rollback restores default-off behavior.

The local stack was restored to default-off after verification.

## Next decision

Promote entry-point preflight next.

The inspection route is now contract-backed and browser-verified, but it remains a hidden URL. The next contained productization step should decide how the canonical Source Outputs page can expose a safe link to the no-code inspection route.

## #801 boundary

#801 should define, before implementation:

- where the link appears on the canonical Source Outputs page;
- whether the same `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED` flag gates the link;
- whether non-MTOOL projects must hide the link;
- exact link target and selector behavior;
- copy that explains this is read-only generated inspection, not replacement;
- tests proving default-off hidden, enabled visible, MTOOL-only, no form/action binding changes;
- rollback by unsetting the feature flag.

## Non-goals

#801 must not add:

- generated mutation;
- Source Output create/edit/delete/reorder/build/publish replacement;
- review request persistence;
- public/lab/current/alias exposure;
- broad navigation redesign.

If entry-point design would require any of those, it should stop and create a separate preflight.
