<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-dataclassfieldsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-dataclassfields.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-dataclassfields.php` and extend `dataclassfieldsDBAccessBase` for project-specific customizations.

    class dataclassfieldsDBAccessLegacy extends dataclassfieldsDBAccessBase
    {
    }
}

?>
