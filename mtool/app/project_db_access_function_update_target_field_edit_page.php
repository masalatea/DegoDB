<?php

declare(strict_types=1);

require_once __DIR__ . '/project_db_access_function_insert_update_target_field_common.php';

function app_render_project_db_access_function_update_target_field_edit_page(array $app, array $request): void
{
    app_render_project_db_access_function_insert_update_target_field_edit_page($app, $request, 'update');
}
