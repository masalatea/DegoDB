<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-chattopicAttachmentBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-chattopicAttachment.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-chattopicAttachment.php` and extend `chattopicAttachmentDataBase` for project-specific customizations.

    class chattopicAttachmentData extends chattopicAttachmentDataBase
    {
    }
}

?>
