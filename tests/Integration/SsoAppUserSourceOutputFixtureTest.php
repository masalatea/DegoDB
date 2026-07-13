<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_runtime_generator.php';

final class SsoAppUserSourceOutputFixtureTest extends TestCase
{
    public function testQualifiedProjectEmitsResolverAndDisabledRegenerationRemovesIt(): void
    {
        [$app, $projectKey] = $this->fixture();
        $definition = ['source_output_key' => 'SSO-RUNTIME', 'runtime_source_relative_path' => 'work/fixture-runtime'];
        $result = app_project_output_prepare_runtime_source_tree($app, $projectKey, $definition);
        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('emitted', $result['generation_summary']['sso_app_user_resolver']['status'], $result['generation_summary']['sso_app_user_resolver']['warning']);
        $resolverRoot = $result['runtime_source_root'] . '/_support/sso-app-user';
        self::assertFileExists($resolverRoot . '/resolver-contract.php');
        self::assertFileExists($resolverRoot . '/sso_app_user_generated_resolver.php');
        $manifest = json_decode((string) file_get_contents($result['runtime_source_root'] . '/_support/runtime-generation-manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($manifest['generation_summary']['sso_app_user_resolver']['emitted']);
        self::assertStringContainsString('sso_app_user_generated_resolver.php', (string) file_get_contents($result['runtime_source_root'] . '/autoload_mtool.php'));

        $disabled = app_upsert_sso_app_user_project_policy($app, $projectKey, ['enabled' => false]);
        self::assertTrue($disabled['ok'], $disabled['error']);
        $regenerated = app_project_output_prepare_runtime_source_tree($app, $projectKey, $definition);
        self::assertTrue($regenerated['ok'], $regenerated['error']);
        self::assertSame('disabled', $regenerated['generation_summary']['sso_app_user_resolver']['status']);
        self::assertFileDoesNotExist($regenerated['runtime_source_root'] . '/_support/sso-app-user/resolver-contract.php');
    }

    private function fixture(): array
    {
        $root = sys_get_temp_dir() . '/dego-sso-output-' . bin2hex(random_bytes(5));
        $config = app_config_store_config('sqlite', 'db', '0', 'config', 'user', 'secret', '/tmp', $root . '/config');
        $app = ['site'=>'admin', 'db'=>$config, 'config_db'=>$config, 'work'=>['root'=>$root . '/work']];
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);
        $projectKey = 'SSO-OUTPUT-' . strtoupper(bin2hex(random_bytes(2)));
        $project = app_pdo_insert_project($app, ['project_key'=>$projectKey, 'name'=>'SSO Output', 'slug'=>strtolower($projectKey), 'lifecycle_status'=>'active', 'owner_login_id'=>'owner', 'description'=>'fixture']);
        self::assertTrue($project['ok'], $project['error']);
        $pdo = app_create_config_pdo($app);
        $projectId = app_sso_app_user_project_policy_resolve_project_id($pdo, $projectKey);
        $isNullIdentifier = app_sql_identifier('sqlite', 'IsNull');

        $catalog = [];
        foreach ([
            'app_user' => [['app_user_id','PRI'],['status','']],
            'app_user_external_identity' => [['app_user_id',''],['issuer',''],['subject','']],
            'app_user_profile' => [['app_user_id','']],
        ] as $tableName => $columns) {
            $pdo->prepare('INSERT INTO dbtable (ProjectPID,name,physical_name) VALUES (:p,:n,:n)')->execute([':p'=>$projectId, ':n'=>$tableName]);
            $tablePid = (int) $pdo->lastInsertId();
            $catalog[$tableName] = ['table'=>$tablePid, 'columns'=>[]];
            foreach ($columns as $order => $column) {
                $pdo->prepare('INSERT INTO dbtablecolumns (ProjectPID,dbtablePID,name,physical_name,datatype,' . $isNullIdentifier . ',IsKey,IsDefault,Extra,ColumnListOrder,memo) VALUES (:p,:t,:n,:n,"text","NO",:k,"","",:o,"")')->execute([':p'=>$projectId, ':t'=>$tablePid, ':n'=>$column[0], ':k'=>$column[1], ':o'=>$order+1]);
                $catalog[$tableName]['columns'][$column[0]] = (int) $pdo->lastInsertId();
            }
            $pdo->prepare('INSERT INTO dataclass (ProjectPID,name,physical_name,StoreBasePath,IsAutoload,InheritParentDataClassName) VALUES (:p,:n,:n,"",1,"")')->execute([':p'=>$projectId, ':n'=>$tableName]);
            $dataClassPid = (int) $pdo->lastInsertId();
            foreach ($columns as $order => $column) {
                $reference = $column[0] === 'app_user_id' && $tableName !== 'app_user' ? 'app_user' : '';
                $pdo->prepare('INSERT INTO dataclassfields (ProjectPID,dataclassPID,name,physical_name,datatype,FieldListOrder,RefDataClassName,RefDataClassFieldName) VALUES (:p,:d,:n,:n,"string",:o,:r,:rf)')->execute([':p'=>$projectId, ':d'=>$dataClassPid, ':n'=>$column[0], ':o'=>$order+1, ':r'=>$reference, ':rf'=>$reference === '' ? '' : 'app_user_id']);
            }
        }

        $classes = [
            'AppUser' => [['SelectAppUserByAppUserId','SELECTSINGLE'],['InsertAppUser','INSERT']],
            'AppUserExternalIdentity' => [['SelectAppUserExternalIdentityByIssuerSubject','SELECTSINGLE'],['InsertAppUserExternalIdentity','INSERT'],['UpdateAppUserExternalIdentityLastAuthenticatedAt','UPDATE']],
            'AppUserProfile' => [['UpsertAppUserProfile','UPDATE']],
        ];
        foreach ($classes as $className => $functions) {
            $pdo->prepare('INSERT INTO project_db_access_classes (project_id,source_name,notes,source_of_truth) VALUES (:p,:n,"","fixture")')->execute([':p'=>$projectId, ':n'=>$className]);
            $classId = (int) $pdo->lastInsertId();
            foreach ($functions as $order => $function) {
                $pdo->prepare('INSERT INTO project_db_access_functions (db_access_class_id,function_name,function_list_order,action_type,memo,detected_signature,source_of_truth) VALUES (:c,:n,:o,:a,"",:s,"fixture")')->execute([':c'=>$classId, ':n'=>$function[0], ':o'=>$order+1, ':a'=>$function[1], ':s'=>'public function '.$function[0].'(array $input)']);
            }
        }
        $constraints = app_replace_project_table_constraints($app, $projectKey, [
            'keys'=>[['table_pid'=>$catalog['app_user_external_identity']['table'],'key_name'=>'uq_issuer_subject','key_kind'=>'unique','columns'=>[['column_pid'=>$catalog['app_user_external_identity']['columns']['issuer']],['column_pid'=>$catalog['app_user_external_identity']['columns']['subject']]]]],
            'foreign_keys'=>[
                ['table_pid'=>$catalog['app_user_external_identity']['table'],'constraint_name'=>'fk_identity_user','referenced_table_pid'=>$catalog['app_user']['table'],'columns'=>[['column_pid'=>$catalog['app_user_external_identity']['columns']['app_user_id'],'referenced_column_pid'=>$catalog['app_user']['columns']['app_user_id']]]],
                ['table_pid'=>$catalog['app_user_profile']['table'],'constraint_name'=>'fk_profile_user','referenced_table_pid'=>$catalog['app_user']['table'],'columns'=>[['column_pid'=>$catalog['app_user_profile']['columns']['app_user_id'],'referenced_column_pid'=>$catalog['app_user']['columns']['app_user_id']]]],
            ],
        ]);
        self::assertTrue($constraints['ok'], $constraints['error']);
        $policy = app_upsert_sso_app_user_project_policy($app, $projectKey, ['enabled'=>true,'auth_mode'=>'oidc','provisioning_mode'=>'jit','provider_key'=>'primary-oidc','sso_profile_fields'=>['display_name','email'],'application_profile_fields'=>[],'user_owned_data'=>[],'lifecycle_custom_boundary'=>[]]);
        self::assertTrue($policy['ok'], $policy['error']);

        $sourceRoot = app_runtime_storage_runtime_source_root($app, 'work/fixture-runtime');
        mkdir($sourceRoot, 0777, true);
        file_put_contents($sourceRoot . '/autoload_mtool.php', "<?php\n// MTOOL_GENERATED_AUTOLOAD_START\n// MTOOL_GENERATED_AUTOLOAD_END\n");
        foreach (array_keys($classes) as $className) {
            $methods = '';
            foreach ($classes[$className] as $function) {
                $methods .= ' public function ' . $function[0] . '(array $input) { return true; }';
            }
            file_put_contents($sourceRoot . '/dbaccess-' . $className . '.php', '<?php class ' . $className . 'DBAccess {' . $methods . ' }');
        }
        return [$app, $projectKey];
    }
}
