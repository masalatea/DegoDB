<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-TestPatternBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-TestPattern.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-TestPattern.php` and extend `TestPatternDBAccessBase` for project-specific customizations.

    class TestPatternDBAccess extends TestPatternDBAccessBase
    {
    }
}

?>
