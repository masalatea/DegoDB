<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/generated_name.php';

use PHPUnit\Framework\TestCase;

final class GeneratedNameTest extends TestCase
{
    public function testPhysicalSnakeCaseBecomesLogicalPascalCase(): void
    {
        self::assertSame('SupportTicket', app_physical_name_to_logical_name('support_ticket'));
        self::assertSame('UpdatedAt', app_physical_name_to_logical_name('updated_at'));
        self::assertSame('ProjectUserProfile', app_physical_name_to_logical_name('project_user_profile'));
        self::assertSame('Support', app_physical_name_to_logical_name('support_'));
    }

    public function testExistingPascalCaseIsKeptAsLogicalName(): void
    {
        self::assertSame('SupportTicket', app_physical_name_to_logical_name('SupportTicket'));
        self::assertSame('UpdatedAt', app_physical_name_to_logical_name('UpdatedAt'));
    }

    public function testDelimitedMixedCaseSegmentsKeepExistingBoundaries(): void
    {
        self::assertSame(
            'HtmlTemplateLeftouterjoinParentHtmlTemplate',
            app_physical_name_to_logical_name('htmlTemplate_leftouterjoin_ParentHtmlTemplate'),
        );
        self::assertSame(
            'DaCustomProxyFuncLeftouterjoinDafuncAndDa',
            app_physical_name_to_logical_name('daCustomProxyFunc_leftouterjoin_dafunc_and_da'),
        );
        self::assertSame(
            'MinutesAndRelatedTables',
            app_physical_name_to_logical_name('minutes_and_RelatedTables'),
        );
        self::assertSame(
            'IDToken',
            app_physical_name_to_logical_name('ID_token'),
        );
    }

    public function testLowercaseWithoutWordBoundaryIsNotGuessed(): void
    {
        self::assertSame('Supportticket', app_physical_name_to_logical_name('supportticket'));
        self::assertSame('Updatedat', app_physical_name_to_logical_name('updatedat'));
    }

    public function testGeneratedNameCanTargetClassOrPropertySurface(): void
    {
        self::assertSame('SupportTicket', app_logical_name_to_generated_name('SupportTicket', 'php-class'));
        self::assertSame('supportTicket', app_logical_name_to_generated_name('SupportTicket', 'php-property'));
        self::assertSame('updatedAt', app_logical_name_to_generated_name('UpdatedAt', 'php-parameter'));
        self::assertSame(
            'htmlTemplateLeftouterjoinParentHtmlTemplate',
            app_logical_name_to_generated_name('HtmlTemplateLeftouterjoinParentHtmlTemplate', 'php-property'),
        );
    }

    public function testGeneratedNameMapKeepsPhysicalNameSeparate(): void
    {
        self::assertSame(
            [
                'physical_name' => 'support_ticket',
                'logical_name' => 'SupportTicket',
                'generated_name' => 'supportTicket',
            ],
            app_generated_name_map_for_physical_name('support_ticket', 'php-property'),
        );
    }

    public function testSafeUnquotedSqlIdentifierPolicy(): void
    {
        self::assertTrue(app_physical_name_is_safe_unquoted_sql_identifier('support_ticket'));
        self::assertTrue(app_physical_name_is_safe_unquoted_sql_identifier('updated_at2'));

        self::assertFalse(app_physical_name_is_safe_unquoted_sql_identifier('SupportTicket'));
        self::assertFalse(app_physical_name_is_safe_unquoted_sql_identifier('support-ticket'));
        self::assertFalse(app_physical_name_is_safe_unquoted_sql_identifier('2support_ticket'));
        self::assertFalse(app_physical_name_is_safe_unquoted_sql_identifier('user'));
        self::assertFalse(app_physical_name_is_safe_unquoted_sql_identifier('order'));
    }

    public function testGeneratedNamePolicyModeIsOptIn(): void
    {
        $previous = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY');

        try {
            self::assertSame('', app_generated_name_policy_mode());
            self::assertFalse(app_generated_name_policy_uses_physical_logical_names());

            putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');
            self::assertSame('physical-logical-v1', app_generated_name_policy_mode());
            self::assertTrue(app_generated_name_policy_uses_physical_logical_names());
        } finally {
            if ($previous === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previous);
            }
        }
    }
}
