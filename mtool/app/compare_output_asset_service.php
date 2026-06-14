<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/runtime_storage_paths.php';

/**
 * @return list<string>
 */
function app_compare_output_default_ignore_pattern_list(): array
{
    return [
        '/\/fonts$/i',
        '/\/images$/i',
        '/\/js$/i',
        '/\/database_backup$/i',
        '/\/root_tools$/i',
        '/\/css$/i',
        '/\/live_check$/i',
        '/\/PHPMailer$/i',
        '/\/enroll_logs$/i',
        '/\/obj$/i',
        '/\/bin$/i',
        '/\/.gradle$/i',
        '/\/gradle$/i',
        '/\/.idea$/i',
        '/\/build$/i',
        '/\/.vs$/i',
        '/\/packages$/i',
        '/\/no[ _\-]?upload$/i',
        '/\/old$/i',
        '/\/old - modules$/i',
        '/\/Templates$/i',
        '/\/_notes$/i',
        '/\/_cnskin$/i',
        '/\/past_terms_of_service$/i',
        '/\/_module$/i',
        '/\/photocinema$/i',
        '/\/cn26$/i',
        '/\/_src$/i',
        '/phpMyAdmin.*management$/i',
        '/\/資料$/i',
        '/\/素材$/i',
        '/\/メモ$/i',
        '/\/移動用ドメイン各種$/i',
        '/\/\.DS_Store$/i',
    ];
}

/**
 * @return array{
 *     template:string,
 *     line_template:string
 * }
 */
function app_compare_output_builtin_template_spec(string $outputFileType): array
{
    return match ($outputFileType) {
        'Text' => [
            'template' => "# Deviation List\n__LINES__",
            'line_template' => "__PATH_A__\t__PATH_B__\n",
        ],
        'WindowsBatch' => [
            'template' => "@echo off\n__LINES__",
            'line_template' => "\"__COMPARE_COMMAND__\" \"__PATH_A__\" \"__PATH_B__\"\n",
        ],
        'MacCommand' => [
            'template' => "# Deviation List\n__LINES__",
            'line_template' => "__COMPARE_COMMAND__ \"__PATH_A__\" \"__PATH_B__\"\n",
        ],
        default => [
            'template' => '',
            'line_template' => '',
        ],
    };
}

function app_compare_output_default_ignore_asset_text(): string
{
    $lines = ['# 無視するディレクトリ設定', ''];
    foreach (app_compare_output_default_ignore_pattern_list() as $pattern) {
        $lines[] = $pattern;
    }

    return implode("\n", $lines) . "\n";
}

/**
 * @return array<string,array{
 *     form_key:string,
 *     filename:string,
 *     label:string,
 *     description:string,
 *     kind:string,
 *     output_file_type:string,
 *     required_tokens:list<string>,
 *     default_content:string
 * }>
 */
function app_compare_output_asset_specs(): array
{
    $textSpec = app_compare_output_builtin_template_spec('Text');
    $windowsBatchSpec = app_compare_output_builtin_template_spec('WindowsBatch');
    $macCommandSpec = app_compare_output_builtin_template_spec('MacCommand');

    return [
        'template_text' => [
            'form_key' => 'template_text',
            'filename' => 'compare_output_template_for_text.txt',
            'label' => 'Text Template',
            'description' => 'Text 出力全体の template です。`__LINES__` を含めます。',
            'kind' => 'template',
            'output_file_type' => 'Text',
            'required_tokens' => ['__LINES__'],
            'default_content' => $textSpec['template'],
        ],
        'template_text_line' => [
            'form_key' => 'template_text_line',
            'filename' => 'compare_output_template_for_text_line.txt',
            'label' => 'Text Line Template',
            'description' => 'Text 出力 1 行分の template です。`__PATH_A__` と `__PATH_B__` を含めます。',
            'kind' => 'template_line',
            'output_file_type' => 'Text',
            'required_tokens' => ['__PATH_A__', '__PATH_B__'],
            'default_content' => $textSpec['line_template'],
        ],
        'template_windows_batch' => [
            'form_key' => 'template_windows_batch',
            'filename' => 'compare_output_template_for_windows_batch.txt',
            'label' => 'Windows Batch Template',
            'description' => 'Windows Batch 出力全体の template です。`__LINES__` を含めます。',
            'kind' => 'template',
            'output_file_type' => 'WindowsBatch',
            'required_tokens' => ['__LINES__'],
            'default_content' => $windowsBatchSpec['template'],
        ],
        'template_windows_batch_line' => [
            'form_key' => 'template_windows_batch_line',
            'filename' => 'compare_output_template_for_windows_batch_line.txt',
            'label' => 'Windows Batch Line Template',
            'description' => 'Windows Batch 出力 1 行分の template です。`__COMPARE_COMMAND__`、`__PATH_A__`、`__PATH_B__` を含めます。',
            'kind' => 'template_line',
            'output_file_type' => 'WindowsBatch',
            'required_tokens' => ['__COMPARE_COMMAND__', '__PATH_A__', '__PATH_B__'],
            'default_content' => $windowsBatchSpec['line_template'],
        ],
        'template_mac_command' => [
            'form_key' => 'template_mac_command',
            'filename' => 'compare_output_template_for_mac_command.txt',
            'label' => 'Mac Command Template',
            'description' => 'Mac Command 出力全体の template です。`__LINES__` を含めます。',
            'kind' => 'template',
            'output_file_type' => 'MacCommand',
            'required_tokens' => ['__LINES__'],
            'default_content' => $macCommandSpec['template'],
        ],
        'template_mac_command_line' => [
            'form_key' => 'template_mac_command_line',
            'filename' => 'compare_output_template_for_mac_command_line.txt',
            'label' => 'Mac Command Line Template',
            'description' => 'Mac Command 出力 1 行分の template です。`__COMPARE_COMMAND__`、`__PATH_A__`、`__PATH_B__` を含めます。',
            'kind' => 'template_line',
            'output_file_type' => 'MacCommand',
            'required_tokens' => ['__COMPARE_COMMAND__', '__PATH_A__', '__PATH_B__'],
            'default_content' => $macCommandSpec['line_template'],
        ],
        'ignore_rules' => [
            'form_key' => 'ignore_rules',
            'filename' => 'compare_ignore_dir_setting_regex.txt',
            'label' => 'Ignore Rule Asset',
            'description' => '1 行 1 正規表現です。空行と `#` コメント行は無視します。',
            'kind' => 'ignore_rule',
            'output_file_type' => '',
            'required_tokens' => [],
            'default_content' => app_compare_output_default_ignore_asset_text(),
        ],
    ];
}

