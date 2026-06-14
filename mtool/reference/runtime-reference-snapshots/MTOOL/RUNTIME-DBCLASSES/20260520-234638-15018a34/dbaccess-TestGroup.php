<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-TestGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-TestGroup.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-TestGroup.php` and extend `TestGroupDBAccessBase` for project-specific customizations.

    class TestGroupDBAccess extends TestGroupDBAccessBase
    {
    }
}

?>
