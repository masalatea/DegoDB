# Mtool entry-point evidence lane closure

Date: 2026-07-12

## Summary

#805 closes the MTOOL Source Output inspection entry-point evidence lane.

The Mtool contained hybrid replacement phase now has one credible partial workflow:

- generated/no-code rendering owns read-only list/detail inspection;
- custom Mtool owns auth, routing, repository reads, canonical CRUD/build/publish pages, feature flag rollout, and any future mutation boundary;
- the route is default-off and rollback is `unset MTOOL_NO_CODE_SELF_INSPECTION_ENABLED`;
- the canonical Source Outputs page exposes the link only when the feature flag is on;
- browser evidence proves default-off, flag-on, and rollback visibility;
- no Source Output inspection or operation POSTs occur in the browser flow;
- the full test suite passed after the final entry-point smoke update.

## Decision

N5 does not require replacing more Mtool screens.

The agreed product philosophy is partial, representative, and hybrid: automate the portion the tool can cover, keep custom code where that is simpler or safer, and do not chase 100% conversion. Therefore, one bounded Mtool workflow with explicit coexistence, rollback, authority, and test evidence satisfies the contained hybrid Mtool replacement exit condition.

## Next phase

Promote N6 / #806: AI material-to-UI replan checkpoint.

The next step is not implementation yet. It should select a bounded material-to-UI investigation by defining:

- source material;
- user Q&A purpose;
- normalized intermediate structure;
- generated UI/action target;
- validation pipeline;
- rollback/no-broad-rollout boundary.
