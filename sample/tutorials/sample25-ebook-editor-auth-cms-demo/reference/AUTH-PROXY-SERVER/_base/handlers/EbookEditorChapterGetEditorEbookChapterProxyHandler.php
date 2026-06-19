<?php

declare(strict_types=1);

class EbookEditorChapterGetEditorEbookChapterProxyHandlerBase extends MtoolGeneratedSingleProxyEndpointBase
{
    protected function proxyDisplayName(): string
    {
        return 'EbookEditorChapter.GetEditorEbookChapter';
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
        return 'project-token';
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
                'source_name' => 'EbookEditorChapter',
                'dbaccess_class' => 'EbookEditorChapterDBAccess',
                'function_name' => 'GetEditorEbookChapter',
                'action' => 'select-single',
                'input_kind' => 'scalar',
                'object_param_name' => '',
                'object_class' => '',
                'parameter_names' =>
                    [
                        '0' => 'param_EbookEditorChapter_Id_where',
                    ],
                'response_key' => 'Result',
                'response_mode' => 'direct-result',
            ]
        ];
    }
}
