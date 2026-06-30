<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_db_access_sync_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/source_output_repository.php';

use PHPUnit\Framework\TestCase;

final class ZzzAiContextStandardOutputTest extends TestCase
{
    private const SOURCE_OUTPUT_KEY = 'AI-CONTEXT-MD';
    private const MTOOL_PROJECT_KEY = 'MTOOL';

    public function testTutorialSampleSeedsDeclareStandardAiContextOutput(): void
    {
        $projects = $this->tutorialSeedProjectKeys();

        self::assertCount(29, $projects);

        foreach ($projects as $projectKey => $seedPath) {
            $seed = file_get_contents($seedPath);
            self::assertIsString($seed, 'failed to read seed: ' . $seedPath);

            self::assertStringContainsString("'AI-CONTEXT-MD'", $seed, 'missing AI context output: ' . $projectKey);
            self::assertStringContainsString("'md'", $seed, 'missing Markdown program language: ' . $projectKey);
            self::assertStringContainsString("'AIContext'", $seed, 'missing AIContext class type: ' . $projectKey);
            self::assertStringContainsString("'ai-context-md'", $seed, 'missing AI context artifact strategy: ' . $projectKey);
            self::assertStringContainsString(
                "'mtool/ai-context-source-outputs/" . $projectKey . "/AI-CONTEXT-MD'",
                $seed,
                'missing AI context runtime source path: ' . $projectKey,
            );
            self::assertStringContainsString(
                'Authored by DegoDB/Mtool generator code; AI is reader/consumer only.',
                $seed,
                'missing authorship rule: ' . $projectKey,
            );
        }
    }

    public function testTutorialSamplesPublishStandardAiContextOutput(): void
    {
        $app = app_bootstrap();
        $projects = $this->tutorialSeedProjectKeys();
        $errors = [];
        $summaries = [];

        foreach (array_keys($projects) as $projectKey) {
            $definitionResult = app_fetch_project_source_output_item($app, $projectKey, self::SOURCE_OUTPUT_KEY);
            if (!$definitionResult['ok'] || !is_array($definitionResult['item'])) {
                $errors[] = $projectKey . ': source output definition missing: ' . ($definitionResult['error'] ?? '');
                continue;
            }

            $definition = $definitionResult['item'];
            $this->assertStandardDefinition($projectKey, $definition, $errors);

            $artifactResult = app_project_output_create_from_definition(
                $app,
                $projectKey,
                $definition,
                'phpunit-ai-context-standard-output',
            );
            if (!$artifactResult['ok'] || !is_array($artifactResult['artifact'])) {
                $errors[] = $projectKey . ': create failed: ' . ($artifactResult['error'] ?? '');
                continue;
            }

            $publishResult = app_project_output_publish_artifact($app, $artifactResult['artifact'], $definition);
            if (!$publishResult['ok'] || !is_array($publishResult['published'])) {
                $errors[] = $projectKey . ': publish failed: ' . ($publishResult['error'] ?? '');
                continue;
            }

            $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
            $context = $this->readContextJson($projectKey, $publishedRoot, $errors);
            if ($context === null) {
                continue;
            }

            $this->assertPublishedPackage($projectKey, $publishedRoot, $context, $errors);
            $summaries[$projectKey] = [
                'tables' => count(is_array($context['tables'] ?? null) ? $context['tables'] : []),
                'data_classes' => count(is_array($context['data_classes'] ?? null) ? $context['data_classes'] : []),
                'db_access_classes' => count(is_array($context['db_access_classes'] ?? null) ? $context['db_access_classes'] : []),
            ];
        }

        if ($errors !== []) {
            fwrite(
                STDERR,
                json_encode(
                    [
                        'errors' => $errors,
                        'summaries' => $summaries,
                    ],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                ) . PHP_EOL,
            );
        }

        self::assertSame([], $errors);
        self::assertCount(29, $summaries);
    }

