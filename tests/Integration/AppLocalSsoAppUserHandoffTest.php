<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/app_local_user_identity.php';

final class AppLocalSsoAppUserHandoffTest extends TestCase
{
    public function testServerResolvedAppUserIdPersistsAndBecomesCorrelationOnlyActorMetadata(): void
    {
        $base = app_local_user_identity_from_principal([
            'auth_source' => 'oidc',
            'issuer' => 'https://idp.example.test',
            'subject' => 'subject-1',
            'display_name' => 'User',
        ], ['device_id' => 'device-1']);
        self::assertTrue($base['ok'], $base['error']);
        $handoff = app_local_user_identity_with_server_resolved_app_user($base['identity'], [
            'ok' => true,
            'app_user_id' => 'usr_server_owned',
            'actor' => ['app_user_id' => 'usr_server_owned'],
            'access_token' => 'must-not-persist',
        ], ['bound_at' => '2026-07-13T12:00:00Z']);
        self::assertTrue($handoff['ok'], $handoff['error']);

        $pdo = new PDO('sqlite::memory:');
        $saved = app_local_user_identity_save($pdo, $handoff['identity']);
        self::assertTrue($saved['ok'], $saved['error']);
        $restored = app_local_user_identity_restore($pdo, $saved['local_user_id']);
        self::assertSame('usr_server_owned', $restored['identity']['app_user_id']);
        self::assertSame('server-resolved-v1', $restored['identity']['app_user_id_source']);
        self::assertArrayNotHasKey('access_token', $restored['identity']);

        $actor = app_local_user_identity_actor_snapshot($restored['identity']);
        self::assertSame('usr_server_owned', $actor['app_user_id']);
        self::assertSame('correlation-only', $actor['app_user_id_authority']);
        self::assertTrue($actor['server_revalidation_required']);

        $revalidated = app_local_user_identity_server_revalidate_sync_actor($actor, ['ok' => true, 'actor' => ['app_user_id' => 'usr_server_owned']]);
        self::assertTrue($revalidated['ok'], $revalidated['error']);
        self::assertSame('usr_server_owned', $revalidated['app_user_id']);
    }

    public function testClientClaimOrInconsistentServerResultCannotBindAppUserId(): void
    {
        $identity = ['local_user_id' => 'local-1', 'app_user_id' => 'client-claimed'];
        $failedResult = app_local_user_identity_with_server_resolved_app_user($identity, ['ok' => false, 'actor' => ['app_user_id' => 'client-claimed']]);
        self::assertFalse($failedResult['ok']);

        $inconsistent = app_local_user_identity_with_server_resolved_app_user($identity, ['ok' => true, 'app_user_id' => 'usr_a', 'actor' => ['app_user_id' => 'usr_b']]);
        self::assertFalse($inconsistent['ok']);

        $untrustedActor = app_local_user_identity_actor_snapshot($identity);
        self::assertArrayNotHasKey('app_user_id', $untrustedActor);

        $stale = app_local_user_identity_server_revalidate_sync_actor(
            ['app_user_id' => 'usr_stale', 'app_user_id_authority' => 'correlation-only'],
            ['ok' => true, 'actor' => ['app_user_id' => 'usr_current']],
        );
        self::assertFalse($stale['ok']);
    }
}
