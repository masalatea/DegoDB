# Generated SSO Resolver Contract Gate

## Outcome

Plan #863B adds a generation-specific gate after canonical schema readiness. It preserves the #862E schema result and separately proves that the exact DBAccess operations required by the generated composite resolver exist with the expected action types.

The gate derives class and function names from the configured schema roles through the existing generated-name policy. It does not select the first `select`, `insert`, or `update` function and does not store function wiring in the SSO policy.

## Fail-closed cases

No artifact is emitted when:

- canonical schema status is not `generation_ready`;
- any of the six standard operations is missing;
- an operation name is ambiguous within its class;
- an operation has a different action type;
- the policy is invalid.

## Artifact

When every gate passes, Mtool emits deterministic PHP text containing a versioned, non-secret resolver contract and exact operation map. The artifact is currently a contract boundary, not yet the executable JIT resolver. It contains no token, credential, or client-secret material.

## Evidence and next step

Focused tests prove schema-blocked and missing-operation behavior plus deterministic output for the exact default-role operation set. The full integration suite passed with 541 tests and 14854 assertions; one existing test is skipped.

Plan #863C next binds this contract to one qualified generated DBAccess fixture and implements the executable composite resolver with first-login creation, repeat-login restoration, shared transaction ownership, full rollback, and server-owned canonical actor output.
