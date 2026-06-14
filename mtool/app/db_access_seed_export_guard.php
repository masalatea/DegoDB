<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository_pdo.php';

function app_db_access_seed_export_function_key(string $sourceName, string $functionName): string
{
    return strtolower(trim($sourceName)) . "\n" . strtolower(trim($functionName));
}

/**
 * @param list<array<string,mixed>> $classRows
 * @param list<array<string,mixed>> $functionRows
 * @param array{
 *     select_wheres:list<array<string,mixed>>,
 *     insert_target_fields:list<array<string,mixed>>,
 *     update_target_fields:list<array<string,mixed>>,
 *     update_delete_wheres:list<array<string,mixed>>
 * } $designerRows
 * @return list<string>
 */
function app_db_access_seed_export_collect_blob_contract_errors(
    array $app,
    array $classRows,
    array $functionRows,
    array $designerRows,
): array {
    $errors = [];

    $classBySource = [];
    foreach ($classRows as $row) {
        $sourceName = trim((string) ($row['source_name'] ?? ''));
        if ($sourceName === '') {
            continue;
        }

        $classBySource[strtolower($sourceName)] = $row;
    }

    $functionByKey = [];
    foreach ($functionRows as $row) {
        $sourceName = trim((string) ($row['source_name'] ?? ''));
        $functionName = trim((string) ($row['function_name'] ?? ''));
        if ($sourceName === '' || $functionName === '') {
            continue;
        }

        $functionByKey[app_db_access_seed_export_function_key($sourceName, $functionName)] = $row;
        $classRow = $classBySource[strtolower($sourceName)] ?? null;
        $blobTargetError = app_pdo_validate_db_access_function_blob_target_constraint(
            $app,
            [
                'source_name' => $sourceName,
                'function_name' => $functionName,
                'action_type' => (string) ($row['action_type'] ?? ''),
                'is_blob_target' => ((int) ($row['is_blob_target'] ?? 0)) === 1 ? '1' : '0',
                'last_detected_dbaccess_file' => (string) ($classRow['last_detected_dbaccess_file'] ?? ''),
            ],
        );
        if ($blobTargetError !== '') {
            $errors[] = 'function ' . $sourceName . '.' . $functionName . ': ' . $blobTargetError;
        }
    }

    foreach (
        [
            'select_wheres' => false,
            'insert_target_fields' => true,
            'update_target_fields' => true,
            'update_delete_wheres' => false,
        ] as $sectionName => $supportsBlobTarget
    ) {
        foreach ($designerRows[$sectionName] ?? [] as $row) {
            $parameterDataType = trim((string) ($row['parameter_data_type'] ?? ''));
            if ($parameterDataType !== 'file') {
                continue;
            }

            $sourceName = trim((string) ($row['source_name'] ?? ''));
            $functionName = trim((string) ($row['function_name'] ?? ''));
            if ($sourceName === '' || $functionName === '') {
                $errors[] = $sectionName . ': file data type row に source/function がありません。';
                continue;
            }

            if (!$supportsBlobTarget) {
                $errors[] = $sectionName . ' ' . $sourceName . '.' . $functionName
                    . ': file data type はこの designer row type では未対応です。';
                continue;
            }

            $functionRow = $functionByKey[app_db_access_seed_export_function_key($sourceName, $functionName)] ?? null;
            if (!is_array($functionRow)) {
                $errors[] = $sectionName . ' ' . $sourceName . '.' . $functionName
                    . ': 対応する function metadata が見つかりません。';
                continue;
            }

            $classRow = $classBySource[strtolower($sourceName)] ?? null;
            $fileParameterError = app_pdo_validate_db_access_function_file_parameter_constraint(
                $app,
                $sourceName,
                $functionName,
                $parameterDataType,
                ((int) ($functionRow['is_blob_target'] ?? 0)) === 1 ? '1' : '0',
                (string) ($classRow['last_detected_dbaccess_file'] ?? ''),
            );
            if ($fileParameterError !== '') {
                $errors[] = $sectionName . ' ' . $sourceName . '.' . $functionName . ': ' . $fileParameterError;
            }
        }
    }

    return $errors;
}
