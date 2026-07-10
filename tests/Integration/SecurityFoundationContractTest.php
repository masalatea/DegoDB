<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class SecurityFoundationContractTest extends TestCase
{
    public function testOpenApiAndArtifactRawRoutesRemainAbsent(): void
    {
        foreach ([
            '/artifacts/openapi/PROJECT/openapi.json',
            '/work/source-outputs/PROJECT/current/openapi.json',
            '/source-outputs/PROJECT/openapi.json',
            '/openapi/PROJECT/openapi.json',
        ] as $path) {
            $match = app_route_match(['path' => $path]);
            self::assertSame('not_found', $match['name'], $path);
        }
    }

    public function testSupportedOpenApiAndArtifactViewersRequireAuth(): void
    {
        foreach ([
            '/runs/swagger/PROJECT1' => 'lab_swagger',
            '/runs/proxy/PROJECT1/source-output/proxy.php' => 'lab_published_single_proxy',
            '/projects/PROJECT1/source-outputs/artifacts/20260619-test' => 'project_source_output_artifact_detail',
            '/projects/PROJECT1/source-outputs/artifacts/20260619-test/download' => 'project_source_output_download',
            '/projects/PROJECT1/sync-outbox/abcdef123456' => 'project_sync_outbox_detail',
            '/projects/PROJECT1/sync-outbox/abcdef123456.json' => 'project_sync_outbox_status_json',
        ] as $path => $expectedRoute) {
            $match = app_route_match(['path' => $path]);
            self::assertSame($expectedRoute, $match['name'], $path);
            self::assertTrue(app_route_requires_auth($match['name']), $path);
        }
    }
}
