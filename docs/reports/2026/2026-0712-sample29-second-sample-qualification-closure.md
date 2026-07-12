# Sample29 Second-Sample Qualification Closure

Status: `DONE_QUALIFIED_WITH_EXCLUSIONS`

## Decision

Sample29 is `QUALIFIED_WITH_EXCLUSIONS` as the second bounded L1 no-code sample. The reusable-pattern gate G-L2 is satisfied.

The only gap identified by #749 was production-shaped UI execution authority. #750 through #753 closed it with a reusable project/action policy, explicit execution-model capability diagnostics, authenticated live current/alias availability, and a real managed-outbox enqueue without test-forced enablement.

## Qualification checklist

| Area | Sample29 evidence | Result |
| --- | --- | --- |
| Sample boundary | Stable support-case seed and bounded `update_support_case` slice. | Pass |
| Shared schema | Shared key/read-only/editable field metadata with required inputs. | Pass |
| Screen shape | Common generated list, detail, and form definitions. | Pass |
| Read behavior | Current/alias rows, selection, filters, search, sort, pagination, and invalid-query fail-closed coverage. | Pass |
| Action contract | Keyed update operation, field roles, editor role, `support_case:write`, readiness, and outbox metadata. | Pass |
| Static safety | Artifact preview has no authority; unavailable states fail closed. | Pass |
| Approved selectors | Current and custom alias resolve approved immutable artifact identity. | Pass |
| Authorization | Authenticated scoped availability plus denied/default-off behavior. | Pass |
| Execution authority | Global default-off gate, narrow `SAMPLE29:update_support_case` allowlist, matching live `managed_operation_outbox` model. | Pass |
| Mutation outcome | Real pending enqueue, generated DBAccess processing success, and shared failed/requeue/retry recovery evidence. | Pass |
| Test pyramid | Fast PHP contracts, browser current/alias integration, endpoint, and processing smokes. | Pass |
| Explicit exclusions | Named below. | Pass |

## Reuse comparison with Sample18

Unchanged common contracts include generated screen/schema artifacts, action-intent drafts, project/action allowlisting, authenticated selector-bound availability, static non-authority, current/alias delivery, and the fast/browser/real-endpoint test layering.

The domain metadata differs: Sample18 creates task cards, while Sample29 updates a keyed support case with role/scope requirements. The execution model also differs explicitly: Sample18 uses a direct guarded route with `transaction_full_v1`; Sample29 uses durable asynchronous enqueue with `managed_outbox_v1`. This difference is handled by the shared availability model rather than a copied sample adapter.

Both conform to the success policy: direct guarded success means the required same-request transaction committed, while managed-outbox submission success means durable acceptance as pending and exposes later completion/failure recovery separately.

## Explicit exclusions

- Sample29 UI execution remains default-off outside its dedicated smoke configuration.
- Qualification covers only `update_support_case`, not every generated action or full CRUD.
- Pending enqueue is not described as completed downstream mutation.
- The asynchronous outbox path is not described as one transaction across request and worker processing.
- Qualification does not replace hand-coded routes or introduce custom components.

## G-L2 conclusion

Two different domains now demonstrate the reusable screen/action/schema pattern with shared authority and test contracts, while supporting two explicit execution models. G-L2 is complete.

## Next

#755 inventories contained Mtool admin/lab workflows and selects one low-risk G-L3 dogfooding candidate with an explicit rollback boundary before implementation.
