# AI Generation Review / AI 生成可否レビュー

English companion:
Use this contract when deciding whether Mtool should generate DBAccess/runtime output for a user DB case, or whether the behavior should live in an inherited class, custom proxy, custom runtime, or handwritten repository.

この文書は、ユーザー DB の処理を Mtool generator で出すべきか、継承先 class / custom proxy / custom runtime / handwritten repository に委ねるべきかを AI が判定するための恒久 contract です。

## Classification

AI は各 candidate を次のいずれかに分類する。

| Classification | Meaning |
| --- | --- |
| `generated` | Mtool の標準 generator で生成すべき。 |
| `generated_with_options` | generator で生成できるが、dialect、pagination、null handling、naming などの option 指定が必要。 |
| `inherited_custom` | generated base class を出した上で、継承先 class に個別実装すべき。 |
| `manual_runtime` | generator の責務外として、custom proxy / custom runtime / handwritten repository に委ねるべき。 |
| `needs_design_review` | schema、relation、key、transaction boundary が曖昧で、生成判断前に設計確認が必要。 |

## Review Inputs

最低限見るもの:

- project key / source output key
- target DB dialect
- table / column metadata
- primary key / unique key / nullable / default
- relation / join requirement
- expected API or screen behavior
- existing SQL, if any
- runtime constraints
- security / auth requirements
- expected test or smoke path

## Decision Criteria

生成器に寄せやすいもの:

- table / column metadata から自然に導ける CRUD
- simple SELECT / where / order / pagination
- common join / aggregate
- scalar prepared parameters
- MySQL / MariaDB と SQLite で dialect mapping が説明できる処理
- generated base class contract を壊さない処理

継承先または custom runtime に逃がすもの:

- blob / file binding strategy
- vendor-specific SQL function
- stored procedure / trigger-dependent behavior
- complex transaction / lock / upsert conflict policy
- advanced JSON path / full-text / geospatial / window function
- generated code の読みやすさや安全性を壊す汎用化

## Review Artifact Template

```md
# AI Generation Review: <project>/<source-output>/<candidate>

## Summary

- Classification:
- Confidence:
- Target dialects:
- Recommended implementation:

## Inputs Reviewed

- Tables:
- Functions / methods:
- Existing SQL:
- Expected runtime behavior:
- Security / auth:

## Generation Decision

Explain why this should be generated, generated with options, implemented in an inherited class, handled by manual runtime, or sent back to design review.

## Dialect Notes

| Dialect | Status | Notes |
| --- | --- | --- |
| MySQL / MariaDB |  |  |
| SQLite |  |  |
| PostgreSQL | future |  |
| SQL Server | future |  |

## Generator Options

- Option:
- Value:
- Reason:

## Inherited / Custom Implementation Notes

- Class:
- Method:
- Responsibility:
- Inputs:
- Outputs:

## Test / Smoke Expectations

- Unit / contract test:
- Runtime output test:
- HTTP smoke:
- Fixture / seed:

## Risks

- Risk:
- Mitigation:

## Open Questions

- Question:
```

## Policy

- AI review is advisory by default.
- Complex behavior must not be auto-applied without human review.
- MySQL / MariaDB and SQLite must be considered together when a generated output path claims dual-dialect support.
- If one dialect can be generated and another needs custom implementation, record the fallback explicitly.
