<?php

declare(strict_types=1);

const APP_AI_PLUGIN_SCHEMA_VERSION = 'mtool-ai-plugin-v1';
const APP_AI_PLUGIN_TASK_SCHEMA_VERSION = 'mtool_ai_plugin_task.v1';
const APP_GAME_CONTENT_PLUGIN_ID = 'domain.game-content';
const APP_GAME_CONTENT_CANDIDATE_SCHEMA_VERSION = 'game_content_ai_candidate.v1';
const APP_GAME_AUDIO_PLUGIN_ID = 'domain.game-audio';
const APP_GAME_AUDIO_CANDIDATE_SCHEMA_VERSION = 'game_audio_ai_candidate.v1';

/**
 * @param array<string,mixed> $plugin
 * @param array<string,mixed> $task
 * @param array<string,mixed> $candidate
 * @return array<string,mixed>
 */
function app_ai_plugin_packet_validate(array $plugin, array $task, array $candidate): array
{
    $pluginId = (string) ($plugin['id'] ?? '');
    $errors = array_merge(
        app_ai_plugin_manifest_errors($plugin),
        app_ai_plugin_task_errors($task),
        app_ai_plugin_candidate_errors($pluginId, $candidate),
    );

    if (($plugin['id'] ?? null) !== ($task['plugin_id'] ?? null)) {
        $errors[] = 'plugin_task_id_mismatch';
    }
    if (($plugin['id'] ?? null) !== ($candidate['plugin_id'] ?? null)) {
        $errors[] = 'plugin_candidate_id_mismatch';
    }
    if (($task['candidate_schema_version'] ?? null) !== ($candidate['schema_version'] ?? null)) {
        $errors[] = 'task_candidate_schema_version_mismatch';
    }

    return [
        'ok' => $errors === [],
        'validator' => 'app_ai_plugin_packet_validate',
        'schema_version' => 'ai_plugin_packet_validation.v1',
        'plugin_id' => (string) ($plugin['id'] ?? ''),
        'candidate_id' => (string) ($candidate['candidate_id'] ?? ''),
        'errors' => array_values(array_unique($errors)),
        'mutation_performed' => false,
    ];
}

/**
 * @param array<string,mixed> $plugin
 * @return list<string>
 */
function app_ai_plugin_manifest_errors(array $plugin): array
{
    $errors = [];
    $pluginId = (string) ($plugin['id'] ?? '');
    if (($plugin['schema_version'] ?? '') !== APP_AI_PLUGIN_SCHEMA_VERSION) {
        $errors[] = 'plugin_schema_version';
    }
    if (!in_array($pluginId, [APP_GAME_CONTENT_PLUGIN_ID, APP_GAME_AUDIO_PLUGIN_ID], true)) {
        $errors[] = 'plugin_id';
    }
    if (($plugin['kind'] ?? '') !== 'ai_facing') {
        $errors[] = 'plugin_kind';
    }
    if (($plugin['runtime_execution'] ?? true) !== false) {
        $errors[] = 'plugin_runtime_execution_must_be_false';
    }
    if (($plugin['generator_hooks'] ?? true) !== false) {
        $errors[] = 'plugin_generator_hooks_must_be_false';
    }
    foreach (['task_packet', 'schema', 'example', 'validator_contract', 'handoff'] as $interface) {
        if (!in_array($interface, app_ai_plugin_string_list($plugin['interfaces'] ?? null), true)) {
            $errors[] = 'plugin_missing_interface:' . $interface;
        }
    }
    if (($plugin['validation']['required'] ?? false) !== true) {
        $errors[] = 'plugin_validation_required';
    }
    if (($plugin['validation']['implemented'] ?? false) !== true) {
        $errors[] = 'plugin_validation_implemented';
    }
    foreach (app_ai_plugin_required_non_goals($pluginId) as $nonGoal) {
        if (!in_array($nonGoal, app_ai_plugin_string_list($plugin['non_goals'] ?? null), true)) {
            $errors[] = 'plugin_missing_non_goal:' . $nonGoal;
        }
    }
    return $errors;
}

/**
 * @param array<string,mixed> $task
 * @return list<string>
 */
