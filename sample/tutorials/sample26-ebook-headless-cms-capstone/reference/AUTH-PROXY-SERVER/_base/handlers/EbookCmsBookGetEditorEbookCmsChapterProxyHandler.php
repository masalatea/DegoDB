<?php

declare(strict_types=1);

class EbookCmsBookGetEditorEbookCmsChapterProxyHandlerBase extends MtoolGeneratedSingleProxyEndpointBase
{
    protected function proxyDisplayName(): string
    {
        return 'EbookCmsBook.GetEditorEbookCmsChapter';
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
                'request_key' => '',
                'is_list' => false,
                'source_name' => 'EbookCmsBook',
                'dbaccess_class' => 'EbookCmsBookDBAccess',
                'function_name' => 'GetEditorEbookCmsChapter',
                'action' => 'select-single',
                'input_kind' => 'scalar',
                'object_param_name' => '',
                'object_class' => '',
                'parameter_names' =>
                    [
                        '0' => 'param_EbookCmsChapter_Id_where',
                    ],
                'response_key' => 'Result',
                'response_mode' => 'direct-result',
            ]
        ];
    }
}
