<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-dataclassBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-dataclass.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-dataclass.php` and extend `dataclassDBAccessBase` for project-specific customizations.

    class dataclassDBAccess extends dataclassDBAccessBase
    {
    }
}

?>
