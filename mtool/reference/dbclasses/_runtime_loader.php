<?php

function mtool_runtime_bundle_custom_layer_root(): string
{
    return dirname(__DIR__, 2) . '/' . 'mtool/extensions/MTOOL/RUNTIME-DBCLASSES';
}

function mtool_runtime_bundle_load_custom_bootstrap(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }

    $loaded = true;
    $bootstrapPath = mtool_runtime_bundle_custom_layer_root() . '/bootstrap.php';
    if (is_file($bootstrapPath)) {
        require_once $bootstrapPath;
    }
}

function mtool_runtime_bundle_custom_wrapper_path(string $relativePath): string
{
    return mtool_runtime_bundle_custom_layer_root() . '/' . $relativePath;
}

function mtool_runtime_bundle_load_custom_wrapper(string $relativePath): bool
{
    mtool_runtime_bundle_load_custom_bootstrap();

    $customWrapperPath = mtool_runtime_bundle_custom_wrapper_path($relativePath);
    if (!is_file($customWrapperPath)) {
        return false;
    }

    require_once $customWrapperPath;

    return true;
}

function mtool_runtime_bundle_load_layered_file(string $relativePath): void
{
    $runtimeRoot = __DIR__;
    $basePath = $runtimeRoot . '/_base/' . $relativePath;
    $defaultWrapperPath = $runtimeRoot . '/_wrappers/' . $relativePath;

    if (!is_file($basePath)) {
        throw new RuntimeException('Missing runtime base file: ' . $relativePath);
    }

    require_once $basePath;
    if (mtool_runtime_bundle_load_custom_wrapper($relativePath)) {
        return;
    }

    if (!is_file($defaultWrapperPath)) {
        throw new RuntimeException('Missing runtime wrapper file: ' . $relativePath);
    }

    require_once $defaultWrapperPath;
}

?>
