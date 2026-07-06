SET @sample31_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE31'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample31_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample31_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample31_project_id;

DELETE FROM project_shared_contracts
WHERE project_id = @sample31_project_id;

DELETE FROM project_source_outputs
WHERE project_id = @sample31_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample31_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample31_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample31_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample31_project_id;

DROP TABLE IF EXISTS inventory_request;

CREATE TABLE inventory_request (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    request_number VARCHAR(40) NOT NULL,
    requester_name VARCHAR(120) NOT NULL,
    warehouse_code VARCHAR(40) NOT NULL,
    item_sku VARCHAR(80) NOT NULL,
    quantity_needed INT NOT NULL DEFAULT 1,
    status VARCHAR(40) NOT NULL DEFAULT 'open',
    fulfillment_note TEXT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inventory_request_number (request_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO inventory_request (
    id,
    request_number,
    requester_name,
    warehouse_code,
    item_sku,
    quantity_needed,
    status,
    fulfillment_note
) VALUES (
    3101,
    'INV-REQ-2026-0001',
    'Northwind Warehouse Ops',
    'WH-TOKYO-01',
    'SKU-BOARD-42',
    12,
    'requested',
    'Prepare inventory pick review before approving replenishment.'
), (
    3102,
    'INV-REQ-2026-0002',
    'Contoso Storefront',
    'WH-OSAKA-02',
    'SKU-CABLE-99',
    24,
    'review',
    'Check alternate warehouse stock before confirming fulfillment.'
)
ON DUPLICATE KEY UPDATE
    request_number = VALUES(request_number),
    requester_name = VALUES(requester_name),
    warehouse_code = VALUES(warehouse_code),
    item_sku = VALUES(item_sku),
    quantity_needed = VALUES(quantity_needed),
    status = VALUES(status),
    fulfillment_note = VALUES(fulfillment_note);

SET @sample31_project_id = NULL;
