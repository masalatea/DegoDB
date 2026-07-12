<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sso_app_user_runtime.php';

final class SsoAppUserRuntimeTest extends TestCase
{
    public function testJitCreatesRestoresRefreshesAndOwnsApplicationData(): void
    {
        $pdo = $this->sqlite();
        $policy = $this->jitPolicy();
        $principal = $this->principal('first@example.test');

        $created = app_sso_app_user_resolve_verified_principal($pdo, $principal, $policy);
        self::assertTrue($created['ok']);
        self::assertSame('created', $created['status']);
        self::assertTrue($created['created']);
        self::assertStringStartsWith('usr_', $created['app_user_id']);

        $changedPrincipal = $this->principal('changed@example.test');
        $changedPrincipal['display_name'] = 'Changed Name';
        $changedPrincipal['access_token'] = 'must-not-persist';
        $changedPrincipal['raw_claims'] = ['secret' => 'must-not-persist'];
        $restored = app_sso_app_user_resolve_verified_principal($pdo, $changedPrincipal, $policy);

        self::assertTrue($restored['ok']);
        self::assertSame('restored', $restored['status']);
        self::assertFalse($restored['created']);
        self::assertSame($created['app_user_id'], $restored['app_user_id']);
        self::assertSame('changed@example.test', $restored['profile']['email']);

        $profileJson = (string) $pdo->query('SELECT profile_json FROM app_user_profile')->fetchColumn();
        self::assertStringNotContainsString('must-not-persist', $profileJson);
        self::assertStringNotContainsString('access_token', $profileJson);
        self::assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM app_user')->fetchColumn());
        self::assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM app_user_external_identity')->fetchColumn());

        $pdo->exec(
            'CREATE TABLE saved_item (
                saved_item_id INTEGER PRIMARY KEY AUTOINCREMENT,
                app_user_id TEXT NOT NULL,
                title TEXT NOT NULL,
                FOREIGN KEY (app_user_id) REFERENCES app_user (app_user_id)
            )',
        );
        $save = $pdo->prepare('INSERT INTO saved_item (app_user_id, title) VALUES (:app_user_id, :title)');
        $save->execute([':app_user_id' => $restored['app_user_id'], ':title' => 'SSO-owned item']);
        self::assertSame($restored['app_user_id'], $pdo->query('SELECT app_user_id FROM saved_item')->fetchColumn());
    }

    public function testSameEmailNeverLinksDifferentExternalIdentity(): void
    {
        $pdo = $this->sqlite();
        $first = app_sso_app_user_resolve_verified_principal($pdo, $this->principal('same@example.test', 'subject-1'), $this->jitPolicy());
        $second = app_sso_app_user_resolve_verified_principal($pdo, $this->principal('same@example.test', 'subject-2'), $this->jitPolicy());

        self::assertTrue($first['ok']);
        self::assertTrue($second['ok']);
        self::assertNotSame($first['app_user_id'], $second['app_user_id']);
        self::assertSame(2, (int) $pdo->query('SELECT COUNT(*) FROM app_user')->fetchColumn());
    }

    public function testInvitationOnlyUnknownIdentityFailsClosed(): void
    {
        $pdo = $this->sqlite();
        $result = app_sso_app_user_resolve_verified_principal($pdo, $this->principal('invited@example.test'), [
            'provider_key' => 'primary-oidc',
            'provisioning_mode' => 'invitation-only',
            'sso_profile_fields' => ['display_name', 'email'],
        ]);

        self::assertFalse($result['ok']);
        self::assertSame('enrollment_required', $result['status']);
        self::assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM app_user')->fetchColumn());
    }

    public function testRequiredIdentityFailureRollsBackCreatedUser(): void
    {
        $pdo = $this->sqlite();
        app_sso_app_user_apply_sqlite_schema($pdo);
        $pdo->exec(
            "CREATE TRIGGER fail_identity_insert
             BEFORE INSERT ON app_user_external_identity
             BEGIN SELECT RAISE(ABORT, 'required identity failure'); END",
        );

        $result = app_sso_app_user_resolve_verified_principal($pdo, $this->principal('rollback@example.test'), $this->jitPolicy());

        self::assertFalse($result['ok']);
        self::assertSame('persistence_failed', $result['status']);
        self::assertStringContainsString('required identity failure', $result['error']);
        self::assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM app_user')->fetchColumn());
        self::assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM app_user_external_identity')->fetchColumn());
    }

    private function sqlite(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        return $pdo;
    }

    /** @return array<string,mixed> */
    private function principal(string $email, string $subject = 'subject-1'): array
    {
        return [
            'issuer' => 'https://idp.example.test/',
            'subject' => $subject,
            'display_name' => 'Example User',
            'email' => $email,
        ];
    }

    /** @return array<string,mixed> */
    private function jitPolicy(): array
    {
        return [
            'provider_key' => 'primary-oidc',
            'provisioning_mode' => 'jit',
            'sso_profile_fields' => ['display_name', 'email'],
        ];
    }
}
