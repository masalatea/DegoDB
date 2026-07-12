# Mtool AI Workspace Onboarding Plan

Date: 2026-07-12

## Status

`PLAN_READY`

## Purpose

Turn the workspace layout discussion into an implementation-ready plan for the AI design assistance layer.

This plan is documentation-first. It does not implement workspace creation yet. It defines the contract, onboarding question, directory model, configuration precedence, and first safe implementation slices.

## Product framing

When a user asks an AI assistant to use Mtool with a Git URL, the assistant should not immediately clone, scan, or write artifacts.

The first product behavior should be:

1. explain that Mtool expects a project-local workspace by default;
2. ask whether to create/use `project_root/mtool-workspace/`;
3. offer development and external workspace alternatives;
4. proceed only after the workspace root is explicit.

This keeps permissions, generated artifacts, and design history understandable to both the user and the AI assistant.

## Workspace concepts

| Concept | Meaning |
| --- | --- |
| `mtool_home` | Location of the Mtool checkout or installed tool |
| `project_root` | Target user project being scanned/designed |
| `workspace_root` | Location where Mtool/AI artifacts are written |

These are independent. The default binds `workspace_root` to `project_root/mtool-workspace`, but development and external modes can override it.

`project_root/mtool-workspace` is not a Mtool clone. It is the project-local artifact workspace. The Mtool checkout stays in `mtool_home`, usually outside the target project.

## Supported profiles

| Profile | Default workspace | Primary use | Notes |
| --- | --- | --- | --- |
| `project-local` | `project_root/mtool-workspace` | Normal user workflows | Preferred AI/user onboarding path |
| `mtool-work` | `mtool_home/work/<project-key>` | Mtool development and sample verification | Preferred in this repository's own development |
| `external` | explicit path | Advanced user or organization policy | Requires explicit `--workspace-root` or equivalent |

## Workspace root resolution

The resolver should be deterministic and explainable:

1. CLI option, for example `--workspace-root`;
2. environment variable, for example `MTOOL_WORKSPACE_ROOT`;
3. explicit profile selector, for example `--profile mtool-work`;
4. explicit profile selector, for example `--profile project-local`;
5. `project_root/mtool-workspace` when a project root is known and writable;
6. `mtool_home/work/<project-key>` as the development fallback.

If more than one source is present, the chosen source and ignored lower-precedence sources should be visible in diagnostics.

### Mtool-owned project convention

Every workspace has a Mtool-owned project area:

```text
mtool_project_root = workspace_root/mtool-project
```

Mtool may freely write this area. User-facing AI agents should treat it as read-only unless a later workflow explicitly exposes a specific editable config file.

When profile `mtool-work` is selected, this same convention applies inside the repository work directory:

```text
DegoDB/
  work/
    sample-ai-workspace-check/
      mtool-project/
        config/
        db/
        metadata/
        output/
        runtime/
      inputs/
      scans/
      design-briefs/
      task-packets/
      proposals/
      review-artifacts/
      generated/
      validation/
      logs/
```

`mtool-project/` is not the Mtool checkout. It is the Mtool-owned artifact project area inside the workspace. Mtool output artifacts that should later be copied into a user project are staged here first, then reviewed by AI/humans before any external project is changed.

## Proposed directory contract

```text
workspace_root/
  mtool-project/
    config/
    db/
    metadata/
    output/
    runtime/
  inputs/
  scans/
  design-briefs/
  task-packets/
  proposals/
  review-artifacts/
  generated/
  validation/
  logs/
```

### Directory responsibilities

| Directory | Owns | Must not contain | Default Git policy | Naming notes |
| --- | --- | --- | --- | --- |
| `mtool-project/` | Mtool-owned project area: generated files, SQLite DB, design metadata, output, runtime state | AI-authored briefs, proposals, raw logs | commit only if explicitly promoted | Mtool-owned; AI read-only by default |
| `mtool-project/config/` | Mtool project/workspace configuration and explicitly editable user settings | provider tokens, private source inputs, raw scans | commit stable non-secret config when useful | `workspace.json`, `resolver.json`, `user-settings.json`, `git-policy.json` |
| `inputs/` | User-provided or copied source material used by AI design assistance | generated outputs, logs, hidden tool state | user decision; may be private | preserve original names when safe; add hashes in metadata |
| `scans/` | Raw/normalized scan outputs and cache-like project observations | human-approved design decisions, final review docs | usually ignore | timestamped or content-hash run directories |
| `design-briefs/` | Human-reviewable AI design briefs, assumptions, questions, selected direction | raw scan dumps, provider chat logs | commit when useful as design history | `YYYY-MMDD-<slug>.md` and optional `.json` companion |
| `task-packets/` | Mtool/Codex/Claude-ready prompt packets and task metadata | scan cache, generated app artifacts | commit when useful for repeatable workflows | versioned packet directories or `task-<slug>.json` |
| `proposals/` | Structured candidate proposals before acceptance | accepted generated output, verbose logs | commit reviewed proposals; ignore transient attempts | include source hash and proposal version |
| `review-artifacts/` | Human-readable review pages/reports derived from proposals | raw logs, private provider transcripts | commit compact reviewed artifacts when useful | markdown first; link to proposal IDs |
| `generated/` | Generated/intermediate Mtool outputs not yet promoted into the project | source inputs, authoritative project files | usually ignore unless explicitly promoted | separate by run/profile; never overwrite project files silently |
| `validation/` | Validation summaries, stable hashes, compact evidence, command outcomes | full noisy logs, generated app bundles | commit compact evidence; ignore bulky details | stable `summary.json` plus optional markdown report |
| `logs/` | Verbose runtime logs and local troubleshooting output | design decisions, review-ready artifacts | ignore | rotate or timestamp; disposable |

