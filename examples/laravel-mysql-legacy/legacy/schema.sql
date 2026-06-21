CREATE TABLE customers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_key VARCHAR(64) NOT NULL,
  display_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_customers_customer_key (customer_key)
);

CREATE TABLE support_tickets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id BIGINT UNSIGNED NOT NULL,
  ticket_key VARCHAR(64) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'open',
  priority VARCHAR(32) NOT NULL DEFAULT 'normal',
  assigned_team VARCHAR(64) DEFAULT NULL,
  opened_at DATETIME NOT NULL,
  resolved_at DATETIME DEFAULT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_support_tickets_ticket_key (ticket_key),
  KEY idx_support_tickets_customer_id (customer_id),
  KEY idx_support_tickets_status_priority (status, priority),
  CONSTRAINT fk_support_tickets_customer
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE ticket_comments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticket_id BIGINT UNSIGNED NOT NULL,
  author_type VARCHAR(32) NOT NULL,
  author_name VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  is_internal TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_ticket_comments_ticket_id (ticket_id),
  CONSTRAINT fk_ticket_comments_ticket
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id)
);
