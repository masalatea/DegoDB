INSERT IGNORE INTO project_custom_proxies (
    project_id,
    custom_proxy_key,
    basename,
    name,
    in_transaction,
    auth_type,
    single_get_function_name,
    continue_even_if_failed_to_insert,
    notes,
    source_of_truth
)
SELECT
    p.id,
    seed.custom_proxy_key,
    seed.basename,
    seed.name,
    seed.in_transaction,
    seed.auth_type,
    seed.single_get_function_name,
    seed.continue_even_if_failed_to_insert,
    seed.notes,
    'seed-legacy'
FROM projects AS p
INNER JOIN (
    SELECT
        'DB-IMPORT' AS custom_proxy_key,
        'DB' AS basename,
        'Import' AS name,
        1 AS in_transaction,
        '' AS auth_type,
        '' AS single_get_function_name,
        0 AS continue_even_if_failed_to_insert,
        '旧 daCustomProxy.PID=9 を取り込んだ seed。target source output は DBIMPORT-PROXY-SERVER / DBIMPORT-PROXY-CLIENT へ seed で再マップする。' AS notes
    UNION ALL
    SELECT
        'DB-GETTABLEDEFINITION',
        'DB',
        'GetTableDefinition',
        1,
        '',
        '',
        0,
        '旧 daCustomProxy.PID=10 を取り込んだ seed。target source output は DBIMPORT-PROXY-SERVER / DBIMPORT-PROXY-CLIENT へ seed で再マップする。'
    UNION ALL
    SELECT
        'DB-GETCOLUMNDEFINITION',
        'DB',
        'GetColumnDefinition',
        1,
        '',
        '',
        0,
        '旧 daCustomProxy.PID=11 を取り込んだ seed。target source output は DBIMPORT-PROXY-SERVER / DBIMPORT-PROXY-CLIENT へ seed で再マップする。'
    UNION ALL
    SELECT
        'DB-GETPROJECTLIST',
        'DB',
        'GetProjectList',
        1,
        '',
        '',
        0,
        '旧 daCustomProxy.PID=12 を取り込んだ seed。target source output は DBIMPORT-PROXY-SERVER / DBIMPORT-PROXY-CLIENT へ seed で再マップする。'
) AS seed
WHERE p.project_key = 'MTOOL';

INSERT INTO project_custom_proxy_steps (
    custom_proxy_id,
    db_access_source_name,
    db_access_function_name,
    is_list,
    step_order,
    notes,
    source_of_truth
)
SELECT
    cp.id,
    seed.db_access_source_name,
    seed.db_access_function_name,
    seed.is_list,
    seed.step_order,
    seed.notes,
    'seed-legacy'
FROM project_custom_proxies AS cp
INNER JOIN projects AS p
    ON p.id = cp.project_id
INNER JOIN (
    SELECT
        'DB-IMPORT' AS custom_proxy_key,
        'dbtable' AS db_access_source_name,
        'Insertdbtable' AS db_access_function_name,
        1 AS is_list,
        29 AS step_order,
        '旧 daCustomProxyFunc.PID=29 / dafuncPID=17 を取り込んだ seed。' AS notes
    UNION ALL
    SELECT
        'DB-IMPORT',
        'dbtablecolumns',
        'Insertdbtablecolumns',
        1,
        30,
        '旧 daCustomProxyFunc.PID=30 / dafuncPID=23 を取り込んだ seed。'
    UNION ALL
    SELECT
        'DB-IMPORT',
        'dbtablecolumns',
        'UpdatedbtablecolumnsIncludeColumnListOrder',
        1,
        31,
        '旧 daCustomProxyFunc.PID=31 / dafuncPID=151 を取り込んだ seed。'
    UNION ALL
    SELECT
        'DB-IMPORT',
        'dbtablecolumns',
        'Deletedbtablecolumns',
        1,
        32,
        '旧 daCustomProxyFunc.PID=32 / dafuncPID=25 を取り込んだ seed。'
    UNION ALL
    SELECT
        'DB-IMPORT',
        'dbtable',
        'Deletedbtable',
        1,
        37,
        '旧 daCustomProxyFunc.PID=37 / dafuncPID=19 を取り込んだ seed。'
    UNION ALL
    SELECT
        'DB-GETTABLEDEFINITION',
        'dbtable',
        'GetdbtableList',
        0,
        33,
        '旧 daCustomProxyFunc.PID=33 / dafuncPID=8 を取り込んだ seed。'
    UNION ALL
    SELECT
        'DB-GETCOLUMNDEFINITION',
        'dbtablecolumns',
        'GetdbtablecolumnsList',
        1,
        35,
        '旧 daCustomProxyFunc.PID=35 / dafuncPID=9 を取り込んだ seed。'
    UNION ALL
    SELECT
        'DB-GETPROJECTLIST',
        'Project',
        'GetProjectbyOwnerOrUserSecurityList',
        0,
        36,
        '旧 daCustomProxyFunc.PID=36 / dafuncPID=99 を取り込んだ seed。'
) AS seed
    ON seed.custom_proxy_key = cp.custom_proxy_key
LEFT JOIN project_custom_proxy_steps AS existing
    ON existing.custom_proxy_id = cp.id
   AND existing.db_access_source_name = seed.db_access_source_name
   AND existing.db_access_function_name = seed.db_access_function_name
   AND existing.is_list = seed.is_list
   AND existing.step_order = seed.step_order
WHERE p.project_key = 'MTOOL'
  AND existing.id IS NULL;
