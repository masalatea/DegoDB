# Next L1 Sample Conversion Increment Replan

Status: `DONE`

Plan item: #557 next L1 sample conversion increment replan

## Decision

Continue with `sample18-mini-task-board-demo` before picking a second existing sample.

The next increment should close the smallest remaining fast-contract gap: generated status filter controls for `task_card`. This keeps the work inside metadata / fixture / DOM contract coverage and avoids enabling generated mutation buttons or replacing the curated route too early.

## Options Considered

- Close sample18 filter contracts next.
- Close sample18 action-input mapping next.
- Pick the next existing sample candidate.

## Rationale

Status filtering is already visible in the curated sample route and named in the sample18 conversion checklist. It is a narrower bridge than action-input mapping because it can be proven through JSON and static DOM markers before any JavaScript interaction or server mutation is required.

Action-input mapping remains important, but it should follow after filter/sort style contracts are reusable enough to prevent each converted sample from inventing its own checklist shape.

Picking a second existing sample now would broaden the surface before the first sample's basic list/filter contract is complete.

## Next

#558 should add the sample18 status filter fast contract: fixture expectations, generated metadata or DOM markers as needed, and PHPUnit assertions that compare the generated no-code preview against the curated route boundary.
