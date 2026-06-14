<?php
function output_json_parameter_by_adding_indent($json_parameter, $add_indent)
{
	$this_parameter_list = explode("\n", trim($json_parameter));
	for($i = 0 ; $i < count($this_parameter_list); $i++) {
		$this_parameter = $this_parameter_list[$i];
		if ($i > 0) {
			print $add_indent;
		}
		print htmlspecialchars($this_parameter);
		
		if ($i < count($this_parameter_list) - 1) {
			print "\n";
		}
	}
}
?>
