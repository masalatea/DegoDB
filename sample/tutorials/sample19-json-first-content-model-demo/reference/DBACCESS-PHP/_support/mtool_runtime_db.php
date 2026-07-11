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

            $host = (string) (getenv('MTOOL_RUNTIME_DB_HOST') ?: '127.0.0.1');
            $user = (string) (getenv('MTOOL_RUNTIME_DB_USER') ?: 'root');
            $password = (string) getenv('MTOOL_RUNTIME_DB_PASSWORD');
            $database = (string) getenv('MTOOL_RUNTIME_DB_NAME');
            $port = (int) (getenv('MTOOL_RUNTIME_DB_PORT') ?: 3306);

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
                $result = $this->mysqli->query($sql);
                $this->errno = (int) $this->mysqli->errno;
                $this->error = (string) $this->mysqli->error;

                return $result;
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

            $this->errno = 1;
            $this->error = 'runtime DB transaction state is not supported by this connection';

            return false;
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