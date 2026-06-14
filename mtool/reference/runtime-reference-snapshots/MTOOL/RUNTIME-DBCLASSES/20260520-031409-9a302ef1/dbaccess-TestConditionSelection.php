<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-TestConditionSelectionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-TestConditionSelection.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-TestConditionSelection.php` and extend `TestConditionSelectionDBAccessBase` for project-specific customizations.

    class TestConditionSelectionDBAccess extends TestConditionSelectionDBAccessBase
    {
    }
}

?>
