<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-LanguageResourceGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-LanguageResourceGroup.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-LanguageResourceGroup.php` and extend `LanguageResourceGroupDataBase` for project-specific customizations.

    class LanguageResourceGroupData extends LanguageResourceGroupDataBase
    {
    }
}

?>
