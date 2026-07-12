# Mtool Source Output Hybrid Contract Lane Closure

## Status

`DONE`

## Result

#797 added the first Mtool contained hybrid product contract:

- `app_no_code_mtool_source_output_inspection_hybrid_contract()`;
- stable HTML marker: `data-mtool-no-code-hybrid-contract="true"`;
- generated/custom/adapter/authority/rollback/exclusion boundaries;
- focused tests proving the contract and preserving no generated execution.

The slice is accepted.

## Closure decision

Promote browser evidence next.

Reason:

- The contract is now visible in HTML, not just PHP helper output.
- The prior browser promotion already proved the default-off/enabled route, live rows, canonical return, and zero POST behavior.
- The new risk is small and specific: confirm the real admin browser path sees the hybrid contract marker while preserving the existing zero-execution guarantees.

Entry-point hardening and broader productization should wait until the browser-visible contract is proven.

## #799 boundary

#799 should verify:

- unauthenticated behavior still redirects to login;
- flag-off state still returns not found;
- enabled admin route renders the inspection page;
- the page contains `data-mtool-no-code-hybrid-contract="true"`;
- parsed contract version is `no-code-mtool-source-output-inspection-hybrid-v0`;
- generated execution controls remain absent;
- inspection POST count remains zero except normal login POST;
- rollback by unsetting `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED` still hides the route.

## Non-goals

#799 must not add:

- mutation;
- a new route;
- canonical Source Outputs page replacement;
- review request persistence;
- build/publish/edit/delete/reorder behavior;
- broader public/lab/current/alias exposure.

If browser evidence reveals a missing marker or route issue, fix only that issue and keep the same scope.
