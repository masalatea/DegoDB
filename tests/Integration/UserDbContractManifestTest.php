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
            ['param_SupportTicket_Status_where', 'limit'],
            array_column($methods['GetSupportTicketList']['parameters'], 'name'),
        );
        self::assertSame(
            ['$param_SupportTicket_Status_where', '$limit'],
            $methods['GetSupportTicketList']['binds'],
        );
        self::assertSame(
            ['Id', 'Title', 'Status', 'AssignedTo', 'UpdatedAt'],
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
