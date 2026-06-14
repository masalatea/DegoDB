<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-chattopicBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-chattopic.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-chattopic.php` and extend `chattopicDBAccessBase` for project-specific customizations.

    class chattopicDBAccessLegacy extends chattopicDBAccessBase
    {
    }
}

?>
