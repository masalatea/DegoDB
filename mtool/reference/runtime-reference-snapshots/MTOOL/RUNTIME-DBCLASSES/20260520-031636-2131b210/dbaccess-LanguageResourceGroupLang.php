<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-LanguageResourceGroupLangBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-LanguageResourceGroupLang.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-LanguageResourceGroupLang.php` and extend `LanguageResourceGroupLangDBAccessBase` for project-specific customizations.

    class LanguageResourceGroupLangDBAccess extends LanguageResourceGroupLangDBAccessBase
    {
    }
}

?>
