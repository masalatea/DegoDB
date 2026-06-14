<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-TestConditionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-TestCondition.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-TestCondition.php` and extend `TestConditionDBAccessBase` for project-specific customizations.

    class TestConditionDBAccess extends TestConditionDBAccessBase
    {
    }
}

?>
