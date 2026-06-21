SELECT
    a.account_key,
    a.display_name,
    s.subscription_key,
    s.plan_code,
    s.status,
    COUNT(e.id) AS usage_event_count,
    MAX(e.occurred_at) AS last_usage_at
FROM accounts a
JOIN subscriptions s
    ON s.account_id = a.id
LEFT JOIN usage_events e
    ON e.subscription_id = s.id
GROUP BY
    a.account_key,
    a.display_name,
    s.subscription_key,
    s.plan_code,
    s.status
ORDER BY
    a.account_key,
    s.subscription_key;
