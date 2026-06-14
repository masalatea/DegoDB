INSERT IGNORE INTO project_source_outputs (
    project_id,
    source_output_key,
    name,
    program_language,
    class_type,
    release_target_type,
    source_template_dir,
    source_output_dir,
    source_temp_output_dir,
    proxy_base_url,
    autoload_filename_suffix,
    source_text_char_code,
    runtime_source_relative_path,
    artifact_strategy,
    target_binding_type,
    output_archive_format,
    source_output_list_order,
    notes,
    source_of_truth
)
SELECT
    p.id,
    seed.source_output_key,
    seed.name,
    'php',
    'html',
    'Release',
    CONCAT('catalog://html-module/MTOOL/', seed.source_output_key),
    CONCAT('work/source-outputs/MTOOL/', seed.source_output_key),
    CONCAT('work/staging/source-outputs/MTOOL/', seed.source_output_key),
    '',
    '',
    'UTF-8',
    CONCAT('mtool/html-source-outputs/MTOOL/', seed.source_output_key),
    'html-module-catalog',
    'metadata-only',
    'tar.gz',
    seed.source_output_list_order,
    seed.notes,
    'bootstrap-default'
FROM projects AS p
INNER JOIN (
    SELECT
        'HTML-DB' AS source_output_key,
        'Mtool HTML DB Module' AS name,
        60 AS source_output_list_order,
        '旧 ProjectSourceOutput.PID=13 (/db) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。' AS notes
    UNION ALL
    SELECT
        'HTML-CHAT',
        'Mtool HTML Chat Module',
        70,
        '旧 ProjectSourceOutput.PID=14 (/chat) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-MINUTES',
        'Mtool HTML Minutes Module',
        80,
        '旧 ProjectSourceOutput.PID=15 (/minutes) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-REQ',
        'Mtool HTML Req Module',
        90,
        '旧 ProjectSourceOutput.PID=16 (/req) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SPEC',
        'Mtool HTML Spec Module',
        100,
        '旧 ProjectSourceOutput.PID=17 (/spec) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-TEST',
        'Mtool HTML Test Module',
        110,
        '旧 ProjectSourceOutput.PID=18 (/test) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SETTINGS-UPLOADER',
        'Mtool HTML Settings Uploader Module',
        120,
        '旧 ProjectSourceOutput.PID=19 (/settings/uploader) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SETTINGS-APACHE',
        'Mtool HTML Settings Apache Module',
        130,
        '旧 ProjectSourceOutput.PID=20 (/settings/apache) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SYSTEMSETTINGS-DROPBOX',
        'Mtool HTML System Settings Dropbox Module',
        140,
        '旧 ProjectSourceOutput.PID=21 (/systemsettings/dropbox) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SYSTEMSETTINGS-SPECIALHOLIDAY',
        'Mtool HTML System Settings Special Holiday Module',
        150,
        '旧 ProjectSourceOutput.PID=27 (/systemsettings/specialholiday) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SETTINGS-SERVER',
        'Mtool HTML Settings Server Module',
        160,
        '旧 ProjectSourceOutput.PID=31 (/settings/server) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SETTINGS-DBUSER',
        'Mtool HTML Settings DBUser Module',
        170,
        '旧 ProjectSourceOutput.PID=32 (/settings/dbuser) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SETTINGS-DBCONNECTION',
        'Mtool HTML Settings DBConnection Module',
        180,
        '旧 ProjectSourceOutput.PID=33 (/settings/dbconnection) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SYSTEMSETTINGS-SECURITY',
        'Mtool HTML System Settings Security Module',
        190,
        '旧 ProjectSourceOutput.PID=34 (/systemsettings/security) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SYSTEMSETTINGS-INTERNALUSER',
        'Mtool HTML System Settings Internal User Module',
        200,
        '旧 ProjectSourceOutput.PID=35 (/systemsettings/internaluser) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SETTINGS-TOP',
        'Mtool HTML Settings Top Module',
        210,
        '旧 ProjectSourceOutput.PID=36 (/settings) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SYSTEMSETTINGS-HTMLTEMPLATE',
        'Mtool HTML System Settings HtmlTemplate Module',
        220,
        '旧 ProjectSourceOutput.PID=38 (/systemsettings/htmltemplate) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SETTINGS-DROPBOX',
        'Mtool HTML Settings Dropbox Module',
        230,
        '旧 ProjectSourceOutput.PID=83 (/settings/dropbox) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SYSTEMSETTINGS-APACHE',
        'Mtool HTML System Settings Apache Module',
        240,
        '旧 ProjectSourceOutput.PID=84 (/systemsettings/apache) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SYSTEMSETTINGS-PROJECTGROUP',
        'Mtool HTML System Settings ProjectGroup Module',
        250,
        '旧 ProjectSourceOutput.PID=150 (/systemsettings/projectgroup) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
    UNION ALL
    SELECT
        'HTML-SETTINGS-DBBACKUP',
        'Mtool HTML Settings DBBackup Module',
        260,
        '旧 ProjectSourceOutput.PID=356 (/settings/dbbackup) を html module catalog ref で保持する core seed。resolver が canonical module / copied snapshot / placeholder を順に解決する。'
) AS seed
WHERE p.project_key = 'MTOOL';
