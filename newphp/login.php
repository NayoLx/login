<?php

$username = isset($_POST["username"]) ? $_POST["username"]:'';
$password = isset($_POST["password"]) ? $_POST["password"]:'';
require_once ('new.php');
// echo $username;
//获取cookie
$cookie = dirname(__FILE__).'/cookie.txt';//保存cookie在本地
$url = "http://class.sise.com.cn:7001/sise/";//主页URl
$loginUrl = "http://class.sise.com.cn:7001/sise/login_check_login.jsp"; //登录url
$schedularUrl = "http://class.sise.com.cn:7001/sise/module/student_schedular/student_schedular.jsp"; //课程表url

//获取登录时需要的数据
$logindatas = get_post_data($url, $username, $password, "183.14.134.29", 2);
header("Content-type: text/html; charset=utf-8");
//登录
login_post($loginUrl, $cookie, $logindatas);

//判断是否登录成功
$check = load($schedularUrl, $cookie);
echo json_encode($arrayName = array('state' => $check!=''?true:false));//获取课表的数据



//获取登录需要的数据
function get_post_data($url, $user, $pass, $ip, $type) {
       // <input type="hidden" name="96472b58bd088887635ed9bcbe33b07f"  value="aa220502833452d8c2c8f0391863dd24">
        $md5_key = md5($ip);
        $md5_value = md5(md5($ip) . "sise");
        
        $datas = $md5_key . "=" . $md5_value;//拼凑post需要数据
        //第一种登录方式
        if ($type == 1) {
            $datas .= "&username=" . $user . "&password=" . $pass;
        } 
        //第二种登录方式
        else if ($type == 2) {
            $datas .= getRT($url) . "&username=" .$user . "&password=" . $pass;
        }
        return $datas;
    }
//获取random和token
function getRT($url) {
        $datas="";
        //获取头部cookie
        $content = getResponse($url);//获取头部内容
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
        for($index = 0;$index < $len;$index++) {
            $token .= $value[$index];
            if ($index < $randomlen) $token .= $random_value[$index];
        }
        
        $datas .= "&random=" . $random_value . "&token=" . $token;
        return $datas;
    }
//获取头部信息
function getResponse($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true); //返回头信息
        curl_setopt($ch, CURLOPT_NOBODY, false); //
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回数据
        $content = curl_exec($ch); //执行并存储结果
        curl_close($ch);
        return $content;
    }
//登录
function login_post($loginurl, $cookie, $post) {

        $curl = curl_init(); //初始化curl模块
        curl_setopt($curl, CURLOPT_URL, $loginurl); //登录提交的地址
        curl_setopt($curl, CURLOPT_HEADER, false); //是否显示头信息
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //是否自动显示返回的信息
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //
        curl_setopt($curl, CURLOPT_POST, true); //post方式提交
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post); //要提交的信息
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);      
        $rs = curl_exec($curl); //执行cURL闭cURL资源，并且释放系统资源
        curl_close($curl);
        return $rs;
    }