### Directory invariants

- The workspace directory is artifact storage, not the Mtool checkout.
- Every write belongs to exactly one top-level directory.
- `mtool-project/` is Mtool-owned. AI agents can read it, but should write requested changes into `design-briefs/`, `task-packets/`, or `proposals/` instead of editing Mtool-owned state directly.
- Only explicitly documented files under `mtool-project/config/` may be AI/user editable.
- Reviewable human decisions should not live only in `scans/`, `generated/`, or `logs/`.
- Cache-like or bulky data should not be required to understand a final proposal.
- Mtool must never silently overwrite files outside `workspace_root`.
- Promotion from `generated/` into the user project requires an explicit later workflow.
- Provider chat transcripts, credentials, and secrets are not first-class workspace artifacts unless a separate privacy policy is created.
- Stable artifacts should carry enough project identity, source hash, and tool version metadata to be audited later.

### Minimal manifest files

The first implementation should reserve these stable manifest names, even if not all are populated immediately:

| File | Location | Purpose |
| --- | --- | --- |
| `workspace.json` | `mtool-project/config/` | workspace version, selected profile, mtool project root, mtool home hint, created/updated timestamps |
| `git-policy.json` | `mtool-project/config/` | default commit/ignore recommendations by directory; may be AI/user editable when explicitly requested |
| `resolver.json` | `mtool-project/config/` | diagnostic record of how the workspace root and profile were selected |
| `role-mapping.json` | `mtool-project/config/` | semantic role mapping for standard, external, disabled, or user-provided directories |
| `user-settings.json` | `mtool-project/config/` | explicitly editable user/AI preferences that Mtool may consume |
| `summary.json` | `validation/` | latest compact validation summary, if a validation run occurs |

## Git policy defaults

The workspace should not force one Git policy. It should provide defaults and explain them.

| Directory | Default Git policy |
| --- | --- |
| `mtool-project/` | commit only when explicitly promoted or when the workspace is intentionally versioned |
| `mtool-project/config/` | commit stable non-secret config; AI edits only documented user-editable files |
| `inputs/` | user decision; may contain private material |
| `design-briefs/` | commit when useful as design history |
| `task-packets/` | commit when useful for repeatable workflows |
| `proposals/` | commit reviewed proposals; ignore transient attempts |
| `review-artifacts/` | commit compact reviewed artifacts when useful |
| `scans/` | usually ignore |
| `generated/` | usually ignore unless promoted |
| `validation/` | commit compact evidence; ignore bulky details |
| `logs/` | ignore |

The first implementation should generate a suggested `.gitignore` snippet rather than silently editing the user's repository.

For Mtool repository development, `mtool_home/work/` should be ignored by default. Small stable fixtures should be promoted intentionally into `sample/`, `docs/`, or a dedicated fixture location after review; raw `work/` artifacts are not product documentation by default.

## Directory flexibility

Only `mtool-project/` is a fixed Mtool-owned convention in the standard layout.

The other top-level directories are recommended names, not mandatory names. Users may already have Obsidian folders, project notes, design directories, review folders, or organization-specific artifact locations. A workspace may map those existing directories into the Mtool roles instead of creating every recommended directory.

For example:

| Mtool role | Standard name | User-provided equivalent examples |
| --- | --- | --- |
| source material | `inputs/` | `docs/source/`, `references/`, Obsidian attachments folder |
| design briefs | `design-briefs/` | `docs/design/`, `notes/`, Obsidian notes |
| task packets | `task-packets/` | `ai/tasks/`, `prompts/` |
| proposals | `proposals/` | `docs/proposals/`, `architecture/proposals/` |
| review artifacts | `review-artifacts/` | `docs/reviews/`, `reports/` |
| validation evidence | `validation/` | `reports/validation/`, `ci/evidence/` |

The resolver/manifest should therefore distinguish:

- `standard_path`: the default directory name Mtool would create;
- `mapped_path`: the actual directory selected by the user or detected from project conventions;
- `role`: the semantic purpose Mtool expects.
- `disabled`: whether the role is intentionally unused rather than missing;
- `owner`: whether the role is Mtool-owned, AI/user-owned, or external.

