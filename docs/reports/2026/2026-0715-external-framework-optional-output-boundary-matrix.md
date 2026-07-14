# External framework optional output boundary matrix

## Status

`EF_M1_DONE_FIRST_MATRIX`

## Purpose

Resolve EF-M1 into an optional-output boundary matrix and first target recommendation.

The goal is not to replace `mtool_no_code`. The goal is to let Mtool emit useful external no-code/tool artifacts while keeping Mtool's current no-code runtime as the supported baseline.

## Position

External no-code/tool support should be additive:

| Mode | Role |
| --- | --- |
| `mtool_no_code` | Supported Mtool baseline and fallback/reference runtime. |
| `external_no_code` | Optional output artifacts for an external tool/framework/code-builder. |
| `hybrid` | Keep Mtool output and also emit external handoff artifacts. |

## Boundary matrix

| Area | Mtool owns | External tool/framework owns | Notes |
| --- | --- | --- | --- |
| canonical app contract | schema version, project/app identity, source artifact index, non-goals | consumes the contract | Mtool contract remains the source of truth. |
| screen/action metadata | list/detail/form, selected row/key, field metadata, action intent, readiness | rendering/component mapping | External UI should not infer missing keys/actions. |
| server authority | auth boundary, CSRF boundary, idempotency, Transaction Full statement, audit/outbox policy | request submission and UI feedback | Business mutation authority stays server-side. |
| generated/core surface | declares supported/core behavior | implements optional UI/app surface | Rename as `contract-owned/core` in external context. |
| custom/extension surface | declares extension slots and forbidden/confirmation-required actions | custom components, app shell, framework choices | Rename as `external/custom/extension-owned`. |
| validation | validation command map, artifact hash checks, readiness checks | target-specific build/test commands | External target must provide conformance evidence before being considered usable. |
| delivery/native | boundary metadata, native wrapper checklist, PWA readiness metadata | Capacitor/native project, dependencies, signing, store, device QA | Mtool does not create native project by default. |
| AI/code-builder | provider-neutral task packet, forbidden-guess list, confirmation-required list | code generation and project editing after user approval | Durable core must remain provider-neutral. |

## Layered target recommendation

Do not compare these as alternatives; they sit in different layers.

| Priority | Layer | Target | Recommendation |
| --- | --- | --- | --- |
| 1 | Layer A: FE/app framework | React/Web + Capacitor-style wrapper | First optional output target. Existing evidence is strongest and sample35 proves direct artifact import without Mtool initializing Capacitor. |
| 2 | Layer B: AI/code-builder | Codex/Claude-style provider-neutral task packet | Useful in parallel as a guidance layer, but should consume the same target-neutral packet and not become the durable core. |
| 3 | Layer C: delivery/runtime | PWA readiness and native wrapper boundary metadata | Good checklist/metadata output; not a full app implementation. |
| Later | Layer A | Flutter / React Native input packets | Promising, but should follow after the first target-neutral optional-output contract is hardened. |

## First optional target slice

Recommended first implementation slice:

`EF-M2 React/Web + Capacitor optional output packet`

Scope:

- produce or harden an optional external output packet for React/Web + Capacitor-style consumers;
- include source artifact refs/hashes;
- include screen/action/readiness metadata refs;
- include ownership boundary;
- include validation command map;
- include confirmation-required actions;
- include forbidden-without-artifact list;
- include non-goals;
- do not initialize Capacitor;
- do not install dependencies;
- do not create or overwrite user app source;
- do not claim native build/signing/store readiness.

Why this first:

- It is closest to the current Mtool web/no-code runtime.
- Existing `NO-CODE-REACT-BRIDGE`, mobile handoff, wrapper target, and sample35 evidence already point here.
- It keeps external app shell ownership outside Mtool.
- It is useful to app creators and AI builders without requiring Mtool to own a production frontend framework.

## Required output shape

The first target packet should be target-specific but still contract-first:

```text
react-web-capacitor-output/
  external-output.json
  EXTERNAL-OUTPUT.md
  source-artifact-index.json
  ownership-boundary.json
  validation-map.json
```

Minimum JSON sections:

- `schema_version`;
- `mode`;
- `target`;
- `project_identity`;
- `source_artifacts`;
- `screens`;
- `actions`;
- `readiness`;
- `server_authority`;
- `ownership_boundary`;
- `requires_user_confirmation`;
- `forbidden_without_artifact`;
- `validation`;
- `non_goals`.

## Open items before implementation

Before coding EF-M2, decide:

1. whether the target packet should extend `mobile-app-handoff.json` or be a sibling artifact;
2. whether output should live under `NO-CODE-REACT-BRIDGE`, `MOBILE-WRAPPER-TARGET`, or a new source output key;
3. whether the existing sample35 fixture can be reused as first proof input;
4. which fast test should own the packet schema;
5. which optional smoke, if any, proves the handoff without opening a visible browser.

## Decision

EF-M1 is complete for first planning purposes.

Next active slice should be EF-M2: define and implement a React/Web + Capacitor optional output packet, without replacing `mtool_no_code`.
