<?php

declare(strict_types=1);

class EbookEditorChapterPublishEditorEbookChapterProxyHandlerBase extends MtoolGeneratedSingleProxyEndpointBase
{
    protected function proxyDisplayName(): string
    {
        return 'EbookEditorChapter.PublishEditorEbookChapter';
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
                'source_name' => 'EbookEditorChapter',
                'dbaccess_class' => 'EbookEditorChapterDBAccess',
                'function_name' => 'PublishEditorEbookChapter',
                'action' => 'update',
                'input_kind' => 'object',
                'object_param_name' => 'EbookEditorChapterObj',
                'object_class' => 'EbookEditorChapterData',
                'parameter_names' =>
                    [
                        '0' => 'EbookEditorChapterObj',
                    ],
                'response_key' => '',
                'response_mode' => 'none',
            ]
        ];
    }
}
