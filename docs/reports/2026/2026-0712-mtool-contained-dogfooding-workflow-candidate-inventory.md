# Mtool Contained Dogfooding Workflow Candidate Inventory

Status: `DONE`

## Decision

Select a read-only Mtool Source Output inspection workflow as the first contained G-L3 candidate.

The repository already has an MTOOL dogfooding screen contract for Source Output review, generated list/detail/form artifacts, configured presentation, related-settings/status/action slots, and inspection tests. The missing step is to feed that contract from real Mtool repository data on a parallel operator surface. This advances from a fixture-shaped probe to genuine self-use without making generated UI canonical or enabling mutation.

## Candidate comparison

| Candidate | Write/side-effect risk | Existing reusable boundary | Operator value | Decision |
| --- | --- | --- | --- | --- |
| Source Output live inspection | None when kept read-only | Existing MTOOL dogfooding contract, Source Output repository, generated list/detail/form and extension slots | Reviews output identity, type, artifact strategy, binding, visibility, and directory in one generated surface | **Select first** |
| Review artifact request | Config DB review request plus audit append; atomic ownership needs an explicit decision | Existing custom-operation preflight, repository, duplicate reuse, route-local persistence | Useful review handoff | Defer until after read-only dogfooding; not the first mutation |
| Managed sync/outbox inspection | Read-only inspection is possible, but state/retry concepts add workflow-specific UI | Existing operator sync inspection and outbox repositories | Strong operational value | Candidate after the first live read adapter is proven |
| Project tables/fields | Editing quickly introduces schema mutation and validation risk | Mature repositories and admin routes | High | Defer mutation; too broad for first G-L3 slice |
| DBAccess functions/managed operations | Multi-record configuration and execution-policy risk | Rich metadata/repositories | High | Defer until read-only self-use is stable |
| Lab build/publish | Filesystem, process, artifact, and publication side effects | Existing job services | High | Reject as first candidate |
| Dashboard/project overview | Low risk | Existing read repositories | Moderate | Safe but too shallow to pressure the no-code contract |

## Selected bounded workflow

The first workflow is operator inspection of real Mtool Source Output rows through the existing no-code screen/schema contract:

- authenticated Mtool admin/lab user opens a parallel generated inspection route;
- the route reads existing Source Output repository data;
- list and detail views expose the already-declared identity/artifact fields;
- related settings and artifact-status slots remain navigational/read-only;
- custom operations remain disabled and issue no POST;
- empty and repository-error states fail closed and show no fabricated rows.

## Why this is not duplicate work

The earlier #431–#440 lane proved metadata, emitted artifact shape, presentation, slots, and inspection summaries using the MTOOL dogfooding fixture. It did not establish a live operator route backed by current repository rows. #755 selects that live read connection as the next distinct boundary.

## Safety and rollback

- The new surface must be parallel and default-off; existing Source Output admin pages remain canonical.
- No create/update/delete, review-request persistence, audit append, build, publish, or generated-button execution belongs to the first slice.
- Authorization must reuse existing Mtool admin/lab guards rather than introduce a public route.
- Rollback is deterministic: disable the route switch and remove its optional navigation entry. No data migration or compensating write is required.

## Evidence expected before G-L3 qualification

- Fast contract proves repository rows map to declared fields without changing the shared schema contract.
- Authorization and default-off route behavior fail closed.
- Empty/error states produce zero mutation and no stale fallback.
- One representative browser smoke covers the authenticated parallel route and navigation back to canonical settings.
- Existing admin workflow remains unchanged and independently usable.

## Next

#756 is implementation-free preflight. It must identify the exact repository API, row normalization, route/auth guard, selectors, default-off switch, browser path, and rollback check before code changes.
