INSERT IGNORE INTO project_html_source_bindings (
    project_id,
    legacy_project_source_output_pid,
    source_output_key,
    module_source_ref,
    refresh_policy,
    notes,
    source_of_truth
)
SELECT
    p.id,
    seed.legacy_project_source_output_pid,
    seed.source_output_key,
    CONCAT('catalog://html-module/MTOOL/', seed.source_output_key),
    'follow-source-output',
    seed.notes,
    'bootstrap-default'
FROM projects AS p
INNER JOIN (
    SELECT
        13 AS legacy_project_source_output_pid,
        'HTML-DB' AS source_output_key,
        '旧 html bucket PID=13 (/db) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。' AS notes
    UNION ALL
    SELECT
        14,
        'HTML-CHAT',
        '旧 html bucket PID=14 (/chat) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        15,
        'HTML-MINUTES',
        '旧 html bucket PID=15 (/minutes) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        16,
        'HTML-REQ',
        '旧 html bucket PID=16 (/req) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        17,
        'HTML-SPEC',
        '旧 html bucket PID=17 (/spec) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        18,
        'HTML-TEST',
        '旧 html bucket PID=18 (/test) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        19,
        'HTML-SETTINGS-UPLOADER',
        '旧 html bucket PID=19 (/settings/uploader) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        20,
        'HTML-SETTINGS-APACHE',
        '旧 html bucket PID=20 (/settings/apache) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        21,
        'HTML-SYSTEMSETTINGS-DROPBOX',
        '旧 html bucket PID=21 (/systemsettings/dropbox) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        27,
        'HTML-SYSTEMSETTINGS-SPECIALHOLIDAY',
        '旧 html bucket PID=27 (/systemsettings/specialholiday) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        31,
        'HTML-SETTINGS-SERVER',
        '旧 html bucket PID=31 (/settings/server) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        32,
        'HTML-SETTINGS-DBUSER',
        '旧 html bucket PID=32 (/settings/dbuser) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        33,
        'HTML-SETTINGS-DBCONNECTION',
        '旧 html bucket PID=33 (/settings/dbconnection) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        34,
        'HTML-SYSTEMSETTINGS-SECURITY',
        '旧 html bucket PID=34 (/systemsettings/security) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        35,
        'HTML-SYSTEMSETTINGS-INTERNALUSER',
        '旧 html bucket PID=35 (/systemsettings/internaluser) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        36,
        'HTML-SETTINGS-TOP',
        '旧 html bucket PID=36 (/settings) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        38,
        'HTML-SYSTEMSETTINGS-HTMLTEMPLATE',
        '旧 html bucket PID=38 (/systemsettings/htmltemplate) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        83,
        'HTML-SETTINGS-DROPBOX',
        '旧 html bucket PID=83 (/settings/dropbox) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        84,
        'HTML-SYSTEMSETTINGS-APACHE',
        '旧 html bucket PID=84 (/systemsettings/apache) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        150,
        'HTML-SYSTEMSETTINGS-PROJECTGROUP',
        '旧 html bucket PID=150 (/systemsettings/projectgroup) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
    UNION ALL
    SELECT
        356,
        'HTML-SETTINGS-DBBACKUP',
        '旧 html bucket PID=356 (/settings/dbbackup) を current HTML binding table に写した bootstrap seed。effective source ref は source output key 追従とする。'
) AS seed
WHERE p.project_key = 'MTOOL';
