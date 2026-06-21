# Generation Plan / 生成計画

Status: `DRAFT`

## First Pass / first pass

1. Register the PostgreSQL user database as an import source.
2. Import schema metadata for `accounts`, `subscriptions`, and `usage_events`.
3. Sync DataClass metadata.
4. Draft DBAccess metadata for the subscription summary read model.
5. Publish actual generated output under `reference/` after a concrete Mtool run exists.

## Output Boundaries / 出力境界

| Output | Scope / 範囲 |
| --- | --- |
| DataClass | One generated class per table. |
| DBAccess | Account subscription summary read path first. |
| OpenAPI | Later, after DBAccess read model is stable. |
| AI context | Out of scope; tracked in `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md`. |
| Audit | Out of scope; tracked in `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md`. |
| PostgreSQL config store | Out of scope. |

## First Read Model / 最初の read model

```json
{
  "account_key": "acct-example",
  "display_name": "Example Operations",
  "subscription_key": "sub-example-pro",
  "plan_code": "pro",
  "status": "active",
  "usage_event_count": 2,
  "last_usage_at": "2026-06-21T10:30:00Z"
}
```

The generated path should avoid exposing `billing_email` and raw `event_payload` by default.
