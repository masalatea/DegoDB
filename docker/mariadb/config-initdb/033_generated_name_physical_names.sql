ALTER TABLE dbtable
    ADD COLUMN IF NOT EXISTS physical_name VARCHAR(255) NOT NULL DEFAULT '' AFTER name;

ALTER TABLE dbtablecolumns
    ADD COLUMN IF NOT EXISTS physical_name VARCHAR(255) NOT NULL DEFAULT '' AFTER name;

ALTER TABLE dataclass
    ADD COLUMN IF NOT EXISTS physical_name VARCHAR(255) NOT NULL DEFAULT '' AFTER name;

ALTER TABLE dataclassfields
    ADD COLUMN IF NOT EXISTS physical_name VARCHAR(255) NOT NULL DEFAULT '' AFTER name;

UPDATE dbtable
SET physical_name = name
WHERE physical_name = '';

UPDATE dbtablecolumns
SET physical_name = name
WHERE physical_name = '';

UPDATE dataclass
SET physical_name = name
WHERE physical_name = '';

UPDATE dataclassfields
SET physical_name = name
WHERE physical_name = '';