function app_compare_output_asset_content_normalize(string $content): string
{
    return str_replace(["\r\n", "\r"], "\n", $content);
}

function app_compare_output_asset_storage_root(array $app, string $projectKey): string
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        throw new InvalidArgumentException('project key の形式が不正です。');
    }

    return app_runtime_storage_compare_output_assets_root($app, $normalizedProjectKey);
}

/**
 * @return array{
 *     ok:bool,
 *     patterns:list<string>,
 *     error:string
 * }
 */
function app_compare_output_ignore_patterns_from_text(string $content): array
{
    $normalized = app_compare_output_asset_content_normalize($content);
    $patterns = [];
    $lines = explode("\n", $normalized);

    foreach ($lines as $lineNumber => $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        set_error_handler(static fn (): bool => true);
        $result = preg_match($trimmed, '');
        restore_error_handler();

        if ($result === false) {
            return [
                'ok' => false,
                'patterns' => [],
                'error' => 'ignore rule asset の ' . ($lineNumber + 1) . ' 行目の正規表現が不正です: ' . $trimmed,
            ];
        }

        $patterns[] = $trimmed;
    }

    return [
        'ok' => true,
        'patterns' => $patterns,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         form_key:string,
 *         filename:string,
 *         label:string,
 *         description:string,
 *         kind:string,
 *         output_file_type:string,
 *         required_tokens:list<string>,
 *         default_content:string,
 *         current_content:string,
 *         is_custom:bool,
 *         asset_path:string
 *     }>,
 *     storage_root:string,
 *     custom_count:int,
 *     error:string
 * }
 */
function app_compare_output_asset_catalog(array $app, string $projectKey): array
{
    try {
        $storageRoot = app_compare_output_asset_storage_root($app, $projectKey);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'storage_root' => '',
            'custom_count' => 0,
            'error' => $throwable->getMessage(),
        ];
    }

    $items = [];
    $customCount = 0;

    foreach (app_compare_output_asset_specs() as $spec) {
        $assetPath = $storageRoot . '/' . $spec['filename'];
        $isCustom = is_file($assetPath);
        $currentContent = $spec['default_content'];

        if ($isCustom) {
            $readContent = file_get_contents($assetPath);
            if ($readContent === false) {
                return [
                    'ok' => false,
                    'items' => [],
                    'storage_root' => $storageRoot,
                    'custom_count' => $customCount,
                    'error' => 'compare output asset の読み込みに失敗しました: ' . $assetPath,
                ];
            }

            $currentContent = app_compare_output_asset_content_normalize($readContent);
            $customCount++;
        }

        $items[] = [
            'form_key' => $spec['form_key'],
            'filename' => $spec['filename'],
            'label' => $spec['label'],
            'description' => $spec['description'],
            'kind' => $spec['kind'],
            'output_file_type' => $spec['output_file_type'],
            'required_tokens' => $spec['required_tokens'],
            'default_content' => $spec['default_content'],
            'current_content' => $currentContent,
            'is_custom' => $isCustom,
            'asset_path' => $assetPath,
        ];
    }

    return [
        'ok' => true,
        'items' => $items,
        'storage_root' => $storageRoot,
        'custom_count' => $customCount,
        'error' => '',
    ];
}

/**
 * @param array<string,string> $inputByFormKey
 * @return array{
 *     input_by_form_key:array<string,string>,
 *     contents_by_filename:array<string,string>,
 *     errors:list<string>
 * }
 */
