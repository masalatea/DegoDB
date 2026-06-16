SET @sample14_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE14'
);

DELETE targets
FROM project_custom_proxy_source_output_targets AS targets
INNER JOIN project_custom_proxies AS custom_proxies
    ON custom_proxies.id = targets.custom_proxy_id
WHERE custom_proxies.project_id = @sample14_project_id;

DELETE steps
FROM project_custom_proxy_steps AS steps
INNER JOIN project_custom_proxies AS custom_proxies
    ON custom_proxies.id = steps.custom_proxy_id
WHERE custom_proxies.project_id = @sample14_project_id;

DELETE FROM project_custom_proxies
WHERE project_id = @sample14_project_id;

INSERT INTO project_custom_proxies (
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
) VALUES (
    @sample14_project_id,
    'CATALOG-SUMMARY',
    'Catalog',
    'Summary',
    0,
    'NoSecurity',
    '',
    0,
    'Sample14 custom proxy that bundles two read-only generated DBAccess functions into one proxy endpoint.',
    'manual'
);

SET @sample14_custom_proxy_id = LAST_INSERT_ID();

INSERT INTO project_custom_proxy_steps (
    custom_proxy_id,
    db_access_source_name,
    db_access_function_name,
    is_list,
    step_order,
    notes,
    source_of_truth
) VALUES
(
    @sample14_custom_proxy_id,
    'dbtable',
    'GetdbtableList',
    1,
    10,
    'Read canonical table catalog rows for the requested project.',
    'manual'
),
(
    @sample14_custom_proxy_id,
    'ProjectSourceOutput',
    'GetProjectSourceOutputList',
    1,
    20,
    'Read source output definitions for the requested project.',
    'manual'
);

INSERT INTO project_custom_proxy_source_output_targets (
    custom_proxy_id,
    source_output_key
) VALUES (
    @sample14_custom_proxy_id,
    'CUSTOM-PROXY-SERVER'
);

SET @sample14_project_id = NULL;
SET @sample14_custom_proxy_id = NULL;
