<?php

declare(strict_types=1);

abstract class MtoolGeneratedSingleProxyEndpointBase
{
    final public function handle(): void
    {
        $this->sendCorsHeaders();

        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'POST'));
        if ($method === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        try {
            $payload = $this->decodeRequestPayload();
            $this->beforeHandle($payload);
            $this->authorizeRequest($payload);

            $response = [
                '_status' => 'OK',
                'Message' => $this->proxyDisplayName() . ' completed.',
            ];

            $this->withOptionalTransaction(function () use ($payload, &$response): void {
                foreach ($this->stepDefinitions() as $step) {
                    $this->handleStep($step, $payload, $response);
                }
            });

            $this->afterHandle($payload, $response);
            $this->sendJson(200, $response);
        } catch (Throwable $throwable) {
            $this->onException($throwable);
            $this->sendJson(500, [
                '_status' => 'NG',
                'Message' => $throwable->getMessage(),
            ]);
        }
    }

    abstract protected function proxyDisplayName(): string;

    abstract protected function stepDefinitions(): array;

    protected function usesTransaction(): bool
    {
        return false;
    }

    protected function continueEvenIfFailedToInsert(): bool
    {
        return false;
    }

    protected function authStrategy(): string
    {
        return 'manual';
    }

    protected function expectedProjectToken(): string
    {
        return getenv('MTOOL_PROXY_PROJECT_TOKEN') ?: '';
    }

    protected function expectedStaticBearerToken(): string
    {
        return getenv('DEGODB_PROXY_BEARER_TOKEN') ?: (getenv('MTOOL_PROXY_BEARER_TOKEN') ?: '');
    }

    protected function authorizationHeader(): string
    {
        return $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';
    }

    protected function singleGetFunctionName(): string
    {
        return '';
    }

    protected function authorizeByGetFunction(array $payload, string $singleGetFunctionName): bool
    {
        return false;
    }

    protected function authorizeByLoginCookieToken(string $loginCookieToken, array $payload): bool
    {
        return false;
    }

    protected function beforeHandle(array $payload): void
    {
    }

    protected function afterHandle(array $payload, array &$response): void
    {
    }

    protected function onException(Throwable $throwable): void
    {
    }

    private function decodeRequestPayload(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('request body は JSON object である必要があります。');
        }

        return $decoded;
    }

    private function authorizeRequest(array $payload): void
    {
        $strategy = $this->authStrategy();
        $projectTokenAttempted = false;
        $projectTokenFailureReason = '';
        if ($strategy === 'no-security') {
            return;
        }

        if ($strategy === 'manual') {
            return;
        }

        if ($strategy === 'static-bearer') {
            $header = trim($this->authorizationHeader());
            if ($header === '') {
                throw new RuntimeException('Authorization bearer header が必要です。');
            }
            if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
                throw new RuntimeException('Authorization header は Bearer token 形式である必要があります。');
            }

            $suppliedToken = trim((string) ($matches[1] ?? ''));
            if ($suppliedToken === '') {
                throw new RuntimeException('Bearer token は空でない string である必要があります。');
            }

            $expectedToken = $this->expectedStaticBearerToken();
            if ($expectedToken === '') {
                throw new RuntimeException('DEGODB_PROXY_BEARER_TOKEN が未設定です。');
            }

            if (!hash_equals($expectedToken, $suppliedToken)) {
                throw new RuntimeException('Bearer token が一致しません。');
            }

            return;
        }

        if ($strategy === 'project-token' || $strategy === 'project-token-or-get-function') {
            if (!array_key_exists('TOKEN', $payload)) {
                if ($strategy === 'project-token') {
                    throw new RuntimeException('TOKEN が必要です。');
                }
            } else {
                $projectTokenAttempted = true;
                if (!is_string($payload['TOKEN']) || trim($payload['TOKEN']) === '') {
                    throw new RuntimeException('TOKEN は空でない string である必要があります。');
                }

                $expectedToken = $this->expectedProjectToken();
                if ($expectedToken === '') {
                    $projectTokenFailureReason = 'MTOOL_PROXY_PROJECT_TOKEN が未設定です。';
                    if ($strategy === 'project-token') {
                        throw new RuntimeException($projectTokenFailureReason);
                    }
                } elseif ($payload['TOKEN'] === $expectedToken) {
                    return;
                } else {
                    $projectTokenFailureReason = 'TOKEN が一致しません。';
                    if ($strategy === 'project-token') {
                        throw new RuntimeException($projectTokenFailureReason);
                    }
                }
            }
        }

        if ($strategy === 'get-function' || $strategy === 'project-token-or-get-function') {
            $singleGetFunctionName = trim($this->singleGetFunctionName());
            if ($singleGetFunctionName === '') {
                if ($strategy === 'project-token-or-get-function' && $projectTokenAttempted && $projectTokenFailureReason !== '') {
                    throw new RuntimeException($projectTokenFailureReason . ' get-function 用 single get function name が必要です。');
                }
                throw new RuntimeException('single get function name が必要です。');
            }

            if ($this->authorizeByGetFunction($payload, $singleGetFunctionName)) {
                return;
            }

            if ($strategy === 'project-token-or-get-function' && $projectTokenAttempted) {
                if ($projectTokenFailureReason !== '') {
                    throw new RuntimeException($projectTokenFailureReason . ' get-function 認証にも失敗しました。');
                }

                throw new RuntimeException('TOKEN も get-function も認証に失敗しました。');
            }

            throw new RuntimeException('get-function 認証に失敗しました。');
        }

        if ($strategy === 'login-cookie-token') {
            if (!array_key_exists('LOGIN_COOKIE_TOKEN', $payload)) {
                throw new RuntimeException('LOGIN_COOKIE_TOKEN が必要です。');
            }

            if (!is_string($payload['LOGIN_COOKIE_TOKEN']) || trim($payload['LOGIN_COOKIE_TOKEN']) === '') {
                throw new RuntimeException('LOGIN_COOKIE_TOKEN は空でない string である必要があります。');
            }

            if ($this->authorizeByLoginCookieToken($payload['LOGIN_COOKIE_TOKEN'], $payload)) {
                return;
            }

            throw new RuntimeException('LOGIN_COOKIE_TOKEN 認証に失敗しました。');
        }

        throw new RuntimeException('未対応の auth strategy です。');
    }

    private function withOptionalTransaction(callable $callback): void
    {
        if (!$this->usesTransaction()) {
            $callback();
            return;
        }

        connect_mtooldb_if_not_yet();
        global $mtooldb;

        if (!$mtooldb instanceof mysqli) {
            throw new RuntimeException('transaction に必要な DB connection がありません。');
        }

        $mtooldb->autocommit(false);
        try {
            $callback();
            $mtooldb->commit();
        } catch (Throwable $throwable) {
            $mtooldb->rollback();
            throw $throwable;
        } finally {
            $mtooldb->autocommit(true);
        }
    }

    private function handleStep(array $step, array $payload, array &$response): void
    {
        $requestKey = (string) ($step['request_key'] ?? '');
        $responseMode = (string) ($step['response_mode'] ?? 'none');
        $responseKey = (string) ($step['response_key'] ?? '');

        if ($requestKey === '') {
            $stepInput = $payload;
            unset($stepInput['TOKEN'], $stepInput['LOGIN_COOKIE_TOKEN']);
        } else {
            $stepInput = $payload[$requestKey] ?? ($step['is_list'] ? [] : []);
        }

        if (!is_array($stepInput)) {
            $inputLabel = $requestKey !== '' ? $requestKey : 'request payload';
            throw new RuntimeException($inputLabel . ' は object または array である必要があります。');
        }

        if ($step['is_list']) {
            $responseItems = [];
            $insertIds = [];

            foreach ($stepInput as $item) {
                if (!is_array($item)) {
                    throw new RuntimeException($requestKey . ' の各要素は object である必要があります。');
                }

                $result = $this->executeStep($step, $item);
                if ($responseMode === 'insert-id-list') {
                    $insertIds[] = $this->lastInsertIdOrNull();
                } elseif ($responseMode === 'step-result-list') {
                    $responseItems[] = [
                        'Result' => $this->normalizeValue($result),
                    ];
                }
            }

            if ($responseKey !== '') {
                if ($responseMode === 'insert-id-list') {
                    $response[$responseKey] = $insertIds;
                } elseif ($responseMode === 'step-result-list') {
                    $response[$responseKey] = $responseItems;
                }
            }

            return;
        }

        $result = $this->executeStep($step, $stepInput);
        if ($responseKey === '') {
            return;
        }

        if ($responseMode === 'insert-id-single') {
            $response[$responseKey] = $this->lastInsertIdOrNull();
            return;
        }

        if ($responseMode === 'direct-result') {
            $response[$responseKey] = $this->normalizeValue($result);
            return;
        }

        if ($responseMode === 'step-result-single') {
            $response[$responseKey] = [
                'Result' => $this->normalizeValue($result),
            ];
        }
    }

    private function executeStep(array $step, array $stepInput)
    {
        $dbaccessClass = (string) ($step['dbaccess_class'] ?? '');
        $functionName = (string) ($step['function_name'] ?? '');
        if ($dbaccessClass === '' || $functionName === '') {
            throw new RuntimeException('dbaccess step 定義が不正です。');
        }

        if (!class_exists($dbaccessClass)) {
            throw new RuntimeException('dbaccess class が見つかりません: ' . $dbaccessClass);
        }

        $instance = new $dbaccessClass();
        if (!method_exists($instance, $functionName)) {
            throw new RuntimeException('dbaccess function が見つかりません: ' . $functionName);
        }

        $arguments = [];
        if (($step['input_kind'] ?? '') === 'object') {
            $paramName = (string) ($step['object_param_name'] ?? '');
            $objectClass = (string) ($step['object_class'] ?? '');
            $objectPayload = $stepInput[$paramName] ?? [];
            if (!is_array($objectPayload)) {
                throw new RuntimeException($paramName . ' は object である必要があります。');
            }

            $arguments[] = $this->hydrateDataObject($objectClass, $objectPayload);
        } else {
            foreach ((array) ($step['parameter_names'] ?? []) as $paramName) {
                $arguments[] = $stepInput[(string) $paramName] ?? null;
            }
        }

        $result = $instance->$functionName(...$arguments);
        if ($result === false) {
            $action = (string) ($step['action'] ?? '');
            if ($action === 'insert' && $this->continueEvenIfFailedToInsert()) {
                return null;
            }

            throw new RuntimeException('step failed: ' . $functionName);
        }

        return $result;
    }

    private function hydrateDataObject(string $className, array $payload): object
    {
        if ($className === '' || !class_exists($className)) {
            throw new RuntimeException('data class が見つかりません: ' . $className);
        }

        $object = new $className();
        foreach ($payload as $key => $value) {
            if (!is_string($key) || !property_exists($object, $key)) {
                continue;
            }

            $object->$key = $value;
        }

        return $object;
    }

    private function normalizeValue($value)
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            return $this->normalizeValue(get_object_vars($value));
        }

        return $value;
    }

    private function lastInsertIdOrNull(): ?int
    {
        global $mtooldb;

        if (!$mtooldb instanceof mysqli) {
            return null;
        }

        $insertId = $mtooldb->insert_id;
        return is_numeric($insertId) ? (int) $insertId : null;
    }

    private function sendCorsHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: ' . (getenv('MTOOL_PROXY_CORS_ALLOW_ORIGIN') ?: '*'));
        header('Access-Control-Allow-Headers: ' . (getenv('MTOOL_PROXY_CORS_ALLOW_HEADERS') ?: 'Origin, X-Requested-With, Content-Type, Accept'));
    }

    private function sendJson(int $statusCode, array $payload): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        echo is_string($json) ? ($json . PHP_EOL) : "{}\n";
    }
}
