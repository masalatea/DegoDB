# Mtool AI Workspace Dry-Run Simulation

Date: 2026-07-12

## Status

`SIMULATION_RECORDED`

## Purpose

Simulate how an AI assistant that has never seen Mtool before would use the current workspace definitions when asked to create or prepare a project in a work directory.

No files were created. This was a reasoning-only dry run against the current workspace onboarding plan.

## Scenario 1: normal user project

### User request

```text
Use Mtool from this Git URL to inspect /projects/my-app.
```

### Expected AI response

```text
Mtool itself should be cloned/installed outside the target project, for example:

  ~/tools/DegoDB

For this project, Mtool normally writes artifacts under:

  /projects/my-app/mtool-workspace

This workspace stores scan results, design briefs, task packets, proposals,
review artifacts, generated intermediates, validation evidence, and logs.

Do you want to use this project-local workspace?
```

### Resolved paths

| Concept | Value |
| --- | --- |
| `mtool_home` | `~/tools/DegoDB` |
| `project_root` | `/projects/my-app` |
| `workspace_root` | `/projects/my-app/mtool-workspace` |
| `profile` | `project-local` |

### Result

This works cleanly. The Mtool checkout and project artifacts are separated, and the AI has one clear permission question before writing.

## Scenario 2: Mtool development work directory

### User request

```text
For Mtool development, create a sample project in the work directory and try the workspace flow.
```

### Expected AI response

```text
Because this is Mtool development, I will use the Mtool repository work area:

  <mtool_home>/work/<project-key>

I suggest project key:

  sample-ai-workspace-check

This is a development workspace, not the normal user-project default.
Do you want to use it?
```

### Resolved paths

| Concept | Value |
| --- | --- |
| `mtool_home` | `/Users/matsue/dev/DegoDB` |
| `workspace_root` | `/Users/matsue/dev/DegoDB/work/sample-ai-workspace-check` |
| `profile` | `mtool-work` |
| `mtool_project_root` | `workspace_root/mtool-project` |

### Result

This mostly works, but the current definition needed one clarification:

- In `mtool-work` profile, Mtool output and persistent state should live under `workspace_root/mtool-project`.
- This keeps the workspace root stable while giving tests and fixtures a predictable Mtool-owned artifact project area.

Recommended development layout:

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

`mtool-project/` is the Mtool-owned artifact project area. User-facing AI agents should read it, not edit it directly; requested changes belong in design briefs, task packets, or proposals.

## Scenario 3: external workspace

### User request

```text
Use Mtool for /projects/my-app, but put all Mtool artifacts in /tmp/mtool-work/my-app.
```

### Expected AI response

```text
I will treat:

  /projects/my-app

as the target project, and:

  /tmp/mtool-work/my-app

as the explicit external Mtool workspace.

Mtool itself remains in mtool_home, outside both paths.
```

### Resolved paths

| Concept | Value |
| --- | --- |
| `mtool_home` | user-selected Mtool checkout |
| `project_root` | `/projects/my-app` |
| `workspace_root` | `/tmp/mtool-work/my-app` |
| `profile` | `external` |

### Result

This works cleanly. The explicit workspace root must take precedence over profile defaults.

## Observed improvement needs

### 1. Add an explicit profile selector

The existing precedence had CLI workspace root, env workspace root, project-local default, then development fallback.

The simulation showed that `mtool-work` needs an explicit selector so development flows do not depend on failing project-local detection.

Recommended precedence:

1. `--workspace-root`;
2. `MTOOL_WORKSPACE_ROOT`;
3. explicit `--profile mtool-work`;
4. explicit `--profile project-local`;
5. project-local default: `project_root/mtool-workspace`;
6. development fallback: `mtool_home/work/<project-key>`.

### 2. Define `mtool-project` ownership

Every workspace should include:

```text
mtool_project_root = workspace_root/mtool-project
```

Mtool can freely write there. User-facing AI agents should treat it as read-only, except for explicitly documented editable files under `mtool-project/config/`.

### 3. Keep standard directories flexible

Other workspace directories such as `inputs/`, `design-briefs/`, `task-packets/`, `proposals/`, `review-artifacts/`, and `validation/` are recommended names, not mandatory names.

Users may already have Obsidian notes, project documentation, proposal folders, or organization-specific directories. Mtool should support mapping existing directories to semantic roles rather than forcing every standard directory name.

### 4. Keep `mtool-workspace/` visibly separate from the Mtool clone

The simulation confirmed that visible `mtool-workspace/` is clearer than hidden `.mtool/`, and less ambiguous than `mtool/`.

