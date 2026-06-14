<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-SpecBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Spec.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Spec.php` and extend `SpecDataBase` for project-specific customizations.

    class SpecData extends SpecDataBase
    {
    }
}

?>
