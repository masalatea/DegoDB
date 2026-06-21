# Mtool Import Notes / mtool 取り込みメモ

Status: `DRAFT`

## Source / 入力

- Source database: synthetic SQLite schema under `schema/schema.sql`
- Seed data: `seed.sql`
- CRUD smoke: `checks/crud-smoke.sql`

## Import Goals / 取り込み目標

- Import `projects` and `tasks` into canonical metadata.
- Preserve physical table and column names.
- Treat `project_key` and `task_key` as public identifier candidates.
- Keep `tasks.internal_note` visible as a risk / exclusion field for public API output.

## Review Points / 確認点

- SQLite stores booleans as integer-like values in this example.
- `created_at` and `updated_at` are text timestamps for lightweight portability.
- `tasks.status` should remain string-coded until product values are reviewed.
- `internal_note` must not appear in public API responses.

## Expected Outputs / 期待する出力

- DataClass for `projects` and `tasks`.
- DBAccess helpers for list/detail/create/update.
- API surface output after a concrete Mtool generator run exists.
- Response examples only after actual generated output exists or a runtime smoke produces them.
