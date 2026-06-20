<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DbtableBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Dbtable.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Dbtable.php` and extend `DbtableDataBase` for project-specific customizations.

    class DbtableData extends DbtableDataBase
    {
    }
}

?>
