<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample27AppLocalPersistenceDemoTest extends TestCase
{
    public function testServerDtoAppLocalRoundTrip(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $result = app_sample27_app_local_persistence_run(
                app_bootstrap(),
                'phpunit-sample27',
            );
        } finally {
            if ($previousPolicy === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
            }
        }

        if (!$result['ok']) {
            fwrite(
                STDERR,
                json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            );
        }

        self::assertTrue(
            $result['ok'],
            is_string($result['error'] ?? null) && $result['error'] !== ''
                ? $result['error']
                : 'sample27 App-local persistence verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertTrue((bool) ($result['steps']['manifest']['validation']['ok'] ?? false));
        self::assertTrue((bool) ($result['steps']['manifest']['compare']['ok'] ?? false));
        self::assertTrue((bool) ($result['steps']['source_output_artifact']['ok'] ?? false));
        self::assertSame(
            'APP-LOCAL-PERSISTENCE',
            $result['steps']['source_output_artifact']['source_output_key'] ?? '',
        );
        self::assertSame(
            'app-local-persistence-php',
            $result['steps']['source_output_artifact']['artifact_strategy'] ?? '',
        );
        self::assertSame(5, $result['steps']['source_output_artifact']['source_file_count'] ?? null);
        self::assertSame(
            $result['steps']['server_dto'],
            $result['steps']['read']['dto'] ?? null,
        );
        self::assertSame(1, $result['steps']['read']['local_metadata']['dirty'] ?? null);
        self::assertSame('dirty', $result['steps']['read']['local_metadata']['sync_status'] ?? null);
    }
}