function app_validate_compare_output_asset_submission(array $inputByFormKey): array
{
    $normalizedInput = [];
    $contentsByFilename = [];
    $errors = [];

    foreach (app_compare_output_asset_specs() as $spec) {
        $content = app_compare_output_asset_content_normalize((string) ($inputByFormKey[$spec['form_key']] ?? ''));
        $normalizedInput[$spec['form_key']] = $content;

        foreach ($spec['required_tokens'] as $token) {
            if (!str_contains($content, $token)) {
                $errors[] = $spec['label'] . ' には ' . $token . ' を含めてください。';
            }
        }

        if ($spec['kind'] === 'ignore_rule') {
            $ignoreRuleResult = app_compare_output_ignore_patterns_from_text($content);
            if (!$ignoreRuleResult['ok']) {
                $errors[] = $ignoreRuleResult['error'];
            }
        }

        $contentsByFilename[$spec['filename']] = $content;
    }

    return [
        'input_by_form_key' => $normalizedInput,
        'contents_by_filename' => $contentsByFilename,
        'errors' => $errors,
    ];
}

function app_compare_output_asset_ensure_directory(string $directory): void
{
    if (is_dir($directory)) {
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('compare output asset directory を作成できませんでした: ' . $directory);
    }
}

/**
 * @param array<string,string> $contentsByFilename
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_save_compare_output_assets(array $app, string $projectKey, array $contentsByFilename): array
{
    try {
        $storageRoot = app_compare_output_asset_storage_root($app, $projectKey);
        $specs = app_compare_output_asset_specs();
        $specsByFilename = [];
        foreach ($specs as $spec) {
            $specsByFilename[$spec['filename']] = $spec;
        }

        app_compare_output_asset_ensure_directory($storageRoot);

        foreach ($contentsByFilename as $filename => $content) {
            if (!isset($specsByFilename[$filename])) {
                continue;
            }

            $spec = $specsByFilename[$filename];
            $assetPath = $storageRoot . '/' . $filename;
            $normalizedContent = app_compare_output_asset_content_normalize($content);
            if ($normalizedContent === app_compare_output_asset_content_normalize($spec['default_content'])) {
                if (is_file($assetPath) && !unlink($assetPath)) {
                    throw new RuntimeException('default に戻すための asset 削除に失敗しました: ' . $assetPath);
                }
                continue;
            }

            if (file_put_contents($assetPath, $normalizedContent) === false) {
                throw new RuntimeException('compare output asset の保存に失敗しました: ' . $assetPath);
            }
        }

        $remainingEntries = @scandir($storageRoot);
        if (is_array($remainingEntries)) {
            $realEntries = array_values(array_diff($remainingEntries, ['.', '..']));
            if ($realEntries === []) {
                @rmdir($storageRoot);
            }
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }

    return [
        'ok' => true,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_reset_compare_output_assets(array $app, string $projectKey): array
{
    try {
        $storageRoot = app_compare_output_asset_storage_root($app, $projectKey);
        foreach (app_compare_output_asset_specs() as $spec) {
            $assetPath = $storageRoot . '/' . $spec['filename'];
            if (is_file($assetPath) && !unlink($assetPath)) {
                throw new RuntimeException('compare output asset の削除に失敗しました: ' . $assetPath);
            }
        }

        if (is_dir($storageRoot)) {
            @rmdir($storageRoot);
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }

    return [
        'ok' => true,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     template:string,
 *     line_template:string,
 *     error:string
 * }
 */
function app_compare_output_template_spec_for_project(array $app, string $projectKey, string $outputFileType): array
{
    $catalogResult = app_compare_output_asset_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'template' => '',
            'line_template' => '',
            'error' => $catalogResult['error'],
        ];
    }

    $requiredFormKeys = match ($outputFileType) {
        'Text' => ['template_text', 'template_text_line'],
        'WindowsBatch' => ['template_windows_batch', 'template_windows_batch_line'],
        'MacCommand' => ['template_mac_command', 'template_mac_command_line'],
        default => [],
    };

    if ($requiredFormKeys === []) {
        return [
            'ok' => false,
            'template' => '',
            'line_template' => '',
            'error' => '未対応の output file type です。',
        ];
    }

    $contentByFormKey = [];
    foreach ($catalogResult['items'] as $item) {
        $contentByFormKey[$item['form_key']] = $item['current_content'];
    }

    return [
        'ok' => true,
        'template' => (string) ($contentByFormKey[$requiredFormKeys[0]] ?? ''),
        'line_template' => (string) ($contentByFormKey[$requiredFormKeys[1]] ?? ''),
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     patterns:list<string>,
 *     error:string
 * }
 */
function app_compare_output_ignore_patterns_for_project(array $app, string $projectKey): array
{
    $catalogResult = app_compare_output_asset_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'patterns' => [],
            'error' => $catalogResult['error'],
        ];
    }

    foreach ($catalogResult['items'] as $item) {
        if ($item['form_key'] !== 'ignore_rules') {
            continue;
        }

        return app_compare_output_ignore_patterns_from_text($item['current_content']);
    }

    return [
        'ok' => true,
        'patterns' => app_compare_output_default_ignore_pattern_list(),
        'error' => '',
    ];
}
