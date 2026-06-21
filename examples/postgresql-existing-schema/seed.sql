INSERT INTO accounts (
    id,
    account_key,
    display_name,
    billing_email,
    created_at
) VALUES
    (
        '11111111-1111-1111-1111-111111111111',
        'acct-example',
        'Example Operations',
        'billing@example.invalid',
        '2026-06-01 09:00:00+00'
    ),
    (
        '22222222-2222-2222-2222-222222222222',
        'acct-paused',
        'Paused Customer',
        'billing-paused@example.invalid',
        '2026-06-03 09:00:00+00'
    );

INSERT INTO subscriptions (
    id,
    subscription_key,
    account_id,
    plan_code,
    status,
    starts_at,
    ends_at,
    created_at
) VALUES
    (
        'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        'sub-example-pro',
        '11111111-1111-1111-1111-111111111111',
        'pro',
        'active',
        '2026-06-01',
        NULL,
        '2026-06-01 09:05:00+00'
    ),
    (
        'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
        'sub-paused-basic',
        '22222222-2222-2222-2222-222222222222',
        'basic',
        'paused',
        '2026-06-03',
        NULL,
        '2026-06-03 09:05:00+00'
    );

INSERT INTO usage_events (
    id,
    subscription_id,
    event_type,
    event_payload,
    occurred_at
) VALUES
    (
        'aaaaaaaa-0001-0000-0000-000000000001',
        'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        'api_call',
        '{"endpoint": "/v1/reports", "units": 3}',
        '2026-06-21 10:00:00+00'
    ),
    (
        'aaaaaaaa-0002-0000-0000-000000000002',
        'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        'export',
        '{"format": "csv", "rows": 42}',
        '2026-06-21 10:30:00+00'
    ),
    (
        'bbbbbbbb-0001-0000-0000-000000000001',
        'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
        'api_call',
        '{"endpoint": "/v1/status", "units": 1}',
        '2026-06-20 11:00:00+00'
    );
