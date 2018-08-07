<?php

/***********************获取不同学期的课表***************************/
$schoolyear = isset($_POST["schoolyear"]) ? $_POST["schoolyear"]:'';
$semester = isset($_POST["semester"]) ? $_POST["semester"]:'';

$cookie = dirname(__FILE__).'/cookie.txt';//保存cookie在本地

if (!empty($schoolyear) && !empty($semester)) {
    $schedularUrl = "http://class.sise.com.cn:7001/sise/module/student_schedular/student_schedular.jsp?schoolyear=".$schoolyear."&semester=".$semester; //课程表url
    setJson($schedularUrl, $cookie);
}
else {
    $schedularUrl = "http://class.sise.com.cn:7001/sise/module/student_schedular/student_schedular.jsp"; //课程表url
    load($schedularUrl, $cookie);
}
function setJson($Url, $cookies){
    echo get_schedular(get_content($Url, $cookies));
}
function load($Url, $cookies){
    return get_schedular(get_content($Url, $cookies));
}

/**********************防止翻转乱码*****************************/
function utf8_strrev($str) {
	preg_match_all('/./us', $str, $ar);
	return join('', array_reverse($ar[0]));
}

//获取课表
function get_content($Url, $cookies) {
         $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url); //定义地址
        curl_setopt($ch, CURLOPT_HEADER, false); //显示头信息
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //跟随转跳
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //以数据流返回，是
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); //读取cookie
        // curl_setopt($ch, CURLOPT_COOKIE, $cookie);//设置cookie
        $rs = curl_exec($ch); //执行cURL抓取页面内容
        curl_close($ch);
        return $rs;
    }

function get_schedular($content) {
	$content = iconv('GBK', 'UTF-8', $content);
	$preg_name = "/<td width=\"70%\" nowrap>\<span class=\"style15\">&nbsp;\<span class=\"style16\">学号: (.*?) &nbsp;姓名: (.*?) &nbsp;年级: (.*?) &nbsp;专业:(.*?)<\/span> <\/span><\/td>/";
	preg_match_all($preg_name, $content, $name_info);
	$stu = array();
	$stu['stuNumber'] = isset($name_info[1][0]) ? $name_info[1][0] : '';
	$stu['stuName'] = isset($name_info[2][0]) ? $name_info[2][0] : '';
	$stu['stugrade'] = isset($name_info[3][0]) ? $name_info[3][0] : '';
	$stu['stuMajor'] = isset($name_info[4][0]) ? $name_info[4][0] : '';

	if ($stu['stuNumber'] == '') {
		return '';
		exit ;

	} else {
		/*存储学号和姓名到json*/
		$data["stuNumber"] = isset($name_info[1][0]) ? $name_info[1][0] : '';
		$data["stuName"] = isset($name_info[2][0]) ? $name_info[2][0] : '';
		$data["stugrade"] = isset($name_info[3][0]) ? $name_info[3][0] : '';
//		session_start();
		$_SESSION["temp"] = array($stu['stuName'], $stu['stugrade'], $stu['stuMajor']);

		preg_match_all("/(教学周: 第(.*?)周)/", $content, $teach_time);

		$stu_now_week = isset($teach_time[2][0]) ? $teach_time[2][0]:'';

		$preg_time = "/<td width='10%' align='center' valign='top' class='font12'>(.*?)节<br>(.*?)<\/td><td width='10%' align='left' valign='top' class='font12'>/";
		preg_match_all($preg_time, $content, $schooltime);

		$class_time = array();
		$class_time['time_num'] = count($schooltime[2]);
		//获取有多少个时间段，注释的是之前以为12:30-13:50是休息时间需要跳过，但现在好多在线课都在12:30-13:50，所以正常获取了
		for ($num = 0; $num < $class_time['time_num']; $num++) {
			$class_time[] = $schooltime[2][$num];
			$num1 = $num + 1;
		}

		$preg = "/<td width='10%' align='left' valign='top' class='font12'>(.*?)<\/td>/si";
		preg_match_all($preg, $content, $arr);

		$subject = array();
		static $vline = 0;
		static $hline = 1;
		$arr_size = count($arr[1]);
		
		/*********************************正则截取字段并且存入json***********************************************/
		$json_string="";
		for ($subject_count = 0; $subject_count != $arr_size; $subject_count++) {
			if ($hline > 7) {
				$hline = 1;
				$vline += 1;
			}

			$subject[$hline][$vline] = $arr[1][$subject_count];
			if ($arr[1][$subject_count] != "&nbsp;") {
				$class_content1 = $subject[$hline][$vline];
				$class_content = utf8_strrev($class_content1);

				/* 课程名称*/
				$preg_hz = "/(.*?)[\x{4e00}-\x{9fa5}a-zA-Z 0-9]{2,}\(/u";
				preg_match_all($preg_hz, $class_content, $class_name_info);
				$class_name1 = substr($class_name_info[0][0], 0, strlen($class_name_info[0][0]));
				$class_name2 = substr($class_content, strlen($class_name1), strlen($class_name_info[0][0]) - 1);
				$class_name = utf8_strrev($class_name2);
				/*周数和教师名称*/
				$preg_name = "/[\x{4e00}-\x{9fa5}a-zA-Z 0-9]{2,}\(/u";
				preg_match_all($preg_name, $class_content, $class_details1);
				$class_details = utf8_strrev($class_details1[0][0]);
				/*去掉空格*/
				$preg_hz = "/(.*?)[\s　]+/s";
				preg_match_all($preg_hz, $class_details, $hz);

				/*输出教学班和任课老师*/
				$preg_name1 = "/[a-zA-Z 0-9]{2,}/";
				$class_learn_class1 = $hz[0][0];
				//教学班
				preg_match_all($preg_name1, $class_learn_class1, $class_learn);
				$class_learn_class = $class_learn[0][0];
				$class_teacher = $hz[0][1];
				
				$WeekNum = count($hz[0], 0) - 3;
				$class_weeks = null;
				/*截取课室*/
				$class_rom = substr($class_name_info[0][0], 0, strlen($class_name_info[0][0]) - strlen($class_details1[0][0]));
				$preg_name = "/[a-zA-Z 0-9]{2,}/";
				preg_match_all($preg_name, $class_rom, $class_room_info);
				$class_room = utf8_strrev($class_room_info[0][0]);

				$which_class = $vline + 1;
				$all_WeekNum = $WeekNum + 1;

				/*保存为键值对*/
				$data["time"][$vline][] = array(
				"day" => "row$hline",
				 "class" => "教学班$class_learn_class",
				  "classname" => "$class_name",
				   "teacher" => "任课老师$class_teacher",
				    "classroom" => "课室$class_room",
				     "week" => "一共$all_WeekNum"."周"
					 );

			} 
			else {
				$which_class = $vline + 1;
				$data["time"][$vline][] = array(
				"day" => "row$hline",
				 );
			}
			$json_string = json_encode($data, JSON_UNESCAPED_UNICODE);
			file_put_contents('../json/test.json', $json_string);
			$hline += 1;		
		}
	return $json_string;
	}


}

?>
