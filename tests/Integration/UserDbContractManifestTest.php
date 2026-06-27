<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/scripts/lib/user_db_contract.php';
require_once dirname(__DIR__, 2) . '/mtool/scripts/lib/user_db_contract_runtime.php';

use PHPUnit\Framework\TestCase;

final class UserDbContractManifestTest extends TestCase
{
    public function testSample10DbAccessReferenceBuildsNormalizedContractManifest(): void
    {
        $root = dirname(__DIR__, 2)
            . '/sample/tutorials/sample10-dbaccess-mini-crud-flow/reference/DBACCESS-PHP';

        $manifest = app_user_db_contract_manifest(
            $root,
            'mysql',
            'sample10-dbaccess-mini-crud-flow',
        );

        self::assertSame('user-db-contract-manifest-v1', $manifest['schema']);
        self::assertSame('mysql', $manifest['dialect']);
        self::assertSame('sample10-dbaccess-mini-crud-flow', $manifest['sample']);
        self::assertSame(1, $manifest['class_count']);

        $class = $manifest['classes'][0] ?? null;
        self::assertIsArray($class);
        self::assertSame('SupportTicket', $class['class']);
        self::assertSame(5, $class['method_count']);

        $methods = [];
        foreach ($class['methods'] as $method) {
            self::assertIsArray($method);
            $methods[(string) $method['name']] = $method;
        }

        self::assertSame(
            ['GetSupportTicketList', 'GetSupportTicket', 'InsertSupportTicket', 'UpdateSupportTicket', 'DeleteSupportTicket'],
            array_keys($methods),
        );

        self::assertSame('SELECTLIST', $methods['GetSupportTicketList']['action_type']);
        self::assertSame('list', $methods['GetSupportTicketList']['cardinality']);
        self::assertSame(
            ['param_SupportTicket_status_where', 'limit'],
            array_column($methods['GetSupportTicketList']['parameters'], 'name'),
        );
        self::assertSame(
            ['$param_SupportTicket_status_where', '$limit'],
            $methods['GetSupportTicketList']['binds'],
        );
        self::assertSame(
            ['id', 'title', 'status', 'assignedTo', 'updatedAt'],
            array_column($methods['GetSupportTicketList']['result_fields'], 'name'),
        );

        self::assertSame('SELECTSINGLE', $methods['GetSupportTicket']['action_type']);
        self::assertSame('single', $methods['GetSupportTicket']['cardinality']);

        self::assertSame('INSERT', $methods['InsertSupportTicket']['action_type']);
        self::assertSame('write-result', $methods['InsertSupportTicket']['cardinality']);

        self::assertSame('UPDATE', $methods['UpdateSupportTicket']['action_type']);
        self::assertSame('DELETE', $methods['DeleteSupportTicket']['action_type']);
    }

