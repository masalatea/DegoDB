<?php

declare(strict_types=1);

class TransactionPairProxyHandlerBase extends MtoolGeneratedCustomProxyEndpointBase
{
    protected function proxyDisplayName(): string
    {
        return 'Transaction::Pair';
    }

    protected function usesTransaction(): bool
    {
        return true;
    }

    protected function continueEvenIfFailedToInsert(): bool
    {
        return false;
    }

    protected function authStrategy(): string
    {
        return 'no-security';
    }

    protected function authPolicy(): array
    {
        return         [
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
                'request_key' => 'step1',
                'is_list' => false,
                'source_name' => 'sample14_transaction_item',
                'dbaccess_class' => 'Sample14TransactionItemDBAccess',
                'function_name' => 'InsertSample14TransactionItem',
                'action' => 'insert',
                'input_kind' => 'object',
                'object_param_name' => 'Sample14TransactionItemObj',
                'object_class' => 'Sample14TransactionItemData',
                'parameter_names' =>
                    [
                        '0' => 'Sample14TransactionItemObj',
                    ],
                'response_key' => 'insert_id1',
                'response_mode' => 'insert-id-single',
            ],
            [
                'step_no' => 2,
                'request_key' => 'step2',
                'is_list' => false,
                'source_name' => 'sample14_transaction_item',
                'dbaccess_class' => 'Sample14TransactionItemDBAccess',
                'function_name' => 'InsertSample14TransactionItem',
                'action' => 'insert',
                'input_kind' => 'object',
                'object_param_name' => 'Sample14TransactionItemObj',
                'object_class' => 'Sample14TransactionItemData',
                'parameter_names' =>
                    [
                        '0' => 'Sample14TransactionItemObj',
                    ],
                'response_key' => 'insert_id2',
                'response_mode' => 'insert-id-single',
            ]
        ];
    }
}
