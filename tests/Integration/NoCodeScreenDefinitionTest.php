<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/no_code_screen_definition.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_mtool_dogfooding_probe.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_no_code_runtime_generator.php';
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
        self::assertSame([
            'intent' => 'screen',
            'source' => 'no_code_role:managed-screen',
            'allowed_intents' => ['screen', 'external_integration', 'sync', 'reporting', 'workflow', 'internal'],
            'presentation_layer' => 'view_variant',
        ], $contract['interface_usage'] ?? []);
        self::assertSame([
            'variant' => 'auto',
            'source' => 'derived:screen_type',
            'allowed_variants' => ['auto', 'standard_table', 'detail_record', 'edit_form', 'review_list'],
        ], $contract['view_variant_preference'] ?? []);
        self::assertSame([
            'profile_key' => 'task_auto',
            'source' => 'derived:default',
            'density' => 'standard',
            'emphasis' => 'balanced',
            'primary_fields' => [],
            'secondary_fields' => [],
            'field_groups' => [],
        ], $contract['presentation_profile'] ?? []);
        self::assertSame([], $contract['extension_slots'] ?? ['unexpected']);
        self::assertSame('task', $contract['traceability']['source_contract']['contract_key'] ?? '');
        self::assertSame('NO-CODE-RUNTIME', $contract['traceability']['source_output']['source_output_key'] ?? '');
        self::assertSame('managed_operation', $contract['traceability']['managed_operations'][0]['target'] ?? '');

        self::assertSame(['list', 'detail', 'form'], array_column($contract['screens'], 'screen_type'));
        self::assertSame(['standard_table', 'detail_record', 'edit_form'], array_column($contract['screens'], 'view_variant'));
        self::assertSame('standard', $contract['screens'][0]['presentation_hint']['density'] ?? '');
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

    public function testExplicitUsageIntentOverridesDerivedManagedScreenIntent(): void
    {
        $manifest = $this->managedScreenManifest();
        $manifest['contracts'][0]['contract_metadata']['usage_intent'] = 'workflow';

        $result = app_no_code_screen_definition_from_snapshots(
            'SCREEN-DEF-TEST',
            $manifest,
            $this->managedOperations(),
            $this->editorPrincipal(),
        );

        self::assertTrue($result['ok'], $result['error']);
        $contract = $result['definition']['contracts'][0] ?? [];
        self::assertIsArray($contract);
        self::assertSame('workflow', $contract['interface_usage']['intent'] ?? '');
        self::assertSame('usage_intent:explicit', $contract['interface_usage']['source'] ?? '');
    }

    public function testExplicitViewVariantPreferenceIsCarriedSeparatelyFromScreenVariants(): void
    {
        $manifest = $this->managedScreenManifest();
        $manifest['contracts'][0]['contract_metadata']['view_variant_preference'] = 'review_list';

        $result = app_no_code_screen_definition_from_snapshots(
            'SCREEN-DEF-TEST',
            $manifest,
            $this->managedOperations(),
            $this->editorPrincipal(),
        );

        self::assertTrue($result['ok'], $result['error']);
        $contract = $result['definition']['contracts'][0] ?? [];
        self::assertIsArray($contract);
        self::assertSame([
            'variant' => 'review_list',
            'source' => 'view_variant_preference:explicit',
            'allowed_variants' => ['auto', 'standard_table', 'detail_record', 'edit_form', 'review_list'],
        ], $contract['view_variant_preference'] ?? []);
        self::assertSame(['standard_table', 'detail_record', 'edit_form'], array_column($contract['screens'] ?? [], 'view_variant'));
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

    public function testBuildsMtoolSourceOutputReviewDogfoodingProbeDefinition(): void
    {
        $result = app_no_code_mtool_dogfooding_probe_screen_definition($this->mtoolViewerPrincipal());

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('MTOOL', $result['definition']['project_key'] ?? '');

        $contract = $result['definition']['contracts'][0] ?? null;
        self::assertIsArray($contract);
        self::assertSame('mtool_source_output_review', $contract['contract_key'] ?? '');
        self::assertSame('internal', $contract['interface_usage']['intent'] ?? '');
        self::assertSame('usage_intent:explicit', $contract['interface_usage']['source'] ?? '');
        self::assertSame('review_list', $contract['view_variant_preference']['variant'] ?? '');
        self::assertSame('view_variant_preference:explicit', $contract['view_variant_preference']['source'] ?? '');
        self::assertSame('mtool_source_output_review_compact', $contract['presentation_profile']['profile_key'] ?? '');
        self::assertSame('presentation_profile:explicit', $contract['presentation_profile']['source'] ?? '');
        self::assertSame('compact', $contract['presentation_profile']['density'] ?? '');
        self::assertSame('review', $contract['presentation_profile']['emphasis'] ?? '');
        self::assertSame(
            ['source_output_key', 'name', 'class_type'],
            $contract['presentation_profile']['primary_fields'] ?? [],
        );
        self::assertSame(
            ['artifact_strategy', 'target_binding_type', 'spec_visibility', 'source_output_dir'],
            $contract['presentation_profile']['secondary_fields'] ?? [],
        );
        self::assertSame(
            ['identity', 'artifact'],
            array_column($contract['presentation_profile']['field_groups'] ?? [], 'group_key'),
        );
        self::assertSame('compact', $contract['screens'][0]['presentation_hint']['density'] ?? '');
        self::assertSame('review', $contract['screens'][0]['presentation_hint']['emphasis'] ?? '');
        self::assertSame(
            ['related_settings_panel', 'artifact_status_panel', 'operator_actions_panel'],
            array_column($contract['extension_slots'] ?? [], 'slot_type'),
        );
        self::assertSame(
            ['review_source_output_artifact', 'request_source_output_publish'],
            array_column($contract['custom_operations'] ?? [], 'operation_key'),
        );
        self::assertSame('review_request', $contract['custom_operations'][0]['category'] ?? '');
        self::assertSame('external_handoff', $contract['custom_operations'][0]['side_effect_class'] ?? '');
        self::assertTrue($contract['custom_operations'][0]['csrf_required'] ?? false);
        self::assertSame('POST', $contract['custom_operations'][0]['route_boundary']['method'] ?? '');
        self::assertSame(
            '/projects/{project_key}/source-outputs/{source_output_key}/operations/review-source-output-artifact',
            $contract['custom_operations'][0]['route_boundary']['path'] ?? '',
        );
        self::assertContains('stale_artifact', $contract['custom_operations'][0]['route_boundary']['failure_modes'] ?? []);
        self::assertSame('deferred', $contract['custom_operations'][0]['availability_read_model']['operation_availability'] ?? '');
        self::assertSame('deferred', $contract['custom_operations'][0]['availability_read_model']['availability_state'] ?? '');
        self::assertSame('blocked', $contract['custom_operations'][0]['availability_read_model']['preflight_result'] ?? '');
        self::assertSame('metadata-only', $contract['custom_operations'][0]['availability_read_model']['execution_mode'] ?? '');
        self::assertFalse($contract['custom_operations'][0]['availability_read_model']['generated_button_enabled'] ?? true);
        self::assertSame(
            '/projects/{project_key}/source-outputs/{source_output_key}/operations/review-source-output-artifact',
            $contract['custom_operations'][0]['availability_read_model']['route_boundary']['path'] ?? '',
        );
        self::assertStringContainsString(
            'Execution route is not wired yet',
            $contract['custom_operations'][0]['unavailable_reason'] ?? '',
        );
        self::assertSame('POST', $contract['custom_operations'][1]['route_boundary']['method'] ?? '');
        self::assertSame(
            '/projects/{project_key}/source-outputs/{source_output_key}/operations/request-source-output-publish',
            $contract['custom_operations'][1]['route_boundary']['path'] ?? '',
        );
        self::assertSame('mtool_operator_admin', $contract['custom_operations'][1]['route_boundary']['auth_guard'] ?? '');
        self::assertContains('duplicate_request', $contract['custom_operations'][1]['route_boundary']['failure_modes'] ?? []);
        self::assertSame('deferred', $contract['custom_operations'][1]['availability_read_model']['availability_state'] ?? '');
        self::assertStringContainsString(
            'Publish request execution is deferred',
            $contract['custom_operations'][1]['availability_read_model']['availability_reason'] ?? '',
        );
        self::assertSame(
            ['related_settings_panel', 'artifact_status_panel'],
            array_column($contract['screens'][0]['extension_slots'] ?? [], 'slot_type'),
        );
        self::assertSame(
            ['related_settings_panel', 'artifact_status_panel', 'operator_actions_panel'],
            array_column($contract['screens'][1]['extension_slots'] ?? [], 'slot_type'),
        );
        self::assertSame([], $contract['screens'][2]['extension_slots'] ?? ['unexpected']);
        self::assertSame('NO-CODE-RUNTIME', $contract['traceability']['source_output']['source_output_key'] ?? '');
        self::assertSame(
            ['source_output_key', 'name', 'class_type', 'artifact_strategy', 'target_binding_type', 'spec_visibility', 'source_output_dir'],
            array_column($contract['screens'][0]['fields'] ?? [], 'field_key'),
        );

        $fieldsByKey = $this->indexBy($contract['screens'][0]['fields'] ?? [], 'field_key');
        self::assertTrue($fieldsByKey['source_output_key']['readonly'] ?? false);
        self::assertTrue($fieldsByKey['artifact_strategy']['readonly'] ?? false);

        $actionsByKey = $this->indexBy($contract['actions'] ?? [], 'action_key');
        self::assertSame('read', $actionsByKey['review_mtool_source_output_profile']['operation_type'] ?? '');
        self::assertSame('enabled', $actionsByKey['review_mtool_source_output_profile']['availability'] ?? '');

        $payload = app_project_output_no_code_runtime_payload('MTOOL', $result);
        $bridgeContract = app_project_output_no_code_react_bridge_contract($payload);
        $handoffsByKey = $this->indexBy($bridgeContract['custom_operation_handoffs'] ?? [], 'operation_key');
        self::assertSame('source_output_publish_request', $handoffsByKey['request_source_output_publish']['adapter_handoff'] ?? '');
        self::assertSame('source_output_artifact_review', $handoffsByKey['review_source_output_artifact']['adapter_handoff'] ?? '');
        self::assertSame('deferred', $handoffsByKey['review_source_output_artifact']['availability'] ?? '');
        self::assertSame('POST', $handoffsByKey['review_source_output_artifact']['route_boundary']['method'] ?? '');
        self::assertSame('mtool_operator_admin', $handoffsByKey['review_source_output_artifact']['route_boundary']['auth_guard'] ?? '');
        self::assertSame('POST', $handoffsByKey['request_source_output_publish']['route_boundary']['method'] ?? '');
        self::assertSame(
            '/projects/{project_key}/source-outputs/{source_output_key}/operations/request-source-output-publish',
            $handoffsByKey['request_source_output_publish']['route_boundary']['path'] ?? '',
        );
        self::assertStringContainsString(
            'Execution route is not wired yet',
            $handoffsByKey['review_source_output_artifact']['unavailable_reason'] ?? '',
        );
        self::assertSame(
            ['mtool_source_output_review_list', 'mtool_source_output_review_detail', 'mtool_source_output_review_form'],
            $handoffsByKey['review_source_output_artifact']['screen_keys'] ?? [],
        );
    }

    public function testMtoolDogfoodingProbeUsesNormalNoCodeRuntimeArtifactShape(): void
    {
        $definitionResult = app_no_code_mtool_dogfooding_probe_screen_definition($this->mtoolViewerPrincipal());
        $payload = app_project_output_no_code_runtime_payload('MTOOL', $definitionResult);
        $files = app_project_output_no_code_runtime_build_emitted_files($payload);

        self::assertTrue($payload['ok'], implode(', ', $payload['errors'] ?? []));
        self::assertSame(['screen-definition.json', 'runtime-preview.json', 'runtime-preview.html', 'README.md'], array_keys($files));

        $screenDefinition = json_decode($files['screen-definition.json'], true);
        self::assertIsArray($screenDefinition);
        self::assertSame('MTOOL', $screenDefinition['project_key'] ?? '');
        self::assertSame(
            'mtool_source_output_review',
            $screenDefinition['contracts'][0]['contract_key'] ?? '',
        );
        self::assertSame(
            'review_list',
            $screenDefinition['contracts'][0]['view_variant_preference']['variant'] ?? '',
        );

        $runtimePreview = json_decode($files['runtime-preview.json'], true);
        self::assertIsArray($runtimePreview);
        self::assertSame('no-code-runtime-v0', $runtimePreview['runtime_version'] ?? '');
        self::assertSame('MTOOL', $runtimePreview['project_key'] ?? '');
        self::assertSame(3, count($runtimePreview['screens'] ?? []));
        self::assertSame(
            'mtool_source_output_review_compact',
            $runtimePreview['screens'][0]['presentation_hint']['profile_key'] ?? '',
        );
        self::assertSame('compact', $runtimePreview['screens'][0]['presentation_hint']['density'] ?? '');
        self::assertSame(
            ['related_settings_panel', 'artifact_status_panel'],
            array_column($runtimePreview['screens'][0]['extension_slots'] ?? [], 'slot_type'),
        );
        self::assertSame(
            ['shared_contracts', 'source_outputs'],
            array_column($runtimePreview['screens'][0]['extension_slots'][0]['links'] ?? [], 'target'),
        );
        self::assertSame(
            ['Artifact Strategy', 'Target Binding', 'Spec Visibility'],
            array_column($runtimePreview['screens'][0]['extension_slots'][1]['status_items'] ?? [], 'label'),
        );
        self::assertSame(
            ['review_source_output_artifact', 'request_source_output_publish'],
            array_column($runtimePreview['screens'][1]['extension_slots'][2]['action_items'] ?? [], 'action_key'),
        );
        self::assertSame(
            ['review_source_output_artifact', 'request_source_output_publish'],
            array_column($runtimePreview['screens'][1]['extension_slots'][2]['action_items'] ?? [], 'operation_key'),
        );
        self::assertSame(
            'POST',
            $runtimePreview['screens'][1]['extension_slots'][2]['action_items'][0]['route_boundary']['method'] ?? '',
        );
        self::assertSame(
            'deferred',
            $runtimePreview['screens'][1]['extension_slots'][2]['action_items'][0]['availability_read_model']['availability_state'] ?? '',
        );
        self::assertSame(
            'metadata-only',
            $runtimePreview['screens'][1]['extension_slots'][2]['action_items'][0]['availability_read_model']['execution_mode'] ?? '',
        );
        self::assertFalse(
            $runtimePreview['screens'][1]['extension_slots'][2]['action_items'][0]['availability_read_model']['generated_button_enabled'] ?? true,
        );
        self::assertSame(
            'mtool_operator_admin',
            $runtimePreview['screens'][1]['extension_slots'][2]['action_items'][0]['route_boundary']['auth_guard'] ?? '',
        );
        self::assertSame(
            'POST',
            $runtimePreview['screens'][1]['extension_slots'][2]['action_items'][1]['route_boundary']['method'] ?? '',
        );
        self::assertSame(
            'duplicate_safe',
            $runtimePreview['screens'][1]['extension_slots'][2]['action_items'][1]['route_boundary']['idempotency'] ?? '',
        );
        self::assertSame(
            ['review_source_output_artifact', 'request_source_output_publish'],
            array_column($runtimePreview['screens'][1]['custom_operations'] ?? [], 'operation_key'),
        );
        self::assertSame('approval_transition', $runtimePreview['screens'][1]['custom_operations'][1]['side_effect_class'] ?? '');
        self::assertStringContainsString(
            'approval transition policy',
            $runtimePreview['screens'][1]['custom_operations'][1]['unavailable_reason'] ?? '',
        );
        self::assertStringContainsString('data-extension-slots-for="mtool_source_output_review_list"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot="mtool_source_output_related_settings"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot-link="shared_contracts"', $files['runtime-preview.html']);
        self::assertStringContainsString('/projects/MTOOL/shared-contracts', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot="mtool_source_output_artifact_status"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot-status-item="Artifact Strategy"', $files['runtime-preview.html']);
        self::assertStringContainsString('no-code-runtime', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot="mtool_source_output_operator_actions"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot-action="review_source_output_artifact"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot-operation="review_source_output_artifact"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot-operation-key="request_source_output_publish"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot-unavailable-reason="review_source_output_artifact"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot-route-boundary="review_source_output_artifact"', $files['runtime-preview.html']);
        self::assertStringContainsString('data-extension-slot-route-boundary="request_source_output_publish"', $files['runtime-preview.html']);
        self::assertStringContainsString('request-source-output-publish', $files['runtime-preview.html']);
        self::assertStringContainsString('Route boundary declared, execution still disabled', $files['runtime-preview.html']);
        self::assertStringContainsString('Execution route is not wired yet', $files['runtime-preview.html']);
        self::assertStringContainsString('Request Publish', $files['runtime-preview.html']);
        self::assertStringContainsString('Mtool Source Output Review List', $files['runtime-preview.html']);
        self::assertStringContainsString('Generated no-code screen definition and runtime preview from canonical Mtool metadata.', $files['README.md']);
    }

    public function testMtoolDogfoodingProbeInspectionSummaryClassifiesConfiguredAndSlotMetadata(): void
    {
        $summary = app_no_code_mtool_dogfooding_probe_inspection_summary($this->mtoolViewerPrincipal());

        self::assertTrue($summary['ok'], implode(', ', $summary['errors'] ?? []));
        self::assertSame(['screen-definition.json', 'runtime-preview.json', 'runtime-preview.html', 'README.md'], $summary['artifact_files'] ?? []);
        self::assertSame('mtool_source_output_review', $summary['contract_key'] ?? '');
        self::assertSame('internal', $summary['interface_usage_intent'] ?? '');
        self::assertSame('review_list', $summary['view_variant_preference'] ?? '');
        self::assertSame('mtool_source_output_review_compact', $summary['presentation_profile_key'] ?? '');
        self::assertSame('compact', $summary['presentation_density'] ?? '');
        self::assertSame(
            ['related_settings_panel', 'artifact_status_panel', 'operator_actions_panel'],
            $summary['extension_slot_types'] ?? [],
        );
        self::assertSame(
            ['review_source_output_artifact', 'request_source_output_publish'],
            $summary['custom_operation_keys'] ?? [],
        );
        self::assertSame(['review_request', 'publish'], $summary['custom_operation_categories'] ?? []);
        self::assertSame(['external_handoff', 'approval_transition'], $summary['custom_operation_side_effect_classes'] ?? []);
        self::assertSame(['deferred'], $summary['custom_operation_availability'] ?? []);
        self::assertSame(['deferred'], $summary['custom_operation_availability_states'] ?? []);
        self::assertSame(['metadata-only'], $summary['custom_operation_execution_modes'] ?? []);
        self::assertSame(
            [
                'Execution route is not wired yet; policy, CSRF, audit, and review workflow boundaries must be connected first.',
                'Publish request execution is deferred until approval transition policy, CSRF, and audit boundaries are wired.',
            ],
            $summary['custom_operation_unavailable_reasons'] ?? [],
        );
        self::assertSame(
            ['source_output_artifact_review', 'source_output_publish_request'],
            $summary['custom_operation_adapter_handoffs'] ?? [],
        );
        self::assertSame(
            ['related_settings_panel', 'artifact_status_panel'],
            $summary['screens'][0]['extension_slot_types'] ?? [],
        );
        self::assertSame(
            ['review_source_output_artifact', 'request_source_output_publish'],
            $summary['screens'][0]['custom_operation_keys'] ?? [],
        );
        self::assertSame(
            ['related_settings_panel', 'artifact_status_panel', 'operator_actions_panel'],
            $summary['screens'][1]['extension_slot_types'] ?? [],
        );
        self::assertSame(
            ['review_source_output_artifact', 'request_source_output_publish'],
            $summary['screens'][1]['custom_operation_keys'] ?? [],
        );
        self::assertSame([], $summary['screens'][2]['extension_slot_types'] ?? ['unexpected']);
        self::assertSame(
            ['review_source_output_artifact', 'request_source_output_publish'],
            $summary['screens'][2]['custom_operation_keys'] ?? [],
        );
        self::assertSame('visible_placeholder', $summary['html_boundary']['custom_slot_rendering'] ?? '');
        self::assertTrue($summary['html_boundary']['contains_runtime_preview_json'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_slot_region_markup'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_related_settings_slot'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_related_settings_link_list'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_artifact_status_slot'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_artifact_status_card'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_operator_actions_slot'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_operator_action_panel'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_custom_operation_binding'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_custom_operation_unavailable_reason'] ?? false);
        self::assertTrue($summary['html_boundary']['contains_custom_operation_route_boundary'] ?? false);
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
     * @return array<string,mixed>
     */
    private function mtoolViewerPrincipal(): array
    {
        return [
            'id' => 'mtool-viewer-1',
            'display_name' => 'Mtool Viewer',
            'auth_source' => 'stub',
            'site' => 'local',
            'roles' => ['viewer'],
            'project_roles' => [
                'MTOOL' => ['viewer'],
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
