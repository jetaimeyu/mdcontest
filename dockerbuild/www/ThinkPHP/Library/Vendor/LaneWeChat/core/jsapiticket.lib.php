<?php
namespace LaneWeChat\Core;
/**
 * 微信jsapi_ticket的获取与过期检查
 * User: zhulj-b
 * Date: 16-3-2
 * Time: 下午5:00
 */
class JsapiTicket{
	/**
	 * 获取微信JsapiTicket
	 */
	public static function getJsapiTicket(){
		//检测本地是否已经拥有jsapi_ticket，并且检测jsapi_ticket是否过期
		$jsapi_ticket = self::_checkJsapiTicket();
		if($jsapi_ticket === false){
			$jsapi_ticket = self::_getJsapiTicket();
		}
		return $jsapi_ticket['ticket'];
	}
	
	/**
	 * @descrpition 从微信服务器获取微信JsapiTicket
	 * @return Ambigous|bool
	 */
	private static function _getJsapiTicket(){
		$accessToken=AccessToken::getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$accessToken."&type=jsapi";
		$jsapi_ticket = Curl::callWebServer($url, '', 'GET');
		if(!isset($jsapi_ticket['ticket'])){
			return Msg::returnErrMsg(MsgConstant::ERROR_GET_ACCESS_TOKEN, '获取jsapi_ticket失败');
		}
		$jsapi_ticket['time'] = time();
		$jsapi_ticketJson = json_encode($jsapi_ticket);
		S(WECHAT_APPID.'_jsapi_ticket',$jsapi_ticketJson,7000);
		return $jsapi_ticket;
	}
	
	/**
	 * @descrpition 检测微信JsapiTicket是否过期
	 *              -10是预留的网络延迟时间
	 * @return bool
	 */
	private static function _checkJsapiTicket(){
		//获取jsapi_ticket。是上面的获取方法获取到后存起来的。
		$jsapi_ticket = S(WECHAT_APPID.'_jsapi_ticket');
		if(!empty($jsapi_ticket)){
			return false;
		}
		return $jsapi_ticket;
	}
}
?>