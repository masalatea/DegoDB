# Sample18 L1 No-Code Qualification

Status: `DONE_QUALIFIED`

## Decision

Sample18 qualifies as the first bounded L1 no-code entry and satisfies bridge step B8 and roadmap gate G-L1.

The qualified unit is the generated task-card runtime with read surfaces plus one explicit/default-off create operation. Qualification does not claim full CRUD, replacement of the hand-coded task-board route, or default production enablement.

## Qualification matrix

| Area | Evidence | Decision |
| --- | --- | --- |
| Golden comparison | Stable hand-coded route, seed rows, and `golden/no-code-ui-golden.json`. | Pass |
| Generated screen shape | List, detail, and form screen definitions and runtime artifacts. | Pass |
| Read behavior | Golden rows, stable field markers, selected-row identity, status filtering, pagination/filter runtime contracts. | Pass |
| Field contract | Create inputs are editable; status and system-owned fields remain readonly. | Pass for create slice |
| Action metadata | Create/update/complete metadata and route/readiness contracts exist; reopen/delete remain non-route-compatible. | Pass with exclusions |
| Execution authority | Separate default-off UI flag, create-only allowlist, authenticated live availability, immutable selector/artifact identity, CSRF and guarded POST. | Pass for create only |
| Browser integration | Current and alias previews each observe live create availability and issue exactly one stubbed create POST; excluded complete issues zero POSTs. | Pass |
| Real mutation safety | Authenticated guarded HTTP success commits one MariaDB row; failure-after-SQL rolls back and leaves zero rows. | Pass |
| Fast regression | PHPUnit JSON/DOM/runtime contracts cover the generated artifact and authority boundaries. | Pass |
| Route replacement | Existing hand-coded route remains the golden reference and is not replaced. | Intentionally out of scope |

## Why G-L1 passes

G-L1 asks for one representative sample UI to be generated, inspected, and operated through no-code metadata rather than hand-coded UI assumptions.

Sample18 meets that bounded requirement:

- the generated runtime is produced from shared contract, managed operation, screen-definition, and source-output metadata;
- generated list/detail/form artifacts are inspected by fast and browser tests;
- create input and action intent are assembled from generated metadata;
- browser authority is derived from authenticated live availability and selector identity;
- the real guarded route executes the generated DBAccess call with Transaction Full behavior.

The browser POST is stubbed only to isolate UI authority. Its real transaction behavior is proven separately at the HTTP route boundary. Together these are one evidence ladder, not a claim that a stub performed the database write.

## Explicit exclusions

- `update_task_card`, `complete_task_card`, `reopen_task_card`, and `delete_task_card` are not qualified executable generated actions.
- The generated UI is not enabled by default.
- Static artifact previews receive no execution authority.
- The hand-coded task-board route is not removed or redirected.
- Cross-store config/audit persistence remains a recovery domain rather than distributed atomicity.

## Reusable first-entry pattern

The Sample18 pattern suitable for comparison is:

1. stable golden fixture and hand-coded reference;
2. shared contract plus list/detail/form metadata;
3. fast JSON/DOM contracts before browser coverage;
4. managed action metadata with field/key/readiness contracts;
5. authenticated selector-bound availability;
6. separate default-off UI authority and narrow allowlist;
7. browser authority proof separated from real HTTP transaction proof;
8. explicit exclusions instead of implied full CRUD.

## Next

#748 should extract this pattern into a comparison checklist and select one second sample. The second sample should prove reuse of the pattern, not copy Sample18-specific task-card code or immediately broaden execution.
