<?php
header("content-type:text/html;charset=utf-8");
session_start();
$text=$_REQUEST['captcha'];
echo verify($text);
function verify($text){
	$text=md5($text);
	if($text==$_SESSION['CAPTCHA'] && !empty($text)){
		return 1;
	}else{
		return 0;
	}
}
?>