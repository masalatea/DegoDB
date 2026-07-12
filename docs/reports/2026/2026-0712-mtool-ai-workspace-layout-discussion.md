# Mtool AI Workspace Layout Discussion

Date: 2026-07-12

## Status

`DISCUSSION_RECORDED`

## Context

After separating the No Code coverage/dogfooding layer from the AI design assistance layer, we discussed where AI-assisted scan results, design briefs, task packets, review artifacts, and generated/intermediate outputs should live.

The core distinction is:

- Mtool repository location;
- target user project location;
- workspace location for AI/Mtool artifacts.

These must be configurable independently, even if the default user experience should be simple.

## Candidate placement patterns

### 1. Project-local Mtool workspace

Mtool artifacts live under the target user project.

```text
user-project/
  mtool-workspace/
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

This is the preferred normal-user pattern.

`mtool-workspace/` is not the Mtool clone. It is only the project-local artifact workspace. The Mtool checkout remains `mtool_home`, typically outside the user project.

Reasons:

- AI can keep permission and context boundaries project-local.
- The user can inspect artifacts with ordinary filesystem tools.
- The artifacts naturally belong to the project being designed.
- It is easier for an AI assistant to ask for one bounded approval: create/use `mtool-workspace/` under this project.
- Git policy can be handled per subdirectory rather than mixing project artifacts into the Mtool tool repository.

### 2. Mtool repository work directory

Artifacts live under the Mtool checkout.

```text
DegoDB/
  work/
    <project-key>/
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

This is the preferred development pattern for Mtool itself.

Reasons:

- Sample and fixture development can stay inside the Mtool repository workspace.
- Local tests can use stable, repo-relative paths.
- Repeated experimental runs do not need to write into an external user project.

### 3. External Mtool checkout with project-local artifacts

Mtool is cloned outside the user project, but artifacts still live in the user project.

```text
~/tools/DegoDB/          # mtool_home
~/work/user-project/     # project_root
~/work/user-project/mtool-workspace/
```

This is likely common for real users who install Mtool once and use it across projects.

## Workspace root resolution

The tentative resolution order is:

1. explicit CLI option such as `--workspace-root`;
2. environment variable such as `MTOOL_WORKSPACE_ROOT`;
3. project-local default: `project_root/mtool-workspace`;
4. development fallback: `mtool_home/work/<project-key>`.

Profiles may make this clearer:

| Profile | Intended use | Workspace root |
| --- | --- | --- |
| `project-local` | Normal user/project operation | `project_root/mtool-workspace` |
| `mtool-work` | Mtool development and sample verification | `mtool_home/work/<project-key>` |
| `external` | User-selected location | explicit `--workspace-root` or env |

## First AI interaction

When a user tells an AI assistant to use Mtool by providing the Mtool Git URL, the first interaction should not begin scanning immediately.

The assistant should first explain the expected project-local workspace and ask for approval, for example:

```text
Mtool is designed to keep scan results, design briefs, task packets, review artifacts,
and generated/intermediate outputs under this project in `mtool-workspace/`.

Do you want me to create/use `project_root/mtool-workspace/` for this work?
```

The assistant can then mention alternatives:

- use `mtool_home/work/<project-key>` for Mtool development;
- use a user-selected external directory with `--workspace-root`.

## Obsidian reference point

Obsidian is useful as a conceptual reference: it centers user-owned data in a normal filesystem folder called a vault, with Markdown notes and attachments stored as ordinary files. Attachments can be stored in the vault root, a specified folder, the same folder as a note, or a subfolder next to the note depending on user settings.

The Mtool equivalent should not copy Obsidian terminology literally, but should borrow the design idea:

- keep artifacts visible as ordinary files;
- prefer a project-local workspace;
- allow the user to choose attachment/artifact placement;
- avoid hiding important state in an opaque tool database;
- keep tool configuration separate from project artifacts.

`workspace` is a better term for Mtool than `vault`, because the contents include generated artifacts, validation evidence, and task packets rather than only notes.

## Git policy notes

The workspace should support mixed commit policies.

The detailed directory contract is recorded in [2026-0712 Mtool AI Workspace Onboarding Plan](2026-0712-mtool-ai-workspace-onboarding-plan.md). The summary below is intentionally lightweight history.

Likely defaults:

| Directory | Default policy |
| --- | --- |
| `mtool-project/` | Mtool-owned; commit only when explicitly promoted or intentionally versioned |
| `mtool-project/config/` | commit stable non-secret config; AI edits only documented user-editable files |
| `inputs/` | user decision; may contain copied source material |
| `design-briefs/` | commit when useful as project design history |
| `task-packets/` | commit when useful for repeatable AI/Mtool workflows |
| `proposals/` | commit reviewed proposals; ignore transient attempts |
| `review-artifacts/` | commit reviewed human-readable artifacts when useful |
| `scans/` | usually ignore/cache |
| `generated/` | usually ignore unless explicitly promoted |
| `validation/` | commit compact evidence when useful; ignore bulky logs |
| `logs/` | ignore |

## Current decision

Use project-local `mtool-workspace/` as the normal-user default and Mtool repo `work/<project-key>/` as the development default.

Keep external workspace support through explicit configuration.

Do not implement yet. The next step is to turn this into a workspace layout contract and onboarding prompt plan before adding code.