    public function testManifestCompareIgnoresLaneMetadata(): void
    {
        $root = dirname(__DIR__, 2)
            . '/sample/tutorials/sample10-dbaccess-mini-crud-flow/reference/DBACCESS-PHP';

        $mysql = app_user_db_contract_manifest($root, 'mysql', 'sample10-dbaccess-mini-crud-flow');
        $sqlite = app_user_db_contract_manifest($root, 'sqlite', 'sample10-dbaccess-mini-crud-flow');
        $result = app_user_db_contract_compare_manifests($mysql, $sqlite);

        self::assertTrue($result['ok'], json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        self::assertSame('mysql', $result['left_dialect']);
        self::assertSame('sqlite', $result['right_dialect']);
        self::assertSame($result['left_digest'], $result['right_digest']);
    }

    public function testSample10RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample10-dbaccess-mini-crud-flow');

        self::assertSame('sample10-dbaccess-mini-crud-flow', $definition['sample']);
        self::assertSame(['data-SupportTicket.php'], $definition['dataclass_files']);
        self::assertSame(['dbaccess-SupportTicket.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample10', $definition['runner']);
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(4, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample06RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample06-dbaccess-filter-sort-page');

        self::assertSame('sample06-dbaccess-filter-sort-page', $definition['sample']);
        self::assertSame(['data-Announcement.php'], $definition['dataclass_files']);
        self::assertSame(['dbaccess-Announcement.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample06', $definition['runner']);
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(4, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample08RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample08-dbaccess-join-read-model');

        self::assertSame('sample08-dbaccess-join-read-model', $definition['sample']);
        self::assertSame(
            ['data-BlogAuthor.php', 'data-BlogPost.php', 'data-BlogPostAuthorSummary.php'],
            $definition['dataclass_files'],
        );
        self::assertSame(['dbaccess-BlogPost.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample08', $definition['runner']);
        self::assertCount(8, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(8, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(10, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample09RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample09-dbaccess-aggregate-report');

        self::assertSame('sample09-dbaccess-aggregate-report', $definition['sample']);
        self::assertSame(
            ['data-SalesCategory.php', 'data-SalesRecord.php', 'data-SalesCategoryReport.php'],
            $definition['dataclass_files'],
        );
        self::assertSame(['dbaccess-SalesRecord.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample09', $definition['runner']);
        self::assertCount(8, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(8, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(10, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample18RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample18-mini-task-board-demo');

        self::assertSame('sample18-mini-task-board-demo', $definition['sample']);
        self::assertSame(['data-TaskCard.php'], $definition['dataclass_files']);
        self::assertSame(['dbaccess-TaskCard.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample18', $definition['runner']);
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(4, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample19RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample19-json-first-content-model-demo');

        self::assertSame('sample19-json-first-content-model-demo', $definition['sample']);
        self::assertSame(
            [
                'data-ArticleJsonModel.php',
                'data-ArticlePublicSummary.php',
                'data-JsonAuthor.php',
                'data-JsonCategory.php',
            ],
            $definition['dataclass_files'],
        );
        self::assertSame(['dbaccess-ArticleJsonModel.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample19', $definition['runner']);
        self::assertCount(9, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(9, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(9, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample21RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample21-ebook-catalog-api-demo');

        self::assertSame('sample21-ebook-catalog-api-demo', $definition['sample']);
        self::assertSame(['data-EbookCatalogItem.php'], $definition['dataclass_files']);
        self::assertSame(['dbaccess-EbookCatalogItem.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample21', $definition['runner']);
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample22RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample22-ebook-chapter-workflow-demo');

        self::assertSame('sample22-ebook-chapter-workflow-demo', $definition['sample']);
        self::assertSame(
            ['data-EbookWorkflowChapter.php', 'data-EbookWorkflowPublishedChapter.php'],
            $definition['dataclass_files'],
        );
        self::assertSame(['dbaccess-EbookWorkflowChapter.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample22', $definition['runner']);
        self::assertCount(6, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(6, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(7, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample23RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample23-ebook-media-metadata-demo');

        self::assertSame('sample23-ebook-media-metadata-demo', $definition['sample']);
        self::assertSame(
            ['data-EbookMediaAsset.php', 'data-EbookMediaDelivery.php'],
            $definition['dataclass_files'],
        );
        self::assertSame(['dbaccess-EbookMediaAsset.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample23', $definition['runner']);
        self::assertCount(6, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(6, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(7, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample24RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample24-ebook-public-reader-site-demo');

        self::assertSame('sample24-ebook-public-reader-site-demo', $definition['sample']);
        self::assertSame(
            ['data-EbookReaderBook.php', 'data-EbookReaderChapter.php', 'data-EbookReaderMediaDelivery.php'],
            $definition['dataclass_files'],
        );
        self::assertSame(['dbaccess-EbookReaderBook.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample24', $definition['runner']);
        self::assertCount(9, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(9, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(9, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample25RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample25-ebook-editor-auth-cms-demo');

        self::assertSame('sample25-ebook-editor-auth-cms-demo', $definition['sample']);
        self::assertSame(['data-EbookEditorChapter.php'], $definition['dataclass_files']);
        self::assertSame(['dbaccess-EbookEditorChapter.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample25', $definition['runner']);
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(3, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testSample26RuntimeDefinitionIsAvailable(): void
    {
        $definition = app_user_db_contract_runtime_sample_definition('sample26-ebook-headless-cms-capstone');

        self::assertSame('sample26-ebook-headless-cms-capstone', $definition['sample']);
        self::assertSame(
            ['data-EbookCmsBook.php', 'data-EbookCmsChapter.php'],
            $definition['dataclass_files'],
        );
        self::assertSame(['dbaccess-EbookCmsBook.php'], $definition['dbaccess_files']);
        self::assertSame('app_user_db_contract_runtime_run_sample26', $definition['runner']);
        self::assertCount(6, app_user_db_contract_runtime_fixture_sql($definition, 'mysql'));
        self::assertCount(6, app_user_db_contract_runtime_fixture_sql($definition, 'sqlite'));
        self::assertCount(6, app_user_db_contract_runtime_fixture_sql($definition, 'pgsql'));
    }

    public function testRuntimeSummaryNormalizesDecimalStringValues(): void
    {
        $record = new stdClass();
        $record->TotalAmount = '200.00';
        $record->PartialAmount = '200.50';
        $record->Label = '200.00 USD';

        self::assertSame(
            [
                'Label' => '200.00 USD',
                'PartialAmount' => 200.5,
                'TotalAmount' => 200,
            ],
            app_user_db_contract_runtime_object_summary($record),
        );
    }
}
