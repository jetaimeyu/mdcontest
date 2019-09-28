<?php
/**日志
 * 模块：BaseController
 * 创建人:李宝振
 * 创建时间：2016-09-01
 * 描述：用户管理
 *
 * 修改人：
 * 修改时间：
 * 修改描述：所有的前台controller必须继承此controller
 *
 * */
namespace Mobile\Common\Controller;
use Think\Controller;
class BaseController extends Controller{
	//用户session
	protected $cookie_member = null;
	protected function _initialize(){
        $version_no = getSysConfigValue('version_no');
        $this->version_no = $version_no;
        
        
		//初始化登录状态的cookie
		$ticket=I('get.ticket');
		if(!empty($ticket) && !session('cookie_member'))
		{
			$rs = httpGlodonRequest($ticket);
			$xmlstr = str_replace('cas:', '', $rs);
			$xml = xml_decode($xmlstr);
			$glodonid = $xml["authenticationSuccess"]["user"];
			$xml = json_decode($xml["authenticationSuccess"]["attributes"]["detail"],true);
			$xml['id'] = $glodonid;
			if($glodonid != null){
				LoginIn($xml);
			}
		}
		$this->cookie_member = session('cookie_member');
		$this->assign('cookie_member',$this->cookie_member);

		 $detect = new \Org\Net\Mobile_Detect();
         if($detect->isMobile()){
            vendor("LaneWeChat.lanewechat");
                $conf['appId']=C('WECHAT_APPID');
                $conf['nonceStr']=createNonStr();
                $conf['timestamp']=time();
                $jsapi_ticket = S('jsapi_ticket');
                if(!$jsapi_ticket){
                    $conf['jsapi_ticket']=\LaneWeChat\Core\JsapiTicket::getJsapiTicket();
                    S('jsapi_ticket',$conf['jsapi_ticket'],7000);
                }else{
                     $conf['jsapi_ticket'] = $jsapi_ticket;
                }
                $conf['url'] = C("DOMAIN").$_SERVER['REQUEST_URI'];
                $str="jsapi_ticket=".$conf['jsapi_ticket']."&noncestr=".$conf['nonceStr']."&timestamp=".$conf['timestamp']."&url=".$conf['url'];
                $conf['signature']=sha1($str);
                $this->assign('wxconf',$conf);
         }
	}
    //  /**
    // *生成订单号
    // *lj
    // */
    // function getOrdercode(){
    //     // $mid = session('session_user')['current_mid'];
    //     $sql = "SELECT next_serial_num() as num";
    //     $num = M()->query($sql);
    //     $ordercode = trim(date('Ymd').$num[0]['num']);
    //     $len = 13 - strlen($ordercode);
    //     if($len > 0 ){
    //         $exp =  str_repeat('0',$len);
    //         $ordercode = trim(date('Ymd').$exp.$num[0]['num']);
    //     }
    //     return $ordercode;
    // } 
  
}