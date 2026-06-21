<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DocsEntranceContractTest extends TestCase
{
    private const FULL_SUITE_COMMAND =
        'ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test';

    public function testEntranceDocsKeepCoreLinks(): void
    {
        $readme = $this->readRepoFile('README.md');
        $quickstart = $this->readRepoFile('docs/quickstart.md');
        $startHere = $this->readRepoFile('docs/start-here.md');
        $docsIndex = $this->readRepoFile('docs/README.md');
        $chooseYourPath = $this->readRepoFile('docs/choose-your-path.md');

        $this->assertContainsAll(
            [
                'docs/quickstart.md',
                'docs/start-here.md',
                'docs/choose-your-path.md',
                'docs/existing-db-to-output.md',
                'docs/common-tasks.md',
                'docs/current-supported-workflow.md',
                'docs/storage-and-state-model.md',
                'docs/project-metadata-bundle.md',
                'docs/config-db-externalization.md',
                'docs/troubleshooting.md',
                'docs/glossary.md',
                'docs/sample-tutorial-roadmap.md',
                'docs/study/README.md',
                'docs/internal/README.md',
                'tests/README.md',
            ],
            $readme,
            'README.md',
        );

        $this->assertContainsAll(
            [
                '[choose-your-path.md](choose-your-path.md)',
                '[start-here.md](start-here.md)',
                '[existing-db-to-output.md](existing-db-to-output.md)',
                '[common-tasks.md](common-tasks.md)',
                '[study/README.md](study/README.md)',
                '[sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)',
                'make env',
                'make up-mtool',
                'make health-mtool',
                'make config-db-preflight-mtool',
                'make mtool-canonical-sync',
                'make sample01-pack-runtime-test',
                'mtool/reference/legacy-dbclasses/',
                'mtool/reference/legacy-mtool-build/',
                'mtool/reference/legacy-mtool-templates/',
            ],
            $quickstart,
            'docs/quickstart.md',
        );

        $this->assertContainsAll(
            [
                '[quickstart.md](quickstart.md)',
                '[choose-your-path.md](choose-your-path.md)',
                '[existing-db-to-output.md](existing-db-to-output.md)',
                '[existing-db-to-output.md#e10-handoff-payload](existing-db-to-output.md#e10-handoff-payload)',
                '[storage-and-state-model.md](storage-and-state-model.md)',
                '[storage-and-state-model.md#s1-resume-checkpoints](storage-and-state-model.md#s1-resume-checkpoints)',
                '[common-tasks.md](common-tasks.md)',
                '[current-supported-workflow.md](current-supported-workflow.md)',
                '[overview.md](overview.md)',
                '[troubleshooting.md](troubleshooting.md)',
                '[sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)',
                '[study/README.md](study/README.md)',
                '[internal/README.md](internal/README.md)',
                '[../tests/README.md](../tests/README.md)',
            ],
            $startHere,
            'docs/start-here.md',
        );

        $this->assertContainsAll(
            [
                'quickstart.md',
                'start-here.md',
                'choose-your-path.md',
                'existing-db-to-output.md',
                'common-tasks.md',
                'current-supported-workflow.md',
                'storage-and-state-model.md',
                'project-metadata-bundle.md',
                'config-db-externalization.md',
                'troubleshooting.md',
                'glossary.md',
                'sample-tutorial-roadmap.md',
                'study/README.md',
                'internal/README.md',
                '../tests/README.md',
            ],
            $docsIndex,
            'docs/README.md',
        );

        $this->assertContainsAll(
            [
                '[quickstart.md](quickstart.md)',
                '[start-here.md](start-here.md)',
                '[existing-db-to-output.md](existing-db-to-output.md)',
                '[existing-db-to-output.md#e10-handoff-payload](existing-db-to-output.md#e10-handoff-payload)',
                '[storage-and-state-model.md](storage-and-state-model.md)',
                '[storage-and-state-model.md#s1-resume-checkpoints](storage-and-state-model.md#s1-resume-checkpoints)',
                '[common-tasks.md](common-tasks.md)',
                '[current-supported-workflow.md](current-supported-workflow.md)',
                '[project-metadata-bundle.md](project-metadata-bundle.md)',
                '[config-db-externalization.md](config-db-externalization.md)',
                '[troubleshooting.md](troubleshooting.md)',
                '[sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)',
                '[study/README.md](study/README.md)',
                '[internal/README.md](internal/README.md)',
                '[../sample/tutorials/README.md](../sample/tutorials/README.md)',
            ],
            $chooseYourPath,
            'docs/choose-your-path.md',
        );
    }

    public function testEntranceDocsDescribeThreeLayerReadingModel(): void
    {
        $readme = $this->readRepoFile('README.md');
        $docsIndex = $this->readRepoFile('docs/README.md');
        $quickstart = $this->readRepoFile('docs/quickstart.md');
        $startHere = $this->readRepoFile('docs/start-here.md');
        $chooseYourPath = $this->readRepoFile('docs/choose-your-path.md');

        foreach ([$readme, $docsIndex, $startHere, $chooseYourPath] as $content) {
            self::assertStringContainsString('入口 layer', $content);
            self::assertStringContainsString('golden path layer', $content);
            self::assertStringContainsString('detail layer', $content);
        }

        self::assertStringContainsString('最初に 1 周だけ動かす', $quickstart);

        foreach (
            [
                'docs/existing-db-to-output.md',
                'docs/project-metadata-bundle.md',
                'docs/config-db-externalization.md',
                'docs/internal/README.md',
            ] as $needle
        ) {
            self::assertStringContainsString($needle, $readme);
        }

        $this->assertContainsAll(
            [
                '[quickstart.md](quickstart.md)',
                '[start-here.md](start-here.md)',
                '[existing-db-to-output.md](existing-db-to-output.md)',
                '[existing-db-to-output.md#e10-handoff-payload](existing-db-to-output.md#e10-handoff-payload)',
                '[storage-and-state-model.md](storage-and-state-model.md)',
                '[storage-and-state-model.md#s1-resume-checkpoints](storage-and-state-model.md#s1-resume-checkpoints)',
                '[project-metadata-bundle.md](project-metadata-bundle.md)',
                '[config-db-externalization.md](config-db-externalization.md)',
                '[current-supported-workflow.md](current-supported-workflow.md)',
                '[common-tasks.md](common-tasks.md)',
                '[troubleshooting.md](troubleshooting.md)',
                '[internal/README.md](internal/README.md)',
            ],
            $chooseYourPath,
            'docs/choose-your-path.md',
        );
    }

    public function testTopLevelDocsExposeInternalReferenceThroughIndexOnly(): void
    {
        $readme = $this->readRepoFile('README.md');
        $docsIndex = $this->readRepoFile('docs/README.md');
        $quickstart = $this->readRepoFile('docs/quickstart.md');
        $startHere = $this->readRepoFile('docs/start-here.md');
        $chooseYourPath = $this->readRepoFile('docs/choose-your-path.md');

        self::assertStringContainsString('docs/internal/README.md', $readme);
        foreach ([$docsIndex, $startHere, $chooseYourPath] as $content) {
            self::assertStringContainsString('internal/README.md', $content);
        }

        foreach (
            [
                'internal/ai-operator-contract.md',
                'internal/repo-boundaries.md',
                'internal/runtime-architecture.md',
                'internal/generated-code-strategy.md',
            ] as $needle
        ) {
            self::assertStringNotContainsString($needle, $readme);
            self::assertStringNotContainsString($needle, $docsIndex);
            self::assertStringNotContainsString($needle, $quickstart);
            self::assertStringNotContainsString($needle, $startHere);
            self::assertStringNotContainsString($needle, $chooseYourPath);
        }
    }

    public function testInternalDocsIndexExplainsOneStepInwardLayout(): void
    {
        $internalIndex = $this->readRepoFile('docs/internal/README.md');

        $this->assertContainsAll(
            [
                '[DegoDB](../../README.md)',
                '../README.md',
                '../start-here.md',
                '../choose-your-path.md',
                'ai-operator-contract.md',
                'repo-boundaries.md',
                'runtime-architecture.md',
                'generated-code-strategy.md',
                'site-boundaries.md',
                'source-output-path-policy.md',
                'auth-architecture.md',
                'data-model.md',
                'mtool-admin-roadmap.md',
                'html-db-rewrite-map.md',
                'legacy-new-db-mapping.md',
                'language-resource-separation.md',
            ],
            $internalIndex,
            'docs/internal/README.md',
        );

        self::assertStringContainsString('top-level `docs/` は external / user-facing guide', $internalIndex);
        self::assertStringContainsString('まずこの索引を 1 段はさみます', $internalIndex);
    }

    public function testEntranceDocsKeepBoundaryWording(): void
    {
        foreach (
            [
                'README.md',
                'docs/quickstart.md',
                'docs/start-here.md',
                'docs/storage-and-state-model.md',
                'docs/internal/ai-operator-contract.md',
                'docs/internal/repo-boundaries.md',
                'docs/current-supported-workflow.md',
            ] as $relativePath
        ) {
            $content = $this->readRepoFile($relativePath);
            self::assertStringContainsString('`mtool/reference/legacy-', $content, 'missing curated legacy reference mention: ' . $relativePath);
            self::assertStringContainsString('runtime input', $content, 'missing runtime input boundary wording: ' . $relativePath);
        }
    }

    public function testExistingDbJourneyDocsStayConcrete(): void
    {
        $chooseYourPath = $this->readRepoFile('docs/choose-your-path.md');
        $journey = $this->readRepoFile('docs/existing-db-to-output.md');
        $stateModel = $this->readRepoFile('docs/storage-and-state-model.md');
        $aiContract = $this->readRepoFile('docs/internal/ai-operator-contract.md');
        $workflow = $this->readRepoFile('docs/current-supported-workflow.md');
        $commonTasks = $this->readRepoFile('docs/common-tasks.md');
        $bundleDoc = $this->readRepoFile('docs/project-metadata-bundle.md');
        $configDoc = $this->readRepoFile('docs/config-db-externalization.md');

        $this->assertContainsAll(
            [
                '[existing-db-to-output.md](existing-db-to-output.md)',
                '[storage-and-state-model.md](storage-and-state-model.md)',
                '[common-tasks.md](common-tasks.md)',
                '[current-supported-workflow.md](current-supported-workflow.md)',
                '[internal/README.md](internal/README.md)',
            ],
            $chooseYourPath,
            'docs/choose-your-path.md',
        );

        $this->assertContainsAll(
            [
                '`named-live-schema:{source_key}`',
                '`config_db.database_sources`',
                'import_project_tables.php',
                'sync_project_data_classes.php',
                'sync_project_db_access.php',
                'create_project_output.php',
                '`db_source_key`',
                '## この文書の使い方',
                '## Quick Start',
                '### Purpose',
                '### UI',
                '### CLI',
                '### Persistence',
                '### Success Markers',
                '### Troubleshooting',
                '<a id="e10-handoff-payload"></a>',
                '### Handoff Payload Example',
                '## 完了時に手元に残るもの',
                'preview は Stage 4 の UI を使う',
                '`import_project_tables.php` は preview ではなく apply',
                '`current_stage`',
                '`last_artifact_key`',
                '(troubleshooting.md#t1-lane-mixups)',
            ],
            $journey,
            'docs/existing-db-to-output.md',
        );

        $this->assertContainsAll(
            [
                '`config_db`',
                'work/artifacts/source-outputs/',
                'work/source-outputs/',
                '<a id="s1-resume-checkpoints"></a>',
                '## resume / handoff でどこを見るか',
                '`config_db.database_sources`',
                '`config_db.project_db_access_*`',
                'curated legacy reference only',
            ],
            $stateModel,
            'docs/storage-and-state-model.md',
        );

        $this->assertContainsAll(
            [
                '[../existing-db-to-output.md](../existing-db-to-output.md)',
                '[../storage-and-state-model.md](../storage-and-state-model.md)',
                '`mtool/reference/legacy-*`',
                '`/settings/database-sources`',
                '`import_project_tables.php` は apply',
                '<a id="a1-handoff-payload"></a>',
                '## handoff / resume に残す最小 payload',
                '`chosen lane`',
                '`artifact_key`',
                '`database-source-secrets`',
            ],
            $aiContract,
            'docs/internal/ai-operator-contract.md',
        );

        $troubleshooting = $this->readRepoFile('docs/troubleshooting.md');
        $this->assertContainsAll(
            [
                '## T1. Lane Mixups',
                '## T2. Source Missing From Import Options',
                '## T3. Import Preview / Apply Confusion',
                '## T4. Config DB Preflight',
                '## T5. Missing Secret Env In Bundle Preview',
                '## T6. Runtime Source Selection In Swagger And Proxy',
                '## T7. OpenAPI Visibility And Raw Route Assumptions',
                '## T8. External Lane Advanced Operations',
                '## T9. Reference Snapshot Only',
            ],
            $troubleshooting,
            'docs/troubleshooting.md',
        );

        $sharedWorkflowNeedles = [
            'existing-db-to-output.md#e10-handoff-payload',
            'storage-and-state-model.md#s1-resume-checkpoints',
            'existing-db-to-output.md#e3-register-source',
            'existing-db-to-output.md#e8-publish-output',
            'existing-db-to-output.md#e9-verify-output',
        ];

        $this->assertContainsAll($sharedWorkflowNeedles, $workflow, 'docs/current-supported-workflow.md');
        $this->assertContainsAll($sharedWorkflowNeedles, $commonTasks, 'docs/common-tasks.md');
        self::assertStringContainsString('internal/README.md', $workflow);
        self::assertStringContainsString('internal/README.md', $commonTasks);

        $this->assertContainsAll(
            [
                'existing-db-to-output.md#e10-capture-rerun-path',
                'troubleshooting.md#t5-missing-secret-env-in-bundle-preview',
                '<a id="b2-export"></a>',
                '<a id="b3-import"></a>',
                '<a id="b4-secret-handling"></a>',
                '<a id="b5-database-source-rules"></a>',
            ],
            $bundleDoc,
            'docs/project-metadata-bundle.md',
        );

        $this->assertContainsAll(
            [
                'existing-db-to-output.md#e1-choose-topology',
                'existing-db-to-output.md#e2-boot-and-preflight',
                'troubleshooting.md#t1-lane-mixups',
                'troubleshooting.md#t4-config-db-preflight',
                '<a id="c2-compose-topology"></a>',
                '<a id="c5-preflight-migrate"></a>',
                '<a id="c7-advanced-operations"></a>',
            ],
            $configDoc,
            'docs/config-db-externalization.md',
        );
    }

    public function testMovedInternalDocsKeepRelativeLinksCurrent(): void
    {
        $aiContract = $this->readRepoFile('docs/internal/ai-operator-contract.md');
        $repoBoundaries = $this->readRepoFile('docs/internal/repo-boundaries.md');

        $this->assertContainsAll(
            [
                '[../../README.md](../../README.md)',
                '[../README.md](../README.md)',
                '[README.md](README.md)',
                '[../start-here.md](../start-here.md)',
                '[../choose-your-path.md](../choose-your-path.md)',
                '[../existing-db-to-output.md](../existing-db-to-output.md)',
                '[../storage-and-state-model.md](../storage-and-state-model.md)',
                '[../current-supported-workflow.md](../current-supported-workflow.md)',
                '[../common-tasks.md](../common-tasks.md)',
                '[../troubleshooting.md](../troubleshooting.md)',
                '[../project-metadata-bundle.md](../project-metadata-bundle.md)',
                '[../config-db-externalization.md](../config-db-externalization.md)',
            ],
            $aiContract,
            'docs/internal/ai-operator-contract.md',
        );

        $this->assertContainsAll(
            [
                '[../start-here.md](../start-here.md)',
                '[../current-supported-workflow.md](../current-supported-workflow.md)',
                '[source-output-path-policy.md](source-output-path-policy.md)',
                '[site-boundaries.md](site-boundaries.md)',
            ],
            $repoBoundaries,
            'docs/internal/repo-boundaries.md',
        );
    }

    public function testEntranceDocsKeepSampleLaneMentions(): void
    {
        foreach (
            [
                'README.md',
                'docs/start-here.md',
                'docs/internal/repo-boundaries.md',
            ] as $relativePath
        ) {
            $content = $this->readRepoFile($relativePath);
            self::assertStringContainsString('sample/tutorials/', $content, 'missing tutorial lane: ' . $relativePath);
            self::assertStringContainsString('sample/internal-patterns/', $content, 'missing internal pattern lane: ' . $relativePath);
            self::assertStringContainsString('sample/legacy-projects/', $content, 'missing legacy project lane: ' . $relativePath);
        }
    }

    public function testCurrentMainlineDocsKeepKnownFullSuiteCommand(): void
    {
        foreach (
            [
                'README.md',
                'docs/start-here.md',
                'docs/current-supported-workflow.md',
                'docs/common-tasks.md',
            ] as $relativePath
        ) {
            self::assertStringContainsString(self::FULL_SUITE_COMMAND, $this->readRepoFile($relativePath), 'missing full-suite command: ' . $relativePath);
        }
    }

    public function testEntranceDocsKeepConfigDbComposeLaneWording(): void
    {
        $readme = $this->readRepoFile('README.md');
        $startHere = $this->readRepoFile('docs/start-here.md');
        $workflow = $this->readRepoFile('docs/current-supported-workflow.md');
        $commonTasks = $this->readRepoFile('docs/common-tasks.md');

        foreach ([$readme, $startHere, $workflow, $commonTasks] as $content) {
            self::assertStringContainsString('compose.local-db-config.yaml', $content);
            self::assertStringContainsString('make up-external-config-db', $content);
            self::assertStringContainsString('make config-db-preflight-external-config-db', $content);
            self::assertStringContainsString('docker compose -f compose.yaml', $content);
        }

        foreach ([$workflow, $commonTasks] as $content) {
            self::assertStringContainsString('docker compose -f compose.yaml exec web-admin bash', $content);
            self::assertStringContainsString('COMPOSE_PROFILES=lab-db-ui docker compose -f compose.yaml stop', $content);
        }
    }

    public function testGlossaryKeepsCoreTerms(): void
    {
        $glossary = $this->readRepoFile('docs/glossary.md');

        foreach (
            [
                '`dbtable` / `dbtablecolumns`',
                '`dataclass` / `dataclassfields`',
                '`da`',
                '`dafunc`',
                '`single-function proxy`',
                '`custom proxy`',
                '`source output`',
                '`runtime reference`',
                '`sampleNN-pack-runtime-test`',
                '`patternNN-output-test`',
                'curated legacy reference only',
            ] as $needle
        ) {
            self::assertStringContainsString($needle, $glossary);
        }
    }

    public function testPermanentDocsKeepEnglishCompanionAndReportLanguageRule(): void
    {
        $repoRoot = dirname(__DIR__, 2);
        $docsFiles = glob($repoRoot . '/docs/*.md') ?: [];
        $internalDocsFiles = glob($repoRoot . '/docs/internal/*.md') ?: [];
        $studyDocsFiles = glob($repoRoot . '/docs/study/*.md') ?: [];

        $permanentDocs = ['README.md'];
        foreach (array_merge($docsFiles, $internalDocsFiles, $studyDocsFiles) as $absolutePath) {
            if (str_contains($absolutePath, '/docs/internal/')) {
                $prefix = 'docs/internal/';
            } elseif (str_contains($absolutePath, '/docs/study/')) {
                $prefix = 'docs/study/';
            } else {
                $prefix = 'docs/';
            }
            $permanentDocs[] = $prefix . basename($absolutePath);
        }
        $permanentDocs = array_values(array_unique($permanentDocs));
        sort($permanentDocs);

        foreach ($permanentDocs as $relativePath) {
            self::assertStringContainsString(
                'English companion:',
                $this->readRepoFile($relativePath),
                'missing English companion in: ' . $relativePath,
            );
        }

        $this->assertContainsAll(
            [
                '恒久文書は日本語本文を正本にしつつ、冒頭に英語 companion を添えて日英併記で維持する',
                'top-level `docs/` は外部ユーザ向け導線を優先し、個別 internal doc は [Internal Documentation Index / 内部ドキュメント索引](internal/README.md) から辿る',
                '`docs/reports/` 配下の progress / handoff / resume prompt / slice report は日本語のみ運用でよい',
            ],
            $this->readRepoFile('docs/README.md'),
            'docs/README.md',
        );

        $this->assertContainsAll(
            [
                'top-level `docs/` は外部ユーザ向け導線を優先し、個別 internal doc はこの索引から辿る',
                '恒久文書は日本語本文を正本にしつつ、冒頭に英語 companion を添えて日英併記で維持する',
                '`docs/reports/` 配下の progress / handoff / resume prompt / slice report は日本語のみ運用でよい',
            ],
            $this->readRepoFile('docs/internal/README.md'),
            'docs/internal/README.md',
        );
    }

    private function assertContainsAll(array $needles, string $content, string $label): void
    {
        foreach ($needles as $needle) {
            self::assertStringContainsString(
                $needle,
                $content,
                $label . ': missing ' . $needle,
            );
        }
    }

    private function readRepoFile(string $relativePath): string
    {
        $absolutePath = dirname(__DIR__, 2) . '/' . $relativePath;
        self::assertFileExists($absolutePath, 'missing file: ' . $relativePath);

        $content = file_get_contents($absolutePath);
        self::assertIsString($content, 'failed to read: ' . $relativePath);

        return $content;
    }
}
