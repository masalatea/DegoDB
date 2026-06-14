<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-UploadGroupAssignedUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-UploadGroupAssignedUser.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-UploadGroupAssignedUser.php` and extend `UploadGroupAssignedUserDBAccessBase` for project-specific customizations.

    class UploadGroupAssignedUserDBAccessLegacy extends UploadGroupAssignedUserDBAccessBase
    {
    }
}

?>
