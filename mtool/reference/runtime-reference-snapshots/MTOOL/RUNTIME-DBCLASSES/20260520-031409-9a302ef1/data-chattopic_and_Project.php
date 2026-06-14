<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-chattopic_and_ProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-chattopic_and_Project.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-chattopic_and_Project.php` and extend `chattopic_and_ProjectDataBase` for project-specific customizations.

    class chattopic_and_ProjectData extends chattopic_and_ProjectDataBase
    {
    }
}

?>
