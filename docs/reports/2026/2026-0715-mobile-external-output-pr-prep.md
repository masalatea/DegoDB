# Mobile external output PR prep

## Status

`EF_M16_READY_FOR_PR`

## Branch

```text
feature/mobile-external-output-surfaces
```

## Base

```text
develop
```

## PR URL

```text
https://github.com/masalatea/DegoDB/pull/new/feature/mobile-external-output-surfaces
```

## Commit stack

```text
bf7dc740 Select React Native external consumer extension
5b2a1050 Add React Native extension metadata
7687392d Add app surface config to output mode
8cf19d36 Add Flutter WebView wrapper extension
a4ab83ea Document Flutter WebView wrapper output
b138e52e Checkpoint mobile external output stack
```

The stack is intentionally not squashed.
Each commit is a readable meaning unit.

## Recommended PR title

```text
Add mobile external output surface metadata
```

## Recommended PR description

```markdown
## Summary

- add React Native second-pass metadata to the existing later-platform input packet
- add `app_surface_config` to `output-mode-config.json` so PWA, Flutter WebView, and React/Web Capacitor surfaces can share a backend endpoint by default
- add Flutter WebView wrapper metadata to `flutter-input-packet.json`
- document the Flutter WebView wrapper output boundary and its relation to PWA/app surface config
- record the checkpoint decision that this stack does not need squash before PR review

## Boundary

- no React Native project/source/native generation
- no Flutter project/source/native generation
- no dependency installation
- no service worker or PWA manifest generation
- no signing/build/store submission
- backend endpoints remain shared by default unless explicitly configured otherwise

## Validation

- `php -l mtool/app/mobile_wrapper_target.php`
- `php -l mtool/scripts/create_mobile_wrapper_target.php`
- `git diff --check`
- focused `MobileWrapperTargetTest`
  - React Native metadata slice: 39 tests / 306 assertions
  - app surface config slice: 39 tests / 320 assertions
  - Flutter WebView wrapper slice: 39 tests / 331 assertions
```

## Squash recommendation

Do not squash in the PR unless a single narrative commit is preferred.
The current commits are meaningful review units.
