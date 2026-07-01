<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/no_code_screen_definition.php';
require_once dirname(__DIR__, 2) . '/mtool/shared/shared_contract_core.php';

use PHPUnit\Framework\TestCase;

final class NoCodeScreenDefinitionTest extends TestCase
{
    public function testBuildsListDetailFormDefinitionsFromSharedContractAndManagedOperations(): void
    {
        $result = app_no_code_screen_definition_from_snapshots(
            'SCREEN-DEF-TEST',
            $this->managedScreenManifest(),
            $this->managedOperations(),
            $this->editorPrincipal(),
        );

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('no-code-screen-definition-v0', $result['definition']['definition_version'] ?? '');
        self::assertSame('SCREEN-DEF-TEST', $result['definition']['project_key'] ?? '');

        $contract = $result['definition']['contracts'][0] ?? null;
        self::assertIsArray($contract);
        self::assertSame('task', $contract['contract_key'] ?? '');
        self::assertSame([
            'sync_role' => 'server-copy',
            'app_persistence_role' => 'local-copy',
            'sync_status_display' => true,
        ], $contract['storage_hint'] ?? []);

        self::assertSame(['list', 'detail', 'form'], array_column($contract['screens'], 'screen_type'));
        self::assertTrue($contract['screens'][0]['sync_status_hint'] ?? false);
        self::assertTrue($contract['screens'][1]['sync_status_hint'] ?? false);
        self::assertFalse($contract['screens'][2]['sync_status_hint'] ?? true);

        $fieldsByKey = $this->indexBy($contract['screens'][2]['fields'] ?? [], 'field_key');
        self::assertArrayNotHasKey('id', $fieldsByKey);
        self::assertFalse($fieldsByKey['note']['readonly'] ?? true);
        self::assertTrue($fieldsByKey['title']['required'] ?? false);

        $allFieldsByKey = $this->indexBy($contract['screens'][0]['fields'] ?? [], 'field_key');
        self::assertTrue($allFieldsByKey['id']['readonly'] ?? false);

        $actionsByKey = $this->indexBy($contract['actions'] ?? [], 'action_key');
        self::assertSame('enabled', $actionsByKey['read_task']['availability'] ?? '');
        self::assertSame('enabled', $actionsByKey['update_note']['availability'] ?? '');
        self::assertSame([], $actionsByKey['update_note']['policy']['failed_checks'] ?? ['missing']);

        $formActionsByKey = $this->indexBy($contract['screens'][2]['actions'] ?? [], 'action_key');
        self::assertSame('update', $formActionsByKey['update_note']['operation_type'] ?? '');
        self::assertArrayNotHasKey('read_task', $formActionsByKey);
    }

    public function testDisablesActionsWhenPrincipalDoesNotSatisfyOperationPolicy(): void
    {
        $result = app_no_code_screen_definition_from_snapshots(
            'SCREEN-DEF-TEST',
            $this->managedScreenManifest(),
            $this->managedOperations(),
            $this->viewerPrincipal(),
        );

        self::assertTrue($result['ok'], $result['error']);
        $contract = $result['definition']['contracts'][0] ?? [];
        self::assertIsArray($contract);
        $actionsByKey = $this->indexBy($contract['actions'] ?? [], 'action_key');

        self::assertSame('enabled', $actionsByKey['read_task']['availability'] ?? '');
        self::assertSame('disabled', $actionsByKey['update_note']['availability'] ?? '');
        self::assertContains('permission_key:project.edit', $actionsByKey['update_note']['policy']['failed_checks'] ?? []);
        self::assertContains('required_role:editor', $actionsByKey['update_note']['policy']['failed_checks'] ?? []);
        self::assertContains('required_scope:task:write', $actionsByKey['update_note']['policy']['failed_checks'] ?? []);
    }

    public function testFailsClosedWhenNoManagedScreenContractExists(): void
    {
        $manifest = $this->managedScreenManifest();
        unset($manifest['contracts'][0]['contract_metadata']['no_code_role']);

        $result = app_no_code_screen_definition_from_snapshots(
            'SCREEN-DEF-TEST',
            $manifest,
            $this->managedOperations(),
            $this->editorPrincipal(),
        );

        self::assertFalse($result['ok']);
        self::assertSame([], $result['definition']);
        self::assertSame('managed-screen contract がありません。', $result['error']);
    }

    /**
     * @return array<string,mixed>
     */
    private function managedScreenManifest(): array
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $manifest['project_key'] = 'SCREEN-DEF-TEST';
        $manifest['contracts'][0]['contract_metadata'] = [
            'status' => 'active',
            'sync_role' => 'server-copy',
            'no_code_role' => 'managed-screen',
            'app_persistence_role' => 'local-copy',
            'source_of_truth' => 'phpunit',
        ];

        foreach ($manifest['contracts'][0]['fields'] as $index => $field) {
            if (($field['physical_name'] ?? '') === 'note') {
                $manifest['contracts'][0]['fields'][$index]['contract_metadata'] = [
                    'operation_role' => 'editable',
                    'source_of_truth' => 'phpunit',
                ];
            }
        }

        return $manifest;
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function managedOperations(): array
    {
        return [
            [
                'project_key' => 'SCREEN-DEF-TEST',
                'operation_key' => 'read_task',
                'contract_key' => 'task',
                'name' => 'Read Task',
                'operation_type' => 'read',
                'status' => 'active',
                'storage_policy' => 'business-only',
                'permission_key' => 'project.read',
                'required_roles' => ['viewer'],
                'required_scopes' => [],
                'required_claims' => [],
                'fields' => [
                    [
                        'field_physical_name' => 'id',
                        'field_role' => 'key',
                        'is_required' => true,
                        'allow_client_write' => false,
                    ],
                ],
            ],
            [
                'project_key' => 'SCREEN-DEF-TEST',
                'operation_key' => 'update_note',
                'contract_key' => 'task',
                'name' => 'Update Note',
                'operation_type' => 'update',
                'status' => 'active',
                'storage_policy' => 'business-only',
                'permission_key' => 'project.edit',
                'required_roles' => ['editor'],
                'required_scopes' => ['task:write'],
                'required_claims' => [],
                'fields' => [
                    [
                        'field_physical_name' => 'id',
                        'field_role' => 'key',
                        'is_required' => true,
                        'allow_client_write' => false,
                    ],
                    [
                        'field_physical_name' => 'note',
                        'field_role' => 'input',
                        'is_required' => true,
                        'allow_client_write' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function editorPrincipal(): array
    {
        return [
            'id' => 'editor-1',
            'display_name' => 'Editor',
            'auth_source' => 'stub',
            'site' => 'local',
            'roles' => ['viewer', 'editor'],
            'project_roles' => [
                'SCREEN-DEF-TEST' => ['viewer', 'editor'],
            ],
            'scopes' => ['task:write'],
            'claims' => [],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function viewerPrincipal(): array
    {
        return [
            'id' => 'viewer-1',
            'display_name' => 'Viewer',
            'auth_source' => 'stub',
            'site' => 'local',
            'roles' => ['viewer'],
            'project_roles' => [
                'SCREEN-DEF-TEST' => ['viewer'],
            ],
            'scopes' => [],
            'claims' => [],
        ];
    }

    /**
     * @param list<array<string,mixed>> $items
     * @return array<string,array<string,mixed>>
     */
    private function indexBy(array $items, string $key): array
    {
        $indexed = [];
        foreach ($items as $item) {
            $indexed[(string) ($item[$key] ?? '')] = $item;
        }

        return $indexed;
    }
}