    public function testMtoolPublishesSelfAiContextOutputForAiReview(): void
    {
        $app = app_bootstrap();
        $errors = [];

        $definitionBootstrap = $this->ensureMtoolAiContextDefinition($app);
        if (!$definitionBootstrap['ok']) {
            $errors[] = 'MTOOL: source output definition bootstrap failed: ' . $definitionBootstrap['error'];
        }
        $runtimeDefinitionBootstrap = $this->ensureMtoolRuntimeDbclassesDefinition($app);
        if (!$runtimeDefinitionBootstrap['ok']) {
            $errors[] = 'MTOOL: runtime source output definition bootstrap failed: ' . $runtimeDefinitionBootstrap['error'];
        }

        foreach ([
            'live-schema',
            'legacy-reference-test-module',
            'legacy-reference-build-run-state',
        ] as $sourceKey) {
            $importResult = app_project_table_import_apply($app, self::MTOOL_PROJECT_KEY, $sourceKey);
            if (!$importResult['ok']) {
                $errors[] = 'MTOOL: table import failed for ' . $sourceKey . ': ' . $importResult['error'];
            }
        }

        $dataClassSync = app_project_data_class_sync_apply($app, self::MTOOL_PROJECT_KEY);
        if (!$dataClassSync['ok']) {
            $errors[] = 'MTOOL: data class sync failed: ' . $dataClassSync['error'];
        }

        $dbAccessSync = app_project_db_access_sync_from_generated_catalog($app, self::MTOOL_PROJECT_KEY);
        if (!$dbAccessSync['ok']) {
            $errors[] = 'MTOOL: db access sync failed: ' . $dbAccessSync['error'];
        }

        $definitionResult = app_fetch_project_source_output_item($app, self::MTOOL_PROJECT_KEY, self::SOURCE_OUTPUT_KEY);
        if (!$definitionResult['ok'] || !is_array($definitionResult['item'])) {
            $errors[] = 'MTOOL: source output definition missing: ' . ($definitionResult['error'] ?? '');
        }

        if ($errors === [] && is_array($definitionResult['item'] ?? null)) {
            $definition = $definitionResult['item'];
            $this->assertStandardDefinition(self::MTOOL_PROJECT_KEY, $definition, $errors);

            $artifactResult = app_project_output_create_from_definition(
                $app,
                self::MTOOL_PROJECT_KEY,
                $definition,
                'phpunit-mtool-self-ai-context',
            );
            if (!$artifactResult['ok'] || !is_array($artifactResult['artifact'])) {
                $errors[] = 'MTOOL: create failed: ' . ($artifactResult['error'] ?? '');
            } else {
                $publishResult = app_project_output_publish_artifact($app, $artifactResult['artifact'], $definition);
                if (!$publishResult['ok'] || !is_array($publishResult['published'])) {
                    $errors[] = 'MTOOL: publish failed: ' . ($publishResult['error'] ?? '');
                } else {
                    $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
                    $context = $this->readContextJson(self::MTOOL_PROJECT_KEY, $publishedRoot, $errors);
                    if ($context !== null) {
                        $this->assertPublishedPackage(self::MTOOL_PROJECT_KEY, $publishedRoot, $context, $errors);
                        $this->assertMtoolSelfContextLooksReviewable($publishedRoot, $context, $errors);
                    }
                }
            }
        }

        if ($errors !== []) {
            fwrite(
                STDERR,
                json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            );
        }

        self::assertSame([], $errors);
    }

    /**
     * @return array<string,string>
     */
    private function tutorialSeedProjectKeys(): array
    {
        $paths = glob(dirname(__DIR__, 2) . '/sample/tutorials/*/seed/*source_output_seed.sql') ?: [];
        sort($paths, SORT_STRING);

        $projects = [];
        foreach ($paths as $path) {
            $seed = file_get_contents($path);
            self::assertIsString($seed, 'failed to read seed: ' . $path);
            self::assertSame(
                1,
                preg_match("/WHERE\\s+project_key\\s*=\\s*'([^']+)'/i", $seed, $matches),
                'project key missing from seed: ' . $path,
            );
            $projects[$matches[1]] = $path;
        }

        uksort($projects, static function (string $left, string $right): int {
            preg_match('/^SAMPLE0?([0-9]+)$/', $left, $leftMatch);
            preg_match('/^SAMPLE0?([0-9]+)$/', $right, $rightMatch);

            return ((int) ($leftMatch[1] ?? 0)) <=> ((int) ($rightMatch[1] ?? 0));
        });

        return $projects;
    }

