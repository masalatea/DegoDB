<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DataclassfieldsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Dataclassfields.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Dataclassfields.php` and extend `DataclassfieldsDBAccessBase` for project-specific customizations.

    class DataclassfieldsDBAccess extends DataclassfieldsDBAccessBase
    {
    }
}

?>
