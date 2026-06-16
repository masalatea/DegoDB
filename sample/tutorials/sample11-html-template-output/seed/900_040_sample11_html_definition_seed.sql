SET @sample11_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE11'
);

INSERT INTO project_html_definitions (
    project_id,
    legacy_html_pid,
    html_key,
    name,
    legacy_project_source_output_pid,
    legacy_html_template_pid,
    html_list_order,
    last_modified_dt,
    notes,
    source_of_truth
) VALUES (
    @sample11_project_id,
    110001,
    'SAMPLE11-PAGE',
    'Sample11 Page',
    110030,
    110100,
    10,
    '2026-06-16 00:00:00',
    'Tutorial HTML definition bound to HTML-PAGE.',
    'manual'
)
ON DUPLICATE KEY UPDATE
    html_key = VALUES(html_key),
    name = VALUES(name),
    legacy_project_source_output_pid = VALUES(legacy_project_source_output_pid),
    legacy_html_template_pid = VALUES(legacy_html_template_pid),
    html_list_order = VALUES(html_list_order),
    last_modified_dt = VALUES(last_modified_dt),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);

SET @sample11_html_definition_id = (
    SELECT id
    FROM project_html_definitions
    WHERE project_id = @sample11_project_id
      AND html_key = 'SAMPLE11-PAGE'
);

INSERT INTO project_html_parameters (
    project_id,
    project_html_definition_id,
    legacy_parameter_pid,
    parameter_name,
    parameter_value,
    parameter_list_order,
    notes,
    source_of_truth
) VALUES
    (
        @sample11_project_id,
        @sample11_html_definition_id,
        110011,
        'PAGE_TITLE',
        'Sample11 HTML Template Output',
        10,
        'Page title used by the sample11 tutorial module.',
        'manual'
    ),
    (
        @sample11_project_id,
        @sample11_html_definition_id,
        110012,
        'PAGE_BODY',
        'This page is a minimal html-module-catalog output generated from curated current metadata.',
        20,
        'Page body used by the sample11 tutorial module.',
        'manual'
    )
ON DUPLICATE KEY UPDATE
    project_html_definition_id = VALUES(project_html_definition_id),
    parameter_name = VALUES(parameter_name),
    parameter_value = VALUES(parameter_value),
    parameter_list_order = VALUES(parameter_list_order),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);

SET @sample11_html_definition_id = NULL;
SET @sample11_project_id = NULL;
