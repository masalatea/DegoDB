<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-TestConditionSelectionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-TestConditionSelection.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-TestConditionSelection.php` and extend `TestConditionSelectionDataBase` for project-specific customizations.

    class TestConditionSelectionData extends TestConditionSelectionDataBase
    {
	public $AlreadyCheckedWhenEdit = false;
    }
}

?>
