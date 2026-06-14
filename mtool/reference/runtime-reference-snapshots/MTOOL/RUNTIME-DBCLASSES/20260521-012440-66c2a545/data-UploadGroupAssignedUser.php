<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-UploadGroupAssignedUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-UploadGroupAssignedUser.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-UploadGroupAssignedUser.php` and extend `UploadGroupAssignedUserDataBase` for project-specific customizations.

    class UploadGroupAssignedUserData extends UploadGroupAssignedUserDataBase
    {
    }
}

?>