function app_ai_plugin_task_errors(array $task): array
{
    $errors = [];
    $pluginId = (string) ($task['plugin_id'] ?? '');
    if (($task['schema_version'] ?? '') !== APP_AI_PLUGIN_TASK_SCHEMA_VERSION) {
        $errors[] = 'task_schema_version';
    }
    if (!in_array($pluginId, [APP_GAME_CONTENT_PLUGIN_ID, APP_GAME_AUDIO_PLUGIN_ID], true)) {
        $errors[] = 'task_plugin_id';
    }
    if (($task['task_kind'] ?? '') !== app_ai_plugin_task_kind($pluginId)) {
        $errors[] = 'task_kind';
    }
    if (($task['authority']['task_json_wins_over_task_md'] ?? false) !== true) {
        $errors[] = 'task_json_authority';
    }
    if (($task['candidate_schema_version'] ?? '') !== app_ai_plugin_candidate_schema_version($pluginId)) {
        $errors[] = 'task_candidate_schema_version';
    }
    if (($task['confirmation_required'] ?? false) !== true) {
        $errors[] = 'task_confirmation_required';
    }
    if (($task['validation']['implemented'] ?? false) !== true) {
        $errors[] = 'task_validation_implemented';
    }
    if (!in_array('output/candidate.json', app_ai_plugin_string_list($task['allowed_writes'] ?? null), true)) {
        $errors[] = 'task_allowed_write_candidate';
    }
    foreach (app_ai_plugin_required_prohibited_actions($pluginId) as $action) {
        if (!in_array($action, app_ai_plugin_string_list($task['prohibited_actions'] ?? null), true)) {
            $errors[] = 'task_missing_prohibited_action:' . $action;
        }
    }
    return $errors;
}

/**
 * @param array<string,mixed> $candidate
 * @return list<string>
 */
function app_ai_plugin_candidate_errors(string $pluginId, array $candidate): array
{
    if ($pluginId === APP_GAME_CONTENT_PLUGIN_ID) {
        return app_game_content_candidate_errors($candidate);
    }
    if ($pluginId === APP_GAME_AUDIO_PLUGIN_ID) {
        return app_game_audio_candidate_errors($candidate);
    }
    return ['candidate_unsupported_plugin'];
}

/**
 * @param array<string,mixed> $candidate
 * @return list<string>
 */
function app_game_content_candidate_errors(array $candidate): array
{
    $errors = [];
    if (($candidate['schema_version'] ?? '') !== APP_GAME_CONTENT_CANDIDATE_SCHEMA_VERSION) {
        $errors[] = 'candidate_schema_version';
    }
    if (($candidate['plugin_id'] ?? '') !== APP_GAME_CONTENT_PLUGIN_ID) {
        $errors[] = 'candidate_plugin_id';
    }
    foreach (['candidate_id', 'game', 'scenarios', 'maps', 'scenes', 'characters', 'runtime_handoff', 'validation_notes', 'non_goals'] as $field) {
        if (!array_key_exists($field, $candidate)) {
            $errors[] = 'candidate_missing_field:' . $field;
        }
    }

    $scenes = app_ai_plugin_list_of_objects($candidate['scenes'] ?? null);
    $maps = app_ai_plugin_list_of_objects($candidate['maps'] ?? null);
    $scenarios = app_ai_plugin_list_of_objects($candidate['scenarios'] ?? null);
    $characters = app_ai_plugin_list_of_objects($candidate['characters'] ?? null);
    if ($scenes === []) $errors[] = 'candidate_scenes_required';
    if ($maps === []) $errors[] = 'candidate_maps_required';
    if ($scenarios === []) $errors[] = 'candidate_scenarios_required';
    if ($characters === []) $errors[] = 'candidate_characters_required';

    $sceneIds = app_ai_plugin_ids($scenes, 'scene');
    $mapIds = app_ai_plugin_ids($maps, 'map');
    $characterIds = app_ai_plugin_ids($characters, 'character');
    $errors = array_merge($errors, $sceneIds['errors'], $mapIds['errors'], $characterIds['errors']);

    $game = is_array($candidate['game'] ?? null) ? $candidate['game'] : [];
    $openingSceneId = (string) ($game['opening_scene_id'] ?? '');
    if ($openingSceneId === '' || !in_array($openingSceneId, $sceneIds['ids'], true)) {
        $errors[] = 'candidate_opening_scene_id';
    }

    foreach ($scenarios as $scenario) {
        $entrySceneId = (string) ($scenario['entry_scene_id'] ?? '');
        if ($entrySceneId === '' || !in_array($entrySceneId, $sceneIds['ids'], true)) {
            $errors[] = 'scenario_entry_scene_id:' . (string) ($scenario['id'] ?? '');
        }
    }
    foreach ($maps as $map) {
        foreach (app_ai_plugin_list_of_objects($map['areas'] ?? null) as $area) {
            $sceneId = (string) ($area['scene_id'] ?? '');
            if ($sceneId === '' || !in_array($sceneId, $sceneIds['ids'], true)) {
                $errors[] = 'map_area_scene_id:' . (string) ($map['id'] ?? '') . ':' . (string) ($area['id'] ?? '');
            }
        }
    }
    foreach ($scenes as $scene) {
        $mapId = (string) ($scene['map_id'] ?? '');
        if ($mapId === '' || !in_array($mapId, $mapIds['ids'], true)) {
            $errors[] = 'scene_map_id:' . (string) ($scene['id'] ?? '');
        }
        foreach (app_ai_plugin_list_of_objects($scene['transitions'] ?? null) as $transition) {
            $targetSceneId = (string) ($transition['target_scene_id'] ?? '');
            if ($targetSceneId === '' || !in_array($targetSceneId, $sceneIds['ids'], true)) {
                $errors[] = 'scene_transition_target:' . (string) ($scene['id'] ?? '') . ':' . $targetSceneId;
            }
        }
    }

    $handoff = is_array($candidate['runtime_handoff'] ?? null) ? $candidate['runtime_handoff'] : [];
    if (($handoff['owner'] ?? '') !== 'external_game_runtime_owner') {
        $errors[] = 'runtime_handoff_owner';
    }
    foreach (['mtool_owned', 'external_runtime_owned', 'forbidden_without_explicit_artifact'] as $field) {
        if (app_ai_plugin_string_list($handoff[$field] ?? null) === []) {
            $errors[] = 'runtime_handoff_' . $field;
        }
    }
    foreach (['engine project generation', 'dependency installation', 'runtime execution', 'publish or deployment'] as $forbidden) {
        if (!in_array($forbidden, app_ai_plugin_string_list($handoff['forbidden_without_explicit_artifact'] ?? null), true)) {
            $errors[] = 'runtime_handoff_missing_forbidden:' . $forbidden;
        }
    }
    foreach (['production game server generation', 'matchmaking', 'anti-cheat', 'asset licensing'] as $nonGoal) {
        if (!in_array($nonGoal, app_ai_plugin_string_list($candidate['non_goals'] ?? null), true)) {
            $errors[] = 'candidate_missing_non_goal:' . $nonGoal;
        }
    }
    return $errors;
}

