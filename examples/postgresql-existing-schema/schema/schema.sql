DROP TABLE IF EXISTS usage_events;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS accounts;

CREATE TABLE accounts (
    id uuid PRIMARY KEY,
    account_key text NOT NULL UNIQUE,
    display_name text NOT NULL,
    billing_email text NOT NULL,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE subscriptions (
    id uuid PRIMARY KEY,
    subscription_key text NOT NULL UNIQUE,
    account_id uuid NOT NULL REFERENCES accounts(id),
    plan_code text NOT NULL,
    status text NOT NULL,
    starts_at date NOT NULL,
    ends_at date,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE usage_events (
    id uuid PRIMARY KEY,
    subscription_id uuid NOT NULL REFERENCES subscriptions(id),
    event_type text NOT NULL,
    event_payload jsonb NOT NULL,
    occurred_at timestamptz NOT NULL
);

CREATE INDEX usage_events_subscription_occurred_idx
    ON usage_events (subscription_id, occurred_at DESC);
