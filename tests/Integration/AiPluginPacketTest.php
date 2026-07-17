<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/ai_plugin_packet.php';
require_once dirname(__DIR__, 2) . '/mtool/scripts/validate_ai_plugin_packet.php';

final class AiPluginPacketTest extends TestCase
{
    public function testValidatesGameContentPluginPackageExample(): void
    {
        $result = app_ai_plugin_packet_validate(
            $this->json('plugin.json'),
            $this->json('packets/ai-game-content-task.template.json'),
            $this->json('examples/minimal-rpg/candidate.json'),
        );

        self::assertTrue($result['ok'], implode(', ', $result['errors']));
        self::assertSame('app_ai_plugin_packet_validate', $result['validator']);
        self::assertSame('domain.game-content', $result['plugin_id']);
        self::assertSame('minimal-rpg-candidate', $result['candidate_id']);
        self::assertFalse($result['mutation_performed']);
    }

    public function testRejectsRuntimeBoundaryAndReferenceDrift(): void
    {
        $plugin = $this->json('plugin.json');
        $task = $this->json('packets/ai-game-content-task.template.json');
        $candidate = $this->json('examples/minimal-rpg/candidate.json');
        $plugin['runtime_execution'] = true;
        $task['prohibited_actions'] = [];
        $candidate['scenes'][0]['transitions'][0]['target_scene_id'] = 'missing_scene';
        $candidate['maps'][0]['areas'][0]['scene_id'] = 'missing_scene';
        $candidate['runtime_handoff']['owner'] = 'mtool';
        $candidate['runtime_handoff']['forbidden_without_explicit_artifact'] = [];

        $result = app_ai_plugin_packet_validate($plugin, $task, $candidate);

        self::assertFalse($result['ok']);
        self::assertContains('plugin_runtime_execution_must_be_false', $result['errors']);
        self::assertContains('task_missing_prohibited_action:runtime execution', $result['errors']);
        self::assertContains('scene_transition_target:forest_entry:missing_scene', $result['errors']);
        self::assertContains('map_area_scene_id:forest_shrine:entry_path', $result['errors']);
        self::assertContains('runtime_handoff_owner', $result['errors']);
        self::assertContains('runtime_handoff_forbidden_without_explicit_artifact', $result['errors']);
    }

    public function testCliParserAndValidationFromFiles(): void
    {
        $parsed = app_cli_ai_plugin_packet_parse_args([
            'validate_ai_plugin_packet.php',
            '--plugin=' . $this->root() . '/plugin.json',
            '--task=' . $this->root() . '/packets/ai-game-content-task.template.json',
            '--candidate=' . $this->root() . '/examples/minimal-rpg/candidate.json',
        ]);

        self::assertTrue($parsed['ok'], $parsed['error']);
        self::assertFalse($parsed['help']);

        $result = app_cli_ai_plugin_packet_validate_from_parsed($parsed);
        self::assertTrue($result['ok'], implode(', ', $result['errors']));
    }

    public function testValidatesGameAudioPluginPackageExample(): void
    {
        $result = app_ai_plugin_packet_validate(
            $this->jsonFrom('domain.game-audio', 'plugin.json'),
            $this->jsonFrom('domain.game-audio', 'packets/ai-game-audio-task.template.json'),
            $this->jsonFrom('domain.game-audio', 'examples/minimal-rpg-audio/candidate.json'),
        );

        self::assertTrue($result['ok'], implode(', ', $result['errors']));
        self::assertSame('domain.game-audio', $result['plugin_id']);
        self::assertSame('minimal-rpg-audio-candidate', $result['candidate_id']);
    }

    public function testRejectsGameAudioCueAndBoundaryDrift(): void
    {
        $plugin = $this->jsonFrom('domain.game-audio', 'plugin.json');
        $task = $this->jsonFrom('domain.game-audio', 'packets/ai-game-audio-task.template.json');
        $candidate = $this->jsonFrom('domain.game-audio', 'examples/minimal-rpg-audio/candidate.json');
        $plugin['validation']['implemented'] = false;
        $task['validation']['implemented'] = false;
        $task['prohibited_actions'] = [];
        $candidate['trigger_map'][0]['cue_id'] = 'missing_cue';
        $candidate['runtime_handoff']['owner'] = 'mtool';
        $candidate['runtime_handoff']['forbidden_without_explicit_artifact'] = [];

        $result = app_ai_plugin_packet_validate($plugin, $task, $candidate);

        self::assertFalse($result['ok']);
        self::assertContains('plugin_validation_implemented', $result['errors']);
        self::assertContains('task_validation_implemented', $result['errors']);
        self::assertContains('task_missing_prohibited_action:audio asset generation', $result['errors']);
        self::assertContains('trigger_cue_id:trigger_forest_entry_music:missing_cue', $result['errors']);
        self::assertContains('runtime_handoff_owner', $result['errors']);
        self::assertContains('runtime_handoff_forbidden_without_explicit_artifact', $result['errors']);
    }

    /** @return array<string,mixed> */
    private function json(string $relativePath): array
    {
        return json_decode((string) file_get_contents($this->root() . '/' . $relativePath), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @return array<string,mixed> */
    private function jsonFrom(string $pluginId, string $relativePath): array
    {
        return json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/mtool/plugins/ai/' . $pluginId . '/' . $relativePath), true, 512, JSON_THROW_ON_ERROR);
    }

    private function root(): string
    {
        return dirname(__DIR__, 2) . '/mtool/plugins/ai/domain.game-content';
    }
}