    /**
     * @param array<string,mixed> $definition
     * @param list<string> $errors
     */
    private function assertStandardDefinition(string $projectKey, array $definition, array &$errors): void
    {
        $expected = [
            'source_output_key' => self::SOURCE_OUTPUT_KEY,
            'program_language' => 'md',
            'class_type' => 'AIContext',
            'artifact_strategy' => 'ai-context-md',
            'target_binding_type' => 'runtime',
            'runtime_source_relative_path' => 'mtool/ai-context-source-outputs/' . $projectKey . '/AI-CONTEXT-MD',
        ];

        foreach ($expected as $field => $value) {
            if ((string) ($definition[$field] ?? '') !== $value) {
                $errors[] = $projectKey . ': definition ' . $field . ' expected=' . $value
                    . ' actual=' . (string) ($definition[$field] ?? '');
            }
        }
    }

    /**
     * @return array{ok:bool,error:string}
     */
    private function ensureMtoolAiContextDefinition(array $app): array
    {
        $input = [
            'project_key' => self::MTOOL_PROJECT_KEY,
            'source_output_key' => self::SOURCE_OUTPUT_KEY,
            'name' => 'Mtool AI Context Markdown',
            'program_language' => 'md',
            'class_type' => 'AIContext',
            'release_target_type' => 'Release',
            'source_template_dir' => '',
            'source_output_dir' => 'work/source-outputs/MTOOL/AI-CONTEXT-MD',
            'source_temp_output_dir' => 'work/staging/source-outputs/MTOOL/AI-CONTEXT-MD',
            'proxy_base_url' => '',
            'autoload_filename_suffix' => '',
            'source_text_char_code' => 'UTF-8',
            'runtime_source_relative_path' => 'mtool/ai-context-source-outputs/MTOOL/AI-CONTEXT-MD',
            'artifact_strategy' => 'ai-context-md',
            'target_binding_type' => 'runtime',
            'spec_visibility' => 'internal-only',
            'output_archive_format' => 'tar.gz',
            'source_output_list_order' => '90',
            'notes' => 'Generate AI-readable Markdown and JSON context for Mtool itself from canonical project metadata. Authored by DegoDB/Mtool generator code; AI is reader/consumer only.',
            'source_of_truth' => 'phpunit-fixture',
        ];

        $existing = app_fetch_project_source_output_item($app, self::MTOOL_PROJECT_KEY, self::SOURCE_OUTPUT_KEY);
        if (!$existing['ok']) {
            return [
                'ok' => false,
                'error' => $existing['error'],
            ];
        }

        return is_array($existing['item'])
            ? app_update_project_source_output($app, $input)
            : app_create_project_source_output($app, $input);
    }

    /**
     * @return array{ok:bool,error:string}
     */
    private function ensureMtoolRuntimeDbclassesDefinition(array $app): array
    {
        $input = [
            'project_key' => self::MTOOL_PROJECT_KEY,
            'source_output_key' => 'RUNTIME-DBCLASSES',
            'name' => 'Mtool Runtime DBClasses',
            'program_language' => 'php',
            'class_type' => 'DBAccess',
            'release_target_type' => 'Release',
            'source_template_dir' => '',
            'source_output_dir' => 'work/source-outputs/MTOOL/RUNTIME-DBCLASSES',
            'source_temp_output_dir' => 'work/staging/source-outputs/MTOOL/RUNTIME-DBCLASSES',
            'proxy_base_url' => '',
            'autoload_filename_suffix' => 'mtool',
            'source_text_char_code' => 'UTF-8',
            'runtime_source_relative_path' => 'mtool/dbclasses',
            'artifact_strategy' => 'generated-bootstrap-dbclasses',
            'target_binding_type' => 'runtime',
            'spec_visibility' => 'internal-only',
            'output_archive_format' => 'tar.gz',
            'source_output_list_order' => '10',
            'notes' => 'current bootstrap runtime mtool/reference/dbclasses を source output artifact として固める既定 definition です。',
            'source_of_truth' => 'phpunit-fixture',
        ];

        $existing = app_fetch_project_source_output_item($app, self::MTOOL_PROJECT_KEY, 'RUNTIME-DBCLASSES');
        if (!$existing['ok']) {
            return [
                'ok' => false,
                'error' => $existing['error'],
            ];
        }

        return is_array($existing['item'])
            ? app_update_project_source_output($app, $input)
            : app_create_project_source_output($app, $input);
    }