Mtool should present the standard layout first, then allow the user/AI to reuse existing directories when that better fits the project. It should not force users to adopt Mtool directory names outside the Mtool-owned `mtool-project/` area.

The role mapping must allow common cases such as Obsidian notes, existing `docs/` trees, organization review folders, and deliberately disabled roles. Only `mtool-project/` remains a fixed Mtool-owned convention.

## Promotion and copy/adaptation plans

Mtool output should be staged inside the workspace first. Moving useful results into an actual user project is a separate review action.

When an AI/user wants to copy or adapt Mtool output into a user project, the workspace should record a proposal before mutation:

```text
proposals/copy-plan-<slug>.md
proposals/copy-plan-<slug>.json
```

The copy plan should identify:

- source artifact path;
- intended destination path;
- whether the destination already exists;
- expected overwrite or merge behavior;
- validation command or manual check;
- approval status.

This keeps Mtool-generated candidates, AI review, and real user-project changes separated.

## Mtool-owned read-only guard

User-facing AI agents may read `mtool-project/`, but direct writes should be refused unless the target is an explicitly documented editable config file.

Recommended guard wording:

```text
Refusing direct AI write to Mtool-owned path.
Write a proposal or task packet instead, or use an explicit Mtool command that owns this state.
```

This is a product boundary, not just a filesystem convention. It prevents an AI assistant from silently making Mtool state inconsistent with Mtool's own validation model.

## First AI onboarding prompt

Recommended prompt text for an AI assistant:

```text
Mtool normally keeps AI/Mtool artifacts for this project under:

  <project_root>/mtool-workspace/

This workspace will hold scan results, design briefs, task packets, proposals,
review artifacts, generated intermediates, validation evidence, and logs.

Do you want to use this project-local workspace?

Alternatives:
- use Mtool's development workspace: <mtool_home>/work/<project-key>/
- choose a custom workspace with --workspace-root
```

The assistant should ask this before creating directories or running scans.

## First implementation slices

### Slice W1: workspace layout contract

Add a code-backed contract that defines:

- supported profiles;
- directory names;
- resolver precedence;
- artifact categories;
- role mapping metadata, including disabled and external mapped roles;
- default Git policy metadata.

Expected output:

- a PHP or JSON-accessible contract helper;
- focused tests for profile names, directory names, precedence labels, role mapping defaults, disabled roles, and Mtool-owned read-only guard metadata;
- no filesystem writes yet.

### Slice W2: workspace resolver dry run

Add a side-effect-free resolver that accepts `mtool_home`, `project_root`, CLI/env-like inputs, and returns:

- selected `workspace_root`;
- selected profile;
- explicit profile source, when supplied;
- source of the decision;
- warnings/errors;
- directories that would be created.
- role mapping diagnostics;
- Mtool-owned direct-write refusal diagnostics.

Expected output:

- no directory creation;
- no scan;
- no artifact writes;
- focused unit/contract tests, including project-local, explicit external workspace, `mtool-work` with fixture project, `mtool-work` with an external project root, existing notes/Obsidian-style mapped roles, disabled roles, and Mtool-owned read-only boundaries.

### Slice W3: onboarding prompt artifact

Generate a reviewable onboarding prompt/plan from the dry-run result.

Expected output:

- human-readable prompt text;
- machine-readable prompt metadata;
- explicit "ask before write" flag;
- tests proving project-local, mtool-work, and external variants.

### Slice W4: explicit workspace initialization

Only after the dry-run/prompt contract is stable, add an explicit initialization command that creates directories.

Expected safeguards:

- require explicit user approval or a clear CLI flag;
- never overwrite existing non-Mtool files;
- write a manifest describing the workspace version/profile;
- optionally emit a `.gitignore` suggestion rather than editing Git by default.

### Slice W5: first scan/design artifact placement

Route an existing deterministic scan or task packet through the workspace layout.

Expected safeguards:

- write only into the selected workspace;
- preserve zero-mutation behavior for the target project;
- keep logs/cache separate from reviewable artifacts;
- validate artifact hashes/paths.

## Non-goals for this plan

- Do not implement AI provider integration.
- Do not auto-clone Mtool into user projects.
- Do not scan before workspace approval.
- Do not mutate the user's `.gitignore` without an explicit choice.
- Do not make `mtool-workspace/` mandatory when a user provides an external workspace.
- Do not treat the AI design assistance layer as the No Code execution layer.

## Completion criteria for the planning lane

This planning lane is complete when:

- project-local, mtool-work, and external profiles are documented;
- the workspace directory contract is explicit;
- the first AI onboarding prompt is explicit;
- the implementation slices are ordered from no-write to explicit-write;
- `docs/current-plans.md` points to the next preflight/implementation candidate.

## Recommended next step

Promote Slice W1 as the next implementation preflight:

`Mtool AI workspace layout contract preflight`

It should define the exact helper/contract shape and test matrix before any filesystem writes are added.
