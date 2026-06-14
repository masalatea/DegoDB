UPDATE project_source_outputs AS so
INNER JOIN projects AS p
    ON p.id = so.project_id
SET
    so.source_template_dir = CONCAT(
        'catalog://html-module/MTOOL/',
        so.source_output_key
    ),
    so.runtime_source_relative_path = CONCAT(
        'mtool/html-source-outputs/MTOOL/',
        so.source_output_key
    ),
    so.artifact_strategy = 'html-module-catalog',
    so.target_binding_type = 'metadata-only',
    so.notes = CONCAT(
        'Project 1 html module catalog seed for ',
        so.source_output_key,
        '. resolver が canonical module / copied snapshot / placeholder を順に解決し、new runtime から original-codes へ直接依存しない。'
    )
WHERE p.project_key = 'MTOOL'
  AND so.class_type = 'html'
  AND so.source_output_key IN (
      'HTML-DB',
      'HTML-CHAT',
      'HTML-MINUTES',
      'HTML-REQ',
      'HTML-SPEC',
      'HTML-TEST',
      'HTML-SETTINGS-UPLOADER',
      'HTML-SETTINGS-APACHE',
      'HTML-SYSTEMSETTINGS-DROPBOX',
      'HTML-SYSTEMSETTINGS-SPECIALHOLIDAY',
      'HTML-SETTINGS-SERVER',
      'HTML-SETTINGS-DBUSER',
      'HTML-SETTINGS-DBCONNECTION',
      'HTML-SYSTEMSETTINGS-SECURITY',
      'HTML-SYSTEMSETTINGS-INTERNALUSER',
      'HTML-SETTINGS-TOP',
      'HTML-SYSTEMSETTINGS-HTMLTEMPLATE',
      'HTML-SETTINGS-DROPBOX',
      'HTML-SYSTEMSETTINGS-APACHE',
      'HTML-SYSTEMSETTINGS-PROJECTGROUP',
      'HTML-SETTINGS-DBBACKUP'
  );
