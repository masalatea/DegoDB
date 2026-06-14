<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/project_output_proxy_generator.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_project_output_openapi_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'openapi-json';
}

function app_project_output_openapi_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_openapi_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @param array<string,mixed> $values
 * @return array<string,mixed>|object
 */
function app_project_output_openapi_object_map(array $values): array|object
{
    return $values === [] ? (object) [] : $values;
}

/**
 * @param array<mixed> $payload
 */
function app_project_output_openapi_json_text(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('OpenAPI JSON の生成に失敗しました。');
    }

    return $json . PHP_EOL;
}

/**
 * @param list<array{
 *     name:string,
 *     inherit_parent_data_class_name:string,
 *     fields:list<array{
 *         name:string,
 *         datatype:string,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }>
 * }> $snapshotItems
 * @return array<string,array{
 *     name:string,
 *     inherit_parent_data_class_name:string,
 *     fields:list<array{
 *         name:string,
 *         datatype:string,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }>
 * }>
 */
function app_project_output_openapi_data_class_index(array $snapshotItems): array
{
    $index = [];

    foreach ($snapshotItems as $item) {
        $className = trim((string) ($item['name'] ?? ''));
        if ($className === '') {
            continue;
        }

        $index[$className] = [
            'name' => $className,
            'inherit_parent_data_class_name' => trim((string) ($item['inherit_parent_data_class_name'] ?? '')),
            'fields' => array_values(array_filter(
                $item['fields'] ?? [],
                static fn (mixed $field): bool => is_array($field),
            )),
        ];
    }

    return $index;
}

/**
 * @param array{
 *     source_entities?:array<string,array{
 *         data_class:string,
 *         data_properties:list<string>
 *     }>
 * } $context
 * @return array<string,list<string>>
 */
