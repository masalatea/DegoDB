<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample32NoCodeUiTestLabTest extends TestCase
{
    public function testNoCodeUiLabRuntimeArtifactBuildsWithFastDomContracts(): void
    {
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
        self::assertSame('no-code-screen-definition-v0', $result['steps']['screen_definition']['definition_version'] ?? '');
        self::assertSame('no-code-runtime-v0', $result['steps']['runtime_preview']['runtime_version'] ?? '');
        self::assertSame('no_code_lab_card', $result['steps']['screen_definition']['contract_key'] ?? '');
        self::assertSame(['list', 'detail', 'form'], $result['steps']['screen_definition']['screen_types'] ?? []);
        self::assertSame(
            ['id', 'title', 'status', 'owner_name', 'priority', 'due_on', 'notes'],
            $result['steps']['screen_definition']['field_keys'] ?? [],
        );
        self::assertSame('archive_no_code_lab_card', $result['steps']['screen_definition']['action_key'] ?? '');
        self::assertSame('disabled', $result['steps']['screen_definition']['action_availability'] ?? '');
        self::assertSame(3, $result['steps']['runtime_preview']['screen_count'] ?? null);
        self::assertSame(2, $result['steps']['runtime_preview']['seeded_preview_row_count'] ?? null);

        $publishedRoot = (string) ($result['steps']['runtime_preview']['published_root'] ?? '');
        self::assertDirectoryExists($publishedRoot);
        $runtimePreview = NoCodeUiContractAssertions::readJsonFile($this, $publishedRoot . '/runtime-preview.json');
        NoCodeUiContractAssertions::assertRuntimePreviewScreenKeys(
            $this,
            $runtimePreview,
            ['no_code_lab_card_list', 'no_code_lab_card_detail', 'no_code_lab_card_form'],
        );

        $runtimePreviewHtml = (string) file_get_contents($publishedRoot . '/runtime-preview.html');
        NoCodeUiContractAssertions::assertPreviewHtmlScreens($this, $runtimePreviewHtml, [
            'no_code_lab_card_list' => 'list',
            'no_code_lab_card_detail' => 'detail',
            'no_code_lab_card_form' => 'form',
        ]);
        NoCodeUiContractAssertions::assertPreviewHtmlFormFields(
            $this,
            $runtimePreviewHtml,
            ['title', 'status', 'owner_name', 'priority', 'due_on', 'notes'],
        );
        self::assertStringContainsString('data-action-key="archive_no_code_lab_card"', $runtimePreviewHtml);
        self::assertStringContainsString('data-action-disabled-reason="policy-not-enabled"', $runtimePreviewHtml);
    }
}
