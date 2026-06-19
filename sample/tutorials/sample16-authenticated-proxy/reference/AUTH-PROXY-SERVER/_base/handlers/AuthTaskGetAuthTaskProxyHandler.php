<?php

declare(strict_types=1);

class AuthTaskGetAuthTaskProxyHandlerBase extends MtoolGeneratedSingleProxyEndpointBase
{
    protected function proxyDisplayName(): string
    {
        return 'AuthTask.GetAuthTask';
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
        return 'static-bearer';
    }

    protected function authPolicy(): array
    {
        return         [
            'type' => 'static-bearer',
            'secret_env' => 'DEGODB_PROXY_BEARER_TOKEN',
        ];
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
                'request_key' => '',
                'is_list' => false,
                'source_name' => 'AuthTask',
                'dbaccess_class' => 'AuthTaskDBAccess',
                'function_name' => 'GetAuthTask',
                'action' => 'select-single',
                'input_kind' => 'scalar',
                'object_param_name' => '',
                'object_class' => '',
                'parameter_names' =>
                    [
                        '0' => 'param_AuthTask_Id_where',
                    ],
                'response_key' => 'Result',
                'response_mode' => 'direct-result',
            ]
        ];
    }
}
