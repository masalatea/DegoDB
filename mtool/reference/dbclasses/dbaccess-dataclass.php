<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DataclassBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Dataclass.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Dataclass.php` and extend `DataclassDBAccessBase` for project-specific customizations.

    class DataclassDBAccess extends DataclassDBAccessBase
    {
    }
}

?>
