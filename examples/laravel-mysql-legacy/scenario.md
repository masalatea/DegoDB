# Scenario / シナリオ

## Business Context / 業務背景

An older support desk application stores customer tickets in MySQL. / 古いサポート窓口アプリケーションが、顧客問い合わせを MySQL に保存しています。

The application has grown through small fixes, and the team wants to modernize it without losing important behavior. / 小さな修正を積み重ねてきたため、重要な振る舞いを失わずに現代化したい状況です。

## Tables / テーブル

- `customers`: customer master data;
- `support_tickets`: ticket lifecycle records;
- `ticket_comments`: public replies and internal staff notes.

## Behavior To Preserve / 保つべき振る舞い

- A public ticket detail view shows the customer name, ticket summary, and public comments.
- Internal comments are visible to staff but not to customers.
- Tickets can be filtered by status and priority.
- Comments are displayed in chronological order.

## Modernization Goals / 現代化の目的

- Document the current schema.
- Generate DataClass and DBAccess foundations from canonical metadata.
- Keep public behavior stable while generated layers are introduced.

## Non-Goals / 対象外

- Full Laravel application rewrite.
- Billing, payment, tax, or compliance logic.
- User authentication and staff permission redesign.
- Production migration automation.
