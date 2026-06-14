CREATE TABLE IF NOT EXISTS dataclass (
    ProjectPID BIGINT UNSIGNED NOT NULL,
    PID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    StoreBasePath TEXT NOT NULL,
    IsAutoload TINYINT(1) NOT NULL DEFAULT 1,
    InheritParentDataClassName VARCHAR(255) NOT NULL,
    LastModifiedDT TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (PID),
    UNIQUE KEY uq_dataclass_project_name (ProjectPID, name),
    KEY idx_dataclass_name (name),
    CONSTRAINT fk_dataclass_project
        FOREIGN KEY (ProjectPID) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dataclassfields (
    ProjectPID BIGINT UNSIGNED NOT NULL,
    dataclassPID BIGINT UNSIGNED NOT NULL,
    PID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    datatype VARCHAR(255) NOT NULL,
    FieldListOrder INT NOT NULL DEFAULT 99999999,
    RefDataClassName VARCHAR(255) NOT NULL,
    RefDataClassFieldName VARCHAR(255) NOT NULL,
    PRIMARY KEY (PID),
    UNIQUE KEY uq_dataclassfields_project_class_name (ProjectPID, dataclassPID, name),
    KEY idx_dataclassfields_project_class (ProjectPID, dataclassPID),
    KEY idx_dataclassfields_project_class_order (ProjectPID, dataclassPID, FieldListOrder, PID),
    CONSTRAINT fk_dataclassfields_project
        FOREIGN KEY (ProjectPID) REFERENCES projects(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_dataclassfields_class
        FOREIGN KEY (dataclassPID) REFERENCES dataclass(PID)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
