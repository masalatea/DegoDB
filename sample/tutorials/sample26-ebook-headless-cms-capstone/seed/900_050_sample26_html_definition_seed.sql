SET @sample26_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE26'
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
    @sample26_project_id,
    260001,
    'SAMPLE26-HEADLESS-CMS-READER-PAGE',
    'Sample26 Headless CMS Reader Page',
    260030,
    260100,
    10,
    '2026-06-19 00:00:00',
    'Tutorial HTML definition bound to sample26 HTML-PAGE.',
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

SET @sample26_html_definition_id = (
    SELECT id
    FROM project_html_definitions
    WHERE project_id = @sample26_project_id
      AND html_key = 'SAMPLE26-HEADLESS-CMS-READER-PAGE'
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
) VALUES (
    @sample26_project_id,
    @sample26_html_definition_id,
    260011,
    'BOOK_TITLE',
    'JSONから始める電子書籍CMS',
    10,
    'Reader page title used by the sample26 capstone module.',
    'manual'
)
ON DUPLICATE KEY UPDATE
    project_html_definition_id = VALUES(project_html_definition_id),
    parameter_name = VALUES(parameter_name),
    parameter_value = VALUES(parameter_value),
    parameter_list_order = VALUES(parameter_list_order),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);

SET @sample26_html_definition_id = NULL;
SET @sample26_project_id = NULL;
