<?php

function print_alternative_header_tag($target_lang, $alternative_url)
{
?><link rel="alternate" hreflang="<?php print htmlspecialchars($target_lang); ?>" href="<?php print $alternative_url; ?>" />
<?php
}
?>