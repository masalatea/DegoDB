#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/validate_ai_plugin_packet.php';

function app_cli_ai_game_audio_packet_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/validate_ai_game_audio_packet.php --plugin=mtool/plugins/ai/domain.game-audio/plugin.json --task=<task.json> --candidate=<candidate.json>

Boundary:
  Validates the game-audio AI-facing plugin packet only.
  Does not generate audio, choose licenses, execute playback code, install dependencies, build, publish, or deploy.
TEXT;
}

/** @param list<string> $argv */
function app_cli_ai_game_audio_packet_main(array $argv): int
{
    $parsed = app_cli_ai_plugin_packet_parse_args($argv);
    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_ai_game_audio_packet_usage() . PHP_EOL);
        return 0;
    }
    if (!$parsed['ok']) {
        fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_ai_game_audio_packet_usage() . PHP_EOL);
        return 64;
    }

    $result = app_cli_ai_plugin_packet_validate_from_parsed($parsed);
    if (($result['plugin_id'] ?? '') !== APP_GAME_AUDIO_PLUGIN_ID) {
        $result['ok'] = false;
        $result['errors'][] = 'expected_game_audio_plugin';
    }
    fwrite($result['ok'] ? STDOUT : STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
    return $result['ok'] ? 0 : 2;
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(app_cli_ai_game_audio_packet_main($argv));
}
