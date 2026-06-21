# Mtool Import Notes / mtool 取り込みメモ

Status: `DRAFT`

## Source / 入力

- Source database: synthetic PostgreSQL schema under `schema/schema.sql`
- Seed data: `seed.sql`
- Representative query: `checks/representative-query.sql`

## Import Goals / 取り込み目標

- Import `accounts`, `subscriptions`, and `usage_events` into canonical metadata.
- Preserve PostgreSQL physical names.
- Keep `uuid`, `timestamptz`, and `jsonb` visible during review.
- Generate predictable logical names without changing the source schema.

## Review Points / 確認点

- `accounts.billing_email` is personal data.
- `usage_events.event_payload` is flexible `jsonb`; expose it only after field-level review.
- `subscriptions.status` and `plan_code` are string-coded sample values, not complete product enums.
- `account_key` and `subscription_key` are better public identifiers than internal UUID values.

## Expected Outputs / 期待する出力

- DataClass for the three tables.
- DBAccess read model for account subscription summaries.
- Future AI context and audit output are tracked in `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md`.
