SET @sample11_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE11'
);

DELETE FROM project_html_parameters
WHERE project_id = @sample11_project_id;

DELETE FROM project_html_definitions
WHERE project_id = @sample11_project_id;

DELETE FROM project_html_source_bindings
WHERE project_id = @sample11_project_id;

DELETE FROM html_template_parameters
WHERE legacy_html_template_pid = 110100
   OR legacy_template_parameter_pid IN (110101, 110102);

DELETE FROM html_templates
WHERE legacy_html_template_pid = 110100;

INSERT INTO html_templates (
    legacy_html_template_pid,
    target_type,
    parent_legacy_html_template_pid,
    name,
    program_language,
    file_name,
    comment,
    notes,
    source_of_truth
) VALUES (
    110100,
    'html',
    0,
    'Sample11 Page Template',
    'php',
    'page.php',
    'Minimal HTML module template for sample11.',
    'Tutorial-owned canonical HTML template metadata. Runtime source is mtool/reference/html-modules/sample11/HTML-PAGE/current/page.php.',
    'manual'
)
ON DUPLICATE KEY UPDATE
    target_type = VALUES(target_type),
    parent_legacy_html_template_pid = VALUES(parent_legacy_html_template_pid),
    name = VALUES(name),
    program_language = VALUES(program_language),
    file_name = VALUES(file_name),
    comment = VALUES(comment),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);

INSERT INTO html_template_parameters (
    legacy_template_parameter_pid,
    legacy_html_template_pid,
    parameter_name,
    target_value_type,
    target_variable_or_class_object,
    target_property_of_class_object,
    another_template_pid,
    trim_last_space,
    trim_last_return,
    data_type,
    notes,
    source_of_truth
) VALUES
    (
        110101,
        110100,
        'PAGE_TITLE',
        'code',
        '',
        '',
        0,
        0,
        0,
        'string',
        'Title value used by the sample11 page module.',
        'manual'
    ),
    (
        110102,
        110100,
        'PAGE_BODY',
        'code',
        '',
        '',
        0,
        0,
        0,
        'string',
        'Body value used by the sample11 page module.',
        'manual'
    )
ON DUPLICATE KEY UPDATE
    legacy_html_template_pid = VALUES(legacy_html_template_pid),
    parameter_name = VALUES(parameter_name),
    target_value_type = VALUES(target_value_type),
    target_variable_or_class_object = VALUES(target_variable_or_class_object),
    target_property_of_class_object = VALUES(target_property_of_class_object),
    another_template_pid = VALUES(another_template_pid),
    trim_last_space = VALUES(trim_last_space),
    trim_last_return = VALUES(trim_last_return),
    data_type = VALUES(data_type),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);

SET @sample11_project_id = NULL;
