<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DropboxBaseFolderUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DropboxBaseFolderUser.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DropboxBaseFolderUser.php` and extend `DropboxBaseFolderUserDataBase` for project-specific customizations.

    class DropboxBaseFolderUserData extends DropboxBaseFolderUserDataBase
    {
    }
}

?>
