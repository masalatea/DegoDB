<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample32NoCodeUiTestLabTest extends TestCase
{
    public function testNoCodeUiLabRuntimeArtifactBuildsWithFastDomContracts(): void
    {
        $fixture = NoCodeUiContractAssertions::readJsonFile(
            $this,
            dirname(__DIR__, 2) . '/sample/tutorials/sample32-no-code-ui-test-lab/fixtures/no-code-ui-contract-fixtures.json',
        );

        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $result = app_sample32_no_code_ui_test_lab_run(
                app_bootstrap(),
                'phpunit-sample32',
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
                : 'sample32 no-code UI test lab verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertSame($fixture['definition_version'], $result['steps']['screen_definition']['definition_version'] ?? '');
        self::assertSame($fixture['runtime_version'], $result['steps']['runtime_preview']['runtime_version'] ?? '');
        self::assertSame($fixture['contract_key'], $result['steps']['screen_definition']['contract_key'] ?? '');
        self::assertSame($fixture['screen_types'], $result['steps']['screen_definition']['screen_types'] ?? []);
        self::assertSame($fixture['list_field_keys'], $result['steps']['screen_definition']['field_keys'] ?? []);
        self::assertSame(
            $fixture['disabled_managed_actions'][0]['action_key'] ?? '',
            $result['steps']['screen_definition']['action_key'] ?? '',
        );
        self::assertSame(
            $fixture['disabled_managed_actions'][0]['availability'] ?? '',
            $result['steps']['screen_definition']['action_availability'] ?? '',
        );
        self::assertSame($fixture['runtime']['screen_count'] ?? null, $result['steps']['runtime_preview']['screen_count'] ?? null);
        self::assertSame(
            $fixture['runtime']['seeded_preview_row_count'] ?? null,
            $result['steps']['runtime_preview']['seeded_preview_row_count'] ?? null,
        );

        $publishedRoot = (string) ($result['steps']['runtime_preview']['published_root'] ?? '');
        self::assertDirectoryExists($publishedRoot);
        $runtimePreview = NoCodeUiContractAssertions::readJsonFile($this, $publishedRoot . '/runtime-preview.json');
        NoCodeUiContractAssertions::assertRuntimePreviewScreenKeys(
            $this,
            $runtimePreview,
            $fixture['screen_keys'],
        );

        $runtimePreviewHtml = (string) file_get_contents($publishedRoot . '/runtime-preview.html');
        NoCodeUiContractAssertions::assertPreviewHtmlScreens($this, $runtimePreviewHtml, $fixture['screen_types_by_key']);
        NoCodeUiContractAssertions::assertPreviewHtmlFormFields(
            $this,
            $runtimePreviewHtml,
            $fixture['form_field_names'],
        );
        NoCodeUiContractAssertions::assertPreviewHtmlDisabledManagedActions(
            $this,
            $runtimePreviewHtml,
            $fixture['disabled_managed_actions'],
        );
    }
}
