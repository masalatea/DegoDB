# Scenario / シナリオ

Status: `DRAFT`

## Background / 背景

An existing SaaS operations database runs on PostgreSQL. / 既存 SaaS 運用 DB は PostgreSQL 上で動いています。

The team needs a safer way to understand the schema before adding admin tools and API endpoints. / チームは admin tool や API endpoint を追加する前に、schema を安全に理解したいと考えています。

## Domain / ドメイン

- `accounts` represents customer organizations.
- `subscriptions` represents active and paused product subscriptions.
- `usage_events` stores event-level usage data as `jsonb`.

## Important Behavior / 重要な振る舞い

- Public identifiers use `account_key` and `subscription_key`; internal `uuid` values should not become public URLs by default.
- `usage_events.event_payload` may contain operational details and should be reviewed before exposure.
- `subscriptions.status` is string-coded in this example; do not infer a complete enum from the sample values alone.

## First Read Model / 最初の read model

The first generated DBAccess target is an account subscription summary:

- account display name;
- subscription key;
- plan code;
- subscription status;
- last usage event time;
- usage event count.
