$SecurityCheckResult = false;
$username = "";

$DAstudent_login_cookie = new student_login_cookieDBAccess();
$student_login_cookieObj = $DAstudent_login_cookie->Getstudent_login_cookie($data->LOGIN_COOKIE_TOKEN);
if ($student_login_cookieObj) {
	$username = $student_login_cookieObj->STUDENT_ID;
	
	$SecurityCheckResult = true;
}
