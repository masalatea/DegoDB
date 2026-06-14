<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-TestPatternSelectionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-TestPatternSelection.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-TestPatternSelection.php` and extend `TestPatternSelectionDataBase` for project-specific customizations.

    class TestPatternSelectionData extends TestPatternSelectionDataBase
    {
    }
}

?>
