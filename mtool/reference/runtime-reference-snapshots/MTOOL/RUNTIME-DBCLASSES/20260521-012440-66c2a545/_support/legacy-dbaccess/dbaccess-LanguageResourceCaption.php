<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-LanguageResourceCaptionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-LanguageResourceCaption.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-LanguageResourceCaption.php` and extend `LanguageResourceCaptionDBAccessBase` for project-specific customizations.

    class LanguageResourceCaptionDBAccessLegacy extends LanguageResourceCaptionDBAccessBase
    {
    }
}

?>
