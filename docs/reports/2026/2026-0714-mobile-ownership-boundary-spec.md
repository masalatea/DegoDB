# Mobile ownership boundary spec / mobile ownership boundary spec

Date: 2026-07-14

## Summary / summary

Added a stable boundary reference:

- `docs/mobile-ownership-boundaries.md`

This document makes the mobile/app handoff responsibility split explicit:

- Mtool-owned;
- external owner/tool-owned;
- user-confirmation required;
- forbidden without explicit artifact;
- parked/new product scope.

## Reason / 理由

The FS reports already contained boundary notes, but they were spread across target-specific reports and output mode policy. A single durable boundary table is needed so app creators, AI builders, and maintainers can quickly see who owns what.

## Decision / 判断

Mtool owns:

- handoff contracts;
- source artifact indexes;
- app behavior metadata;
- server authority metadata;
- ownership boundary metadata;
- target extension packets;
- provider-neutral AI/code-builder task packets;
- validation maps;
- bundle manifests;
- read-only guidance UI.

External owner/tool owns by default:

- production frontend source;
- framework architecture choices;
- API/OIDC client implementation;
- secure token storage implementation;
- external app project files;
- native projects;
- dependencies;
- signing;
- store submission;
- device QA.

User confirmation is required for project creation, dependency installation, native initialization, token storage choice, offline sync, signing/store decisions, overwrite, and UI-triggered execution.

Forbidden without explicit artifact:

- offline sync;
- persistent business-data storage;
- refresh-token persistence;
- native plugin selection;
- app signing;
- store submission;
- production frontend architecture;
- external app overwrite;
- automatic dependency installation.

## Links / links

The boundary spec is linked from:

- `docs/README.md`;
- `docs/mobile-external-feasibility-study.md`;
- `docs/mobile-output-modes.md`;
- `docs/mobile-artifact-execution-ui-policy.md`.
