<?php

declare(strict_types=1);

class CatalogSummaryProxyHandlerBase extends MtoolGeneratedCustomProxyEndpointBase
{
    protected function proxyDisplayName(): string
    {
        return 'Catalog::Summary';
    }

    protected function usesTransaction(): bool
    {
        return false;
    }

    protected function continueEvenIfFailedToInsert(): bool
    {
        return false;
    }

    protected function authStrategy(): string
    {
        return 'no-security';
    }

    protected function singleGetFunctionName(): string
    {
        return '';
    }

    protected function stepDefinitions(): array
    {
        return [
            [
                'step_no' => 1,
                'request_key' => 'step1',
                'is_list' => true,
                'source_name' => 'dbtable',
                'dbaccess_class' => 'dbtableDBAccess',
                'function_name' => 'GetdbtableList',
                'action' => 'select-list',
                'input_kind' => 'scalar',
                'object_param_name' => '',
                'object_class' => '',
                'parameter_names' =>
                    [
                        '0' => 'param_dbtable_ProjectPID_where',
                    ],
                'response_key' => 'Result1',
                'response_mode' => 'step-result-list',
            ],
            [
                'step_no' => 2,
                'request_key' => 'step2',
                'is_list' => true,
                'source_name' => 'ProjectSourceOutput',
                'dbaccess_class' => 'ProjectSourceOutputDBAccess',
                'function_name' => 'GetProjectSourceOutputList',
                'action' => 'select-list',
                'input_kind' => 'scalar',
                'object_param_name' => '',
                'object_class' => '',
                'parameter_names' =>
                    [
                        '0' => 'param_ProjectSourceOutput_ProjectPID_where',
                    ],
                'response_key' => 'Result2',
                'response_mode' => 'step-result-list',
            ]
        ];
    }
}
