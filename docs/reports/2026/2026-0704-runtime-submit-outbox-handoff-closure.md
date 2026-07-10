# Runtime Submit / Outbox Handoff Closure

Status: `FIRST_SLICE_DONE`

Push: not performed.

## Summary

This closes the current server-backed runtime submit / sync outbox handoff lane for the no-code minimum path.

The lane now proves the first useful end-to-end shape:

- generated public runtime submit can call the authenticated current / alias execution endpoint
- the endpoint can create a managed-operation sync intent and enqueue pending outbox work
- the runtime UI reports that the accepted work is pending
- the runtime UI exposes the accepted outbox item id, operation key, and operator detail path
- sample28 smoke processing proves the queued work can be completed through generated server DBAccess against isolated SQLite data
- the operator sync outbox detail page explains pending, running, done, and failed handoff states without doing inline processing

## Accepted Capability

The accepted capability is not synchronous business-row mutation from the browser. The accepted capability is a traceable handoff:

1. User submits from the generated no-code runtime.
2. Server accepts the request under authenticated current / alias runtime routes.
3. Server enqueues managed-operation sync work.
4. Browser feedback tells the user where the work went.
5. Operator inspection explains what must process the work next.
6. Focused smoke coverage proves the queued payload can update data through generated DBAccess.

This keeps the product behavior honest: public runtime submit is server-backed and testable, while the processing model remains outbox-based.

## Verification Baseline

Latest implementation slices in this lane were verified with:

- `make sample28-no-code-public-runtime-browser-smoke`
- `php -l mtool/app/project_sync_outbox_detail_page.php`
- focused `NoCodeOperatorSyncInspectionTest`
- focused `OpenApiSourceOutputContractTest`
- `make test`

This closure is docs-only. The local verification for this closure commit is `git diff --check`.

## Remaining Candidates

These remain useful, but they should start from a fresh priority decision:

- Live UI result refresh after submit, for example polling or explicit refresh guidance tied to the outbox item.
- Synchronous endpoint processing for a narrow local/demo-only path, if the product chooses direct feedback over outbox honesty.
- Runtime retry mutation from a failed outbox item, with authorization, idempotency, and audit boundaries.
- A second sample that exercises a different operation shape through the same submit/outbox/operator handoff.
- Broader operator workflow polish around batch processors, filters, and retry queues.

## Next Recommendation

Choose a fresh no-code product-goal replan before adding more code.

Recommended candidates:

1. Live UI result refresh / result follow-up for the generated runtime.
2. Another scenario sample using the now-traceable handoff.
3. Commit stack review / push cleanup, because the local branch is intentionally ahead of `origin/develop`.

