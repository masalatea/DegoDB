# Authorization Hardening Plan

Status: `I/F_BASELINE_DONE / PARKED_MINIMAL`

English companion:
This document is the current plan for applying project permissions across Mtool routes after SSO completion. SSO is done; this phase uses the authenticated principal and project roles to decide whether each route or operation is allowed.

## Boundary

Status: `NEXT`

This phase is not SSO implementation. It starts after the SSO boundary documented in `docs/internal/sso-oidc-connection.md`.

This phase is primarily for Mtool itself: admin / lab routes, project metadata screens, source output operations, database source settings, and secret-backed configuration. It is not the main security lane for generated runtime code.

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

Generated runtime security is a separate lane:

- generated proxy API auth v2;
- static bearer policy;
- OpenAPI bearer security scheme;
- generated proxy fail-closed behavior;
- generated runtime browser / HTTP smoke.

This plan may protect the Mtool operations that create, publish, download, or configure generated output. It does not redesign every emitted application's own authorization model.

## Status Labels

| Label | Meaning |
| --- | --- |
| `NEXT` | Planned next work. |
| `IN_PROGRESS` | Actively being implemented. |
| `DONE` | Implemented, documented, and verified. |
| `PARKED` | Explicitly deferred. |
| `PARKED_MINIMAL` | Interface and path are ready; broad enforcement is intentionally deferred. |
| `NOT_IN_SCOPE` | Not part of this phase. |

## Current Status

| Area | Status | Notes |
| --- | --- | --- |
| SSO / OIDC login | `DONE` | Completed in `docs/internal/sso-oidc-connection.md`. |
| Route capability inventory | `DONE` | Route cluster inventory is documented below; no behavior change. |
| Route authorization I/F | `DONE` | `mtool/app/project_route_authorization.php` fixes route-name / method / capability contract before broad enforcement. |
| Read-only project route enforcement | `PARKED_MINIMAL` | Path is ready, but broad enforcement is deferred until real deployment/user separation needs appear. |
| Edit route enforcement | `PARKED_MINIMAL` | Route clusters are identified; avoid large 403/404 behavior churn for now. |
| Source output permission consistency | `PARKED_MINIMAL` | Publish/download already have enforcement; surrounding route hardening can wait. |
| Strong capability enforcement | `PARKED_MINIMAL` | Apply later when DB source / secret operations need real multi-user separation. |
| Audit coverage expansion | `PARKED_MINIMAL` | Audit shape exists; expand only with newly enforced routes. |
| Viewer/editor/publisher/admin smoke | `PARKED_MINIMAL` | Contract smoke exists for I/F; route smoke waits for enforcement work. |
| Member management UI | `NOT_IN_SCOPE` | External IdP responsibility. |

## Minimal Baseline Decision

Status: `DONE`

For the current phase, Mtool's own authorization hardening stops at the I/F baseline:

- SSO and principal shape are complete;
- project role / capability contract exists;
- audited permission decisions exist;
- route capability inventory exists;
- `mtool/app/project_route_authorization.php` fixes the route requirement I/F.

Broad route-by-route enforcement is intentionally parked. This keeps the path open for enterprise deployment later without spending the current sample/runtime security budget on premature admin UI behavior changes.

## Capability Contract

| Capability | Minimum role | Intended use |
| --- | --- | --- |
| `project.read` | `viewer` | Project detail and read-only project metadata views. |
| `project.edit` | `editor` | Project metadata, table metadata, DataClass metadata, DB access metadata, custom proxy metadata, and HTML metadata writes. |
| `source_output.publish` | `publisher` | Generating or publishing source output artifacts. |
| `source_output.download` | `publisher` | Downloading source output archives. |
| `db_source.manage` | `admin` | Creating, updating, or deleting database source configuration. |
| `secret.manage` | `admin` | Creating, updating, or deleting secret-backed configuration. |

## Route Capability Inventory

Status: `DONE`

This inventory is intentionally cluster-based. It records the target capability for route groups before behavior changes are introduced. Method-sensitive routes should keep GET/read behavior separate from POST/write behavior when enforcement is implemented.

The first code I/F for this inventory is `mtool/app/project_route_authorization.php`. It records a small route-name / method / capability contract and deliberately does not enforce new routes yet.

### Global / Site Routes

| Route name | Proposed capability | Status | Notes |
| --- | --- | --- | --- |
| `dashboard` | site role only | `DONE` | Not project-scoped. Keep current `admin` / `config` / `lab` site-role behavior. |
| `projects` | site role only | `DONE` | Project list is not a single-project permission decision. |
| `experiments` | site role only | `DONE` | Lab-side route; not part of project authorization hardening first slice. |
| `html_templates` / `html_template_detail` / `html_template_parameters` | site role only | `DONE` | Global template settings, not project-scoped. |
| `database_sources` | `db_source.manage` | `NEXT` | Global admin setting. GET may remain admin/config-readable, but create/update/delete should require `db_source.manage`. |

### Project Read Routes

