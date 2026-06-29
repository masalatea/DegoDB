<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/shared/shared_contract_core.php';

use PHPUnit\Framework\TestCase;

final class SharedContractCoreVocabularyTest extends TestCase
{
    public function testSample02TaskFixtureDefinesManifestV0Vocabulary(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $validation = app_shared_contract_core_validate_manifest($manifest);

        self::assertTrue($validation['ok'], json_encode($validation['errors'], JSON_PRETTY_PRINT));
        self::assertSame('shared-contract-manifest-v0', $manifest['manifest_version']);

        $contract = $manifest['contracts'][0] ?? null;
        self::assertIsArray($contract);
        self::assertSame('task', $contract['contract_key']);
        self::assertSame([
            'logical_name' => 'Task',
            'physical_name' => 'task',
            'generated_name' => 'Task',
        ], $contract['entity']);

        self::assertSame(
            ['id', 'title', 'status', 'sort_order', 'is_pinned', 'published_at', 'note'],
            array_column($contract['fields'], 'physical_name'),
        );
        self::assertSame(
            ['id', 'title', 'status', 'sortOrder', 'isPinned', 'publishedAt', 'note'],
            array_column($contract['fields'], 'generated_name'),
        );
        self::assertSame(
            ['integer', 'string', 'string', 'integer', 'boolean', 'datetime', 'text'],
            array_column($contract['fields'], 'type'),
        );

        $fieldsByPhysicalName = [];
        foreach ($contract['fields'] as $field) {
            self::assertIsArray($field);
            $fieldsByPhysicalName[(string) $field['physical_name']] = $field;
        }

        self::assertTrue($fieldsByPhysicalName['id']['is_key']);
        self::assertFalse($fieldsByPhysicalName['id']['nullable']);
        self::assertSame('draft', $fieldsByPhysicalName['status']['default']);
        self::assertSame(0, $fieldsByPhysicalName['sort_order']['default']);
        self::assertFalse($fieldsByPhysicalName['is_pinned']['default']);
        self::assertTrue($fieldsByPhysicalName['published_at']['nullable']);
        self::assertNull($fieldsByPhysicalName['note']['default']);
        self::assertSame(
            app_shared_contract_core_reserved_local_metadata_columns(),
            $contract['local_metadata']['reserved_columns'],
        );
        self::assertSame('reject', $contract['local_metadata']['collision_policy']);
    }

    public function testValidatorRejectsBusinessFieldCollidingWithLocalMetadata(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $manifest['contracts'][0]['fields'][] = [
            'logical_name' => 'Dirty',
            'physical_name' => 'dirty',
            'generated_name' => 'dirty',
            'type' => 'boolean',
            'nullable' => false,
            'default' => false,
            'is_key' => false,
            'storage_role' => 'business',
        ];

        $validation = app_shared_contract_core_validate_manifest($manifest);

        self::assertFalse($validation['ok']);
        self::assertContains('business field collides with reserved local metadata column: dirty', $validation['errors']);
    }

    public function testValidatorRejectsMissingRequiredContractSemantics(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        unset($manifest['contracts'][0]['fields'][0]['nullable']);
        unset($manifest['contracts'][0]['fields'][1]['default']);
        $manifest['contracts'][0]['fields'][2]['is_key'] = 'no';
        $manifest['contracts'][0]['fields'][3]['type'] = 'json';
        $manifest['contracts'][0]['local_metadata']['collision_policy'] = 'append';

        $validation = app_shared_contract_core_validate_manifest($manifest);

        self::assertFalse($validation['ok']);
        self::assertContains('contracts[0].fields[0].nullable must be boolean', $validation['errors']);
        self::assertContains('contracts[0].fields[1].default must be present', $validation['errors']);
        self::assertContains('contracts[0].fields[2].is_key must be boolean', $validation['errors']);
        self::assertContains('contracts[0].fields[3].type is unsupported', $validation['errors']);
        self::assertContains('contracts[0].local_metadata.collision_policy must be reject', $validation['errors']);
    }

    public function testValidatorRejectsDuplicateIdentityAndMissingKey(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $manifest['contracts'][0]['fields'][0]['is_key'] = false;
        $manifest['contracts'][0]['fields'][1]['physical_name'] = 'id';
        $manifest['contracts'][0]['fields'][1]['generated_name'] = 'id';

        $validation = app_shared_contract_core_validate_manifest($manifest);

        self::assertFalse($validation['ok']);
        self::assertContains('duplicate physical_name: id', $validation['errors']);
        self::assertContains('duplicate generated_name: id', $validation['errors']);
        self::assertContains('contracts[0].fields must include at least one key field', $validation['errors']);
    }
}

