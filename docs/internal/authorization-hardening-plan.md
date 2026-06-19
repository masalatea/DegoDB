# Authorization Hardening Plan

Status: `NEXT`

English companion:
This document is the current plan for applying project permissions across Mtool routes after SSO completion. SSO is done; this phase uses the authenticated principal and project roles to decide whether each route or operation is allowed.

## Boundary

Status: `NEXT`

This phase is not SSO implementation. It starts after the SSO boundary documented in `docs/internal/sso-oidc-connection.md`.

In scope:

- map authenticated project routes to project capabilities;
- apply `project.read`, `project.edit`, `source_output.publish`, `source_output.download`, `db_source.manage`, and `secret.manage`;
- preserve current not-found / validation behavior where possible;
- record audited permission decisions for newly enforced route clusters;
- add small contract or smoke checks for each enforced cluster.

Out of scope:

- member / group lifecycle management;
- IdP administration UI;
- SCIM;
- invitation flows;
- password reset or MFA enrollment;
- full legacy `ProjectUser` reconstruction;
- product-style role management screens.

## Status Labels

| Label | Meaning |
| --- | --- |
| `NEXT` | Planned next work. |
| `IN_PROGRESS` | Actively being implemented. |
| `DONE` | Implemented, documented, and verified. |
| `PARKED` | Explicitly deferred. |
| `NOT_IN_SCOPE` | Not part of this phase. |

## Current Status

| Area | Status | Notes |
| --- | --- | --- |
| SSO / OIDC login | `DONE` | Completed in `docs/internal/sso-oidc-connection.md`. |
| Route capability inventory | `NEXT` | First step; no behavior change. |
| Read-only project route enforcement | `NEXT` | Apply `project.read` to low-risk view routes first. |
| Edit route enforcement | `NEXT` | Apply `project.edit` to metadata write routes by route cluster. |
| Source output permission consistency | `NEXT` | Publish/download already have enforcement; surrounding routes need inventory and consistency check. |
| Strong capability enforcement | `NEXT` | Apply `db_source.manage` and `secret.manage` only to operations that need stronger-than-editor rights. |
| Audit coverage expansion | `NEXT` | Use audited permission decisions for new enforcement points. |
| Viewer/editor/publisher/admin smoke | `NEXT` | Add one narrow smoke or contract per completed route cluster. |
| Member management UI | `NOT_IN_SCOPE` | External IdP responsibility. |

## Capability Contract

| Capability | Minimum role | Intended use |
| --- | --- | --- |
| `project.read` | `viewer` | Project detail and read-only project metadata views. |
| `project.edit` | `editor` | Project metadata, table metadata, data class metadata, DB access metadata, custom proxy metadata, and HTML metadata writes. |
| `source_output.publish` | `publisher` | Generating or publishing source output artifacts. |
| `source_output.download` | `publisher` | Downloading source output archives. |
| `db_source.manage` | `admin` | Creating, updating, or deleting database source configuration. |
| `secret.manage` | `admin` | Creating, updating, or deleting secret-backed configuration. |

## Planned Order

### 1. Route Capability Inventory

Status: `NEXT`

Create a route-to-capability table for authenticated project routes. This step must not change runtime behavior.

Completion line:

- authenticated project routes are listed;
- each route has a proposed capability;
- ambiguous routes are marked with a short reason;
- no route enforcement changes are included in the inventory commit.

### 2. Read-Only Enforcement

Status: `NEXT`

Apply `project.read` to low-risk project view routes.

Initial candidates:

- project detail;
- source output list/detail read views;
- table list/detail read views;
- data class list/detail read views;
- DB access list/detail read views.

Completion line:

- viewer can open read-only routes;
- unrelated principal is denied;
- existing invalid project key / missing project behavior remains readable as the same class of response where practical;
- contract test covers allowed and denied decisions.

### 3. Edit-Route Enforcement

Status: `NEXT`

Apply `project.edit` to metadata write routes by cluster.

Route clusters:

- project settings;
- project tables and columns;
- project data classes and fields;
- project DB access metadata;
- custom proxy metadata;
- HTML metadata.

Completion line:

- editor can perform the intended write;
- viewer cannot perform the write;
- denied write records an audit event;
- each route cluster lands in its own commit or tightly related pair of commits.

### 4. Source Output Permission Consistency

Status: `NEXT`

Source output publish/download already requires `publisher` or stronger. This step checks the surrounding source output routes for consistent boundaries.

Completion line:

- read/list/detail routes are classified;
- edit/write/publish/download operations have separate capabilities;
- existing publish/download tests remain green;
- any newly enforced source output route uses audited permission decisions.

### 5. Strong Capability Enforcement

Status: `NEXT`

Apply stronger-than-editor rights only where the operation changes external connectivity or secret-backed configuration.

Completion line:

- database source management requires `db_source.manage`;
- secret-backed configuration requires `secret.manage`;
- editor role is denied for these operations;
- admin role is allowed;
- audit event records the capability and result.

### 6. Smoke / Contract Expansion

Status: `NEXT`

Add narrowly scoped verification after each route cluster, then one representative broader smoke if needed.

Minimum coverage:

- viewer can read but cannot write;
- editor can edit metadata but cannot publish;
- publisher can publish/download source output;
- admin can manage database source / secret-backed configuration.

## Commit Policy

Status: `NEXT`

- Commit route inventory separately from behavior changes.
- Commit read-only enforcement before write enforcement.
- Keep each route cluster in a small commit.
- Include tests or smoke updates in the same commit as the enforcement they verify.
- Do not combine this phase with SSO, member management, SCIM, invitation, or IdP UI work.
