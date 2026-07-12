<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/generated_name.php';
require_once __DIR__ . '/project_output_php_namespace.php';
require_once __DIR__ . '/project_output_template_renderer.php';
require_once __DIR__ . '/project_output_runtime_sql_generator.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_project_output_db_access_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'canonical-dbaccess-php';
}

function app_project_output_db_access_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_db_access_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

function app_project_output_db_access_identifier_fragment(string $value): string
{
    $normalized = preg_replace('/[^A-Za-z0-9_]+/', '_', trim($value));
    if (!is_string($normalized)) {
        $normalized = '';
    }

    $normalized = trim($normalized, '_');
    if ($normalized === '') {
        return 'Value';
    }

    if (preg_match('/^[0-9]/', $normalized) === 1) {
        return '_' . $normalized;
    }

    return $normalized;
}

function app_project_output_db_access_output_source_name(array $classItem): string
{
    $sourceName = trim((string) ($classItem['source_name'] ?? ''));
    if (!app_generated_name_policy_uses_physical_logical_names()) {
        return $sourceName;
    }

    $physicalName = trim((string) ($classItem['physical_name'] ?? $sourceName));
    return app_generated_name_map_for_physical_name($physicalName, 'class')['generated_name'];
}

function app_project_output_db_access_argument_name(
    string $sourceName,
    string $columnName,
    string $suffix = '',
): string {
    $segments = [
        'param',
        app_project_output_db_access_identifier_fragment($sourceName),
    ];

    if ($columnName !== '') {
        $segments[] = app_project_output_db_access_identifier_fragment($columnName);
    }

    if ($suffix !== '') {
        $segments[] = app_project_output_db_access_identifier_fragment($suffix);
    }

    return '$' . implode('_', $segments);
}

function app_project_output_db_access_object_argument_name(string $sourceName): string
{
    return '$' . app_project_output_db_access_identifier_fragment($sourceName) . 'Obj';
}

function app_project_output_db_access_wrapper_relative_path(
    string $storeBasePath,
    string $sourceName,
): string {
    return ($storeBasePath !== '' ? $storeBasePath . '/' : '')
        . 'dbaccess-'
        . $sourceName
        . '.php';
}

function app_project_output_db_access_base_relative_path(
    string $storeBasePath,
    string $sourceName,
): string {
    return ($storeBasePath !== '' ? $storeBasePath . '/' : '')
        . 'base/dbaccess-'
        . $sourceName
        . 'Base.php';
}

function app_project_output_db_access_runtime_support_relative_path(): string
{
    return '_support/mtool_runtime_db.php';
}

function app_project_output_db_access_runtime_support_require_path(string $storeBasePath): string
{
    $segments = $storeBasePath !== ''
        ? array_values(array_filter(explode('/', trim($storeBasePath, '/')), static fn (string $value): bool => $value !== ''))
        : [];

    return str_repeat('../', count($segments) + 1)
        . app_project_output_db_access_runtime_support_relative_path();
}

