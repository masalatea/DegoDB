# Mtool Import Notes / mtool 取り込みメモ

Status: `DRAFT`

## Source / 入力

- Source database: synthetic MySQL schema under `legacy/schema.sql`
- Seed data: `legacy/seed.sql`
- Baseline behavior: `legacy/checks/baseline.sh`

## Import Goals / 取り込み目標

- Import `customers`, `support_tickets`, and `ticket_comments` into canonical metadata.
- Preserve physical table and column names.
- Derive logical/generated names from snake_case physical names.
- Keep the `ticket_comments.is_internal` public API guard visible in generated context and audit notes.

## Review Points / 確認点

- `customers.email` is personal data.
- `ticket_comments.body` may contain sensitive support content.
- `ticket_comments.is_internal` separates public comments from staff-only notes.
- `status`, `priority`, and `author_type` should remain string-coded until product values are reviewed.

## Expected Outputs / 期待する出力

- DataClass for the three tables.
- DBAccess list/detail helpers for tickets and comments.
- AI-readable context is out of scope until the planned source output exists.
- Modernization audit is out of scope until the planned audit generator exists.
- A behavior parity check that confirms internal comments are not exposed publicly.