function app_project_output_openapi_fallback_properties_by_class(array $context): array
{
    $map = [];

    foreach (($context['source_entities'] ?? []) as $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $className = trim((string) ($entity['data_class'] ?? ''));
        if ($className === '') {
            continue;
        }

        $properties = [];
        $seen = [];
        foreach (($entity['data_properties'] ?? []) as $propertyName) {
            if (!is_string($propertyName)) {
                continue;
            }

            $normalized = trim($propertyName);
            if ($normalized === '' || isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $properties[] = $normalized;
        }

        $map[$className] = $properties;
    }

    return $map;
}

/**
 * @return array{
 *     type:string,
 *     example:mixed,
 *     format?:string
 * }
 */
function app_project_output_openapi_scalar_schema_from_datatype(string $datatype): array
{
    $normalized = strtolower(trim($datatype));
    if ($normalized === '') {
        return [
            'type' => 'string',
            'example' => 'string',
        ];
    }

    $isArray = str_ends_with($normalized, '[]');
    if ($isArray) {
        $itemSchema = app_project_output_openapi_scalar_schema_from_datatype(substr($normalized, 0, -2));

        return [
            'type' => 'array',
            'items' => $itemSchema,
            'example' => [$itemSchema['example'] ?? 'string'],
        ];
    }

    return match ($normalized) {
        'int', 'integer', 'tinyint', 'smallint', 'mediumint', 'bigint', 'long', 'pid' => [
            'type' => 'integer',
            'example' => 0,
        ],
        'float', 'double', 'decimal', 'numeric', 'real' => [
            'type' => 'number',
            'example' => 0,
        ],
        'bool', 'boolean', 'bit' => [
            'type' => 'boolean',
            'example' => false,
        ],
        'date' => [
            'type' => 'string',
            'format' => 'date',
            'example' => '2026-05-25',
        ],
        'datetime', 'timestamp' => [
            'type' => 'string',
            'format' => 'date-time',
            'example' => '2026-05-25T00:00:00+09:00',
        ],
        'time' => [
            'type' => 'string',
            'example' => '00:00:00',
        ],
        'json', 'map', 'object' => [
            'type' => 'object',
            'example' => (object) [],
        ],
        'array', 'list' => [
            'type' => 'array',
            'items' => [
                'type' => 'string',
                'example' => 'string',
            ],
            'example' => ['string'],
        ],
        'blob', 'binary', 'varbinary', 'file', 'image' => [
            'type' => 'string',
            'format' => 'binary',
            'example' => '',
        ],
        default => [
            'type' => 'string',
            'example' => 'string',
        ],
    };
}

/**
 * @param array{
 *     name:string,
 *     datatype:string,
 *     ref_data_class_name:string,
 *     ref_data_class_field_name:string
 * } $field
 * @param array<string,array{
 *     name:string,
 *     inherit_parent_data_class_name:string,
 *     fields:list<array{
 *         name:string,
 *         datatype:string,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }>
 * }> $snapshotIndex
 * @param array<string,list<string>> $fallbackPropertiesByClass
 * @param array<string,array<string,mixed>> $components
 * @param array<string,bool> $stack
 * @return array<string,mixed>
 */
function app_project_output_openapi_field_schema(
    array $field,
    array $snapshotIndex,
    array $fallbackPropertiesByClass,
    array &$components,
    array &$stack,
): array {
    $refClassName = trim((string) ($field['ref_data_class_name'] ?? ''));
    if ($refClassName !== '') {
        app_project_output_openapi_register_data_class_schema(
            $refClassName,
            $snapshotIndex,
            $fallbackPropertiesByClass,
            $components,
            $stack,
        );

        return [
            '$ref' => '#/components/schemas/' . $refClassName,
        ];
    }

    return app_project_output_openapi_scalar_schema_from_datatype((string) ($field['datatype'] ?? ''));
}

/**
 * @param array<string,array{
 *     name:string,
 *     inherit_parent_data_class_name:string,
 *     fields:list<array{
 *         name:string,
 *         datatype:string,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }>
 * }> $snapshotIndex
 * @param array<string,list<string>> $fallbackPropertiesByClass
 * @param array<string,array<string,mixed>> $components
 * @param array<string,bool> $stack
 */
function app_project_output_openapi_register_data_class_schema(
    string $className,
    array $snapshotIndex,
    array $fallbackPropertiesByClass,
    array &$components,
    array &$stack,
): void {
    $normalizedClassName = trim($className);
    if ($normalizedClassName === '' || isset($components[$normalizedClassName]) || isset($stack[$normalizedClassName])) {
        return;
    }

    $stack[$normalizedClassName] = true;
    $properties = [];

    if (isset($snapshotIndex[$normalizedClassName])) {
        $item = $snapshotIndex[$normalizedClassName];
        $parentClassName = trim((string) ($item['inherit_parent_data_class_name'] ?? ''));
        if ($parentClassName !== '') {
            app_project_output_openapi_register_data_class_schema(
                $parentClassName,
                $snapshotIndex,
                $fallbackPropertiesByClass,
                $components,
                $stack,
            );

            $parentSchema = $components[$parentClassName] ?? null;
            if (is_array($parentSchema) && is_array($parentSchema['properties'] ?? null)) {
                $properties = $parentSchema['properties'];
            }
        }

        foreach (($item['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldName = trim((string) ($field['name'] ?? ''));
            if ($fieldName === '') {
                continue;
            }

            $properties[$fieldName] = app_project_output_openapi_field_schema(
                $field,
                $snapshotIndex,
                $fallbackPropertiesByClass,
                $components,
                $stack,
            );
        }
    }

    if ($properties === [] && isset($fallbackPropertiesByClass[$normalizedClassName])) {
        foreach ($fallbackPropertiesByClass[$normalizedClassName] as $propertyName) {
            $properties[$propertyName] = [
                'type' => 'string',
                'example' => 'string',
            ];
        }
    }

    $schema = [
        'type' => 'object',
        'title' => $normalizedClassName,
        'properties' => app_project_output_openapi_object_map($properties),
    ];
    if ($properties === []) {
        $schema['additionalProperties'] = true;
    }

    $components[$normalizedClassName] = $schema;
    unset($stack[$normalizedClassName]);
}

/**
 * @param array<string,array<string,mixed>> $components
 * @param array<string,bool> $stack
 */
function app_project_output_openapi_example_from_schema(
    mixed $schema,
    array $components,
    array &$stack = [],
): mixed {
    if (!is_array($schema)) {
        return null;
    }

    if (array_key_exists('example', $schema)) {
        return $schema['example'];
    }

    $ref = trim((string) ($schema['$ref'] ?? ''));
    if ($ref !== '' && str_starts_with($ref, '#/components/schemas/')) {
        $schemaName = substr($ref, strlen('#/components/schemas/'));
        if (!is_string($schemaName) || $schemaName === '' || isset($stack[$schemaName])) {
            return (object) [];
        }

        $stack[$schemaName] = true;
        $example = app_project_output_openapi_example_from_schema($components[$schemaName] ?? null, $components, $stack);
        unset($stack[$schemaName]);

        return $example;
    }

    $type = trim((string) ($schema['type'] ?? ''));
    return match ($type) {
        'object' => (function () use ($schema, $components, &$stack): mixed {
            $properties = $schema['properties'] ?? null;
            if (!is_array($properties) || $properties === []) {
                return (object) [];
            }

            $example = [];
            foreach ($properties as $propertyName => $propertySchema) {
                if (!is_string($propertyName)) {
                    continue;
                }

                $example[$propertyName] = app_project_output_openapi_example_from_schema(
                    $propertySchema,
                    $components,
                    $stack,
                );
            }

            return $example === [] ? (object) [] : $example;
        })(),
        'array' => [
            app_project_output_openapi_example_from_schema($schema['items'] ?? null, $components, $stack),
        ],
        'integer' => 0,
        'number' => 0,
        'boolean' => false,
        'string' => match (trim((string) ($schema['format'] ?? ''))) {
            'date' => '2026-05-25',
            'date-time' => '2026-05-25T00:00:00+09:00',
            'binary' => '',
            default => 'string',
        },
        default => null,
    };
}

/**
 * @param array{
 *     auth_policy:array{
 *         strategy_key:string
 *     }
 * } $item
 * @return array{
 *     properties:array<string,array<string,mixed>>,
 *     required:list<string>
 * }
 */
function app_project_output_openapi_auth_request_contract(array $item): array
{
    $strategy = trim((string) ($item['auth_policy']['strategy_key'] ?? ''));
    $properties = [];
    $required = [];

    if (in_array($strategy, ['project-token', 'project-token-or-get-function'], true)) {
        $properties['TOKEN'] = [
            'type' => 'string',
            'description' => 'Project token body field.',
            'example' => $strategy === 'project-token' ? 'project-token' : 'optional-project-token',
        ];
        if ($strategy === 'project-token') {
            $required[] = 'TOKEN';
        }
    }

    if ($strategy === 'login-cookie-token') {
        $properties['LOGIN_COOKIE_TOKEN'] = [
            'type' => 'string',
            'description' => 'Login cookie token body field.',
            'example' => 'login-cookie-token',
        ];
        $required[] = 'LOGIN_COOKIE_TOKEN';
    }

    return [
        'properties' => $properties,
        'required' => $required,
    ];
}

/**
 * @param array{
 *     source_name:string,
 *     function_name:string,
 *     auth_policy:array{
 *         strategy_key:string
 *     },
 *     steps:list<array{
 *         input_kind:string,
 *         object_param_name:string,
 *         object_class:string,
 *         parameter_names:list<string>
 *     }>
 * } $item
 * @return array<string,mixed>
 */
function app_project_output_openapi_request_schema(array $item): array
{
    $properties = [];
    $required = [];
    $authRequestContract = app_project_output_openapi_auth_request_contract($item);
    $properties = $authRequestContract['properties'];
    $required = $authRequestContract['required'];

    $step = $item['steps'][0] ?? null;
    if (is_array($step)) {
        if (($step['input_kind'] ?? '') === 'object') {
            $objectParamName = trim((string) ($step['object_param_name'] ?? ''));
            $objectClass = trim((string) ($step['object_class'] ?? ''));
            if ($objectParamName !== '') {
                $properties[$objectParamName] = $objectClass !== ''
                    ? ['$ref' => '#/components/schemas/' . $objectClass]
                    : [
                        'type' => 'object',
                        'properties' => (object) [],
                        'additionalProperties' => true,
                    ];
                $required[] = $objectParamName;
            }
        } else {
            foreach (($step['parameter_names'] ?? []) as $parameterName) {
                if (!is_string($parameterName)) {
                    continue;
                }

                $normalizedParameterName = trim($parameterName);
                if ($normalizedParameterName === '') {
                    continue;
                }

                $properties[$normalizedParameterName] = [
                    'type' => 'string',
                    'example' => 'string',
                ];
                $required[] = $normalizedParameterName;
            }
        }
    }

    $required = array_values(array_unique($required));
    $schema = [
        'type' => 'object',
        'properties' => app_project_output_openapi_object_map($properties),
    ];
    if ($required !== []) {
        $schema['required'] = $required;
    }
    if ($properties === []) {
        $schema['additionalProperties'] = true;
    }

    return $schema;
}

/**
 * @param array{
 *     response_property_type:string,
 *     steps:list<array{
 *         action:string,
 *         response_key:string,
 *         response_mode:string,
 *         data_class:string
 *     }>
 * } $item
 * @return array<string,mixed>
 */
function app_project_output_openapi_success_response_schema(array $item): array
{
    $properties = [
        '_status' => [
            'type' => 'string',
            'example' => 'OK',
        ],
        'Message' => [
            'type' => 'string',
            'example' => '',
        ],
    ];

    $step = $item['steps'][0] ?? null;
    if (is_array($step)) {
        $responseKey = trim((string) ($step['response_key'] ?? ''));
        $responseMode = trim((string) ($step['response_mode'] ?? ''));
        $dataClass = trim((string) ($step['data_class'] ?? ''));

        if ($responseKey !== '') {
            if ($responseMode === 'insert-id-single') {
                $properties[$responseKey] = [
                    'type' => 'integer',
                    'example' => 1,
                ];
            } elseif ($responseMode === 'direct-result' && $dataClass !== '') {
                $properties[$responseKey] = ($step['action'] ?? '') === 'select-list'
                    ? [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/' . $dataClass,
                        ],
                    ]
                    : [
                        '$ref' => '#/components/schemas/' . $dataClass,
                    ];
            }
        }
    }

    return [
        'type' => 'object',
        'properties' => app_project_output_openapi_object_map($properties),
    ];
}

/**
 * @return array<string,mixed>
 */
function app_project_output_openapi_error_response_schema(): array
{
    return [
        'type' => 'object',
        'properties' => app_project_output_openapi_object_map([
            '_status' => [
                'type' => 'string',
                'example' => 'NG',
            ],
            'Message' => [
                'type' => 'string',
                'example' => 'error',
            ],
        ]),
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     definition:array{
 *         source_output_key:string,
 *         name:string,
 *         proxy_base_url:string
 *     },
 *     plan:array{
 *         function_count:int,
 *         unresolved_function_count:int,
 *         unresolved_auth_count:int,
 *         items:list<array<string,mixed>>
 *     },
 *     source_entities?:array<string,array{
 *         data_class:string,
 *         data_properties:list<string>
 *     }>,
 *     proxy_items:list<array{
 *         source_name:string,
 *         function_name:string,
 *         display_name:string,
 *         auth_policy:array{
 *             strategy_key:string,
 *             summary:string
 *         },
 *         endpoint_filename:string,
 *         response_property_type:string,
 *         steps:list<array{
 *             action:string,
 *             input_kind:string,
 *             object_param_name:string,
 *             object_class:string,
 *             data_class:string,
 *             parameter_names:list<string>,
 *             response_key:string,
 *             response_mode:string
 *         }>
 *     }>
 * } $context
 * @param list<array{
 *     name:string,
 *     inherit_parent_data_class_name:string,
 *     fields:list<array{
 *         name:string,
 *         datatype:string,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }>
 * }> $snapshotItems
 * @return array<string,mixed>
 */
function app_project_output_openapi_document(array $context, array $snapshotItems): array
{
    $definition = is_array($context['definition'] ?? null) ? $context['definition'] : [];
    $snapshotIndex = app_project_output_openapi_data_class_index($snapshotItems);
    $fallbackPropertiesByClass = app_project_output_openapi_fallback_properties_by_class($context);
    $components = [];
    $stack = [];

    foreach (($context['proxy_items'] ?? []) as $item) {
        if (!is_array($item)) {
            continue;
        }

        $step = $item['steps'][0] ?? null;
        if (!is_array($step)) {
            continue;
        }

        $objectClass = trim((string) ($step['object_class'] ?? ''));
        if ($objectClass !== '') {
            app_project_output_openapi_register_data_class_schema(
                $objectClass,
                $snapshotIndex,
                $fallbackPropertiesByClass,
                $components,
                $stack,
            );
        }

        $dataClass = trim((string) ($step['data_class'] ?? ''));
        if ($dataClass !== '') {
            app_project_output_openapi_register_data_class_schema(
                $dataClass,
                $snapshotIndex,
                $fallbackPropertiesByClass,
                $components,
                $stack,
            );
        }
    }

    $paths = [];
    foreach (($context['proxy_items'] ?? []) as $item) {
        if (!is_array($item)) {
            continue;
        }

        $endpointFilename = trim((string) ($item['endpoint_filename'] ?? ''));
        if ($endpointFilename === '') {
            continue;
        }

        $requestSchema = app_project_output_openapi_request_schema($item);
        $successSchema = app_project_output_openapi_success_response_schema($item);
        $errorSchema = app_project_output_openapi_error_response_schema();
        $exampleStack = [];
        $requestExample = app_project_output_openapi_example_from_schema($requestSchema, $components, $exampleStack);
        $exampleStack = [];
        $successExample = app_project_output_openapi_example_from_schema($successSchema, $components, $exampleStack);
        $exampleStack = [];
        $errorExample = app_project_output_openapi_example_from_schema($errorSchema, $components, $exampleStack);
        $step = $item['steps'][0] ?? null;
        $sourceName = trim((string) ($item['source_name'] ?? ''));
        $functionName = trim((string) ($item['function_name'] ?? ''));

        $descriptionLines = [
            'Generated from single-function proxy target metadata.',
        ];
        if ($sourceName !== '' && $functionName !== '') {
            $descriptionLines[] = 'Source function: `' . $sourceName . '.' . $functionName . '`.';
        }
        $authSummary = trim((string) ($item['auth_policy']['summary'] ?? ''));
        if ($authSummary !== '') {
            $descriptionLines[] = 'Auth policy: ' . $authSummary;
        }

        $paths['/' . $endpointFilename] = [
            'post' => [
                'operationId' => $sourceName !== '' && $functionName !== ''
                    ? ($sourceName . '_' . $functionName)
                    : preg_replace('/[^A-Za-z0-9_]+/', '_', $endpointFilename),
                'tags' => $sourceName !== '' ? [$sourceName] : [],
                'summary' => trim((string) ($item['display_name'] ?? '')) !== ''
                    ? trim((string) ($item['display_name'] ?? ''))
                    : ($sourceName . '.' . $functionName),
                'description' => implode("\n\n", $descriptionLines),
                'x-mtool' => [
                    'source_name' => $sourceName,
                    'function_name' => $functionName,
                    'endpoint_filename' => $endpointFilename,
                    'auth_strategy' => trim((string) ($item['auth_policy']['strategy_key'] ?? '')),
                    'input_kind' => is_array($step) ? trim((string) ($step['input_kind'] ?? '')) : '',
                    'response_mode' => is_array($step) ? trim((string) ($step['response_mode'] ?? '')) : '',
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => $requestSchema,
                            'example' => $requestExample,
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful proxy response.',
                        'content' => [
                            'application/json' => [
                                'schema' => $successSchema,
                                'example' => $successExample,
                            ],
                        ],
                    ],
                    '500' => [
                        'description' => 'Runtime or validation error response.',
                        'content' => [
                            'application/json' => [
                                'schema' => $errorSchema,
                                'example' => $errorExample,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    ksort($paths);

    $tags = [];
    foreach (array_keys($components) as $_schemaName) {
        // keep components ordered deterministically via ksort below
    }

    $tagNames = [];
    foreach (($context['proxy_items'] ?? []) as $item) {
        if (!is_array($item)) {
            continue;
        }

        $sourceName = trim((string) ($item['source_name'] ?? ''));
        if ($sourceName === '' || isset($tagNames[$sourceName])) {
            continue;
        }

        $tagNames[$sourceName] = true;
        $tags[] = [
            'name' => $sourceName,
            'description' => $sourceName . ' single-function proxy endpoints.',
        ];
    }

    ksort($components);

    $servers = [];
    $proxyBaseUrl = trim((string) ($definition['proxy_base_url'] ?? ''));
    if ($proxyBaseUrl !== '') {
        $servers[] = [
            'url' => $proxyBaseUrl,
        ];
    }

    return [
        'openapi' => '3.0.3',
        'info' => [
            'title' => trim((string) ($definition['name'] ?? '')) !== ''
                ? trim((string) ($definition['name'] ?? ''))
                : ((string) ($context['project_key'] ?? 'Project') . ' OpenAPI'),
            'version' => '1.0.0',
            'description' => 'Generated from current single-function proxy target metadata.',
        ],
        'servers' => $servers,
        'tags' => $tags,
        'paths' => app_project_output_openapi_object_map($paths),
        'components' => [
            'schemas' => app_project_output_openapi_object_map($components),
        ],
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     definition:array{
 *         name:string,
 *         proxy_base_url:string
 *     },
 *     plan:array{
 *         function_count:int,
 *         unresolved_function_count:int,
 *         unresolved_auth_count:int,
 *         items:list<array<string,mixed>>
 *     },
 *     proxy_items:list<array{
 *         source_name:string,
 *         function_name:string,
 *         endpoint_filename:string,
 *         auth_policy:array{
 *             strategy_key:string
 *         },
 *         steps:list<array{
 *             input_kind:string,
 *             response_mode:string
 *         }>
 *     }>
 * } $context
 * @return array<string,mixed>
 */
function app_project_output_openapi_build_plan_payload(array $context): array
{
    $operations = [];

    foreach (($context['proxy_items'] ?? []) as $item) {
        if (!is_array($item)) {
            continue;
        }

        $step = $item['steps'][0] ?? null;
        $operations[] = [
            'source_name' => trim((string) ($item['source_name'] ?? '')),
            'function_name' => trim((string) ($item['function_name'] ?? '')),
            'endpoint_filename' => trim((string) ($item['endpoint_filename'] ?? '')),
            'auth_strategy' => trim((string) ($item['auth_policy']['strategy_key'] ?? '')),
            'input_kind' => is_array($step) ? trim((string) ($step['input_kind'] ?? '')) : '',
            'response_mode' => is_array($step) ? trim((string) ($step['response_mode'] ?? '')) : '',
        ];
    }

    return [
        'schema_version' => 1,
        'artifact_type' => 'openapi-build-plan',
        'project_key' => (string) ($context['project_key'] ?? ''),
        'source_output_key' => (string) ($context['source_output_key'] ?? ''),
        'source_output_name' => (string) ($context['definition']['name'] ?? ''),
        'proxy_base_url' => (string) ($context['definition']['proxy_base_url'] ?? ''),
        'function_count' => (int) ($context['plan']['function_count'] ?? 0),
        'unresolved_function_count' => (int) ($context['plan']['unresolved_function_count'] ?? 0),
        'unresolved_auth_count' => (int) ($context['plan']['unresolved_auth_count'] ?? 0),
        'operations' => $operations,
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     definition:array{
 *         name:string,
 *         proxy_base_url:string
 *     },
 *     plan:array{
 *         function_count:int
 *     }
 * } $context
 */
function app_project_output_openapi_readme_text(array $context): string
{
    $projectKey = trim((string) ($context['project_key'] ?? ''));
    $sourceOutputKey = trim((string) ($context['source_output_key'] ?? ''));
    $sourceOutputName = trim((string) ($context['definition']['name'] ?? ''));
    $proxyBaseUrl = trim((string) ($context['definition']['proxy_base_url'] ?? ''));
    $functionCount = (int) ($context['plan']['function_count'] ?? 0);
    $viewerPath = '/runs/swagger/' . rawurlencode($projectKey) . '?source_output_key=' . rawurlencode($sourceOutputKey);

    return <<<TEXT
# OpenAPI Artifact

- source output: `{$projectKey}/{$sourceOutputKey}`
- display name: `{$sourceOutputName}`
- function count: `{$functionCount}`
- proxy base URL: `{$proxyBaseUrl}`

Files:

- `openapi.json` contains the generated OpenAPI 3.0.3 document.
- `build-plan.json` records the single-function proxy targets used to emit the spec.

Viewer:

- Lab/Admin viewer route: `{$viewerPath}`

Notes:

- This artifact models the generated single-function proxy runtime contract.
- Request bodies stay JSON `POST`.
- Response envelopes always include `_status` and `Message`.
TEXT;
}

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     definition:array{
 *         source_output_key:string,
 *         name:string,
 *         proxy_base_url:string
 *     },
 *     plan:array{
 *         function_count:int,
 *         unresolved_function_count:int,
 *         unresolved_auth_count:int,
 *         items:list<array<string,mixed>>
 *     },
 *     source_entities?:array<string,array{
 *         data_class:string,
 *         data_properties:list<string>
 *     }>,
 *     proxy_items:list<array<string,mixed>>
 * } $context
 * @param list<array{
 *     name:string,
 *     inherit_parent_data_class_name:string,
 *     fields:list<array{
 *         name:string,
 *         datatype:string,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }>
 * }> $snapshotItems
 * @return array{
 *     ok:bool,
 *     files:list<array{
 *         relative_path:string,
 *         contents:string
 *     }>,
 *     error:string
 * }
 */
function app_project_output_openapi_build_emitted_files(array $context, array $snapshotItems): array
{
    try {
        $document = app_project_output_openapi_document($context, $snapshotItems);
        $buildPlan = app_project_output_openapi_build_plan_payload($context);

        return [
            'ok' => true,
            'files' => [
                [
                    'relative_path' => 'README.md',
                    'contents' => app_project_output_openapi_readme_text($context) . PHP_EOL,
                ],
                [
                    'relative_path' => 'build-plan.json',
                    'contents' => app_project_output_openapi_json_text($buildPlan),
                ],
                [
                    'relative_path' => 'openapi.json',
                    'contents' => app_project_output_openapi_json_text($document),
                ],
            ],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'files' => [],
            'error' => $throwable->getMessage(),
        ];
    }
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
function app_project_output_prepare_openapi_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_openapi_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の OpenAPI artifact strategy です。',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'json') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'OpenAPI artifact は現在 json のみ対応です。',
        ];
    }

    $contextResult = app_project_output_single_proxy_build_context($app, $projectKey, $definition);
    if (!$contextResult['ok'] || !is_array($contextResult['context'] ?? null)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $contextResult['error'] !== '' ? $contextResult['error'] : 'OpenAPI build context を取得できませんでした。',
        ];
    }

    $snapshotResult = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    if (!$snapshotResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical data class metadata の読み込みに失敗しました: ' . $snapshotResult['error'],
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_openapi_default_runtime_source_relative_path(
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
    $emittedFileResult = app_project_output_openapi_build_emitted_files(
        $contextResult['context'],
        $snapshotResult['items'],
    );
    if (!$emittedFileResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $emittedFileResult['error'],
        ];
    }

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($emittedFileResult['files'] as $file) {
            app_project_output_write_text_file(
                $runtimeSourceRoot . '/' . $file['relative_path'],
                $file['contents'],
            );
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'OpenAPI staging tree の作成に失敗しました: ' . $throwable->getMessage(),
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
