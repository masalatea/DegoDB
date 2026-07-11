# Post Selected Row/Key Handoff Lane Closure

Status: `DONE`

Plan: #692 post selected row/key handoff lane closure

## Summary

#692 accepts #691 as the selected-row/key fast contract for sample18 generated runtime rows and keyed action intents.

The main fast gaps before browser smoke are now covered: route-compatible action metadata, payload handoff, selected row key identity, disabled/default execution state, route response semantics, executor config metadata, and all-success-or-failure route behavior.

## Accepted From #691

- Runtime render fields preserve `is_key`.
- Static generated runtime HTML exposes row key markers.
- Runtime source can derive selected row identity from render fields when action fields do not carry the key.
- Update/complete action intents put `id` in the key payload.
- Missing keyed action input fails closed before guarded submit normalization.

## Decision

Promote a narrow browser smoke before generated availability expansion.

Reason: fast contracts now cover the shape and handoff. The next useful confidence check is whether the generated sample18 runtime preview behaves correctly in a browser at the UI boundary while mutation remains disabled.

## Next

Promote #693: sample18 generated runtime browser smoke first slice.

That slice should verify row key markers, guarded submit attributes, disabled/default execution state, and blocked feedback without enabling mutation or broad availability.
