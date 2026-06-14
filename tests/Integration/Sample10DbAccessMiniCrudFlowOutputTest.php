<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample10DbAccessMiniCrudFlowOutputTest extends TestCase
{
    public function testReferenceOutputsStayInSync(): void
    {
        $result = app_sample10_dbaccess_mini_crud_flow_run(
            app_bootstrap(),
            'phpunit',
            app_sample10_dbaccess_mini_crud_flow_default_reference_root(),
        );

        self::assertTrue(
            $result['ok'],
            $this->failureMessageFromResult($result),
        );

        self::assertCount(
            count(APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_REFERENCE_SOURCE_OUTPUT_KEYS),
            $result['steps']['outputs'],
            'unexpected source output count in verification result',
        );

        self::assertCount(
            count(APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_FUNCTION_NAMES),
            is_array($result['steps']['db_access_functions'] ?? null)
                ? $result['steps']['db_access_functions']
                : [],
            'unexpected DB Access function count in verification result',
        );

        $listFunction = $result['steps']['db_access_functions'][APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_LIST_FUNCTION_NAME] ?? null;
        self::assertIsArray($listFunction, 'list function verification step missing');
        self::assertCount(
            count(APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_LIST_TARGET_FIELDS),
            is_array($listFunction['select_target_fields']['items'] ?? null)
                ? $listFunction['select_target_fields']['items']
                : [],
            'unexpected list select target field count in verification result',
        );
        self::assertCount(
            1,
            is_array($listFunction['select_wheres']['items'] ?? null)
                ? $listFunction['select_wheres']['items']
                : [],
            'unexpected list select where count in verification result',
        );

        $singleFunction = $result['steps']['db_access_functions'][APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_SINGLE_FUNCTION_NAME] ?? null;
        self::assertIsArray($singleFunction, 'single function verification step missing');
        self::assertCount(
            count(APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_SINGLE_TARGET_FIELDS),
            is_array($singleFunction['select_target_fields']['items'] ?? null)
                ? $singleFunction['select_target_fields']['items']
                : [],
            'unexpected single select target field count in verification result',
        );
        self::assertCount(
            1,
            is_array($singleFunction['select_wheres']['items'] ?? null)
                ? $singleFunction['select_wheres']['items']
                : [],
            'unexpected single select where count in verification result',
        );

        $insertFunction = $result['steps']['db_access_functions'][APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_INSERT_FUNCTION_NAME] ?? null;
        self::assertIsArray($insertFunction, 'insert function verification step missing');
        self::assertCount(
            count(APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_WRITE_TARGET_FIELDS),
            is_array($insertFunction['insert_target_fields']['items'] ?? null)
                ? $insertFunction['insert_target_fields']['items']
                : [],
            'unexpected insert target field count in verification result',
        );

        $updateFunction = $result['steps']['db_access_functions'][APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_UPDATE_FUNCTION_NAME] ?? null;
        self::assertIsArray($updateFunction, 'update function verification step missing');
        self::assertCount(
            count(APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_WRITE_TARGET_FIELDS),
            is_array($updateFunction['update_target_fields']['items'] ?? null)
                ? $updateFunction['update_target_fields']['items']
                : [],
            'unexpected update target field count in verification result',
        );
        self::assertCount(
            1,
            is_array($updateFunction['update_delete_wheres']['items'] ?? null)
                ? $updateFunction['update_delete_wheres']['items']
                : [],
            'unexpected update where count in verification result',
        );

        $deleteFunction = $result['steps']['db_access_functions'][APP_SAMPLE10_DBACCESS_MINI_CRUD_FLOW_DELETE_FUNCTION_NAME] ?? null;
        self::assertIsArray($deleteFunction, 'delete function verification step missing');
        self::assertCount(
            1,
            is_array($deleteFunction['update_delete_wheres']['items'] ?? null)
                ? $deleteFunction['update_delete_wheres']['items']
                : [],
            'unexpected delete where count in verification result',
        );

        foreach ($result['steps']['outputs'] as $output) {
            $sourceOutputKey = (string) ($output['source_output_key'] ?? '');
            $fileChecks = $output['file_checks'] ?? [];

            self::assertIsArray($fileChecks, 'file_checks is not an array: ' . $sourceOutputKey);

            foreach ($fileChecks as $fileCheck) {
                self::assertIsArray($fileCheck, 'file_check entry is not an array: ' . $sourceOutputKey);

                $relativePath = (string) ($fileCheck['relative_path'] ?? '');
                self::assertTrue(
                    (bool) ($fileCheck['ok'] ?? false),
                    'generated file does not match reference: '
                    . $sourceOutputKey
                    . '/'
                    . $relativePath,
                );
            }
        }
    }

    private function failureMessageFromResult(array $result): string
    {
        $payload = [
            'error' => $result['error'] ?? '',
            'assertion_errors' => $result['assertion_errors'] ?? [],
            'table_import' => $result['steps']['table_import'] ?? null,
            'data_class_preview_after_sync' => $result['steps']['data_class_preview_after_sync'] ?? null,
            'db_access_class_catalog' => $result['steps']['db_access_class_catalog'] ?? null,
            'db_access_function_catalog' => $result['steps']['db_access_function_catalog'] ?? null,
            'db_access_functions' => $result['steps']['db_access_functions'] ?? null,
            'outputs' => array_map(
                static function ($output): array {
                    if (!is_array($output)) {
                        return ['invalid_output' => true];
                    }

                    return [
                        'source_output_key' => $output['source_output_key'] ?? '',
                        'published_root' => $output['published_root'] ?? '',
                        'reference_root' => $output['reference_root'] ?? '',
                        'file_checks' => $output['file_checks'] ?? [],
                    ];
                },
                is_array($result['steps']['outputs'] ?? null) ? $result['steps']['outputs'] : [],
            ),
        ];

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample10 dbaccess mini crud flow verification returned ok=false';
    }
}
