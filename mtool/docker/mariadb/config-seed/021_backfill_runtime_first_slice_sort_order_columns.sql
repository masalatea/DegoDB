-- Backfill canonical metadata required by the first runtime SQL regeneration slice.

UPDATE project_db_access_functions AS f
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
SET
    f.sort_order_columns = IF(
        f.source_of_truth = 'manual' AND f.sort_order_columns <> '',
        f.sort_order_columns,
        'dbtable.name'
    ),
    f.source_of_truth = IF(f.source_of_truth = 'manual', f.source_of_truth, 'seed-legacy'),
    f.updated_at = CURRENT_TIMESTAMP
WHERE p.project_key = 'MTOOL'
  AND c.source_name = 'dbtable'
  AND f.function_name = 'GetdbtableList';

UPDATE project_db_access_functions AS f
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
SET
    f.sort_order_columns = IF(
        f.source_of_truth = 'manual' AND f.sort_order_columns <> '',
        f.sort_order_columns,
        'dbtablecolumns.ColumnListOrder,dbtablecolumns.PID'
    ),
    f.source_of_truth = IF(f.source_of_truth = 'manual', f.source_of_truth, 'seed-legacy'),
    f.updated_at = CURRENT_TIMESTAMP
WHERE p.project_key = 'MTOOL'
  AND c.source_name = 'dbtablecolumns'
  AND f.function_name = 'GetdbtablecolumnsList';

UPDATE project_db_access_functions AS f
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
SET
    f.sort_order_columns = IF(
        f.source_of_truth = 'manual' AND f.sort_order_columns <> '',
        f.sort_order_columns,
        'Project.PID'
    ),
    f.source_of_truth = IF(f.source_of_truth = 'manual', f.source_of_truth, 'seed-legacy'),
    f.updated_at = CURRENT_TIMESTAMP
WHERE p.project_key = 'MTOOL'
  AND c.source_name = 'Project'
  AND f.function_name = 'GetProjectList';

UPDATE project_db_access_functions AS f
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
SET
    f.sort_order_columns = IF(
        f.source_of_truth = 'manual' AND f.sort_order_columns <> '',
        f.sort_order_columns,
        'Project.PID'
    ),
    f.source_of_truth = IF(f.source_of_truth = 'manual', f.source_of_truth, 'seed-legacy'),
    f.updated_at = CURRENT_TIMESTAMP
WHERE p.project_key = 'MTOOL'
  AND c.source_name = 'Project'
  AND f.function_name = 'GetProjectbyOwnerOrUserSecurityList';
