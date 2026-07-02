# Public Delivery Commit Cleanup / public delivery commit cleanup

Date: 2026-07-02

Status: `DONE`

## Summary / 要約

Grouped the accumulated no-code public runtime delivery worktree into one local reviewable commit without pushing.

no-code public runtime delivery の累積 worktree を、push せずに review 可能な 1 本の local commit へ整理した。

## Commit / コミット

- `e2c5d7e Add no-code public runtime delivery workflow`

## Scope / 範囲

The commit includes:

- approval transition event storage and repository behavior after the prior minimal candidate persistence commit;
- current public revision storage and explicit current selection;
- public artifact-key, current, and alias runtime preview delivery;
- public cache/version policy;
- custom alias storage and alias deletion;
- rollback/current/alias operator UI wording;
- public delivery browser smoke coverage;
- alias lifecycle event storage and recent operator UI display;
- dated reports and current plan updates through public delivery hardening closure.

## Verification / 検証

Recorded before commit:

- `make sample28-no-code-public-runtime-browser-smoke`
- focused PHPUnit
  - repository: `15 tests, 254 assertions`
  - static contract: `22 tests, 1798 assertions`
- `make test`
  - `326 tests, 10699 assertions, skipped 1`
- `git diff --check`

## Boundary / 境界

- Push was not performed.
- No history rewrite was performed.
- No additional code changes were made after the commit.

## Next / 次

Run a post-public-delivery-commit product-goal replan before selecting the next implementation lane.
