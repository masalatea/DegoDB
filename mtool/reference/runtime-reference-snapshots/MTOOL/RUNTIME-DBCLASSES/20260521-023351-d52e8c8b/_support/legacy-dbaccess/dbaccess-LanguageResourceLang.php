<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-LanguageResourceLangBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-LanguageResourceLang.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-LanguageResourceLang.php` and extend `LanguageResourceLangDBAccessBase` for project-specific customizations.

    class LanguageResourceLangDBAccessLegacy extends LanguageResourceLangDBAccessBase
    {
    }
}

?>
