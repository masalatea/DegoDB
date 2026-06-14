<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-LanguageResourceBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-LanguageResource.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-LanguageResource.php` and extend `LanguageResourceDBAccessBase` for project-specific customizations.

    class LanguageResourceDBAccessLegacy extends LanguageResourceDBAccessBase
    {
    }
}

?>
