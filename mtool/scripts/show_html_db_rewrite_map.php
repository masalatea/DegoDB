#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * @return array{
 *     id:string,
 *     status:string,
 *     summary:string,
 *     next_action:string,
 *     current_page_prefixes:list<string>,
 *     patterns:list<string>
 * }
 */
function app_cli_html_db_rewrite_cluster(
    string $id,
    string $status,
    string $summary,
    string $nextAction,
    array $currentPagePrefixes,
    array $patterns,
): array {
    return [
        'id' => $id,
        'status' => $status,
        'summary' => $summary,
        'next_action' => $nextAction,
        'current_page_prefixes' => $currentPagePrefixes,
        'patterns' => $patterns,
    ];
}

function app_cli_show_html_db_rewrite_map_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/show_html_db_rewrite_map.php [--format=text|json]

Options:
  --format=FORMAT    出力形式。text または json (default: text)
  --help             このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     format:string,
 *     error:string
 * }
 */
function app_cli_show_html_db_rewrite_map_parse_args(array $argv): array
{
    $format = 'text';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'format' => $format,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--format=')) {
            $format = trim(substr($argument, strlen('--format=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'format' => $format,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if (!in_array($format, ['text', 'json'], true)) {
        return [
            'ok' => false,
            'help' => false,
            'format' => $format,
            'error' => '有効な --format=text または --format=json を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'format' => $format,
        'error' => '',
    ];
}

function app_cli_html_db_repo_root(): string
{
    return dirname(__DIR__);
}

function app_cli_html_db_current_root(): string
{
    return app_cli_html_db_repo_root() . '/reference/html-modules/mtool/HTML-DB/current';
}

function app_cli_html_db_shared_root(): string
{
    $appRoot = app_cli_html_db_repo_root() . '/app';
    if (is_dir($appRoot)) {
        return $appRoot;
    }

    return app_cli_html_db_repo_root() . '/shared';
}

/**
 * @return list<string>
 */
function app_cli_scan_relative_files(string $root): array
{
    if (!is_dir($root)) {
        throw new RuntimeException('directory が見つかりません: ' . $root);
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    /** @var SplFileInfo $fileInfo */
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $relativePath = substr($fileInfo->getPathname(), strlen($root) + 1);
        if (!is_string($relativePath) || $relativePath === '') {
            continue;
        }

        $files[] = str_replace('\\', '/', $relativePath);
    }

    sort($files);

    return $files;
}

/**
 * @return list<string>
 */
function app_cli_scan_shared_pages(string $sharedRoot): array
{
    $pages = [];
    foreach (glob($sharedRoot . '/*_page.php') ?: [] as $path) {
        $pages[] = basename($path);
    }

    sort($pages);

    return $pages;
}

/**
 * @return list<array{
 *     id:string,
 *     status:string,
 *     summary:string,
 *     next_action:string,
 *     current_page_prefixes:list<string>,
 *     patterns:list<string>
 * }>
 */
function app_cli_html_db_rewrite_clusters(): array
{
    return [
        app_cli_html_db_rewrite_cluster(
            'project-detail',
            'available',
            'legacy HTML-DB index は current project detail route に寄せられる。',
            'entry page の互換 wrapper は最終 path policy を決めてから置き換える。',
            ['project_detail'],
            ['/^index\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'project-settings',
            'available-partial',
            'project identity / settings 系は current settings page へ寄せられる。',
            'project_edit entry を current settings route へ寄せ、include は page composition へ吸収する。',
            ['project_settings'],
            ['/^project_edit(?:_include)?\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'tables',
            'available-partial',
            'dbtable / dbtablecolumns / import は current table routes が受け皿になる。',
            'dbtables*.php 本体から順に current routes へ置き換え、change-order 相当は後段で整理する。',
            ['project_tables', 'project_table'],
            [
                '/^dbtables(?:_import(?:_common|_for_each)?)?\.php$/',
                '/^dbtable_(?:edit|edit_include|column_edit|column_edit_include)\.php$/',
                '/^dbtable_columns\.php$/',
            ],
        ),
        app_cli_html_db_rewrite_cluster(
            'data-classes',
            'available-partial',
            'dataclass / dataclassfields / sync 系は current data class routes が受け皿になる。',
            'dataclasses.php と dataclass_fields.php を優先し、change-order / include は最後に吸収する。',
            ['project_data_class', 'project_data_classes'],
            [
                '/^dataclasses(?:_change_order(?:_include)?|_source|_sync(?:_for_each)?)?\.php$/',
                '/^dataclass_(?:edit|edit_include|field_edit|field_edit_include)\.php$/',
                '/^dataclass_fields(?:_sync_inherit)?\.php$/',
            ],
        ),
        app_cli_html_db_rewrite_cluster(
            'db-access-core',
            'available-partial',
            'DB Access / query 設計本体は current db-access routes が受け皿になる。',
            'da.php / da_source.php の redirect-only guard も current route へ寄せた。残る fallback は da_edit add-flow、da_funcs / da_sync / change-order の action semantics が中心である。',
            ['project_db_access'],
            [
                '/^da(?:|_edit|_edit_include|_source|_sync|_table_include)\.php$/',
                '/^da_funcs(?:_change_order(?:_include)?)?\.php$/',
            ],
        ),
        app_cli_html_db_rewrite_cluster(
            'db-access-functions',
            'available-partial',
            'da_func* 詳細編集群は current db access function pages がかなりの範囲を吸収できる。',
            '最初は detail / source / endpoint / target field 周辺の current pages に対応づける。',
            ['project_db_access_function'],
            ['/^da_func.*\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'proxy-custom',
            'available-partial',
            'custom proxy endpoint preview / single proxy navigator に加え、custom proxy POST action bridge まで current page 群が受け皿になる。',
            'GET/HEAD の unknown PID deep link は current list / functions へ寄せ、POST/action の unknown ID も current-side validation / bridge error に寄せた。endpoint helper include 群も current handoff shim へ置き換わり、残る作業は non-currentizable guard fallback 整理である。',
            ['project_single_proxy', 'project_custom_proxy', 'project_custom_proxy_endpoint', 'project_custom_proxies'],
            [
                '/^da_proxy_custom.*\.php$/',
                '/^da_edit_proxy_single_target\.php$/',
                '/^da_funcs_edit_proxy_single.*\.php$/',
                '/^da_funcs_table_include\.php$/',
                '/^proxy_auth_common_include\.php$/',
            ],
        ),
        app_cli_html_db_rewrite_cluster(
            'source-outputs',
            'available-partial',
            'Source Output list / detail / new / edit / change-order は current source output routes が受け皿になり、legacy add/edit/reorder も main path では current handoff へ寄せられる。',
            '`project_source_output_edit.php` は existing row の update/delete POST を current `/edit` action へ bridge し、blank add-flow GET/POST も current `/new` handoff に切り替わった。legacy handoff の proxy strategy / binding は current `/new` page で初期推定し、key / name は `safe-prefill` / `warning-candidate` / `manual-only` に分類して扱う。残課題は legacy-only fields の扱いである。',
            ['project_source_output'],
            ['/^project_source_output(?:_.*)?\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'compare-output-settings',
            'available-partial',
            'compare output 設定と追加 path 管理は current project compare-output routes が受け皿になる。',
            'settings pages と assets の境界を切り、設定 UI 本体から current routes へ置き換える。',
            ['project_compare_output'],
            [
                '/^compare_output(?:_additional_path(?:_edit(?:_include)?|_table_include)?|_edit(?:_include)?|_table_include)?\.php$/',
                '/^compare_ignore_dir_setting_regex\.txt$/',
                '/^compare_output_template_for_.*\.txt$/',
            ],
        ),
        app_cli_html_db_rewrite_cluster(
            'compare-output-run',
            'available',
            'compare output 実行系は current lab compare-output routes が受け皿になる。',
            'compare_output_do.php は project mismatch / unsupported verb でも current run route へ縮退し、AJAX handoff まで current 化済みである。',
            ['lab_compare_output'],
            ['/^compare_output_do(?:_ajax)?\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'build-project',
            'available',
            'build job 実行系は current build routes が受け皿になる。',
            'build_project.php は project mismatch でも current build route へ縮退し、AJAX / polling handoff まで current 化済みである。',
            ['lab_build'],
            ['/^build_project.*\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'endpoint-test',
            'available-partial',
            'endpoint test 系は current `/runs/endpoints/{project_key}` / `/api/runs/endpoints/{job_key}` route が受け皿になる。',
            '`endpoint_test_json_ajax.php` は GET/HEAD redirect に加え known-project POST も current endpoint-test job service へ bridge する。helper include 群も current handoff shim へ置き換わり、残る `_legacy/` fallback は non-currentizable guard が中心である。',
            ['lab_endpoint'],
            ['/^endpoint_.*\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'html-authoring',
            'available',
            'HTML list / detail / parameter authoring は current `/projects/{project_key}/html*` route が live DB-backed current pages で受ける。',
            '`htmls.php` / `html_parameters.php` は current route へ handoff し、`html_edit.php` / `html_parameter_edit.php` の legacy POST も current action へ bridge する。copied reference は `html_key` 保持と parameter audit metadata に限定した。',
            ['project_htmls', 'project_html_detail', 'project_html_parameters'],
            ['/^html.*\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'language-resource',
            'available-partial',
            'LanguageResource は current `/projects/{project_key}/language-resources*` route で browse/detail/groups の read-only inspector を提供し、主要な `lang_res*.php` entry は generated wrapper で file workflow へ bridge できる。',
            '`lang_res_auto_translate_ajax.php` も current endpoint へ handoff せず、legacy JSON 互換の NG response で file workflow を案内する。残りは generated runtime の end-to-end 確認、helper include 群の fallback 整理、optional module / code-native 管理への分離である。',
            [
                'project_language_resources',
                'project_language_resource_detail',
                'project_language_resource_groups',
            ],
            ['/^lang_res.*\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'security-host',
            'planned',
            'security / host assignment / default permission は current route がまだ無い。',
            'membership / policy の新 route を先に切ってから project_security*.php を置き換える。',
            [],
            [
                '/^project_security.*\.php$/',
                '/^project_host_assignment.*\.php$/',
                '/^project_user_default_permission_lib\.php$/',
            ],
        ),
        app_cli_html_db_rewrite_cluster(
            'default-settings',
            'planned',
            'default setting helper 群は current canonical asset policy 未整理。',
            'template/default asset の source-of-truth を決めてから default_setting*.php を再配置する。',
            [],
            ['/^default_setting_.*\.php$/'],
        ),
        app_cli_html_db_rewrite_cluster(
            'misc-helpers',
            'planned',
            '残りは helper / comment / history / project group 補助群で、current route への対応が未確定。',
            '個別に ownership を決めてから切り出す。',
            [],
            [
                '/^create_project_group\.php$/',
                '/^source_comment_include\.php$/',
                '/^update_history\.php$/',
            ],
        ),
        app_cli_html_db_rewrite_cluster(
            'archived-assets',
            'planned',
            'archive payload は route ではなく asset retention として扱う。',
            'old/ 配下を current source root に残すか、publish 時に別 artifact へ切るかを決める。',
            [],
            ['#^old/.+#'],
        ),
    ];
}

function app_cli_html_db_path_matches_cluster(string $relativePath, array $cluster): bool
{
    foreach ($cluster['patterns'] as $pattern) {
        if (
            preg_match($pattern, basename($relativePath)) === 1
            || preg_match($pattern, $relativePath) === 1
        ) {
            return true;
        }
    }

    return false;
}

/**
 * @param list<string> $sharedPages
 * @param list<string> $prefixes
 * @return list<string>
 */
function app_cli_html_db_current_pages_for_prefixes(array $sharedPages, array $prefixes): array
{
    $matches = [];
    foreach ($sharedPages as $page) {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($page, $prefix)) {
                $matches[$page] = true;
            }
        }
    }

    $result = array_keys($matches);
    sort($result);

    return $result;
}

/**
 * @return array{
 *     legacy_root:string,
 *     shared_root:string,
 *     legacy_files:list<string>,
 *     shared_pages:list<string>,
 *     clusters:list<array{
 *         id:string,
 *         status:string,
 *         summary:string,
 *         next_action:string,
 *         current_page_prefixes:list<string>,
 *         current_pages:list<string>,
 *         legacy_files:list<string>,
 *         legacy_file_count:int
 *     }>,
 *     status_counts:array<string,int>,
 *     unmapped_files:list<string>
 * }
 */
function app_cli_build_html_db_rewrite_map(): array
{
    $legacyRoot = app_cli_html_db_current_root();
    $sharedRoot = app_cli_html_db_shared_root();
    $legacyFiles = app_cli_scan_relative_files($legacyRoot);
    $sharedPages = app_cli_scan_shared_pages($sharedRoot);

    $assigned = [];
    $clusters = [];
    $statusCounts = [];

    foreach (app_cli_html_db_rewrite_clusters() as $cluster) {
        $matchedFiles = [];
        foreach ($legacyFiles as $relativePath) {
            if (isset($assigned[$relativePath])) {
                continue;
            }
            if (!app_cli_html_db_path_matches_cluster($relativePath, $cluster)) {
                continue;
            }

            $matchedFiles[] = $relativePath;
            $assigned[$relativePath] = true;
        }

        if ($matchedFiles === []) {
            continue;
        }

        sort($matchedFiles);
        $currentPages = app_cli_html_db_current_pages_for_prefixes(
            $sharedPages,
            $cluster['current_page_prefixes'],
        );

        $status = $cluster['status'];
        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + count($matchedFiles);

        $clusters[] = [
            'id' => $cluster['id'],
            'status' => $status,
            'summary' => $cluster['summary'],
            'next_action' => $cluster['next_action'],
            'current_page_prefixes' => $cluster['current_page_prefixes'],
            'current_pages' => $currentPages,
            'legacy_files' => $matchedFiles,
            'legacy_file_count' => count($matchedFiles),
        ];
    }

    $unmappedFiles = [];
    foreach ($legacyFiles as $relativePath) {
        if (!isset($assigned[$relativePath])) {
            $unmappedFiles[] = $relativePath;
        }
    }

    sort($unmappedFiles);

    return [
        'legacy_root' => $legacyRoot,
        'shared_root' => $sharedRoot,
        'legacy_files' => $legacyFiles,
        'shared_pages' => $sharedPages,
        'clusters' => $clusters,
        'status_counts' => $statusCounts,
        'unmapped_files' => $unmappedFiles,
    ];
}

function app_cli_render_html_db_rewrite_map_text(array $map): string
{
    $lines = [];
    $lines[] = 'HTML-DB rewrite map';
    $lines[] = 'legacy_root: ' . $map['legacy_root'];
    $lines[] = 'shared_root: ' . $map['shared_root'];
    $lines[] = 'legacy_file_count: ' . count($map['legacy_files']);
    $lines[] = 'cluster_count: ' . count($map['clusters']);
    $lines[] = 'unmapped_file_count: ' . count($map['unmapped_files']);
    foreach ($map['status_counts'] as $status => $count) {
        $lines[] = 'status[' . $status . ']: ' . $count;
    }
    $lines[] = '';

    foreach ($map['clusters'] as $cluster) {
        $lines[] = '[' . $cluster['status'] . '] ' . $cluster['id'] . ' (' . $cluster['legacy_file_count'] . ' files)';
        $lines[] = 'summary: ' . $cluster['summary'];
        if ($cluster['current_pages'] !== []) {
            $lines[] = 'current pages: ' . implode(', ', $cluster['current_pages']);
        } else {
            $lines[] = 'current pages: none';
        }
        $lines[] = 'next: ' . $cluster['next_action'];
        $lines[] = 'legacy files:';
        foreach ($cluster['legacy_files'] as $relativePath) {
            $lines[] = '  - ' . $relativePath;
        }
        $lines[] = '';
    }

    if ($map['unmapped_files'] !== []) {
        $lines[] = '[unmapped]';
        foreach ($map['unmapped_files'] as $relativePath) {
            $lines[] = '  - ' . $relativePath;
        }
        $lines[] = '';
    }

    return implode(PHP_EOL, $lines);
}

$parsed = app_cli_show_html_db_rewrite_map_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_show_html_db_rewrite_map_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_show_html_db_rewrite_map_usage() . PHP_EOL);
    exit(64);
}

try {
    $map = app_cli_build_html_db_rewrite_map();
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}

if ($parsed['format'] === 'json') {
    fwrite(
        STDOUT,
        json_encode($map, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL,
    );
    exit(0);
}

fwrite(STDOUT, app_cli_render_html_db_rewrite_map_text($map) . PHP_EOL);
