# External framework optional output boundary check plan

## Status

`REPOSITIONED_AS_OPTIONAL_OUTPUT`

## Purpose

Check how external FE/no-code/app frameworks should be supported as optional output targets without losing the current Mtool responsibility boundary.

The check is from the Mtool side. It is not a plan to replace Mtool's own no-code runtime now.

Even if full replacement is possible in principle, the current product judgment is that replacing Mtool's own no-code output is not the highest-value next step. The better direction is optional external framework output:

- keep `mtool_no_code` as the supported baseline;
- emit `external_no_code` / `hybrid` artifacts where useful;
- keep Mtool contracts, validation, and server authority as the source of truth;
- let external frameworks own their app/project implementation.

This plan exists because Mtool has already built a meaningful amount of its own no-code output:

- `NO-CODE-RUNTIME` Source Output;
- runtime preview JSON / HTML;
- list/detail/form metadata and rendering;
- action intent draft and readiness metadata;
- guarded submit / outbox handoff where supported;
- publish candidate, current preview, and alias preview;
- representative validation and browser smoke gates.

The goal is not to delete or replace that output. The goal is to determine how the existing contract can become optional input to external frameworks over time.

Precheck:

- [2026-0715 Mtool No-Code External Framework Full-Coverage Check](2026-0715-mtool-no-code-external-framework-full-coverage-check.md)

## Investigation and decision history / 調査・判断経緯

The discussion started from a concern that Mtool may already have built a substantial amount of its own no-code output, and that replacing it with an external framework later might become expensive or blur responsibility boundaries.

The first check separated two questions:

1. Is Mtool's current no-code supported scope portable to an external framework?
2. Even if it is portable, is replacement the right next product move?

For the first question, the precheck found that the Mtool-supported scope is mostly contract-shaped already. Screen metadata, runtime preview metadata, list/detail/form surfaces, action intent drafts, readiness state, validation maps, and generated/custom ownership boundaries can all be represented as external framework input. Server authority, CSRF, idempotency, Transaction Full, audit, guarded operation allow-list, outbox processing, and approval/current/alias policy should remain Mtool/server-owned.

That means Mtool-level full coverage by an external framework is feasible in principle if the external framework consumes the Mtool contract. It is not a promise that the external framework covers all of its own possible features, and it is not a promise that Mtool's no-code scope covers every possible app.

The second question changed the plan. Even though replacement is feasible in principle, doing it now has low product value compared with optional external output. Replacing `NO-CODE-RUNTIME` now would risk turning an implementation detail into the source of truth and would spend effort on migration rather than user-facing output choices.

Final decision for today:

- keep `mtool_no_code` as the supported baseline;
- treat Mtool's current runtime as working output plus reference/fallback for external consumers;
- do not start a migration away from Mtool's no-code runtime;
- add external frameworks as optional output targets through `external_no_code` and `hybrid`;
- preserve the core/custom boundary as `contract-owned/core` vs `external/custom/extension-owned`;
- run the future EF-M1 check as optional-output planning, not migration planning.

## Initial assessment

Full migration of every Mtool-owned no-code surface into an external framework would be large and risky if treated as a rewrite.

However, the current boundary appears portable if the migration is contract-first:

| Current boundary | Portable external-framework interpretation |
| --- | --- |
| Mtool-owned metadata | Stable input contract for external framework / AI builder |
| Mtool generated runtime | Reference implementation and fallback preview |
| Mtool validation gates | Acceptance tests / conformance checks for external output |
| Server authority boundary | Still owned by Mtool/server, not the external UI |
| Generated/basic surface | External framework can render this as the standard/core surface |
| Custom code / custom UI | External framework or app owner keeps this as extension/custom ownership |
| Forbidden/confirmation-required behavior | Same fail-closed boundary in task packets and framework adapters |

The important distinction can survive:

- core / generated / supported function;
- custom / extension / app-owner function.

In other words, the current "Mtool function vs custom" classification can be carried forward, but it should be renamed in external contexts as "contract-owned/core" vs "external/custom/extension-owned" to avoid implying that Mtool owns the external app implementation.

## Check scope

The planned check should answer:

1. Which existing Mtool no-code artifacts are product output versus reference/fallback implementation?
2. Which artifacts can be treated as stable external framework input?
3. Which current runtime behaviors are Mtool-owned authority boundaries and must not move to the external UI?
4. Which current UI/rendering behaviors could move to an external framework with little risk?
5. Which custom extension boundaries already map cleanly to external framework customization points?
6. Which external framework capabilities require new metadata rather than code migration?
7. Whether `mtool_no_code`, `external_no_code`, and `hybrid` are still the right output modes after this mapping.
8. Which optional external output targets should be prioritized first, without turning them into mandatory replacements.

## Non-goals

- Do not rewrite the no-code runtime during the check.
- Do not start a migration away from `mtool_no_code`.
- Do not choose a single external framework as the final owner.
- Do not remove `mtool_no_code` fallback output.
- Do not move server-side authority, CSRF, idempotency, Transaction Full, audit, or outbox processing into browser-only code.
- Do not claim that independent Mtool and external app surfaces stay synchronized unless a separate sync contract exists.

## Expected output

The check should produce:

- an optional-output boundary matrix;
- a list of artifacts that should remain stable inputs;
- a list of Mtool runtime pieces that external targets may consume or mirror;
- a list of missing external-framework metadata, if any;
- a recommendation on whether to keep current modes as-is or adjust mode names/wording;
- a recommended first optional target slice.

## Plan position

Run this after the standalone no-code completion report, before starting any major external-framework optional-output implementation lane.

This keeps the current standalone completion clean, then checks optional external-framework output without mixing it into the completion definition or implying replacement.
