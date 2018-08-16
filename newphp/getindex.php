<?php

$cookie = dirname(__FILE__) . '/cookie.txt';
//保存cookie在本地
$url = "http://class.sise.com.cn:7001/sise/";
//主页URl
$loginUrl = "http://class.sise.com.cn:7001/sise/login_check_login.jsp";
//登录url
$indexUrl = "http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp";
//主页url
$schedularUrl = "http://class.sise.com.cn:7001/sise/module/student_schedular/student_schedular.jsp";

//获取登录时需要的数据
$logindatas = get_post_data($url, '1540332204', '00', "183.14.133.172");
header("Content-type: text/html; charset=utf-8");
//登录
login_post($loginUrl, $cookie, $logindatas);

//判断是否登录成功
header("Content-type: text/html; charset=GBK");
$check = get_index($indexUrl, $cookie);
$studentid = get_indexpage($check);

$detailUrl = "http://class.sise.com.cn:7001/SISEWeb/pub/course/courseViewAction.do?method=doMain&studentid=" . $studentid;
$detail = get_index($detailUrl, $cookie);

echo get_schedular(get_index($schedularUrl, $cookie));

/**********************防止翻转乱码*****************************/
function utf8_strrev($str) {
	preg_match_all('/./us', $str, $ar);
	return join('', array_reverse($ar[0]));
}

//获取个人详细页面的studentid
function get_indexpage($check) {
	$preg_name = "/studentid=(.*?)'/";
	preg_match_all($preg_name, $check, $stu);
	$student = $stu[0][0];
	$studentid1 = trim($student, "studentid  '");
	$id = ltrim($studentid1, "=");
	return $id;
}

//获取登录需要的数据
function get_post_data($url, $user, $pass, $ip) {
	$md5_key = md5($ip);
	$md5_value = md5(md5($ip) . "sise");

	$datas = $md5_key . "=" . $md5_value;
	//拼凑post需要数据
	$datas .= getRT($url) . "&username=" . $user . "&password=" . $pass;

	return $datas;
}

//获取random和token
function getRT($url) {
	$datas = "";
	//获取头部cookie
	$content = getResponse($url);
	//获取头部内容
	$cookie_name = "/JSESSIONID=(.*?)!/";
	preg_match_all($cookie_name, $content, $cookie_info);
	$cookie_value = $cookie_info[1][0];
	//获取random
	$random_name = "/<input id=\"random\"   type=\"hidden\"  value=\"(.*?)\"  name=\"random\" \/>/";
	preg_match_all($random_name, $content, $random_info);
	$random_value = $random_info[1][0];

	//获取Token的算法(需要url+cookie+random)
	$value = strtoupper(md5($url . $cookie_value . $random_value));
	$len = strlen($value);
	$randomlen = strlen($random_value);
	$token = '';
	for ($index = 0; $index < $len; $index++) {
		$token .= $value[$index];
		if ($index < $randomlen)
			$token .= $random_value[$index];
	}

	$datas .= "&random=" . $random_value . "&token=" . $token;
	return $datas;
}

//获取主页信息
function get_index($indexUrl, $cookie) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $indexUrl);
	//定义地址
	curl_setopt($ch, CURLOPT_HEADER, false);
	//显示头信息
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	//跟随转跳
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//以数据流返回，是
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
	//读取cookie
	// curl_setopt($ch, CURLOPT_COOKIE, $cookie);//设置cookie
	$rs = curl_exec($ch);
	//执行cURL抓取页面内容
	curl_close($ch);
	return $rs;
}

//获取头部信息
function getResponse($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	//返回头信息
	curl_setopt($ch, CURLOPT_NOBODY, false);
	//
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//返回数据
	$content = curl_exec($ch);
	//执行并存储结果
	curl_close($ch);
	return $content;
}

//登录
function login_post($loginurl, $cookie, $post) {

	$curl = curl_init();
	//初始化curl模块
	curl_setopt($curl, CURLOPT_URL, $loginurl);
	//登录提交的地址
	curl_setopt($curl, CURLOPT_HEADER, false);
	//是否显示头信息
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	//是否自动显示返回的信息
	curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
	//
	curl_setopt($curl, CURLOPT_POST, true);
	//post方式提交
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	//要提交的信息
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	$rs = curl_exec($curl);
	//执行cURL闭cURL资源，并且释放系统资源
	curl_close($curl);
	return $rs;
}

//获取个人详细信息
function get_page($page) {
	$preg_name = "/<div align=\"left\">(.*?)<\/div>/si";
	preg_match_all($preg_name, $page, $detail);
	return var_dump($detail);
	//专业：[0][5] 身份证：[0][6] 邮箱：[0][7] 班主任：[0][8] 辅导员[0][9]
}

function get_schedular($content) {
	header("Content-type: text/html; charset=utf-8");
	$content = iconv('GBK', 'UTF-8', $content);
	$preg_year = "/<option value=(.*?) selected>(.*?)<\/option>/";
	preg_match_all($preg_year, $content, $select);

	$schoolyear = $select[2][0];
	if ($select[2][1] == "第一学期") {
		$semester = 1;
	} else {
		$semester = 2;
	}

	print_r($schoolyear);
	print_r($semester);

	$preg_name = "/<td width=\"70%\" nowrap>\<span class=\"style15\">&nbsp;\<span class=\"style16\">学号: (.*?) &nbsp;姓名: (.*?) &nbsp;年级: (.*?) &nbsp;专业:(.*?)<\/span> <\/span><\/td>/";
	preg_match_all($preg_name, $content, $name_info);
	$stu = array();
	$stu['stuNumber'] = isset($name_info[1][0]) ? $name_info[1][0] : '';

	if ($stu['stuNumber'] == '') {
		return '';
		exit ;

	} else {

		$preg = "/<td width='10%' align='left' valign='top' class='font12'>(.*?)<\/td>/si";
		preg_match_all($preg, $content, $arr);
		$sc = $arr[1];
		$sc_detail = array();
		for ($i = 0; $i < 8; $i++) {
			$sc_detail[] = array_slice($sc, $i * 7, 7);
		}
		echo '<pre>';
		print_r($sc_detail);
		echo '</pre>';
		return;
	}

}
?>