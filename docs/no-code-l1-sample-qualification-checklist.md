# No-Code L1 Sample Qualification Checklist / No Code L1 sample認定checklist

English companion:
Use this permanent checklist to decide whether a sample is a bounded L1 no-code entry and whether a second sample proves reusable screen, action, authority, execution, and test contracts.

この恒久checklistは、sampleをbounded L1 No Code entryとして認定できるか、第2 sampleがscreen・action・authority・execution・test contractの再利用性を実証できるかを判断する基準です。

## Purpose

Use this checklist to decide whether a sample is a bounded L1 no-code entry. Qualification is evidence-based and slice-specific. It does not imply full CRUD, replacement of an existing hand-coded route, or default production enablement. The product objective is complete support for the declared no-code capability matrix, not complete conversion of every sample or application. Generated/no-code and custom portions may coexist permanently.

このchecklistが求めるのは、宣言したNo Code capability matrixに対する完全なsupport evidenceであり、全sample・全applicationの完全変換ではありません。generated・No Code部分とcustom部分の恒久的な共存を正規の構成として扱います。

## Required qualification areas

| Area | Required evidence |
| --- | --- |
| Sample boundary | Representative domain, stable seeded/reference data, and explicit in-scope/out-of-scope behavior. |
| Shared schema | Shared contract identifies physical/generated fields, keys, types, nullable/default behavior, and editable versus system-owned fields. |
| Screen shape | Generated list, detail, and form artifacts use the same screen-definition contract. |
| Read behavior | Runtime rows, selection, filtering/sorting/pagination as applicable, and fail-closed invalid queries are covered. |
| Action contract | Operation key/type, key/input/filter roles, required inputs, authorization metadata, readiness, and submit boundary are explicit. |
| Static safety | Static artifact preview has no execution authority; unavailable or stale state fails closed. |
| Approved selectors | Current/alias identity is bound to an immutable approved artifact and verified in browser or equivalent integration coverage. |
| Authorization | Authentication, required roles/scopes/claims, CSRF where applicable, and denied behavior are tested. |
| Execution authority | Any executable UI slice has a separate default-off gate and narrow action allowlist; read-only availability alone is not authority. |
| Mutation outcome | Real server-side processing proves the qualified action's success result and a credible failure/recovery boundary. Same-database composite work must use Transaction Full. |
| Test pyramid | Fast JSON/DOM contracts are primary; browser and real endpoint/processing smokes are representative outer gates. |
| Explicit exclusions | Non-qualified actions, route replacement, default enablement, cross-store atomicity, and custom components are named rather than implied. |

## Reuse comparison

A second sample supports G-L2 only when it reuses the common contracts without copying sample-specific adapters. Record:

- which screen/schema/action/authority/test components are unchanged;
- which differences are domain metadata or field types;
- which differences require a reusable platform extension;
- whether the action uses direct guarded execution, asynchronous outbox processing, or another explicit execution model;
- whether success/failure semantics remain compatible with `docs/execution-success-policy.md`.

## Decision values

- `QUALIFIED`: every required area for the declared slice has evidence.
- `QUALIFIED_WITH_EXCLUSIONS`: all requirements pass and named actions/features remain outside the slice.
- `ONE_GAP`: exactly one bounded missing implementation/test unit prevents qualification.
- `NOT_READY`: multiple foundational gaps remain; return the sample to candidate status.

## Non-goals

- Do not optimize for a 100% generated application; automate the supported 80-90% class of repeated work and expose the remaining custom boundary clearly.
- Do not enable all generated actions to make a sample look complete.
- Do not treat a stubbed browser POST as database mutation evidence.
- Do not require a hand-coded route to be removed.
- Do not treat generated/custom coexistence as an incomplete migration when it is the declared design.
- Do not claim one transaction across separate application/config stores.
- Do not add browser coverage where a fast contract proves the same boundary more clearly.
