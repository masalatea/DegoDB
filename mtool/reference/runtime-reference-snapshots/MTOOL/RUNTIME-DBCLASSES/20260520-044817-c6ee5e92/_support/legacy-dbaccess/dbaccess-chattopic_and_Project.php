<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-chattopic_and_ProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-chattopic_and_Project.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-chattopic_and_Project.php` and extend `chattopic_and_ProjectDBAccessBase` for project-specific customizations.

    class chattopic_and_ProjectDBAccessLegacy extends chattopic_and_ProjectDBAccessBase
    {
    }
}

?>