    /**
     * @param list<string> $errors
     * @return array<string,mixed>|null
     */
    private function readContextJson(string $projectKey, string $publishedRoot, array &$errors): ?array
    {
        if ($publishedRoot === '' || !is_dir($publishedRoot)) {
            $errors[] = $projectKey . ': published root missing: ' . $publishedRoot;
            return null;
        }

        $path = $publishedRoot . '/schema-context.json';
        if (!is_file($path)) {
            $errors[] = $projectKey . ': schema-context.json missing: ' . $path;
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            $errors[] = $projectKey . ': schema-context.json is not valid JSON';
            return null;
        }

        return $decoded;
    }

    /**
     * @param array<string,mixed> $context
     * @param list<string> $errors
     */
    private function assertPublishedPackage(string $projectKey, string $publishedRoot, array $context, array &$errors): void
    {
        foreach ([
            'README.md',
            'schema-summary.md',
            'relationships.md',
            'risky-areas.md',
            'generation-map.md',
            'agent-instructions.md',
            'schema-context.json',
        ] as $relativePath) {
            if (!is_file($publishedRoot . '/' . $relativePath)) {
                $errors[] = $projectKey . ': generated file missing: ' . $relativePath;
            }
        }

        $checks = [
            'artifact_type' => 'ai-context-md',
            'generation_rule.author' => 'DegoDB/Mtool generator code',
            'generation_rule.ai_role' => 'reader-consumer',
            'project.project_key' => $projectKey,
            'source_output.source_output_key' => self::SOURCE_OUTPUT_KEY,
            'source_output.artifact_strategy' => 'ai-context-md',
        ];

        foreach ($checks as $path => $expected) {
            $actual = $this->contextValue($context, $path);
            if ($actual !== $expected) {
                $errors[] = $projectKey . ': context ' . $path . ' expected=' . $expected
                    . ' actual=' . (is_scalar($actual) ? (string) $actual : gettype($actual));
            }
        }

        if (($context['generation_rule']['deterministic'] ?? null) !== true) {
            $errors[] = $projectKey . ': context generation_rule.deterministic expected=true';
        }
    }

    /**
     * @param array<string,mixed> $context
     * @param list<string> $errors
     */
    private function assertMtoolSelfContextLooksReviewable(string $publishedRoot, array $context, array &$errors): void
    {
        $tables = is_array($context['tables'] ?? null) ? $context['tables'] : [];
        $dataClasses = is_array($context['data_classes'] ?? null) ? $context['data_classes'] : [];
        $dbAccessClasses = is_array($context['db_access_classes'] ?? null) ? $context['db_access_classes'] : [];
        $sourceOutputs = is_array($context['source_outputs'] ?? null) ? $context['source_outputs'] : [];

        if (count($tables) < 20) {
            $errors[] = 'MTOOL: self context should include substantial table metadata; count=' . count($tables);
        }
        if (count($dataClasses) < 20) {
            $errors[] = 'MTOOL: self context should include substantial data class metadata; count=' . count($dataClasses);
        }
        if (count($dbAccessClasses) < 20) {
            $errors[] = 'MTOOL: self context should include substantial DBAccess metadata; count=' . count($dbAccessClasses);
        }

        $sourceOutputKeys = [];
        foreach ($sourceOutputs as $sourceOutput) {
            if (is_array($sourceOutput)) {
                $sourceOutputKeys[] = (string) ($sourceOutput['source_output_key'] ?? '');
            }
        }
        foreach (['RUNTIME-DBCLASSES', self::SOURCE_OUTPUT_KEY] as $expectedKey) {
            if (!in_array($expectedKey, $sourceOutputKeys, true)) {
                $errors[] = 'MTOOL: self context missing source output key: ' . $expectedKey;
            }
        }

        $readme = (string) file_get_contents($publishedRoot . '/README.md');
        $agentInstructions = (string) file_get_contents($publishedRoot . '/agent-instructions.md');
        foreach ([
            'generated by DegoDB / Mtool generator code',
            'AI is not the author or source of truth',
        ] as $expectedText) {
            if (!str_contains($readme, $expectedText)) {
                $errors[] = 'MTOOL: README missing review rule: ' . $expectedText;
            }
        }
        if (!str_contains($agentInstructions, 'Do not invent table meaning, relationship intent, or migration safety.')) {
            $errors[] = 'MTOOL: agent instructions missing unknown-intent rule';
        }
    }

    private function contextValue(array $context, string $path): mixed
    {
        $value = $context;
        foreach (explode('.', $path) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return null;
            }
            $value = $value[$part];
        }

        return $value;
    }
}
