<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/router.php';
require_once __DIR__ . '/error_page.php';

/**
 * @return array<string,mixed>
 */
function app_mobile_wrapper_artifact_page_contract(): array
{
    return [
        'contract_version' => 'mobile-wrapper-artifact-page-v1',
        'route_candidate' => [
            'method' => 'GET',
            'path' => '/projects/{project_key}/mobile-wrapper-artifacts',
            'default_state' => 'not_routed_yet',
            'mutation_performed' => false,
        ],
        'supported_inputs' => [
            '--sample=sample28',
            '--handoff-file=PATH',
            '--project-key=KEY --source-output-key=KEY',
        ],
        'supported_artifacts' => [
            'c1',
            'react-wrapper-app',
            'platform-input-packets',
            'bundle-manifest',
        ],
        'ownership_boundary' => [
            'mtool_owns' => [
                'artifact selection guidance',
                'CLI command construction',
                'metadata and handoff packet validation',
            ],
            'external_owner_owns' => [
                'React app shell',
                'Capacitor project',
                'Flutter project',
                'React Native project',
                'iOS and Android native builds',
                'signing and store submission',
            ],
        ],
        'excluded_operations' => [
            'artifact generation execution from UI',
            'native project creation',
            'signing file creation',
            'store submission',
        ],
    ];
}

/**
 * @param array<string,mixed> $contract
 */
function app_mobile_wrapper_artifact_page_html(array $contract, string $projectKey = '', string $sourceOutputKey = ''): string
{
    $projectKey = trim($projectKey);
    $sourceOutputKey = trim($sourceOutputKey);
    $sourceArgs = $projectKey !== '' && $sourceOutputKey !== ''
        ? '--project-key=' . $projectKey . ' --source-output-key=' . $sourceOutputKey
        : '--handoff-file=work/mobile-app-handoff.json';
    $exampleCommand = 'php mtool/scripts/create_mobile_wrapper_target.php '
        . $sourceArgs
        . ' --artifact=bundle-manifest'
        . ' --target-dir=work/mobile-wrapper-target/mobile-wrapper-bundle';

    $html = [];
    $html[] = '<section data-mtool-mobile-wrapper-artifact-page="v1">';
    $html[] = '<h1>Mobile Wrapper Artifacts</h1>';
    $html[] = '<p>This page is a read-only handoff guide. It does not generate artifacts or create app projects.</p>';
    $html[] = '<h2>Supported artifacts</h2>';
    $html[] = '<ul>';
    foreach (($contract['supported_artifacts'] ?? []) as $artifact) {
        $html[] = '<li><code>' . app_h((string) $artifact) . '</code></li>';
    }
    $html[] = '</ul>';
    $html[] = '<h2>Example command</h2>';
    $html[] = '<pre><code>' . app_h($exampleCommand) . '</code></pre>';
    $html[] = '<h2>Boundary</h2>';
    $html[] = '<ul>';
    foreach (($contract['excluded_operations'] ?? []) as $operation) {
        $html[] = '<li>' . app_h((string) $operation) . '</li>';
    }
    $html[] = '</ul>';
    $html[] = '</section>';

    return implode("\n", $html) . "\n";
}

/**
 * @param array<string,mixed> $app
 * @param array<string,mixed> $request
 */
function app_render_mobile_wrapper_artifact_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    app_send_html_response_headers($request);
    $projectKey = app_route_param($request, 'project_key');
    $sourceOutputKey = app_query_param('source_output_key', '');
    $html = app_mobile_wrapper_artifact_page_html(
        app_mobile_wrapper_artifact_page_contract(),
        $projectKey,
        $sourceOutputKey,
    );

    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mobile Wrapper Artifacts</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; line-height: 1.6; }
        code { background: #f2f2f2; padding: 0.1rem 0.3rem; }
        pre { background: #f7f7f7; padding: 1rem; overflow-x: auto; }
    </style>
</head>
<body>
<?php echo $html; ?>
</body>
</html>
<?php
}
