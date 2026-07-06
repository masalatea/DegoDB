<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_route_authorization.php';

use PHPUnit\Framework\TestCase;

final class ProjectRouteAuthorizationContractTest extends TestCase
{
    public function testReadAndWriteMethodsCanResolveDifferentCapabilities(): void
    {
        $read = app_project_route_authorization_requirement('project_settings', 'GET');
        $write = app_project_route_authorization_requirement('project_settings', 'POST');

        self::assertTrue($read['ok'], $read['error']);
        self::assertSame('project.read', $read['capability']);
        self::assertSame('viewer', $read['required_role']);
        self::assertFalse($read['audit_required']);

        self::assertTrue($write['ok'], $write['error']);
        self::assertSame('project.edit', $write['capability']);
        self::assertSame('editor', $write['required_role']);
        self::assertTrue($write['audit_required']);
    }

    public function testAlreadyEnforcedSourceOutputDownloadRequirementIsRepresented(): void
    {
        $requirement = app_project_route_authorization_requirement('project_source_output_download', 'GET');

        self::assertTrue($requirement['ok'], $requirement['error']);
        self::assertSame('source_output.download', $requirement['capability']);
        self::assertSame('publisher', $requirement['required_role']);
        self::assertSame('done', $requirement['enforcement_status']);
        self::assertTrue($requirement['audit_required']);
    }

    public function testAlreadyEnforcedSourceOutputArtifactDetailRequirementIsRepresented(): void
    {
        $requirement = app_project_route_authorization_requirement('project_source_output_artifact_detail', 'GET');

        self::assertTrue($requirement['ok'], $requirement['error']);
        self::assertSame('source_output.download', $requirement['capability']);
        self::assertSame('publisher', $requirement['required_role']);
        self::assertSame('done', $requirement['enforcement_status']);
        self::assertTrue($requirement['audit_required']);
    }

    public function testAlreadyEnforcedSyncOutboxDetailRequirementIsRepresented(): void
    {
        $requirement = app_project_route_authorization_requirement('project_sync_outbox_detail', 'GET');

        self::assertTrue($requirement['ok'], $requirement['error']);
        self::assertSame('source_output.download', $requirement['capability']);
        self::assertSame('publisher', $requirement['required_role']);
        self::assertSame('done', $requirement['enforcement_status']);
        self::assertTrue($requirement['audit_required']);
    }

    public function testAlreadyEnforcedSyncOutboxStatusJsonRequirementIsRepresented(): void
    {
        $requirement = app_project_route_authorization_requirement('project_sync_outbox_status_json', 'GET');

        self::assertTrue($requirement['ok'], $requirement['error']);
        self::assertSame('source_output.download', $requirement['capability']);
        self::assertSame('publisher', $requirement['required_role']);
        self::assertSame('done', $requirement['enforcement_status']);
        self::assertTrue($requirement['audit_required']);
    }

    public function testUnknownRouteFailsClosedAtContractLayer(): void
    {
        $requirement = app_project_route_authorization_requirement('not_a_real_route', 'POST');

        self::assertFalse($requirement['ok']);
        self::assertSame('', $requirement['capability']);
        self::assertStringContainsString('unknown route authorization requirement', $requirement['error']);
    }
}
