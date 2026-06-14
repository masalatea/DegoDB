<?php

declare(strict_types=1);

require_once __DIR__ . '/response.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     uri:string,
 *     path:string,
 *     query_string:string,
 *     host:string,
 *     scheme:string,
 *     remote_addr:string,
 *     user_agent:string
 * } $request
 * @param array{
 *     ok:bool,
 *     label:string,
 *     detail:string
 * } $databaseStatus
 * @return array{
 *     ok:bool,
 *     site:string,
 *     site_name:string,
 *     request:array{
 *         request_id:string,
 *         method:string,
 *         path:string
 *     },
 *     database:array{
 *         ok:bool,
 *         label:string,
 *         detail:string
 *     }
 * }
 */
function app_health_payload(array $app, array $request, array $databaseStatus): array
{
    return [
        'ok' => $databaseStatus['ok'],
        'site' => $app['site'],
        'site_name' => $app['site_name'],
        'request' => [
            'request_id' => $request['request_id'],
            'method' => $request['method'],
            'path' => $request['path'],
        ],
        'database' => [
            'ok' => $databaseStatus['ok'],
            'label' => $databaseStatus['label'],
            'detail' => $databaseStatus['detail'],
        ],
    ];
}

/**
 * @param array{
 *     ok:bool,
 *     site:string,
 *     site_name:string,
 *     request:array{
 *         request_id:string,
 *         method:string,
 *         path:string
 *     },
 *     database:array{
 *         ok:bool,
 *         label:string,
 *         detail:string
 *     }
 * } $payload
 * @param array{
 *     request_id:string
 * } $request
 */
function app_render_health_json(array $request, array $payload): void
{
    app_send_json_response($request, $payload, $payload['ok'] ? 200 : 503);
}
