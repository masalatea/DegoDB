<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sso_app_user_generated_resolver.php';
require_once dirname(__DIR__, 2) . '/mtool/app/sso_app_user_runtime.php';

final class SsoAppUserGeneratedResolverContractTest extends TestCase
{
    public function testSchemaGateAndMissingOperationsFailWithoutArtifact(): void
    {
        $blocked = app_sso_app_user_generated_resolver_contract($this->policy(), ['status' => 'metadata_valid_constraint_gap', 'ready_for_generation' => false], []);
        self::assertFalse($blocked['ok']);
        self::assertSame('schema_not_ready', $blocked['status']);
        self::assertSame('', $blocked['artifact_text']);

        $missing = app_sso_app_user_generated_resolver_contract($this->policy(), $this->readySchema(), []);
        self::assertFalse($missing['ok']);
        self::assertSame('operation_contract_gap', $missing['status']);
        self::assertCount(6, $missing['errors']);
        self::assertSame('', $missing['artifact_text']);
    }

    public function testExactOperationsProduceDeterministicResolverContractArtifact(): void
    {
        $classes = [
            $this->dbAccess('AppUser', [
                ['SelectAppUserByAppUserId', 'SELECT'],
                ['InsertAppUser', 'INSERT'],
            ]),
            $this->dbAccess('AppUserExternalIdentity', [
                ['SelectAppUserExternalIdentityByIssuerSubject', 'SELECT'],
                ['InsertAppUserExternalIdentity', 'INSERT'],
                ['UpdateAppUserExternalIdentityLastAuthenticatedAt', 'UPDATE'],
            ]),
            $this->dbAccess('AppUserProfile', [['UpsertAppUserProfile', 'UPDATE']]),
        ];
        $first = app_sso_app_user_generated_resolver_contract($this->policy(), $this->readySchema(), $classes);
        $second = app_sso_app_user_generated_resolver_contract($this->policy(), $this->readySchema(), $classes);
        self::assertTrue($first['ok'], implode(' ', $first['errors']));
        self::assertSame('artifact_ready', $first['status']);
        self::assertSame($first['artifact_text'], $second['artifact_text']);
        self::assertStringContainsString('MtoolGeneratedSsoAppUserResolverContract', $first['artifact_text']);
        self::assertStringContainsString('SelectAppUserExternalIdentityByIssuerSubject', $first['artifact_text']);
        self::assertStringNotContainsString('client_secret', $first['artifact_text']);
    }

    public function testCanonicalSelectVariantsMapToResolverSelectOperations(): void
    {
        $classes = $this->exactClasses();
        $classes[0]['functions'][0]['action_type'] = 'SELECTSINGLE';
        $classes[1]['functions'][0]['action_type'] = 'SELECTLIST';
        $result = app_sso_app_user_generated_resolver_contract($this->policy(), $this->readySchema(), $classes);
        self::assertTrue($result['ok'], implode(' ', $result['errors']));
    }

    public function testBoundGeneratedDbAccessFixtureCreatesRestoresAndRollsBackAtomically(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        app_sso_app_user_apply_sqlite_schema($pdo);
        $fixture = $this->generatedDbAccessFixture($pdo);
        $contract = app_sso_app_user_generated_resolver_contract($this->policy(), $this->readySchema(), $this->exactClasses());
        $binding = app_sso_app_user_generated_resolver_bind($contract, [
            'AppUser' => $fixture,
            'AppUserExternalIdentity' => $fixture,
            'AppUserProfile' => $fixture,
        ]);
        self::assertTrue($binding['ok'], implode(' ', $binding['errors']));

        $principal = ['issuer' => 'https://idp.example.test/', 'subject' => 'subject-1', 'display_name' => 'First', 'email' => 'first@example.test', 'access_token' => 'must-not-persist'];
        $created = app_sso_app_user_generated_resolve_verified_principal($pdo, $binding['operations'], $principal, $this->policy(), static fn (): string => 'usr_fixed');
        self::assertTrue($created['ok'], $created['error']);
        self::assertSame('created', $created['status']);
        self::assertSame(['app_user_id' => 'usr_fixed'], $created['actor']);
        self::assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM app_user')->fetchColumn());

        $principal['display_name'] = 'Restored';
        $restored = app_sso_app_user_generated_resolve_verified_principal($pdo, $binding['operations'], $principal, $this->policy());
        self::assertTrue($restored['ok'], $restored['error']);
        self::assertSame('restored', $restored['status']);
        self::assertSame('usr_fixed', $restored['app_user_id']);
        self::assertSame('Restored', (string) $pdo->query("SELECT display_name FROM app_user_profile WHERE app_user_id = 'usr_fixed'")->fetchColumn());
        self::assertStringNotContainsString('must-not-persist', (string) $pdo->query("SELECT profile_json FROM app_user_profile WHERE app_user_id = 'usr_fixed'")->fetchColumn());

        $fixture->failProfileWrite = true;
        $failed = app_sso_app_user_generated_resolve_verified_principal(
            $pdo,
            $binding['operations'],
            ['issuer' => 'https://idp.example.test', 'subject' => 'subject-rollback', 'display_name' => 'Rollback'],
            $this->policy(),
            static fn (): string => 'usr_rollback',
        );
        self::assertFalse($failed['ok']);
        self::assertSame('persistence_failed', $failed['status']);
        self::assertSame(0, (int) $pdo->query("SELECT COUNT(*) FROM app_user WHERE app_user_id = 'usr_rollback'")->fetchColumn());
        self::assertSame(0, (int) $pdo->query("SELECT COUNT(*) FROM app_user_external_identity WHERE subject = 'subject-rollback'")->fetchColumn());
    }

