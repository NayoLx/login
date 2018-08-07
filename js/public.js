	/*<<<<<<<<<<<<<<登录事件>>>>>>>>>>>>*/
	function Login(username,password){
		var U=username;
		var P=password;
		if(U==""){
			LOG("<<Login(username,password)>>---username NOTNULL");
			SHOW("Username Not Null !");
			return;
		}
		if(P==""){
			LOG("<<Login(username,password)>>---password NOTNULL");
			SHOW("Password Not Null !");
			return;
		}
		$.post('newphp/login.php',{'username':U,'password':P},
			function(data){
			    LOG(data.state);
			    if(data.state){//成功
			        window.location.href='scgedular.html';
			    }
			    else{//失败
			        SHOW("登录失败，账号或密码错误！");
			    }
				
		},"json");
	}
	
	
	
	
	
	/*<<<<<<<<<<<<<<全局输出>>>>>>>>>>>>*/
	/**
	 * Name:输出数据
	 */
	function LOG(Name){
		console.log(Name);
	}
	
	/*<<<<<<<<<<<<<<全局弹框>>>>>>>>>>>>*/
	/**
	 * Name:输出数据
	 */
	function SHOW(Name){
		alert(Name);
	}
	