<?php

declare(strict_types=1);

/**
 * @param array{
 *     request_id:string
 * } $request
 */
function app_send_html_response_headers(array $request, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Request-Id: ' . $request['request_id']);
}

/**
 * @param array{
 *     request_id:string
 * } $request
 */
function app_send_json_response(array $request, array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Request-Id: ' . $request['request_id']);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
}

/**
 * @param array{
 *     request_id:string
 * } $request
 */
function app_send_redirect_response(array $request, string $location, int $statusCode = 302): void
{
    http_response_code($statusCode);
    header('Cache-Control: no-store');
    header('Location: ' . $location);
    header('X-Request-Id: ' . $request['request_id']);
}

/**
 * @param array{
 *     request_id:string
 * } $request
 */
function app_send_file_download_response(
    array $request,
    string $filePath,
    string $downloadName,
    string $contentType = 'application/octet-stream',
): void {
    http_response_code(200);
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . (string) filesize($filePath));
    header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Request-Id: ' . $request['request_id']);

    if (readfile($filePath) === false) {
        throw new RuntimeException('download file の送信に失敗しました。');
    }
}
