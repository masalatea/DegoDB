INSERT IGNORE INTO project_compare_outputs (
    project_id,
    compare_output_key,
    name,
    storage_base_path,
    output_file_path,
    output_file_type,
    compare_path,
    compare_tool_file_path,
    compare_output_list_order,
    notes,
    source_of_truth
)
SELECT
    p.id,
    'MAIN',
    'Mtool Main Compare Output',
    'work/compare-output/MTOOL/MAIN',
    'output/exec_compare_difference_Main.command',
    'MacCommand',
    'compare-root',
    '/Applications/Beyond\\ Compare.app/Contents/MacOS/bcomp',
    10,
    '旧 CompareOutput.PID=1 / DropboxBaseFolder=Main を local filesystem 向け canonical definition に寄せた既定 seed。legacy compare_path=/ は compare-root へ置き換え、空の placeholder directory からでも UI / CLI の動作確認ができるようにしている。',
    'bootstrap-default'
FROM projects AS p
WHERE p.project_key = 'MTOOL';

INSERT IGNORE INTO project_compare_outputs (
    project_id,
    compare_output_key,
    name,
    storage_base_path,
    output_file_path,
    output_file_type,
    compare_path,
    compare_tool_file_path,
    compare_output_list_order,
    notes,
    source_of_truth
)
SELECT
    p.id,
    'CLIENTCOMMON',
    'Mtool Client Common Compare Output',
    'work/compare-output/MTOOL/CLIENTCOMMON',
    'output/exec_compare_difference_ClientCommon.command',
    'MacCommand',
    'compare-root',
    '/Applications/Beyond\\ Compare.app/Contents/MacOS/bcomp',
    20,
    '旧 CompareOutput.PID=2 / DropboxBaseFolder=Client Common を local filesystem 向け canonical definition に寄せた既定 seed。legacy compare_path=/ は compare-root へ置き換え、Project 1 由来の additional path definition をぶら下げる。',
    'bootstrap-default'
FROM projects AS p
WHERE p.project_key = 'MTOOL';

INSERT IGNORE INTO project_compare_output_additional_paths (
    compare_output_id,
    additional_path_key,
    path_a_base_path,
    path_a,
    path_b_base_path,
    path_b,
    is_same_filename_only,
    additional_path_list_order,
    notes,
    source_of_truth
)
SELECT
    co.id,
    'PROXYCLIENT',
    '',
    'Java Library/UTF-8 (For Android or so)/ProxyClientForAuth - tmp output',
    '',
    'Java Library/UTF-8 (For Android or so)/MatsuesoftCommonLib/matsuesoftcommonlib/src/main/java/jp/co/matsuesoft/CommonLib/ProxyClientForAuth',
    0,
    10,
    '旧 CompareOutputAdditionalPath.PID=6 を取り込んだ既定 seed。legacy PathA/PathB の先頭スラッシュだけ外し、compare output の storage_base_path 基準の相対 path として保持する。',
    'bootstrap-default'
FROM project_compare_outputs AS co
INNER JOIN projects AS p
    ON p.id = co.project_id
WHERE p.project_key = 'MTOOL'
  AND co.compare_output_key = 'CLIENTCOMMON';

INSERT IGNORE INTO project_compare_output_additional_paths (
    compare_output_id,
    additional_path_key,
    path_a_base_path,
    path_a,
    path_b_base_path,
    path_b,
    is_same_filename_only,
    additional_path_list_order,
    notes,
    source_of_truth
)
SELECT
    co.id,
    'LANGRESOURCE',
    '',
    'Java Library/UTF-8 (For Android or so)/MatsuesoftCommonLib - Language Resource - tmp output',
    '',
    'Java Library/UTF-8 (For Android or so)/MatsuesoftCommonLib/matsuesoftcommonlib/src/main',
    0,
    20,
    '旧 CompareOutputAdditionalPath.PID=9 を取り込んだ既定 seed。legacy PathA/PathB の先頭スラッシュだけ外し、compare output の storage_base_path 基準の相対 path として保持する。',
    'bootstrap-default'
FROM project_compare_outputs AS co
INNER JOIN projects AS p
    ON p.id = co.project_id
WHERE p.project_key = 'MTOOL'
  AND co.compare_output_key = 'CLIENTCOMMON';
