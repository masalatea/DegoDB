<?php

declare(strict_types=1);

const APP_TASK_PACKET_SCAN_VERSION = 'mtool-deterministic-source-scan-v1';

function app_task_packet_scan_json(string $sourceBytes, string $rootPointer = ''): string
{
    try {
        $source = json_decode($sourceBytes, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new InvalidArgumentException('invalid_scan_source_json', 0, $exception);
    }

    $rootPointer = app_task_packet_scan_normalize_pointer($rootPointer);
    $rootValue = app_task_packet_scan_pointer_value($source, $rootPointer);
    $items = [];
    app_task_packet_scan_value($rootValue, $rootPointer, $items);

    return json_encode([
        'scan_version' => APP_TASK_PACKET_SCAN_VERSION,
        'authority' => 'advisory',
        'source_sha256' => hash('sha256', $sourceBytes),
        'root_pointer' => $rootPointer,
        'items' => $items,
        'inference' => [],
        'mutation_performed' => false,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
}

function app_task_packet_scan_normalize_pointer(string $pointer): string
{
    $pointer = trim($pointer);
    if ($pointer === '') return '';
    if (!str_starts_with($pointer, '/')) throw new InvalidArgumentException('invalid_scan_root_pointer');
    return $pointer;
}

function app_task_packet_scan_pointer_value(mixed $source, string $pointer): mixed
{
    if ($pointer === '') return $source;
    $current = $source;
    foreach (explode('/', substr($pointer, 1)) as $rawSegment) {
        $segment = str_replace(['~1', '~0'], ['/', '~'], $rawSegment);
        if (is_array($current) && array_key_exists($segment, $current)) {
            $current = $current[$segment];
            continue;
        }
        if (is_array($current) && array_is_list($current) && ctype_digit($segment) && array_key_exists((int) $segment, $current)) {
            $current = $current[(int) $segment];
            continue;
        }
        throw new InvalidArgumentException('scan_root_pointer_not_found');
    }
    return $current;
}

/** @param list<array<string,mixed>> $items */
function app_task_packet_scan_value(mixed $value, string $pointer, array &$items): void
{
    $type = match (true) {
        is_array($value) && array_is_list($value) => 'array',
        is_array($value) => 'object',
        is_string($value) => 'string',
        is_int($value) => 'integer',
        is_float($value) => 'number',
        is_bool($value) => 'boolean',
        $value === null => 'null',
        default => 'unknown',
    };
    $item = ['pointer' => $pointer, 'json_type' => $type];
    if (is_array($value)) {
        $item[$type === 'array' ? 'item_count' : 'keys'] = $type === 'array' ? count($value) : array_values(array_map('strval', array_keys($value)));
    }
    $items[] = $item;
    if (!is_array($value)) return;
    foreach ($value as $key => $child) {
        app_task_packet_scan_value($child, $pointer . '/' . app_task_packet_scan_escape_pointer_segment((string) $key), $items);
    }
}

function app_task_packet_scan_escape_pointer_segment(string $segment): string
{
    return str_replace(['~', '/'], ['~0', '~1'], $segment);
}
