# Review Workflow Route Persistence Helper Lane Closure

Date: 2026-07-08

Status: `DONE`

## Summary

#478 closes the review workflow persistence helper lane.

Accepted capability now includes repository-first storage, accepted-plan route-local persistence, duplicate reuse for open requests, audit metadata carry-through, and result-page review request persistence status.

Default dogfooding availability remains deferred, so generated operator buttons remain disabled and the default route path still does not create review requests.

## Accepted Capability

- Config DB table: `no_code_review_requests`.
- Repository API for create-or-reuse and latest fetch.
- Open request reuse for `requested` and `in_review`.
- Route-local persistence helper for allowed `accepted_plan` only.
- Blocked/deferred guard results skip persistence and keep the no-mutation message.
- Accepted persistence changes audit result to `accepted` or `duplicate` and adds `review_request_key`.
- Result page can show `skipped`, `recorded`, `duplicate`, or `failed`.

## Still Parked

- Changing `review_source_output_artifact` availability to `available`.
- Generated button execution.
- Browser-driven route mutation against default dogfooding metadata.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.

## Next Decision

Before enabling availability, review the unpushed local stack. The stack now includes metadata carry-through, guard routing, audit append, repository persistence, and route-local accepted-plan persistence. A push/squash/replan decision should happen before adding a user-visible executable workflow.

## Verification

#478 is docs-only.

- `git diff --check`

## Next Candidate

#479 should review the unpushed local commit stack and decide whether to keep it as-is, squash selected slices, push, or replan before availability enablement.
