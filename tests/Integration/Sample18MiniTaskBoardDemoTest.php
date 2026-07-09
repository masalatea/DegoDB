<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample18MiniTaskBoardDemoTest extends TestCase
{
    public function testMiniTaskBoardNoCodeGoldenFixtureMatchesSeedAndRouteContract(): void
    {
        $fixture = $this->sample18NoCodeGoldenFixture();
        $root = dirname(__DIR__, 2);
        $seedSql = (string) file_get_contents(
            $root . '/sample/tutorials/sample18-mini-task-board-demo/seed/900_020_sample18_table_seed.sql',
        );
        $routeSource = (string) file_get_contents($root . '/mtool/app/lab_sample18_task_board_page.php');

        self::assertSame('sample18-no-code-ui-golden-v1', $fixture['fixture_version'] ?? '');
        self::assertSame('SAMPLE18', $fixture['project_key'] ?? '');
        self::assertSame('/samples/sample18-task-board', $fixture['route_path'] ?? '');
        self::assertSame('task_card', $fixture['source_table'] ?? '');
        self::assertFalse($fixture['no_code_conversion_boundary']['generated_route_replacement'] ?? true);
        self::assertFalse($fixture['no_code_conversion_boundary']['generated_button_execution'] ?? true);

        foreach (($fixture['seed_rows'] ?? []) as $row) {
            self::assertIsArray($row);
            self::assertStringContainsString((string) ($row['title'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['status'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['assigned_to'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['due_date'] ?? ''), $seedSql);
        }

        $contract = $fixture['dom_contract'] ?? [];
        self::assertIsArray($contract);
        self::assertStringContainsString((string) ($contract['title'] ?? ''), $routeSource);
        foreach (($contract['status_filter_values'] ?? []) as $value) {
            self::assertStringContainsString('value="' . $value . '"', $routeSource);
        }
        foreach (($contract['form_fields'] ?? []) as $fieldName) {
            self::assertStringContainsString('name="' . $fieldName . '"', $routeSource);
        }
        foreach (($contract['table_columns'] ?? []) as $columnLabel) {
            self::assertStringContainsString('>' . $columnLabel . '<', $routeSource);
        }
        foreach (($contract['actions'] ?? []) as $action) {
            $needle = in_array($action, ['create', 'update'], true)
                ? "action === '" . $action . "'"
                : 'value="' . $action . '"';
            self::assertStringContainsString($needle, $routeSource);
        }
    }

    public function testMiniTaskBoardDemoReferenceOutputs(): void
    {
        $app = app_bootstrap();
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');
        try {
            $result = app_sample18_mini_task_board_demo_run(
                $app,
                'phpunit-sample18',
                app_sample18_mini_task_board_demo_default_reference_root(),
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
                : 'sample18 mini task board verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(4, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('HTML-PAGE', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
    }

    /**
     * @return array<string,mixed>
     */
    private function sample18NoCodeGoldenFixture(): array
    {
        $path = dirname(__DIR__, 2) . '/sample/tutorials/sample18-mini-task-board-demo/golden/no-code-ui-golden.json';
        $decoded = json_decode((string) file_get_contents($path), true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
