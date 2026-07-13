<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/task_packet_ollama_adapter.php';

use PHPUnit\Framework\TestCase;

final class TaskPacketOllamaAdapterTest extends TestCase
{
    public function testDefaultConfigIsLocalOnlyAndCredentialFree(): void
    {
        $result = app_task_packet_ollama_config_normalize();

        self::assertTrue($result['ok'], implode(',', $result['errors']));
        self::assertSame('config_ready', $result['stage']);
        self::assertSame(APP_TASK_PACKET_OLLAMA_ADAPTER_VERSION, $result['config']['adapter_version']);
        self::assertSame(APP_TASK_PACKET_OLLAMA_DEFAULT_ENDPOINT, $result['config']['endpoint']);
        self::assertSame(APP_TASK_PACKET_OLLAMA_DEFAULT_MODEL, $result['config']['model']);
        self::assertTrue($result['config']['local_only']);
        self::assertFalse($result['config']['credential_required']);
    }

    public function testRejectsRemoteEndpointAndCredentialFields(): void
    {
        $result = app_task_packet_ollama_config_normalize([
            'endpoint' => 'https://api.example.com/generate',
            'model' => 'example',
            'api_key' => 'secret',
        ]);

        self::assertFalse($result['ok']);
        self::assertSame('config_validation', $result['stage']);
        self::assertContains('endpoint_must_use_http', $result['errors']);
        self::assertContains('endpoint_must_be_local', $result['errors']);
        self::assertContains('credential_not_supported:api_key', $result['errors']);
    }

    public function testBuildsOllamaPayloadThroughFakeTransportWithoutNetwork(): void
    {
        $captured = [];
        $result = app_task_packet_ollama_generate_candidate(
            'Return JSON.',
            [
                'endpoint' => 'http://localhost:11434/api/generate',
                'model' => 'local-model',
                'timeout_seconds' => '12',
                'num_ctx' => '4096',
                'temperature' => '0.25',
            ],
            static function (string $endpoint, string $payload, int $timeoutSeconds) use (&$captured): string {
                $captured = [
                    'endpoint' => $endpoint,
                    'payload' => json_decode($payload, true, 512, JSON_THROW_ON_ERROR),
                    'timeout_seconds' => $timeoutSeconds,
                ];
                return json_encode(['response' => '{"ok":true}'], JSON_THROW_ON_ERROR);
            },
        );

        self::assertTrue($result['ok'], implode(',', $result['errors']));
        self::assertSame('candidate_ready', $result['stage']);
        self::assertSame('ollama-local', $result['provider']);
        self::assertSame('local-model', $result['model']);
        self::assertSame('{"ok":true}', $result['candidate_json']);
        self::assertSame('http://localhost:11434/api/generate', $captured['endpoint']);
        self::assertSame(12, $captured['timeout_seconds']);
        self::assertSame('local-model', $captured['payload']['model']);
        self::assertSame('Return JSON.', $captured['payload']['prompt']);
        self::assertSame('json', $captured['payload']['format']);
        self::assertFalse($captured['payload']['stream']);
        self::assertSame(4096, $captured['payload']['options']['num_ctx']);
        self::assertSame(0.25, $captured['payload']['options']['temperature']);
        self::assertFalse($result['mutation_performed']);
    }

    public function testFailsClosedOnEmptyProviderResponse(): void
    {
        $result = app_task_packet_ollama_generate_candidate(
            'Return JSON.',
            [],
            static fn (): string => json_encode(['response' => ''], JSON_THROW_ON_ERROR),
        );

        self::assertFalse($result['ok']);
        self::assertSame('provider_response', $result['stage']);
        self::assertSame(['ollama_empty_response'], $result['errors']);
        self::assertSame('', $result['candidate_json']);
        self::assertFalse($result['mutation_performed']);
    }
}
