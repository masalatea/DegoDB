<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample29NoCodeSupportCaseDemoTest extends TestCase
{
    public function testNoCodeRuntimeArtifactBuildsFromSampleMetadata(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $result = app_sample29_no_code_support_case_demo_run(
                app_bootstrap(),
                'phpunit-sample29',
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
                : 'sample29 no-code support case demo verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertSame('no-code-screen-definition-v0', $result['steps']['screen_definition']['definition_version'] ?? '');
        self::assertSame('no-code-runtime-v0', $result['steps']['runtime_preview']['runtime_version'] ?? '');
        self::assertSame('support_case', $result['steps']['screen_definition']['contract_key'] ?? '');
        self::assertSame('update_support_case', $result['steps']['screen_definition']['action_key'] ?? '');
        self::assertSame(3, $result['steps']['runtime_preview']['screen_count'] ?? null);
    }
}
