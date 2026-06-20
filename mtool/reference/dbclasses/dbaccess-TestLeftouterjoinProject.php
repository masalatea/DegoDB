<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-TestLeftouterjoinProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-TestLeftouterjoinProject.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-TestLeftouterjoinProject.php` and extend `TestLeftouterjoinProjectDBAccessBase` for project-specific customizations.

    class TestLeftouterjoinProjectDBAccess extends TestLeftouterjoinProjectDBAccessBase
    {
    }
}

?>