The first AI prompt should explicitly say:

- Mtool checkout lives in `mtool_home`;
- project artifacts live in `workspace_root`;
- `mtool-workspace/` is not a clone.

## Decision

Update the onboarding plan and #838 preflight scope to include:

- profile selector precedence;
- Mtool-owned `mtool-project/` ownership boundary;
- flexible mapping for non-Mtool-owned standard directories;
- resolver diagnostics for explicit profile choices;
- tests for project-local, mtool-work, external workspace, Mtool-owned read-only boundaries, and standard directory role mappings.

## Additional dry-run notes after ownership clarification

These are intentionally lightweight notes. They capture places where a first-time AI assistant would likely hesitate or make an unsafe assumption.

## Scenario 4: existing Obsidian or notes workspace

### User request

```text
I already manage project notes in Obsidian. Use those notes where possible.
```

### Expected AI response

```text
Mtool still needs a Mtool-owned artifact area:

  <workspace_root>/mtool-project/

For human-readable notes, I can map Mtool roles to your existing Obsidian folders
instead of creating every standard directory. I will not write inside
mtool-project/ except through Mtool or explicitly editable config files.
```

### Result

The standard layout must support role mapping. Otherwise the AI either creates duplicate folders or writes Mtool artifacts into user note folders too aggressively.

Recommended manifest addition:

```text
mtool-project/config/role-mapping.json
```

The manifest should record at least:

- role name;
- standard path;
- mapped path;
- whether the mapped path is inside or outside `workspace_root`;
- owner/writer expectation;
- whether the role is disabled.

## Scenario 5: generated artifact copied into a real user project

### User request

```text
Mtool generated a candidate file. Put the useful parts into my app.
```

### Expected AI response

```text
Mtool output remains staged under mtool-project/.
I will prepare a copy/adaptation proposal first, then ask before changing the user project.
```

### Result

Promotion from Mtool output into a real user project needs an explicit review artifact, not an implicit copy. This is especially important when `workspace_root` is not the user project's root.

Recommended artifact convention:

```text
proposals/copy-plan-<slug>.md
proposals/copy-plan-<slug>.json
```

The copy plan should identify:

- source artifact path;
- intended destination path;
- whether the destination already exists;
- expected overwrite/merge behavior;
- validation command or manual check;
- approval status.

## Scenario 6: AI wants to fix Mtool-owned metadata directly

### User request

```text
The schema looks wrong. Fix mtool-project/metadata/schema.json.
```

### Expected AI response

```text
That file is Mtool-owned. I can read it, but I should not edit it directly.
I will write a requested correction into a task packet/proposal for Mtool to apply.
```

### Result

The read-only boundary needs an obvious diagnostic. Without that, AI agents may "helpfully" edit Mtool state and make later validation meaningless.

Recommended guard wording:

```text
Refusing direct AI write to Mtool-owned path.
Write a proposal or task packet instead, or use an explicit Mtool command that owns this state.
```

## Scenario 7: user disables standard directories

### User request

```text
Do not create logs or validation folders. I only want proposals.
```

### Expected AI response

```text
I can disable optional roles in the role mapping. Mtool-owned config remains
under mtool-project/config/, but non-Mtool standard directories are flexible.
```

### Result

The resolver should distinguish "not created yet" from "disabled by user". A disabled role is a valid configured state, not a missing directory error.

Example shape:

```json
{
  "roles": {
    "proposals": {
      "standard_path": "proposals",
      "mapped_path": "design/proposals",
      "disabled": false
    },
    "logs": {
      "standard_path": "logs",
      "mapped_path": null,
      "disabled": true
    }
  }
}
```

## Scenario 8: Mtool repository development under `work/`

### User request

```text
In this repository, try the workspace flow under work/.
```

### Expected AI response

```text
Because this is Mtool development, I will use:

  DegoDB/work/<project-key>/

The Mtool-owned artifact area inside that workspace is:

  DegoDB/work/<project-key>/mtool-project/
```

### Result

Development workspaces are likely to contain bulky generated files and should be ignored by default unless a fixture is intentionally promoted.

Recommended Git policy:

- ignore `work/` by default;
- promote small stable fixtures into `sample/`, `docs/`, or a dedicated fixture directory only after review;
- do not assume `work/` artifacts are product documentation.

## Additional decisions from these dry runs

Update the preflight to include:

- `role-mapping.json` as a first-class config manifest;
- copy/adaptation plan artifacts under `proposals/`;
- read-only guard wording for Mtool-owned paths;
- disabled/external role states in the resolver result;
- explicit `work/` Git policy for Mtool development.
