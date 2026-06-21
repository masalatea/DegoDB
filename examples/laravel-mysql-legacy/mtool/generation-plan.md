# Generation Plan / 生成計画

Status: `DRAFT`

## First Pass / first pass

1. Import the MySQL schema into canonical metadata.
2. Sync DataClass metadata.
3. Sync DBAccess metadata for safe read paths.
4. Publish DataClass and DBAccess reference output.
5. Add behavior parity checks.

## Output Boundaries / 出力境界

| Output | Scope / 範囲 |
| --- | --- |
| DataClass | One generated class per table. |
| DBAccess | Ticket list/detail and comment read paths first. |
| OpenAPI | Later, after DBAccess read model is stable. |
| AI context | Out of scope; tracked in `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md`. |
| Audit | Out of scope; tracked in `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md`. |
| Laravel app | Out of scope for the first baseline. |

## Public Ticket Detail Read Model / public ticket detail read model

The first behavior parity target is a customer-facing ticket detail shape:

```json
{
  "ticket_key": "TICKET-1001",
  "subject": "Cannot access billing page",
  "status": "open",
  "priority": "normal",
  "customer": {
    "display_name": "Example Customer"
  },
  "comments": [
    {
      "body": "I cannot access the billing page.",
      "created_at": "2026-06-21 10:00:00"
    },
    {
      "body": "We are checking your account access.",
      "created_at": "2026-06-21 10:10:00"
    }
  ]
}
```

The internal comment `Ask billing team to check account status.` must not appear in public output.
