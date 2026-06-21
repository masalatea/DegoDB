INSERT INTO customers (
  id,
  customer_key,
  display_name,
  email,
  status,
  created_at,
  updated_at
) VALUES
  (1, 'CUST-1001', 'Example Customer', 'customer@example.test', 'active', '2026-06-20 09:00:00', '2026-06-20 09:00:00');

INSERT INTO support_tickets (
  id,
  customer_id,
  ticket_key,
  subject,
  status,
  priority,
  assigned_team,
  opened_at,
  resolved_at,
  updated_at
) VALUES
  (1, 1, 'TICKET-1001', 'Cannot access billing page', 'open', 'normal', 'support', '2026-06-21 10:00:00', NULL, '2026-06-21 10:15:00');

INSERT INTO ticket_comments (
  id,
  ticket_id,
  author_type,
  author_name,
  body,
  is_internal,
  created_at
) VALUES
  (1, 1, 'customer', 'Example Customer', 'I cannot access the billing page.', 0, '2026-06-21 10:00:00'),
  (2, 1, 'staff', 'Support Staff', 'Ask billing team to check account status.', 1, '2026-06-21 10:05:00'),
  (3, 1, 'staff', 'Support Staff', 'We are checking your account access.', 0, '2026-06-21 10:10:00');
