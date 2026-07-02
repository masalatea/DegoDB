# 2026-0702 Post-App-Local-Package-Manifest No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **App-local package archive smoke first slice** as the next no-code product-facing confidence step.

The package manifest shape now exists and is covered by focused generation tests. The next smallest risk is whether the generated package artifact can be used as an actual archive boundary, not only as files in the runtime source/published tree.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| App-local package archive smoke first slice | 0.5 - 1 day | Selected. Verify the generated package archive can be listed and extracted with expected manifest files. |
| Operator package readiness display | 0.5 - 1 day | Deferred until archive confidence exists. Readiness display is more useful after package archive behavior is proven. |
| Defer packaging lane | Replan first | Deferred. The archive smoke is small and directly validates the just-added package manifest boundary. |

## Boundary

In scope:

- focused archive list/extract smoke for `app-local-package-manifest`;
- expected manifest/summary file checks inside the archive;
- report and current plan update.

Out of scope:

- native installers;
- new package format;
- operator/admin readiness UI;
- remote sync transport;
- push.

## Verification

Implementation selected immediately after this planning step.
