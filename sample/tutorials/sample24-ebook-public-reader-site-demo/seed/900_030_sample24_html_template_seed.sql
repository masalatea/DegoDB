SET @sample24_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE24'
);

DELETE FROM project_html_parameters
WHERE project_id = @sample24_project_id;

DELETE FROM project_html_definitions
WHERE project_id = @sample24_project_id;

DELETE FROM project_html_source_bindings
WHERE project_id = @sample24_project_id;

DELETE FROM html_template_parameters
WHERE legacy_html_template_pid = 240100
   OR legacy_template_parameter_pid IN (240101);

DELETE FROM html_templates
WHERE legacy_html_template_pid = 240100;

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
    240100,
    'html',
    0,
    'Sample24 Public Reader Page Template',
    'php',
    'page.php',
    'Minimal public reader HTML module template for sample24.',
    'Tutorial-owned canonical HTML template metadata. Runtime source is mtool/reference/html-modules/sample24/HTML-PAGE/current/page.php.',
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
) VALUES (
    240101,
    240100,
    'BOOK_TITLE',
    'code',
    '',
    '',
    0,
    0,
    0,
    'string',
    'Book title value used by the sample24 public reader module.',
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

SET @sample24_project_id = NULL;