function app_project_output_db_access_runtime_support_php_text(): string
{
    return <<<'PHP'
<?php

// Runtime DB adapter for generated canonical DBAccess classes.
// It preserves the legacy $mtooldb surface while allowing a PDO SQLite DSN.

if (!class_exists('MtoolGeneratedDbAccessPdoResult')) {
    class MtoolGeneratedDbAccessPdoResult
    {
        private PDOStatement $statement;

        public function __construct(PDOStatement $statement)
        {
            $this->statement = $statement;
        }

        public function fetch_row()
        {
            $row = $this->statement->fetch(PDO::FETCH_NUM);

            return $row === false ? null : $row;
        }
    }
}

if (!class_exists('MtoolGeneratedDbAccessRuntimeDb')) {
    class MtoolGeneratedDbAccessRuntimeDb
    {
        public int $errno = 0;
        public string $error = '';

        private ?PDO $pdo = null;
        private $mysqli = null;
        private bool $mysqliTransactionActive = false;

        public function __construct()
        {
            $dsn = trim((string) getenv('MTOOL_RUNTIME_DB_DSN'));
            if ($dsn !== '') {
                $this->pdo = new PDO(
                    $dsn,
                    (string) getenv('MTOOL_RUNTIME_DB_USER'),
                    (string) getenv('MTOOL_RUNTIME_DB_PASSWORD'),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM,
                    ],
                );
                return;
            }

            $sqlitePath = trim((string) getenv('MTOOL_RUNTIME_SQLITE_PATH'));
            if ($sqlitePath !== '') {
                $parentDir = dirname($sqlitePath);
                if ($parentDir !== '' && $parentDir !== '.' && !is_dir($parentDir)) {
                    mkdir($parentDir, 0777, true);
                }
                $this->pdo = new PDO(
                    'sqlite:' . $sqlitePath,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM,
                    ],
                );
                return;
            }

            if (!class_exists('mysqli')) {
                throw new RuntimeException('mysqli is not available and no MTOOL_RUNTIME_DB_DSN / MTOOL_RUNTIME_SQLITE_PATH was provided.');
            }

            $host = (string) (getenv('MTOOL_RUNTIME_DB_HOST') ?: (getenv('MTOOL_PROXY_DB_HOST') ?: '127.0.0.1'));
            $user = (string) (getenv('MTOOL_RUNTIME_DB_USER') ?: (getenv('MTOOL_PROXY_DB_USER') ?: 'root'));
            $password = (string) (getenv('MTOOL_RUNTIME_DB_PASSWORD') ?: (getenv('MTOOL_PROXY_DB_PASSWORD') ?: ''));
            $database = (string) (getenv('MTOOL_RUNTIME_DB_NAME') ?: (getenv('MTOOL_PROXY_DB_NAME') ?: ''));
            $port = (int) (getenv('MTOOL_RUNTIME_DB_PORT') ?: (getenv('MTOOL_PROXY_DB_PORT') ?: 3306));

            $this->mysqli = new mysqli($host, $user, $password, $database, $port);
            if ($this->mysqli->connect_errno !== 0) {
                $this->errno = (int) $this->mysqli->connect_errno;
                $this->error = (string) $this->mysqli->connect_error;
            }
        }

        public function query(string $sql)
        {
            $this->errno = 0;
            $this->error = '';

            if ($this->pdo instanceof PDO) {
                try {
                    $statement = $this->pdo->query($sql);
                    if (!$statement instanceof PDOStatement) {
                        return false;
                    }

                    return new MtoolGeneratedDbAccessPdoResult($statement);
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            if ($this->mysqli instanceof mysqli) {
                try {
                    $result = $this->mysqli->query($sql);
                    $this->errno = (int) $this->mysqli->errno;
                    $this->error = (string) $this->mysqli->error;

                    return $result;
                } catch (Throwable $exception) {
                    $this->errno = max(1, (int) $exception->getCode());
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            $this->errno = 1;
            $this->error = 'runtime DB connection is not initialized';

            return false;
        }

        public function execute(string $sql, array $params = [])
        {
            $this->errno = 0;
            $this->error = '';

            if ($this->pdo instanceof PDO) {
                try {
                    $statement = $this->pdo->prepare($sql);
                    $statement->execute(array_values($params));

                    return new MtoolGeneratedDbAccessPdoResult($statement);
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            if ($this->mysqli instanceof mysqli) {
                try {
                    $statement = $this->mysqli->prepare($sql);
                    if (!$statement instanceof mysqli_stmt) {
                        $this->errno = (int) $this->mysqli->errno;
                        $this->error = (string) $this->mysqli->error;

                        return false;
                    }

                    if ($params !== []) {
                        $types = '';
                        $values = [];
                        foreach (array_values($params) as $param) {
                            if (is_int($param)) {
                                $types .= 'i';
                            } elseif (is_float($param)) {
                                $types .= 'd';
                            } else {
                                $types .= 's';
                            }
                            $values[] = $param;
                        }
                        $statement->bind_param($types, ...$values);
                    }

                    $ok = $statement->execute();
                    $this->errno = (int) $statement->errno;
                    $this->error = (string) $statement->error;
                    if (!$ok || $this->errno !== 0) {
                        return false;
                    }

                    $result = $statement->get_result();

                    return $result !== false ? $result : true;
                } catch (Throwable $exception) {
                    $this->errno = max(1, (int) $exception->getCode());
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            $this->errno = 1;
            $this->error = 'runtime DB connection is not initialized';

            return false;
        }

        public function beginTransaction(): bool
        {
            $this->errno = 0;
            $this->error = '';

            if ($this->pdo instanceof PDO) {
                try {
                    return $this->pdo->beginTransaction();
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            if ($this->mysqli instanceof mysqli) {
                try {
                    $started = $this->mysqli->begin_transaction();
                    if ($started) {
                        $this->mysqliTransactionActive = true;
                    } else {
                        $this->errno = (int) $this->mysqli->errno;
                        $this->error = (string) $this->mysqli->error;
                    }

                    return $started;
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            $this->errno = 1;
            $this->error = 'runtime DB transaction begin is not supported by this connection';

            return false;
        }

        public function commit(): bool
        {
            $this->errno = 0;
            $this->error = '';

            if ($this->pdo instanceof PDO) {
                try {
                    return $this->pdo->commit();
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            if ($this->mysqli instanceof mysqli) {
                try {
                    $committed = $this->mysqli->commit();
                    if ($committed) {
                        $this->mysqliTransactionActive = false;
                    } else {
                        $this->errno = (int) $this->mysqli->errno;
                        $this->error = (string) $this->mysqli->error;
                    }

                    return $committed;
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            $this->errno = 1;
            $this->error = 'runtime DB transaction commit is not supported by this connection';

            return false;
        }

        public function rollBack(): bool
        {
            $this->errno = 0;
            $this->error = '';

            if ($this->pdo instanceof PDO) {
                try {
                    return $this->pdo->rollBack();
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            if ($this->mysqli instanceof mysqli) {
                try {
                    $rolledBack = $this->mysqli->rollback();
                    if ($rolledBack) {
                        $this->mysqliTransactionActive = false;
                    } else {
                        $this->errno = (int) $this->mysqli->errno;
                        $this->error = (string) $this->mysqli->error;
                    }

                    return $rolledBack;
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            $this->errno = 1;
            $this->error = 'runtime DB transaction rollback is not supported by this connection';

            return false;
        }

        public function inTransaction(): bool
        {
            $this->errno = 0;
            $this->error = '';

            if ($this->pdo instanceof PDO) {
                try {
                    return $this->pdo->inTransaction();
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return false;
                }
            }

            if ($this->mysqli instanceof mysqli) {
                return $this->mysqliTransactionActive;
            }

            $this->errno = 1;
            $this->error = 'runtime DB transaction state is not supported by this connection';

            return false;
        }

        public function lastInsertId(): ?int
        {
            $this->errno = 0;
            $this->error = '';

            if ($this->pdo instanceof PDO) {
                try {
                    $insertId = $this->pdo->lastInsertId();

                    return is_numeric($insertId) ? (int) $insertId : null;
                } catch (Throwable $exception) {
                    $this->errno = 1;
                    $this->error = $exception->getMessage();

                    return null;
                }
            }

            if ($this->mysqli instanceof mysqli) {
                $insertId = $this->mysqli->insert_id;

                return is_numeric($insertId) ? (int) $insertId : null;
            }

            $this->errno = 1;
            $this->error = 'runtime DB last insert id is not supported by this connection';

            return null;
        }

        public function real_escape_string($value): string
        {
            $stringValue = (string) $value;
            if ($this->mysqli instanceof mysqli) {
                return $this->mysqli->real_escape_string($stringValue);
            }

            if ($this->pdo instanceof PDO) {
                $quoted = $this->pdo->quote($stringValue);
                if (is_string($quoted) && strlen($quoted) >= 2 && $quoted[0] === "'" && substr($quoted, -1) === "'") {
                    return substr($quoted, 1, -1);
                }
            }

            return str_replace("'", "''", $stringValue);
        }
    }
}

if (!function_exists('connect_mtooldb_if_not_yet')) {
    function connect_mtooldb_if_not_yet(): void
    {
        global $mtooldb;
        if (is_object($mtooldb ?? null)) {
            return;
        }

        $mtooldb = new MtoolGeneratedDbAccessRuntimeDb();
    }
}

if (!function_exists('reconnect_mtooldb_if_necessary')) {
    function reconnect_mtooldb_if_necessary(): void
    {
        connect_mtooldb_if_not_yet();
    }
}

?>
PHP;
}

/**
 * @param array<string,bool> $seen
 * @param list<string> $arguments
 */
function app_project_output_db_access_append_unique_argument(
    array &$arguments,
    array &$seen,
    string $argumentName,
): void {
    $normalizedArgumentName = trim($argumentName);
    if ($normalizedArgumentName === '' || isset($seen[$normalizedArgumentName])) {
        return;
    }

    $seen[$normalizedArgumentName] = true;
    $arguments[] = $normalizedArgumentName;
}

/**
 * @param list<array<string,string>> $rows
 * @param array<string,bool> $seen
 * @param list<string> $arguments
 */
function app_project_output_db_access_append_designer_argument_names(
    string $sourceName,
    array $rows,
    string $columnField,
    string $suffix,
    array &$arguments,
    array &$seen,
): void {
    foreach ($rows as $row) {
        $parameterType = strtolower(trim((string) ($row['parameter_type'] ?? 'argument')));
        if ($parameterType === 'fixed' || $parameterType === 'anotherfield') {
            continue;
        }

        $columnName = trim((string) ($row[$columnField] ?? ''));
        app_project_output_db_access_append_unique_argument(
            $arguments,
            $seen,
            app_project_output_db_access_argument_name($sourceName, $columnName, $suffix),
        );
    }
}

function app_project_output_db_access_normalize_detected_signature(
    string $functionName,
    string $detectedSignature,
): string {
    $signature = trim($detectedSignature);
    if ($signature === '') {
        return '';
    }

    $signature = preg_replace('/\s*\{\s*$/', '', $signature) ?? $signature;
    if (preg_match('/\bfunction\s+' . preg_quote($functionName, '/') . '\s*\(/', $signature) !== 1) {
        return '';
    }

    if (preg_match('/^(public|protected|private)\s+function\b/i', $signature) === 1) {
        return $signature;
    }

    if (preg_match('/^function\b/i', $signature) === 1) {
        return preg_replace('/^function\b/i', 'public function', $signature, 1) ?? $signature;
    }

    return 'public ' . ltrim($signature);
}

/**
 * @param array{
 *     function_name:string,
 *     action_type:string,
 *     parameter_type:string,
 *     limit_parameter_type:string,
 *     detected_signature:string
 * } $functionItem
 * @param array{
 *     select_wheres:list<array<string,string>>,
 *     insert_target_fields:list<array<string,string>>,
 *     update_target_fields:list<array<string,string>>,
 *     update_delete_wheres:list<array<string,string>>
 * } $designer
 */
function app_project_output_db_access_generated_method_signature(
    string $sourceName,
    array $functionItem,
    array $designer,
): string {
    $functionName = trim((string) ($functionItem['function_name'] ?? ''));
    $detectedSignature = app_project_output_db_access_normalize_detected_signature(
        $functionName,
        trim((string) ($functionItem['detected_signature'] ?? '')),
    );
    if ($detectedSignature !== '') {
        return $detectedSignature;
    }

    $arguments = [];
    $seen = [];
    $actionType = strtoupper(trim((string) ($functionItem['action_type'] ?? '')));
    $parameterType = strtolower(trim((string) ($functionItem['parameter_type'] ?? '')));

    if (in_array($actionType, ['SELECTSINGLE', 'SELECTLIST'], true)) {
        app_project_output_db_access_append_designer_argument_names(
            $sourceName,
            $designer['select_wheres'],
            'target_table_column_name',
            'where',
            $arguments,
            $seen,
        );
        if (strtolower(trim((string) ($functionItem['limit_parameter_type'] ?? ''))) === 'argument') {
            app_project_output_db_access_append_unique_argument($arguments, $seen, '$limit');
        }
    } elseif ($actionType === 'INSERT') {
        if ($parameterType === 'classobject') {
            app_project_output_db_access_append_unique_argument(
                $arguments,
                $seen,
                app_project_output_db_access_object_argument_name($sourceName),
            );
        } else {
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['insert_target_fields'],
                'target_table_column_name',
                '',
                $arguments,
                $seen,
            );
        }
    } elseif ($actionType === 'UPDATE') {
        if ($parameterType === 'classobject') {
            app_project_output_db_access_append_unique_argument(
                $arguments,
                $seen,
                app_project_output_db_access_object_argument_name($sourceName),
            );
        } elseif ($parameterType === 'setbyclassobjectandwherebyvalforupdate') {
            app_project_output_db_access_append_unique_argument(
                $arguments,
                $seen,
                app_project_output_db_access_object_argument_name($sourceName),
            );
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['update_delete_wheres'],
                'target_table_column_name',
                'where',
                $arguments,
                $seen,
            );
        } else {
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['update_target_fields'],
                'target_table_column_name',
                'set',
                $arguments,
                $seen,
            );
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['update_delete_wheres'],
                'target_table_column_name',
                'where',
                $arguments,
                $seen,
            );
        }
    } elseif ($actionType === 'DELETE') {
        if ($parameterType === 'classobject') {
            app_project_output_db_access_append_unique_argument(
                $arguments,
                $seen,
                app_project_output_db_access_object_argument_name($sourceName),
            );
        } else {
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['update_delete_wheres'],
                'target_table_column_name',
                'where',
                $arguments,
                $seen,
            );
        }
    }

    return 'public function ' . $functionName . '(' . implode(', ', $arguments) . ')';
}

/**
 * @param list<string> $commentLines
 * @param list<string> $bodyLines
 */
function app_project_output_generated_db_access_method_block(
    string $signature,
    array $commentLines,
    array $bodyLines,
): string {
    $commentSection = '';
    foreach ($commentLines as $commentLine) {
        $commentSection .= '    // ' . $commentLine . "\n";
    }

    $bodySection = '';
    foreach ($bodyLines as $bodyLine) {
        $bodySection .= (string) $bodyLine . "\n";
    }

    return rtrim(
        app_project_output_render_reference_template(
            'canonical-dbaccess-php/method.php.tpl',
            [
                'METHOD_COMMENT_SECTION' => $commentSection,
                'METHOD_SIGNATURE' => $signature,
                'METHOD_BODY_SECTION' => $bodySection,
            ],
        ),
        "\n",
    );
}

/**
 * @param array{
 *     source_name:string,
 *     source_of_truth:string
 * } $classItem
 * @param list<array{
 *     function_name:string,
 *     function_list_order:string,
 *     action_type:string,
 *     source_of_truth:string
 * }> $functionItems
 * @param array<string,array{
 *     mode:string,
 *     body_lines:list<string>,
 *     reason:string
 * }> $generatedMethodResults
 * @param array<string,string> $signaturesByFunction
 * @param list<string> $extraClassLines
 */
function app_project_output_generated_db_access_base_php_text(
    array $classItem,
    array $functionItems,
    array $generatedMethodResults,
    array $signaturesByFunction,
    array $extraClassLines,
    string $runtimeDbSupportRequirePath,
    string $phpNamespace = '',
): string {
    $sourceName = trim((string) ($classItem['source_name'] ?? ''));
    $className = $sourceName . 'DBAccessBase';
    $classBody = '';

    if ($extraClassLines !== []) {
        $classBody .= implode("\n", $extraClassLines) . "\n\n";
    }

    $classBody .= implode("\n", [
        '    public function __construct()',
        '    {',
        '    }',
    ]);

    foreach ($functionItems as $functionItem) {
        $functionName = trim((string) ($functionItem['function_name'] ?? ''));
        if ($functionName === '' || $functionName === '__construct') {
            continue;
        }

        $generatedMethodResult = $generatedMethodResults[$functionName] ?? null;
        $signature = $signaturesByFunction[$functionName] ?? '';
        if ($generatedMethodResult === null || $signature === '') {
            continue;
        }

        $commentLines = [
            'source_of_truth=' . trim((string) ($functionItem['source_of_truth'] ?? ''))
            . ' class_source=' . trim((string) ($classItem['source_of_truth'] ?? ''))
            . ' action_type=' . trim((string) ($functionItem['action_type'] ?? ''))
            . ' order=' . trim((string) ($functionItem['function_list_order'] ?? '0'))
            . ' generation=' . trim((string) ($generatedMethodResult['mode'] ?? '')),
        ];
        $reason = trim((string) ($generatedMethodResult['reason'] ?? ''));
        if ($reason !== '') {
            $normalizedReason = preg_replace('/\s+/', ' ', $reason);
            if (!is_string($normalizedReason) || $normalizedReason === '') {
                $normalizedReason = $reason;
            }
            $commentLines[] = 'reason=' . $normalizedReason;
        }

        $classBody .= "\n\n" . app_project_output_generated_db_access_method_block(
            $signature,
            $commentLines,
            $generatedMethodResult['body_lines'] ?? [],
        );
    }

    $classBody .= "\n";

    return rtrim(
        app_project_output_render_reference_template(
            'canonical-dbaccess-php/base.php.tpl',
            [
                'PHP_NAMESPACE_SECTION' => app_project_output_php_namespace_section($phpNamespace),
                'CLASS_NAME' => $className,
                'RUNTIME_DB_SUPPORT_REQUIRE_SECTION' => $runtimeDbSupportRequirePath !== ''
                    ? "require_once __DIR__ . '/" . $runtimeDbSupportRequirePath . "';\n\n"
                    : '',
                'CLASS_BODY_SECTION' => $classBody,
            ],
        ),
        "\r\n",
    );
}

function app_project_output_generated_db_access_wrapper_php_text(string $sourceName, string $phpNamespace = ''): string
{
    $className = $sourceName . 'DBAccess';
    $baseClassName = $className . 'Base';
    $baseRequirePath = var_export('/base/dbaccess-' . $sourceName . 'Base.php', true);

    return rtrim(
        app_project_output_render_reference_template(
            'canonical-dbaccess-php/wrapper.php.tpl',
            [
                'PHP_NAMESPACE_SECTION' => app_project_output_php_namespace_section($phpNamespace),
                'BASE_REQUIRE_PATH' => $baseRequirePath,
                'CLASS_NAME' => $className,
                'BASE_CLASS_NAME' => $baseClassName,
            ],
        ),
        "\r\n",
    );
}

/**
 * @param array{
 *     source_output_key:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     program_language:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{
 *             relative_path:string,
 *             size:int
 *         }>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_db_access_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_db_access_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の db access artifact strategy です。',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'php') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical db access artifact は現在 php のみ対応です。',
        ];
    }

    $classCatalogResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    if (!$classCatalogResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical db access class metadata の読み込みに失敗しました: '
                . $classCatalogResult['error'],
        ];
    }

    if ($classCatalogResult['items'] === []) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical db access metadata がありません。先に db access class / function metadata を登録してください。',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_db_access_default_runtime_source_relative_path(
            $projectKey,
            (string) ($definition['source_output_key'] ?? ''),
        );
    }
    if (!app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'runtime source relative path の形式が不正です。',
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    $phpNamespace = app_project_output_php_namespace_from_project($app, $projectKey);

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);
        app_project_output_write_text_file(
            $runtimeSourceRoot . '/' . app_project_output_db_access_runtime_support_relative_path(),
            app_project_output_db_access_runtime_support_php_text(),
        );

        foreach ($classCatalogResult['items'] as $classItem) {
            $sourceName = trim((string) ($classItem['source_name'] ?? ''));
            if ($sourceName === '') {
                continue;
            }
            $outputSourceName = app_project_output_db_access_output_source_name($classItem);

            $functionCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
            if (!$functionCatalogResult['ok']) {
                throw new RuntimeException(
                    $sourceName . ' function catalog の読み込みに失敗しました: ' . $functionCatalogResult['error']
                );
            }

            $generatedMethodResults = [];
            $signaturesByFunction = [];
            foreach ($functionCatalogResult['items'] as $functionItem) {
                $functionName = trim((string) ($functionItem['function_name'] ?? ''));
                if ($functionName === '' || $functionName === '__construct') {
                    continue;
                }

                $designer = app_project_output_runtime_sql_fetch_designer_resources(
                    $app,
                    $projectKey,
                    $sourceName,
                    $functionName,
                );
                if (!$designer['ok']) {
                    throw new RuntimeException(
                        $sourceName . '::' . $functionName . ' designer metadata の読み込みに失敗しました: '
                        . $designer['error']
                    );
                }

                $signature = app_project_output_db_access_generated_method_signature(
                    $outputSourceName,
                    $functionItem,
                    $designer,
                );
                $method = [
                    'name' => $functionName,
                    'line' => (int) ($functionItem['detected_line'] ?? 0),
                    'end_line' => (int) ($functionItem['detected_line'] ?? 0),
                    'signature' => $signature,
                ];

                $generationResult = app_project_output_runtime_sql_try_generate_method(
                    $app,
                    $projectKey,
                    $sourceName,
                    $functionItem,
                    $method,
                );
                if (!$generationResult['ok']) {
                    throw new RuntimeException(
                        $sourceName . '::' . $functionName . ' method generation に失敗しました。'
                    );
                }

                $generationMode = trim((string) ($generationResult['result']['mode'] ?? ''));
                if (!in_array($generationMode, ['canonical-sql', 'canonical-helper'], true)) {
                    $reason = trim((string) ($generationResult['result']['reason'] ?? ''));
                    throw new RuntimeException(
                        $sourceName . '::' . $functionName
                        . ' は standalone db access として生成できません: '
                        . ($reason !== '' ? $reason : $generationMode)
                    );
                }

                $warning = trim((string) ($generationResult['result']['warning'] ?? ''));
                if ($warning !== '') {
                    throw new RuntimeException($warning);
                }

                $generatedMethodResults[$functionName] = $generationResult['result'];
                $signaturesByFunction[$functionName] = $signature;
            }

            $storeBasePath = trim(str_replace('\\', '/', (string) ($classItem['store_base_path'] ?? '')), '/');
            if ($storeBasePath !== '' && !app_project_output_relative_path_is_safe($storeBasePath)) {
                throw new RuntimeException(
                    'StoreBasePath の形式が不正です: '
                    . $sourceName
                    . ' -> '
                    . (string) ($classItem['store_base_path'] ?? '')
                );
            }

            $wrapperRelativePath = app_project_output_db_access_wrapper_relative_path($storeBasePath, $outputSourceName);
            $baseRelativePath = app_project_output_db_access_base_relative_path($storeBasePath, $outputSourceName);
            $runtimeDbSupportRequirePath = app_project_output_db_access_runtime_support_require_path($storeBasePath);
            app_project_output_write_text_file(
                $runtimeSourceRoot . '/' . $baseRelativePath,
                app_project_output_generated_db_access_base_php_text(
                    [
                        ...$classItem,
                        'source_name' => $outputSourceName,
                    ],
                    $functionCatalogResult['items'],
                    $generatedMethodResults,
                    $signaturesByFunction,
                    app_project_output_runtime_sql_known_helper_class_lines($outputSourceName),
                    $runtimeDbSupportRequirePath,
                    $phpNamespace,
                ),
            );
            app_project_output_write_text_file(
                $runtimeSourceRoot . '/' . $wrapperRelativePath,
                app_project_output_generated_db_access_wrapper_php_text($outputSourceName, $phpNamespace),
            );
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'db access staging tree の作成に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($runtimeSourceRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'runtime_source_root' => $runtimeSourceRoot,
        'scan_result' => $scanResult,
        'error' => '',
    ];
}
