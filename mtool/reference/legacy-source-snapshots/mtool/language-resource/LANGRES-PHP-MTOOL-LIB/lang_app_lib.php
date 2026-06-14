<?php
	function update_lang_based_on_iOS_lang_by_parameter()
	{
		$app_lang = GetParam("app_lang");
		update_lang_based_on_iOS_lang($app_lang);
	}
	function update_lang_based_on_iOS_lang($app_lang)
	{
		global $LANG_ENGLISH;
		global $LANG_TRAITIONAL_CHINESE;
		global $LANG_CHINESE;
		global $LANG_KOREAN;
		global $LANG_SPANISH;
		global $LANG_PORTUGUESE;
		global $LANG_FRENCH;
		global $LANG_HINDI;
		global $lang;
		
		if ($app_lang != "") {
			if (preg_match("/^ja/i", $app_lang)) {
				// Japanese
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is Japanese");
			} else if (preg_match("/^en/i", $app_lang)) {
				$lang = $LANG_ENGLISH;
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is English");
			} else if (preg_match("/^zh-hant/i", $app_lang)) {
				$lang = $LANG_TRAITIONAL_CHINESE;
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is Traditional Chinese");
			} else if (preg_match("/^zh/i", $app_lang)) {
				$lang = $LANG_CHINESE;
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is Simplified Chinese");
			} else if (preg_match("/^ko/i", $app_lang)) {
				$lang = $LANG_KOREAN;
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is Korean");
			} else if (preg_match("/^es/i", $app_lang)) {
				$lang = $LANG_SPANISH;
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is Spanish");
			} else if (preg_match("/^pt/i", $app_lang)) {
				$lang = $LANG_PORTUGUESE;
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is Portuguese");
			} else if (preg_match("/^fr/i", $app_lang)) {
				$lang = $LANG_FRENCH;
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is French");
			} else if (preg_match("/^hi/i", $app_lang)) {
				$lang = $LANG_HINDI;
				error_log("[FYI] Lang from iOS Device: " . $app_lang . " is Hindi");
			} else {
				// Unknown
				error_log("Unknown Lang from iOS Device: " . $app_lang);
				$lang = $LANG_ENGLISH;
			}
		}
	}
?>