/**
 * @param array<string,mixed> $candidate
 * @return list<string>
 */
function app_game_audio_candidate_errors(array $candidate): array
{
    $errors = [];
    if (($candidate['schema_version'] ?? '') !== APP_GAME_AUDIO_CANDIDATE_SCHEMA_VERSION) {
        $errors[] = 'candidate_schema_version';
    }
    if (($candidate['plugin_id'] ?? '') !== APP_GAME_AUDIO_PLUGIN_ID) {
        $errors[] = 'candidate_plugin_id';
    }
    foreach (['candidate_id', 'audio_profile', 'music_cues', 'sfx_cues', 'trigger_map', 'runtime_handoff', 'validation_notes', 'non_goals'] as $field) {
        if (!array_key_exists($field, $candidate)) {
            $errors[] = 'candidate_missing_field:' . $field;
        }
    }

    $musicCues = app_ai_plugin_list_of_objects($candidate['music_cues'] ?? null);
    $sfxCues = app_ai_plugin_list_of_objects($candidate['sfx_cues'] ?? null);
    $triggers = app_ai_plugin_list_of_objects($candidate['trigger_map'] ?? null);
    if ($musicCues === []) $errors[] = 'candidate_music_cues_required';
    if ($sfxCues === []) $errors[] = 'candidate_sfx_cues_required';
    if ($triggers === []) $errors[] = 'candidate_trigger_map_required';

    $musicIds = app_ai_plugin_ids($musicCues, 'music_cue');
    $sfxIds = app_ai_plugin_ids($sfxCues, 'sfx_cue');
    $triggerIds = app_ai_plugin_ids($triggers, 'trigger');
    $errors = array_merge($errors, $musicIds['errors'], $sfxIds['errors'], $triggerIds['errors']);
    $cueIds = array_merge($musicIds['ids'], $sfxIds['ids']);
    foreach ($musicIds['ids'] as $id) {
        if (in_array($id, $sfxIds['ids'], true)) {
            $errors[] = 'cue_duplicate_id:' . $id;
        }
    }

    foreach ($triggers as $trigger) {
        $cueId = (string) ($trigger['cue_id'] ?? '');
        if ($cueId === '' || !in_array($cueId, $cueIds, true)) {
            $errors[] = 'trigger_cue_id:' . (string) ($trigger['id'] ?? '') . ':' . $cueId;
        }
        $kind = (string) ($trigger['kind'] ?? '');
        if (!in_array($kind, ['scene_enter_music', 'event_sfx', 'state_change_sfx'], true)) {
            $errors[] = 'trigger_kind:' . (string) ($trigger['id'] ?? '');
        }
    }

    $handoff = is_array($candidate['runtime_handoff'] ?? null) ? $candidate['runtime_handoff'] : [];
    if (($handoff['owner'] ?? '') !== 'external_audio_runtime_owner') {
        $errors[] = 'runtime_handoff_owner';
    }
    foreach (['mtool_owned', 'external_audio_runtime_owned', 'forbidden_without_explicit_artifact'] as $field) {
        if (app_ai_plugin_string_list($handoff[$field] ?? null) === []) {
            $errors[] = 'runtime_handoff_' . $field;
        }
    }
    foreach (['audio asset generation', 'asset licensing decisions', 'runtime playback implementation', 'dependency installation', 'engine project generation', 'publish or deployment'] as $forbidden) {
        if (!in_array($forbidden, app_ai_plugin_string_list($handoff['forbidden_without_explicit_artifact'] ?? null), true)) {
            $errors[] = 'runtime_handoff_missing_forbidden:' . $forbidden;
        }
    }
    foreach (['audio asset generation', 'asset licensing', 'runtime playback implementation', 'mixer implementation', 'engine project generation', 'publish or deployment'] as $nonGoal) {
        if (!in_array($nonGoal, app_ai_plugin_string_list($candidate['non_goals'] ?? null), true)) {
            $errors[] = 'candidate_missing_non_goal:' . $nonGoal;
        }
    }
    return $errors;
}

