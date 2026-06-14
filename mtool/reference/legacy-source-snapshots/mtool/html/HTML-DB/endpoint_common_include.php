	<h4>Endpoint URL</h4>
    <pre><?php
	print htmlspecialchars($BuildSourceFuncCache->ProxyURL);
    ?></pre>
	<h4>Parameter</h4>
    <pre><?php
	print htmlspecialchars($BuildSourceFuncCache->ProxyParameterForJquery);
    ?></pre>
    <?php
	?>
	<h4>Result</h4>
    <pre><?php
	print htmlspecialchars($BuildSourceFuncCache->ProxyResultFormatForJquery);
    ?></pre>
    
    <h4>Test</h4>
    
    <?php
    include("endpoint_test_json_client_include.php");
	output_mtool_json_test_form($_SERVER['SCRIPT_NAME'], $TargetProjectPID, $BuildSourceFuncCache, $BuildSourceFuncCache->ProxyParameterForJquery);
	?>
    
    <br>
	<br>
	<br>
    
<script>
$(function(){
	$("#accordion").accordion({
		collapsible: true,
		active: true
	});
});
</script>
      
<div id="accordion">
    <h3>Sample Code for jQuery</h3>
    <div>
    <pre>jQuery.ajax(<br>    &quot;<?php print htmlspecialchars($BuildSourceFuncCache->ProxyURL); ?>&quot;,{<br>        type: &quot;POST&quot;,<br>        dataType: 'json',<br>        contentType: 'application/json',<br>        data: JSON.stringify(<?php output_json_parameter_by_adding_indent($BuildSourceFuncCache->ProxyParameterExampleForJquery, "        "); ?>),<br>        success: function(json_result){<br>            if (json_result._status == &quot;OK&quot;) {<br>                // Success<br>            } else {<br>                // Failed<br>            }<br>        },<br>        error : function() {<br>            // Internal Error<br>        },<br>        complete: function() {<br>        }<br>    }<br>);</pre>
    </div>
    
    <h3>Sample Code for PHP</h3>
    <div>
    <pre>$ch = curl_init();<br>curl_setopt_array($ch, array(<br>    CURLOPT_URL =&gt; "<?php print htmlspecialchars($BuildSourceFuncCache->ProxyURL); ?>",<br>    CURLOPT_POST =&gt; true,<br>    CURLOPT_HTTPHEADER =&gt; array(<br>        'Content-Type: application/json'<br>        ),<br>    CURLOPT_POSTFIELDS =&gt; json_encode(<?php output_json_parameter_by_adding_indent($BuildSourceFuncCache->ProxyParameterExampleForPHP, "    "); ?>),<br>    CURLOPT_RETURNTRANSFER =&gt; true<br>));<br>$response = curl_exec($ch);<br>$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);<br>if ($http_code != 200) {<br>    // Error Occured<br>} else {<br>    // Success
    $json_response = json_decode($response);
    if ($json_response-&gt;_status == &quot;OK&quot;) {<br>        // Success. Result is in: $json_response-&gt;Result<br>    } else {<br>        print &quot;Error! Result: &quot; . $json_response-&gt;_status . &quot; : &quot; . $json_response-&gt;Message . &quot;\n&quot;;<br>    }<br>}</pre>
    </div>
    
    <h3>Sample Code for Perl</h3>
    <div>
    <pre>use LWP::UserAgent;
use JSON;

my $url = '<?php print htmlspecialchars($BuildSourceFuncCache->ProxyURL); ?>';
my $json = <?php print htmlspecialchars(trim($BuildSourceFuncCache->ProxyParameterExampleForPerl)); ?>;
my $req = HTTP::Request-&gt;new('POST', $url);
$req-&gt;header('Content-Type' =&gt; 'application/json');
$req-&gt;content(encode_json $json);
my $ua = LWP::UserAgent-&gt;new;
my $response = $ua-&gt;request($req);

if ($response-&gt;is_success) {
    my $json_response = JSON-&gt;new()-&gt;decode($response-&gt;content);
    if ($json_response-&gt;{_status} eq &quot;OK&quot;) {
        # Success. Result is in: $json_response-&gt;{Result}
    } else {<br>        print &quot;Error! Result: &quot; . $json_response-&gt;{_status} . &quot; : &quot; . $json_response-&gt;{Message} . &quot;\n&quot;;<br>    }<br>}</pre>
  </div>
  
    <h3>Sample Code for Ruby</h3>
    <div>
    <pre>require 'net/https'
require &quot;json&quot;

uri = URI.parse(&quot;<?php print htmlspecialchars($BuildSourceFuncCache->ProxyURL); ?>&quot;)
http = Net::HTTP.new(uri.host, uri.port)
http.use_ssl = true
req = Net::HTTP::Post.new(uri.path)
req[&quot;Content-Type&quot;] = &quot;application/json&quot;
req.body = JSON.generate(<?php print htmlspecialchars(trim($BuildSourceFuncCache->ProxyParameterExampleForRuby)); ?>)
res = http.request(req)
if res.code != &quot;200&quot; then
    # Error Occured
else
    # Success
    json_response = JSON.parse(res.body)
    if json_response[&quot;_status&quot;] == &quot;OK&quot; then
        # Success. Result is in: json_response[&quot;Result&quot;]
    else
        print &quot;Error! Result: &quot; + json_response[&quot;_status&quot;] + &quot; : &quot; + json_response[&quot;Message&quot;] + &quot;\n&quot;;
    end
end</pre>
  </div>
  
  
    <h3>Sample Code for C#</h3>
    <div>
    <pre>System.Uri requestURI = new System.Uri(&quot;<?php print htmlspecialchars($BuildSourceFuncCache->ProxyURL); ?>&quot;);
using (System.Net.Http.HttpClient httpClient = new System.Net.Http.HttpClient())
using (System.Net.Http.StringContent content = new System.Net.Http.StringContent(JSON_string))
{
    var request = new System.Net.Http.HttpRequestMessage()
    {
        RequestUri = requestURI,
        Method = System.Net.Http.HttpMethod.Post,
        Content = content
    };
    httpClient.DefaultRequestHeaders.Accept.Add(new System.Net.Http.Headers.MediaTypeWithQualityHeaderValue(&quot;application/json&quot;));
    httpClient.DefaultRequestHeaders.Host = requestURI.Host;
    using (System.Net.Http.HttpResponseMessage response = await httpClient.SendAsync(request))
    {
        if (response.IsSuccessStatusCode)
        {
            string resultContent = await response.Content.ReadAsStringAsync();

            Console.WriteLine(resultContent);
        }
    }
}</pre>
      <?php
switch($lang) {
	case $LANG_JAPANESE:
		?>
    <p>C#のサンプルコードにJSONエンコード/デコードは含まれません。カスタム開発オプションを付けるとC#向けアクセス用クラスおよびJSONエンコード/デコード用クラスを自動作成できますのでより簡単にエンドポイントにアクセスできます。</p>
		<?php
		break;
	case $LANG_ENGLISH:
		?>
		<p>Sample code doesn't include Encode/Decode of JSON for C#. With Custom Development Option, some classes will be created by cloud such as Client Access Class and JSON Encode/Decode class for C# so that you can access Endpoint more easily.</p>
		<?php
		break;
}
?>
  </div>  
    <h3>Sample Code for Java</h3>
    <div>
    <pre>java.net.URL url = new java.net.URL(&quot;<?php print htmlspecialchars($BuildSourceFuncCache->ProxyURL); ?>&quot;);
javax.net.ssl.HttpsURLConnection con = (javax.net.ssl.HttpsURLConnection) url.openConnection();
con.setRequestMethod(&quot;POST&quot;);
con.setInstanceFollowRedirects(false);
con.setDoOutput(true);
con.setRequestProperty(&quot;Content-Type&quot;, &quot;application/json&quot;);
java.io.PrintWriter pw = new java.io.PrintWriter(new java.io.BufferedWriter(new java.io.OutputStreamWriter(con.getOutputStream(), &quot;utf-8&quot;)));
pw.print(JSON_string);
pw.close();

java.io.BufferedReader reader = new java.io.BufferedReader(new java.io.InputStreamReader(con.getInputStream(), &quot;UTF-8&quot;));
String buffer = reader.readLine();

System.out.println(buffer);</pre>
      <?php
switch($lang) {
	case $LANG_JAPANESE:
		?>
		<p>JavaのサンプルコードにJSONエンコード/デコードは含まれません。カスタム開発オプションを付けるとJava向けアクセス用クラスおよびJSONエンコード/デコード用クラスを自動作成できますのでより簡単にエンドポイントにアクセスできます。単純アクセスおよび Android用のAsync Task Loaderパターンに対応しております。</p>
		<?php
		break;
	case $LANG_ENGLISH:
		?>
    <p>Sample code doesn't include Encode/Decode of JSON for Java. With Custom Development Option, some classes will be created by cloud such as Client Access Class and JSON Encode/Decode class for Java so that you can access Endpoint more easily. Supported both simple access and Async Task Loader pattern of Android's Java.</p>
		<?php
		break;
}
?>
  </div>
</div>
