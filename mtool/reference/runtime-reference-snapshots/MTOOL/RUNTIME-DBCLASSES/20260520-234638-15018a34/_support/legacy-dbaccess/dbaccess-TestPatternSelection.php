<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-TestPatternSelectionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-TestPatternSelection.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-TestPatternSelection.php` and extend `TestPatternSelectionDBAccessBase` for project-specific customizations.

    class TestPatternSelectionDBAccessLegacy extends TestPatternSelectionDBAccessBase
    {
    }
}

?>
