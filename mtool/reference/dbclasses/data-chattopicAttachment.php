<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ChattopicAttachmentBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ChattopicAttachment.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ChattopicAttachment.php` and extend `ChattopicAttachmentDataBase` for project-specific customizations.

    class ChattopicAttachmentData extends ChattopicAttachmentDataBase
    {
    }
}

?>
