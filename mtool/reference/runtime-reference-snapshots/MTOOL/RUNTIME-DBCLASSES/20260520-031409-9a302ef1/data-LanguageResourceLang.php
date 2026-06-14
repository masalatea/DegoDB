<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-LanguageResourceLangBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-LanguageResourceLang.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-LanguageResourceLang.php` and extend `LanguageResourceLangDataBase` for project-specific customizations.

    class LanguageResourceLangData extends LanguageResourceLangDataBase
    {
    }
}

?>
