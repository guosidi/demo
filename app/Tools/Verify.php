<?php
namespace App\Tools;

class Verify {

	private static $instance;

	//这个留着，万一想添加进去加6
	private $typeArr = [
			0=>"register_",//注册
			1=>"login_",//登录
			2=>"passwd_",//找回密码
			3=>"width_",//提现验证
			4=>"addcard_",//添加银行卡
			5=>"editcard_",//修改银行卡
	];
	
	public static function instance()
	{
		if (!self::$instance) self::$instance = new Verify();
		return self::$instance;
	}


	/**
	 * 短信拼接
	 * @param unknown $phone
	 * @param unknown $cpname
	 * @param unknown $username
	 * @param unknown $type
	 * @param string $content_add
	 * @return number[]|string[]
	 */
	
	public function sendMsgcp($phone,$cpname,$username,$type,$content_add){
	
	    if(!preg_match("/^1[3456789]{1}\d{9}$/",$phone)){
	        return ['code'=>333,'msg'=>'手机号码不正确！'];
	    }
	    $content=$this->getType($type, $cpname,$username).$content_add;
	    $data = $this->send($content, $phone);
        return $data;
	}
	
	private function getType($type,$cpname,$username=''){
	    
	    $msg= [
	      
	            0=>'',
	            1=>'',
	            2=>'',
	            3=>'',
	            4=>'',
	            5=>'',
	            6=>'',
	            10=>'注意！有新的商家订单，请尽快登录系统处理。',
	     
	    ];
	    if(!isset($msg[$type])){
	        $msg[$type]='';
	    }
	    return $msg[$type];
	}
	
	
	private function send($content,$phone){
	    
		// 微网通联接口地址
		$target = "http://cf.51welink.com/submitdata/Service.asmx/g_Submit";
		// 组织数据
		$post_data = "sname=dlkkfj88&spwd=1234wwtl&scorpid=&sprdid=1012888&sdst={$phone}&smsg=" . rawurlencode("{$content}【抠抠网】");
		$gets      = $this->Post($post_data, $target);
		$array =$this->xmltoArray($gets);
		return $array;
	}
	
	
	
	
	function xmltoArray($xml)
	{
	    $xmlobj = new XmlTool();
	    $arr    = $xmlobj->readXml($xml);
	    
	    return $arr;
	}
	
	function Post($data, $target)
	{
	    $url_info   = parse_url($target);
	    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
	    $httpheader .= "Host:" . $url_info['host'] . "\r\n";
	    $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
	    $httpheader .= "Content-Length:" . strlen($data) . "\r\n";
	    $httpheader .= "Connection:close\r\n\r\n";
	    // $httpheader .= "Connection:Keep-Alive\r\n\r\n";
	    $httpheader .= $data;
	    
	    $fd = fsockopen($url_info['host'], 80);
	    fwrite($fd, $httpheader);
	    $gets = "";
	    while (!feof($fd)) {
	        $gets .= fread($fd, 128);
	    }
	    fclose($fd);
	    if ($gets != '') {
	        $start = strpos($gets, '<?xml');
	        if ($start > 0) {
	            $gets = substr($gets, $start);
	        }
	    }
	    
	    return $gets;
	}
}