#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/ai_plugin_packet.php';

function app_cli_ai_plugin_packet_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/validate_ai_plugin_packet.php --plugin=mtool/plugins/ai/domain.game-content/plugin.json --task=<task.json> --candidate=<candidate.json>

Boundary:
  Validates an AI-facing plugin packet only.
  Does not execute runtime code, install dependencies, mutate metadata, build, publish, or deploy.
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{ok:bool,help:bool,plugin:string,task:string,candidate:string,error:string}
 */
function app_cli_ai_plugin_packet_parse_args(array $argv): array
{
    $parsed = ['plugin' => '', 'task' => '', 'candidate' => ''];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return ['ok' => true, 'help' => true, 'plugin' => '', 'task' => '', 'candidate' => '', 'error' => ''];
        }
        foreach (['plugin', 'task', 'candidate'] as $key) {
            $prefix = '--' . $key . '=';
            if (str_starts_with($argument, $prefix)) {
                $parsed[$key] = trim(substr($argument, strlen($prefix)));
                continue 2;
            }
        }
        return ['ok' => false, 'help' => false] + $parsed + ['error' => 'unsupported argument: ' . $argument];
    }
    foreach (['plugin', 'task', 'candidate'] as $key) {
        if ($parsed[$key] === '' || !is_file($parsed[$key])) {
            return ['ok' => false, 'help' => false] + $parsed + ['error' => 'valid --' . $key . ' file is required'];
        }
    }
    return ['ok' => true, 'help' => false] + $parsed + ['error' => ''];
}

/**
 * @param array{plugin:string,task:string,candidate:string} $parsed
 * @return array<string,mixed>
 */
function app_cli_ai_plugin_packet_validate_from_parsed(array $parsed): array
{
    try {
        $plugin = json_decode((string) file_get_contents($parsed['plugin']), true, 512, JSON_THROW_ON_ERROR);
        $task = json_decode((string) file_get_contents($parsed['task']), true, 512, JSON_THROW_ON_ERROR);
        $candidate = json_decode((string) file_get_contents($parsed['candidate']), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return [
            'ok' => false,
            'validator' => 'app_ai_plugin_packet_validate',
            'schema_version' => 'ai_plugin_packet_validation.v1',
            'plugin_id' => '',
            'candidate_id' => '',
            'errors' => ['invalid_json:' . $exception->getMessage()],
            'mutation_performed' => false,
        ];
    }
    if (!is_array($plugin) || array_is_list($plugin) || !is_array($task) || array_is_list($task) || !is_array($candidate) || array_is_list($candidate)) {
        return [
            'ok' => false,
            'validator' => 'app_ai_plugin_packet_validate',
            'schema_version' => 'ai_plugin_packet_validation.v1',
            'plugin_id' => '',
            'candidate_id' => '',
            'errors' => ['json_roots_must_be_objects'],
            'mutation_performed' => false,
        ];
    }
    return app_ai_plugin_packet_validate($plugin, $task, $candidate);
}

/** @param list<string> $argv */
function app_cli_ai_plugin_packet_main(array $argv): int
{
    $parsed = app_cli_ai_plugin_packet_parse_args($argv);
    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_ai_plugin_packet_usage() . PHP_EOL);
        return 0;
    }
    if (!$parsed['ok']) {
        fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_ai_plugin_packet_usage() . PHP_EOL);
        return 64;
    }

    $result = app_cli_ai_plugin_packet_validate_from_parsed($parsed);
    fwrite($result['ok'] ? STDOUT : STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
    return $result['ok'] ? 0 : 2;
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(app_cli_ai_plugin_packet_main($argv));
}
