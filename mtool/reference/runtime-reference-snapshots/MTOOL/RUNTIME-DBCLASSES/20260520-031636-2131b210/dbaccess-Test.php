<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-TestBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Test.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Test.php` and extend `TestDBAccessBase` for project-specific customizations.

    class TestDBAccess extends TestDBAccessBase
    {
    }
}

?>
