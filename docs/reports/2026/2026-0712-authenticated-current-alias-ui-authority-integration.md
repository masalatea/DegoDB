# Authenticated Current/Alias UI Authority Integration

Status: `DONE`

## Result

Sample18 now proves the complete browser authority handoff for both approved current and alias selectors:

- the browser authenticates before loading the generated preview;
- the stack explicitly enables the server availability overlay, Transaction Full capability gate, Sample18 mutation/executor gates, and the separate generated UI execution gate;
- live authenticated availability enables `create_task_card` only after project policy and artifact identity checks;
- clicking create produces exactly one stubbed guarded POST;
- the UI execution allowlist excludes `complete_task_card`, which produces zero POSTs;
- static artifact previews remain outside this execution-authority path.

The integration uses a stubbed POST intentionally. Real success commit and forced-failure rollback remain covered by the independent authenticated HTTP transaction smoke, keeping browser authority failures separate from database transaction failures.

## Contract correction

The first live run exposed that Sample18's managed create operation allowed client writes while its shared-contract fields still declared those inputs `readonly`. The seed now marks only the create inputs (`title`, `body`, `assigned_to`, `priority`, and `due_date`) as `editable`. `status` remains readonly because generated UI authority is still limited to create.

The dedicated smoke principal explicitly carries the operation metadata requirements:

- role `editor`;
- scope `task_card:write`;
- existing admin/config roles for the admin preview route.

## Verification

- `make sample18-no-code-public-runtime-enabled-candidate-smoke`
  - Sample18 PHPUnit: 31 tests, 1860 assertions;
  - current selector: live create availability enabled, guarded POST count 1, excluded complete POST count 0;
  - alias selector: live create availability enabled, guarded POST count 1, excluded complete POST count 0.

## Boundary

- All new gates remain default-off.
- No update, complete, reopen, or delete UI execution authority is added.
- The browser stub does not replace the real guarded-route transaction smoke.
- No static artifact preview receives execution authority.

## Next

#742 should close the authenticated UI authority integration lane, review the combined fast/browser/HTTP evidence, and decide whether another single action is justified or UI expansion should remain parked.