| Route cluster | Route names | Proposed capability | Status | Notes |
| --- | --- | --- | --- | --- |
| project overview | `project_detail` | `project.read` | `NEXT` | First low-risk read enforcement candidate. |
| source output read views | `project_source_outputs`, `project_source_output_detail` | `project.read` | `NEXT` | Separate from publish/download. |
| table read views | `project_tables`, `project_table_detail`, `project_table_columns` | `project.read` | `NEXT` | Column edit routes are not included here. |
| data class read views | `project_data_classes`, `project_data_class_detail`, `project_data_class_fields`, `project_data_class_source` | `project.read` | `NEXT` | Sync/edit routes are write routes. |
| DB access read views | `project_db_access`, `project_db_access_detail`, `project_db_access_functions`, `project_db_access_function_detail`, `project_db_access_source`, `project_db_access_function_source`, `project_db_access_function_endpoint` | `project.read` | `NEXT` | Function endpoint preview is read-only unless POST behavior is added later. |
| custom proxy read views | `project_custom_proxies`, `project_custom_proxy_detail`, `project_custom_proxy_endpoint`, `project_custom_proxy_functions` | `project.read` | `NEXT` | POST on detail/functions remains write. |
| HTML read views | `project_htmls`, `project_html_detail`, `project_html_parameters` | `project.read` | `NEXT` | POST on detail/parameters remains write. |
| language resource read views | `project_language_resources`, `project_language_resource_detail`, `project_language_resource_groups` | `project.read` | `NEXT` | Current routes are GET-only. |
| security read views | `project_security`, `project_security_users`, `project_security_pages`, `project_host_assignments` | `project.read` | `NEXT` | POST writes should require stronger capability. |

### Project Edit Routes

| Route cluster | Route names | Proposed capability | Status | Notes |
| --- | --- | --- | --- | --- |
| project settings | `project_settings` | `project.edit` | `NEXT` | GET can be read, POST should require edit. |
| table metadata writes | `project_tables_import`, `project_table_edit`, `project_table_column_edit` | `project.edit` | `NEXT` | Import apply / table / column mutations. Preview-only GET should preserve current behavior. |
| DataClass metadata writes | `project_data_classes_sync`, `project_data_class_edit`, `project_data_class_field_edit` | `project.edit` | `NEXT` | Sync and metadata edits. |
| DB access metadata writes | `project_db_access_sync`, `project_db_access_edit`, `project_db_access_function_change_order`, `project_db_access_function_move`, `project_db_access_function_select_where_edit`, `project_db_access_function_select_where_change_order`, `project_db_access_function_select_target_field_edit`, `project_db_access_function_select_having_edit`, `project_db_access_function_update_delete_where_edit`, `project_db_access_function_update_delete_where_change_order`, `project_db_access_function_insert_target_field_edit`, `project_db_access_function_update_target_field_edit` | `project.edit` | `NEXT` | Large cluster; split into smaller commits by function sub-area. |
| custom proxy metadata writes | `project_custom_proxies`, `project_custom_proxy_detail`, `project_custom_proxy_functions` | `project.edit` | `NEXT` | Apply on POST only; GET remains read. |
| HTML metadata writes | `project_htmls`, `project_html_detail`, `project_html_parameters` | `project.edit` | `NEXT` | Apply on POST only; GET remains read. |
| compare output settings | `project_compare_output_settings`, `project_compare_output_additional_paths` | `project.edit` | `NEXT` | Settings writes; GET remains read. |
| single proxy setup | `project_single_proxy` | `project.edit` | `NEXT` | Metadata write route backed by DB access/source output settings. |

### Source Output Routes

| Route names | Proposed capability | Status | Notes |
| --- | --- | --- | --- |
| `project_source_output_new`, `project_source_output_edit`, `project_source_output_change_order` | `source_output.publish` or `project.edit` | `NEXT` | Needs method-level decision. Metadata edit can be `project.edit`; generate/publish actions stay `source_output.publish`. |
| `project_source_output_download` | `source_output.download` | `DONE` | Already enforced with audited permission decision. |
| source output publish path in route bootstrap | `source_output.publish` | `DONE` | Existing source output route bootstrap requires publisher or stronger for publish-oriented routes. |

### Strong Capability Routes

| Route cluster | Route names | Proposed capability | Status | Notes |
| --- | --- | --- | --- | --- |
| database source management | `database_sources` | `db_source.manage` | `NEXT` | Applies to create/update/delete. GET may remain admin/config-readable. |
| project security membership compatibility UI | `project_security_users`, `project_security_pages`, `project_host_assignments` | `project.admin` or `secret.manage` candidate | `NEXT` | Ambiguous because legacy membership is compatibility-only. Inventory marks this for separate design before enforcement. |
| secret-backed configuration | to be identified | `secret.manage` | `NEXT` | No broad route assignment until concrete secret-backed mutations are listed. |

### Lab Runtime Routes

| Route cluster | Route names | Proposed capability | Status | Notes |
| --- | --- | --- | --- | --- |
| lab build / swagger / proxy / compare / endpoint routes | `lab_build`, `lab_build_job`, `lab_build_job_api`, `lab_swagger`, `lab_published_single_proxy`, `lab_compare_output`, `lab_compare_output_job`, `lab_compare_output_job_api`, `lab_endpoint`, `lab_endpoint_job_api` | `PARKED` | `PARKED` | Lab runtime authorization should be planned separately after admin project route hardening. |

## Planned Order

### 1. Route Capability Inventory

Status: `DONE`

Create a route-to-capability table for authenticated project routes. This step must not change runtime behavior.

Completion line:

- authenticated project routes are listed;
- each route has a proposed capability;
- ambiguous routes are marked with a short reason;
- no route enforcement changes are included in the inventory commit.

### 2. Read-Only Enforcement

Status: `PARKED_MINIMAL`

Prerequisite I/F: `DONE`

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
