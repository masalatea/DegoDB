<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';

function app_primary_self_loop_project_key(): string
{
    return 'MTOOL';
}

function app_project_is_primary_self_loop_target(string $projectKey): bool
{
    return app_normalize_project_key($projectKey) === app_primary_self_loop_project_key();
}

/**
 * @return array{
 *     is_primary:bool,
 *     label:string,
 *     summary:string,
 *     details:list<string>
 * }
 */
function app_project_scope_policy(string $projectKey): array
{
    if (app_project_is_primary_self_loop_target($projectKey)) {
        return [
            'is_primary' => true,
            'label' => 'primary self-loop target',
            'summary' => 'Project 1 (MTOOL) を default の self-loop 対象として扱います。DB import から Source Output 生成までの主導線と、将来の期待出力テストはここを基準にします。',
            'details' => [
                '日常の import / sync / generate はまず MTOOL で成立させます。',
                '他 project への展開は、この導線で同等機能を再現できるかの確認として扱います。',
                '将来の期待出力テストは MTOOL artifact を基準に追加します。',
            ],
        ];
    }

    return [
        'is_primary' => false,
        'label' => 'reference / test data',
        'summary' => 'この project は reference/test data として扱います。主目的は、Project 1 (MTOOL) の導線で同等機能を再現できることを確認することです。',
        'details' => [
            'default の self-loop 対象は Project 1 (MTOOL) のままです。',
            'この project は常用の import/sync baseline ではなく、再現確認用の sample として使います。',
        ],
    ];
}
