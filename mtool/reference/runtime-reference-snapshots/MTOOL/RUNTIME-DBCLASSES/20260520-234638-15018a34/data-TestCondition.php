<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-TestConditionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-TestCondition.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-TestCondition.php` and extend `TestConditionDataBase` for project-specific customizations.

    class TestConditionData extends TestConditionDataBase
    {
    }
}

?>
