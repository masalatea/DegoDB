$__DB_OBJECT__ = NULL;

function connect_error_for___DB_OBJECT__($error_message)
{
	error_log($error_message);
	if (function_exists("DB_connect_error_event")) {
		DB_connect_error_event($error_message);
	} else if (php_sapi_name() != "cli") {
		header('HTTP/1.0 503 Service Temporarily Unavailable');
	}
}
function connect___DB_OBJECT___if_not_yet()
{
	global $__DB_OBJECT__;
	global $CustomMySQLDBServerNameFor__DB_OBJECT__;
	global $MySQLDBSSLConnectionServerKeyFor__DB_OBJECT__;
	global $MySQLDBSSLConnectionServerCertFor__DB_OBJECT__;
	global $MySQLDBSSLConnectionCaCertFor__DB_OBJECT__;
	
	if ($__DB_OBJECT__) {
		// Already Connected
		return;
	}
	
	$MySQLDBServerName = "__DB_HOST__";		// If you want to custom Server Name, set to $CustomMySQLDBServerNameFor__DB_OBJECT__ beforehand
	if (isset($CustomMySQLDBServerNameFor__DB_OBJECT__)) {
		$MySQLDBServerName = $CustomMySQLDBServerNameFor__DB_OBJECT__;
	}
	$__DB_OBJECT__ = mysqli_init();
	if (isset($MySQLDBSSLConnectionServerKeyFor__DB_OBJECT__) && isset($MySQLDBSSLConnectionServerCertFor__DB_OBJECT__) && isset($MySQLDBSSLConnectionCaCertFor__DB_OBJECT__)) {
		$__DB_OBJECT__->ssl_set($MySQLDBSSLConnectionServerKeyFor__DB_OBJECT__, $MySQLDBSSLConnectionServerCertFor__DB_OBJECT__, $MySQLDBSSLConnectionCaCertFor__DB_OBJECT__, NULL, NULL);
	}
	$__DB_OBJECT__->real_connect($MySQLDBServerName, "__DB_USER__", "__DB_PASSWORD__", "__DB_NAME__");
	if (!$__DB_OBJECT__) {
		connect_error_for___DB_OBJECT__("error! Failed to connect Database: __DB_NAME__ from $MySQLDBServerName by __DB_USER__");
		exit();
	}
	if ($__DB_OBJECT__->connect_errno) {
		connect_error_for___DB_OBJECT__("Connect failed: " . $__DB_OBJECT__->connect_error);
		exit();
	}
	if (!$__DB_OBJECT__->set_charset("utf8mb4")) {
		connect_error_for___DB_OBJECT__("Error loading character set utf8: " . $__DB_OBJECT__->error);
		exit();
	}
}
function reconnect___DB_OBJECT___if_necessary()
{
	global $__DB_OBJECT__;
	global $time_for_reconnect___DB_OBJECT___if_necessary;
	
	$THRESHOLD_TIMEOUT_SEC = 10;
	
	if (abs(time() - $time_for_reconnect___DB_OBJECT___if_necessary) > $THRESHOLD_TIMEOUT_SEC) {
		$__DB_OBJECT__->ping();
		$time_for_reconnect___DB_OBJECT___if_necessary = time();
	}
}
$time_for_reconnect___DB_OBJECT___if_necessary = time();

$last_sql_command_for___DB_OBJECT__ = "";

