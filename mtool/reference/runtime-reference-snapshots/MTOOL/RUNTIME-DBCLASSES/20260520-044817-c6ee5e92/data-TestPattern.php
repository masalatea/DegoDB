<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-TestPatternBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-TestPattern.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-TestPattern.php` and extend `TestPatternDataBase` for project-specific customizations.

    class TestPatternData extends TestPatternDataBase
    {
	public $TestPatternSelectionList = array();
    }
}

?>
