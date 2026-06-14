<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-dataclassfieldsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-dataclassfields.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-dataclassfields.php` and extend `dataclassfieldsDataBase` for project-specific customizations.

    class dataclassfieldsData extends dataclassfieldsDataBase
    {
    }
}

?>
