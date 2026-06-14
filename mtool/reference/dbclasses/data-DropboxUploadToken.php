<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DropboxUploadTokenBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DropboxUploadToken.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DropboxUploadToken.php` and extend `DropboxUploadTokenDataBase` for project-specific customizations.

    class DropboxUploadTokenData extends DropboxUploadTokenDataBase
    {
    }
}

?>
