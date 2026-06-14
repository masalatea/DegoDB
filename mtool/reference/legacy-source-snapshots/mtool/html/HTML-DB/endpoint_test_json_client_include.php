<?php

function output_mtool_json_test_form($form_url, $ProjectPID, $BuildSourceFuncCache, $json_parameter)
{
	global $LANG_ENGLISH;
	global $LANG_JAPANESE;
	global $lang;
	?>
    <form action="<?php print $form_url; ?>" method="post" id="orderupdateform">
    
    <?php
	$placeholderArray = array($LANG_ENGLISH=>"Please input JSON Parameter", $LANG_JAPANESE=>"JSONパラメータを入力して下さい");
	?>
    <textarea name="POST_JSON" id="POST_JSON" class="form-control" placeholder="<?php print htmlspecialchars($placeholderArray[$lang]); ?>" rows="10"><?php print htmlspecialchars($json_parameter); ?></textarea>

    <input name="DO_TEST_BY_JQUERY" id="DO_TEST_BY_JQUERY" type="button" value="Do Test by jQuery directly"><br>
    <input name="DO_TEST" id="DO_TEST" type="button" value="Do Test via Server by PHP"> 
    (Show JSON with Format. Show Debug Info)
    
    <script>
$(function() {
	$("#DO_TEST").click(function() {
		$("#testresultarea").hide();
		$("#testingmessagearea").show();
		
		var POST_JSON_Value = $("#POST_JSON").val();
		
		jQuery.ajax(
			"endpoint_test_json_ajax.php",{
				type: "POST",
				dataType: 'html',
				data: {
					"ProjectPID": "<?php print htmlspecialchars($ProjectPID); ?>",
					"POST_JSON": POST_JSON_Value,
					"ProxyURL": "<?php print htmlspecialchars($BuildSourceFuncCache->ProxyURL); ?>"
				},
				success: function(html_result){
					$("#testresult").html(html_result);
					$("#testresultarea").show();
					$("#testingmessagearea").hide();
				},
				error : function() {
					alert("Internal Error while test.");
				},
				complete: function() {
				}
			}
		);
	});
});
$(function() {
	$("#DO_TEST_BY_JQUERY").click(function() {
		$("#testresultarea").hide();
		$("#testingmessagearea").show();
		
		var POST_JSON_Value = $("#POST_JSON").val();
		
		jQuery.ajax(
			"<?php print $BuildSourceFuncCache->ProxyURL; ?>",{
				type: "POST",
				dataType: 'json',
				contentType: 'application/json',
				data: POST_JSON_Value,
				success: function(json_result){
					if (json_result._status == "OK") {
						$("#testresult").html("<pre>" + JSON.stringify(json_result) + "</pre>");
					} else {
						$("#testresult").html("<p>Result: " + json_result._status + "</p>" +
											  "<p> " + json_result.Message + "</p>");
					}
					$("#testresultarea").show();
					$("#testingmessagearea").hide();
				},
				error : function() {
					alert("Internal Error while test.");
				},
				complete: function() {
				}
			}
		);
	});
});
</script>

    </form>
    <div id="testingmessagearea" style="display:none">
    <h5>Testing...</h5>
    </div>
    <div id="testresultarea" style="display:none">
    <h5>Done</h5>
    <div id="testresult"></div>
    </div>
<?php
}
?>
