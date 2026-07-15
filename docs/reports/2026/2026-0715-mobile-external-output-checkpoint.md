# Mobile external output checkpoint

## Status

`EF_M15_DONE`

## Scope

Review the unpushed EF-M10 through EF-M14 stack before choosing another implementation slice or opening a PR.

## Commit stack

Current local `develop` is 5 commits ahead of `origin/develop`:

```text
bf7dc740 Select React Native external consumer extension
5b2a1050 Add React Native extension metadata
7687392d Add app surface config to output mode
8cf19d36 Add Flutter WebView wrapper extension
a4ab83ea Document Flutter WebView wrapper output
```

## Review

The stack is already split by readable meaning:

- selection and decision record;
- React Native second-pass metadata;
- shared backend endpoint + multi-surface output config;
- Flutter WebView wrapper metadata;
- durable docs / CLI wording hardening.

This is a reasonable PR commit stack as-is.
Squashing is not required before PR unless the user wants a single narrative commit.

## Validation evidence

Code-bearing slices used focused validation because changes are isolated to mobile wrapper target metadata and its integration test:

- EF-M11 React Native extension metadata:
  - `php -l mtool/app/mobile_wrapper_target.php`
  - `php -l mtool/scripts/create_mobile_wrapper_target.php`
  - `git diff --check`
  - focused `MobileWrapperTargetTest`: `OK (39 tests, 306 assertions)`
- EF-M12 app surface config:
  - `php -l mtool/app/mobile_wrapper_target.php`
  - `php -l mtool/scripts/create_mobile_wrapper_target.php`
  - `git diff --check`
  - focused `MobileWrapperTargetTest`: `OK (39 tests, 320 assertions)`
- EF-M13 Flutter WebView wrapper extension:
  - `php -l mtool/app/mobile_wrapper_target.php`
  - `php -l mtool/scripts/create_mobile_wrapper_target.php`
  - `git diff --check`
  - focused `MobileWrapperTargetTest`: `OK (39 tests, 331 assertions)`
- EF-M14 docs/schema hardening:
  - `php -l mtool/scripts/create_mobile_wrapper_target.php`
  - `git diff --check`

## Current result

The current mobile external output lane can express:

- React Native second-pass metadata without generating React Native project/source/native files;
- PWA + Flutter WebView + React/Web Capacitor as selectable app surfaces with shared backend endpoint by default;
- Flutter WebView wrapper metadata without generating Flutter project/source/native files;
- durable docs for CLI path, boundary, and PWA/app surface relationship.

## Recommendation

Keep the five commits separate for PR review.
If a smaller PR is needed, split after the selection commit:

1. React Native extension metadata;
2. app surface config + Flutter WebView wrapper extension/docs.

Otherwise, one PR to `develop` is coherent.

## Next

Open/push PR when requested, or choose the next product slice after this checkpoint.