function app_ai_plugin_task_kind(string $pluginId): string
{
    if ($pluginId === APP_GAME_CONTENT_PLUGIN_ID) return 'game_content_candidate';
    if ($pluginId === APP_GAME_AUDIO_PLUGIN_ID) return 'game_audio_candidate';
    return '';
}

function app_ai_plugin_candidate_schema_version(string $pluginId): string
{
    if ($pluginId === APP_GAME_CONTENT_PLUGIN_ID) return APP_GAME_CONTENT_CANDIDATE_SCHEMA_VERSION;
    if ($pluginId === APP_GAME_AUDIO_PLUGIN_ID) return APP_GAME_AUDIO_CANDIDATE_SCHEMA_VERSION;
    return '';
}

/** @return list<string> */
function app_ai_plugin_required_non_goals(string $pluginId): array
{
    if ($pluginId === APP_GAME_AUDIO_PLUGIN_ID) {
        return ['audio asset generation', 'asset licensing decisions', 'runtime playback implementation', 'dependency installation', 'game engine project generation', 'publish or deployment'];
    }
    return ['game engine project generation', 'dependency installation', 'runtime execution', 'publish or deployment'];
}

/** @return list<string> */
function app_ai_plugin_required_prohibited_actions(string $pluginId): array
{
    $common = ['dependency installation', 'Mtool metadata mutation', 'database mutation', 'build', 'publish', 'deployment'];
    if ($pluginId === APP_GAME_AUDIO_PLUGIN_ID) {
        return array_merge(['audio asset generation', 'asset licensing decisions', 'runtime playback implementation', 'mixer implementation', 'Unity/Godot/native project generation'], $common);
    }
    return array_merge(['runtime execution', 'Unity/Godot/native project generation'], $common);
}

/**
 * @param mixed $value
 * @return list<string>
 */
function app_ai_plugin_string_list(mixed $value): array
{
    if (!is_array($value)) return [];
    $result = [];
    foreach ($value as $item) {
        if (is_string($item) && $item !== '') $result[] = $item;
    }
    return $result;
}

/**
 * @param mixed $value
 * @return list<array<string,mixed>>
 */
function app_ai_plugin_list_of_objects(mixed $value): array
{
    if (!is_array($value)) return [];
    $result = [];
    foreach ($value as $item) {
        if (is_array($item) && !array_is_list($item)) $result[] = $item;
    }
    return $result;
}

/**
 * @param list<array<string,mixed>> $items
 * @return array{ids:list<string>,errors:list<string>}
 */
function app_ai_plugin_ids(array $items, string $label): array
{
    $ids = [];
    $errors = [];
    foreach ($items as $item) {
        $id = (string) ($item['id'] ?? '');
        if ($id === '') {
            $errors[] = $label . '_missing_id';
            continue;
        }
        if (in_array($id, $ids, true)) {
            $errors[] = $label . '_duplicate_id:' . $id;
            continue;
        }
        $ids[] = $id;
    }
    return ['ids' => $ids, 'errors' => $errors];
}
