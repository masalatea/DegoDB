# 2026-0702 User-Facing No-Code Tryout Guide First Slice

Status: `FIRST_SLICE_DONE`

## Summary

Added a short user-facing no-code tryout guide after the Docker-backed sample28 browser trial showed that the feature works but the first-time path was not obvious from existing docs.

## Changes

- Added `docs/no-code-tryout.md` as the entry guide for starting sample28, logging in, approving `NO-CODE-RUNTIME`, and opening the current public runtime preview.
- Linked the guide from the repository `README.md` entry layer.
- Added a compact browser tryout path to `sample/tutorials/sample28-no-code-data-app-mvp/README.md`.
- Updated `docs/current-plans.md` so the active plan reflects that the guide first slice is complete and the next decision is onboarding polish.

## Observations

The guide keeps implementation terms visible because they match the current UI, but it translates the sequence into a user scenario:

- start Docker;
- log in;
- open `SAMPLE28`;
- approve the generated runtime;
- open the public preview.

The next likely product-facing polish is one of:

- seeded preview data so the list screen does not start empty;
- friendlier operator UI wording around Source Output / Publish Candidate / Current Public Revision;
- a one-click local tryout action that wraps candidate creation and approval for sample/demo use.

## Verification

- `git diff --check` passed.

No runtime code was changed. No push was performed.
