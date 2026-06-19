<?php

declare(strict_types=1);

require_once __DIR__ . '/runtime_dbclasses/autoload_proxy_runtime.php';
require_once __DIR__ . '/' . 'single_proxy_runtime.php';

function mtool_generated_single_proxy_bundle_root(string $runtimeSourceRoot): string
{
    return dirname($runtimeSourceRoot, 4);
}

function mtool_generated_single_proxy_custom_layer_root(string $runtimeSourceRoot): string
{
    return mtool_generated_single_proxy_bundle_root($runtimeSourceRoot) . '/' . 'mtool/extensions/SAMPLE26/AUTH-PROXY-SERVER';
}

function mtool_generated_single_proxy_load_custom_bootstrap(string $runtimeSourceRoot): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }

    $loaded = true;
    $bootstrapPath = mtool_generated_single_proxy_custom_layer_root($runtimeSourceRoot) . '/bootstrap.php';
    if (is_file($bootstrapPath)) {
        require_once $bootstrapPath;
    }
}

function mtool_generated_single_proxy_run(
    string $runtimeSourceRoot,
    string $handlerRelativePath,
    string $baseClassName,
    string $wrapperClassName,
): void {
    $basePath = $runtimeSourceRoot . '/_base/' . $handlerRelativePath;
    $defaultWrapperPath = $runtimeSourceRoot . '/_wrappers/' . $handlerRelativePath;
    $customWrapperPath = mtool_generated_single_proxy_custom_layer_root($runtimeSourceRoot) . '/' . $handlerRelativePath;

    if (!is_file($basePath)) {
        throw new RuntimeException('Missing base handler: ' . $handlerRelativePath);
    }

    require_once $basePath;
    mtool_generated_single_proxy_load_custom_bootstrap($runtimeSourceRoot);

    if (is_file($customWrapperPath)) {
        require_once $customWrapperPath;
    } else {
        if (!is_file($defaultWrapperPath)) {
            throw new RuntimeException('Missing wrapper handler: ' . $handlerRelativePath);
        }
        require_once $defaultWrapperPath;
    }

    if (!class_exists($wrapperClassName)) {
        throw new RuntimeException('Wrapper class が見つかりません: ' . $wrapperClassName);
    }

    $handler = new $wrapperClassName();
    if (!($handler instanceof $baseClassName)) {
        throw new RuntimeException('Wrapper class が base handler を継承していません: ' . $wrapperClassName);
    }

    $handler->handle();
}
