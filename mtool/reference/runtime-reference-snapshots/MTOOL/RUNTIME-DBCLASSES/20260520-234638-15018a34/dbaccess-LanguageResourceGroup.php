<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-LanguageResourceGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-LanguageResourceGroup.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-LanguageResourceGroup.php` and extend `LanguageResourceGroupDBAccessBase` for project-specific customizations.

    class LanguageResourceGroupDBAccess extends LanguageResourceGroupDBAccessBase
    {
    }
}

?>
