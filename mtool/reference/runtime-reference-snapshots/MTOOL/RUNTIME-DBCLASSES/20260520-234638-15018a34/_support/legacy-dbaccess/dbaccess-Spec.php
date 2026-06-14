<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-SpecBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Spec.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-Spec.php` and extend `SpecDBAccessBase` for project-specific customizations.

    class SpecDBAccessLegacy extends SpecDBAccessBase
    {
    }
}

?>
