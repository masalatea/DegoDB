<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample22EbookChapterWorkflowDemoTest extends TestCase
{
    public function testEbookChapterWorkflowReferenceOutputs(): void
    {
        $app = app_bootstrap();
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');
        try {
            $result = app_sample22_ebook_chapter_workflow_run(
                $app,
                'phpunit-sample22',
                app_sample22_ebook_chapter_workflow_default_reference_root(),
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
                : 'sample22 ebook chapter workflow verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(3, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);

        $definitionResult = app_no_code_screen_definition_from_project($app, 'SAMPLE22');
        self::assertTrue($definitionResult['ok'], $definitionResult['error']);
        $contracts = [];
        foreach ($definitionResult['definition']['contracts'] ?? [] as $contract) {
            if (is_array($contract)) {
                $contracts[(string) ($contract['contract_key'] ?? '')] = $contract;
            }
        }
        self::assertSame(
            ['ebook_workflow_book', 'ebook_workflow_published_chapter'],
            array_keys($contracts),
        );
        self::assertSame([], $contracts['ebook_workflow_book']['actions'] ?? ['unexpected']);
        self::assertSame([], $contracts['ebook_workflow_published_chapter']['actions'] ?? ['unexpected']);

        $chapterFields = [];
        foreach ($contracts['ebook_workflow_published_chapter']['screens'][0]['fields'] ?? [] as $field) {
            if (is_array($field)) {
                $chapterFields[(string) ($field['field_key'] ?? '')] = $field;
            }
        }
        self::assertSame([
            'kind' => 'belongs_to',
            'contract_key' => 'ebook_workflow_book',
            'key_field' => 'id',
            'label_field' => 'title',
            'ui_role' => 'parent',
            'required' => true,
        ], $chapterFields['book_id']['relation'] ?? []);
        self::assertTrue($chapterFields['book_id']['readonly'] ?? false);

        $preview = app_project_output_no_code_runtime_preview($definitionResult['definition'], 'SAMPLE22');
        self::assertTrue($preview['ok'], $preview['error']);
        $runtimeScreens = [];
        foreach ($preview['screens'] ?? [] as $screen) {
            if (is_array($screen)) {
                $runtimeScreens[(string) ($screen['screen_key'] ?? '')] = $screen;
            }
        }
        $runtimeFields = [];
        foreach ($runtimeScreens['ebook_workflow_published_chapter_form']['fields'] ?? [] as $field) {
            if (is_array($field)) {
                $runtimeFields[(string) ($field['field_key'] ?? '')] = $field;
            }
        }
        self::assertSame('ready', $runtimeFields['book_id']['relation']['lookup_state'] ?? '');
        self::assertSame([
            ['value' => 1, 'label' => 'JSONから始める電子書籍CMS'],
        ], $runtimeFields['book_id']['relation']['lookup_options'] ?? []);
        self::assertSame([], $runtimeScreens['ebook_workflow_published_chapter_form']['actions'] ?? ['unexpected']);
        $html = app_no_code_runtime_render_preview_html($preview);
        self::assertStringContainsString('data-relation-kind="belongs_to"', $html);
        self::assertStringContainsString('data-relation-contract="ebook_workflow_book"', $html);
        self::assertStringContainsString('data-relation-ui-role="parent"', $html);
        self::assertStringContainsString('data-lookup-state="ready"', $html);
        self::assertStringContainsString('data-lookup-option-count="1"', $html);
        self::assertStringNotContainsString('data-action-enabled="true"', $html);
    }
}