    private function policy(): array
    {
        return ['enabled' => true, 'auth_mode' => 'oidc', 'provisioning_mode' => 'jit', 'provider_key' => 'primary-oidc', 'sso_profile_fields' => ['display_name', 'email'], 'application_profile_fields' => [], 'user_owned_data' => [], 'lifecycle_custom_boundary' => []];
    }

    private function readySchema(): array
    {
        return ['status' => 'generation_ready', 'ready_for_generation' => true];
    }

    private function dbAccess(string $sourceName, array $functions): array
    {
        return ['source_name' => $sourceName, 'functions' => array_map(static fn (array $function): array => ['function_name' => $function[0], 'action_type' => $function[1]], $functions)];
    }

    private function exactClasses(): array
    {
        return [
            $this->dbAccess('AppUser', [['SelectAppUserByAppUserId', 'SELECT'], ['InsertAppUser', 'INSERT']]),
            $this->dbAccess('AppUserExternalIdentity', [['SelectAppUserExternalIdentityByIssuerSubject', 'SELECT'], ['InsertAppUserExternalIdentity', 'INSERT'], ['UpdateAppUserExternalIdentityLastAuthenticatedAt', 'UPDATE']]),
            $this->dbAccess('AppUserProfile', [['UpsertAppUserProfile', 'UPDATE']]),
        ];
    }

    private function generatedDbAccessFixture(PDO $pdo): object
    {
        return new class ($pdo) {
            public bool $failProfileWrite = false;
            public function __construct(private PDO $pdo) {}
            public function SelectAppUserExternalIdentityByIssuerSubject(array $input): ?array
            {
                $statement = $this->pdo->prepare('SELECT app_user_id FROM app_user_external_identity WHERE issuer=:issuer AND subject=:subject LIMIT 1');
                $statement->execute([':issuer'=>$input['issuer'], ':subject'=>$input['subject']]);
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                return is_array($row) ? $row : null;
            }
            public function SelectAppUserByAppUserId(array $input): ?array
            {
                $statement = $this->pdo->prepare('SELECT app_user_id,status FROM app_user WHERE app_user_id=:id LIMIT 1');
                $statement->execute([':id'=>$input['app_user_id']]);
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                return is_array($row) ? $row : null;
            }
            public function InsertAppUser(array $input): bool
            {
                $statement = $this->pdo->prepare('INSERT INTO app_user (app_user_id,status) VALUES (:id,:status)');
                return $statement->execute([':id'=>$input['app_user_id'], ':status'=>$input['status']]);
            }
            public function InsertAppUserExternalIdentity(array $input): bool
            {
                $statement = $this->pdo->prepare('INSERT INTO app_user_external_identity (app_user_id,provider_key,issuer,subject,first_authenticated_at,last_authenticated_at) VALUES (:id,:provider,:issuer,:subject,:first,:last)');
                return $statement->execute([':id'=>$input['app_user_id'], ':provider'=>$input['provider_key'], ':issuer'=>$input['issuer'], ':subject'=>$input['subject'], ':first'=>$input['first_authenticated_at'], ':last'=>$input['last_authenticated_at']]);
            }
            public function UpsertAppUserProfile(array $input): bool
            {
                if ($this->failProfileWrite) {
                    return false;
                }
                $profile = $input['profile'];
                $statement = $this->pdo->prepare('INSERT INTO app_user_profile (app_user_id,display_name,email,profile_json) VALUES (:id,:name,:email,:json) ON CONFLICT(app_user_id) DO UPDATE SET display_name=excluded.display_name,email=excluded.email,profile_json=excluded.profile_json');
                return $statement->execute([':id'=>$input['app_user_id'], ':name'=>$profile['display_name'] ?? '', ':email'=>$profile['email'] ?? '', ':json'=>json_encode($profile, JSON_THROW_ON_ERROR)]);
            }
            public function UpdateAppUserExternalIdentityLastAuthenticatedAt(array $input): bool
            {
                $statement = $this->pdo->prepare('UPDATE app_user_external_identity SET last_authenticated_at=:at WHERE issuer=:issuer AND subject=:subject');
                return $statement->execute([':at'=>$input['authenticated_at'], ':issuer'=>$input['issuer'], ':subject'=>$input['subject']]);
            }
        };
    }
}
