<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/task_packet_scan.php';

use PHPUnit\Framework\TestCase;

final class TaskPacketScanTest extends TestCase
{
    public function testBuildsDeterministicAdvisoryScanFromRootPointer(): void
    {
        $source = '{"article":{"title":"Hello","tags":["php","ai"],"author":{"name":"Ada"}},"ignored":true}';
        $first = app_task_packet_scan_json($source, '/article');
        $second = app_task_packet_scan_json($source, '/article');

        self::assertSame($first, $second);
        $scan = json_decode($first, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(APP_TASK_PACKET_SCAN_VERSION, $scan['scan_version']);
        self::assertSame('advisory', $scan['authority']);
        self::assertSame(hash('sha256', $source), $scan['source_sha256']);
        self::assertSame('/article', $scan['root_pointer']);
        self::assertFalse($scan['mutation_performed']);
        self::assertSame([], $scan['inference']);
        self::assertSame('/article', $scan['items'][0]['pointer']);
        self::assertSame('object', $scan['items'][0]['json_type']);
        self::assertSame(['title', 'tags', 'author'], $scan['items'][0]['keys']);
        self::assertContains('/article/tags/1', array_column($scan['items'], 'pointer'));
    }

    public function testRootCanBeWholeDocumentOrEscapedPointer(): void
    {
        $source = '{"a/b":{"tilde~key":3},"items":[{"name":"first"}]}';
        $whole = json_decode(app_task_packet_scan_json($source), true, 512, JSON_THROW_ON_ERROR);
        $escaped = json_decode(app_task_packet_scan_json($source, '/a~1b/tilde~0key'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('', $whole['root_pointer']);
        self::assertSame('object', $whole['items'][0]['json_type']);
        self::assertSame('/a~1b/tilde~0key', $escaped['root_pointer']);
        self::assertSame('integer', $escaped['items'][0]['json_type']);
    }

    public function testRejectsInvalidJsonAndMissingRootPointer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid_scan_source_json');
        app_task_packet_scan_json('{');
    }

    public function testRejectsMissingRootPointer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('scan_root_pointer_not_found');
        app_task_packet_scan_json('{"article":{}}', '/missing');
    }
}
