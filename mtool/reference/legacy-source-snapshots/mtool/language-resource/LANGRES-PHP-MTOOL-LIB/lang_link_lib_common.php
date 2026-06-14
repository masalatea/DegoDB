<?php
	function output_language_server_link($server_name, $http_prefix, $lang_caption) {
		$url = $http_prefix . $server_name;
		$now_this_server = ($server_name == $_SERVER["SERVER_NAME"]);

		if ($now_this_server) {
			?>
			[<?php print $lang_caption; ?>]
			<?php
		} else {
			?>
			[<a href="<?php print $url; ?>"><?php print $lang_caption; ?></a>]
			<?php
		}
	}
?>