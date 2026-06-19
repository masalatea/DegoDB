<?php

declare(strict_types=1);

class EbookCmsBookPublishEditorEbookCmsChapterProxyHandlerBase extends MtoolGeneratedSingleProxyEndpointBase
{
    protected function proxyDisplayName(): string
    {
        return 'EbookCmsBook.PublishEditorEbookCmsChapter';
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
                'function_name' => 'PublishEditorEbookCmsChapter',
                'action' => 'update',
                'input_kind' => 'object',
                'object_param_name' => 'EbookCmsChapterObj',
                'object_class' => 'EbookCmsBookData',
                'parameter_names' =>
                    [
                        '0' => 'EbookCmsChapterObj',
                    ],
                'response_key' => '',
                'response_mode' => 'none',
            ]
        ];
    }
}
