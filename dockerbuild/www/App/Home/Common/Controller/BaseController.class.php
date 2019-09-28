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
namespace Home\Common\Controller;
use Think\Controller;
class BaseController extends Controller{

	protected function _initialize(){
		$selfurl = __SELF__;
        $this->assign('surl', $selfurl);
        //seo配置
        $version_no = getSysConfigValue('version_no');
        $this->version_no = $version_no;
        $webtitle = S('webtitle');
        if(!$webtitle){
            $webtitle = getSysConfigValue('website_title');
            S('webtitle',$webtitle);
        }
        $this->webtitle = $webtitle;
        $keywords = S('keywords');
        if(!$keywords){
            $keywords = getSysConfigValue('website_keywords');
            S('keywords',$keywords);
        }
        $this->keywords = $keywords;
        $description = S('description');
        if(!$description){
            $description = getSysConfigValue('website_description');
            S('description',$description);

        }
        $this->description = $description;


        //主菜单
        //初始化菜单
        $menuObj = M('menu');
        $menus = $menuObj->cache(true)->field('id,menu_name,menu_url, url_target, englishname,model_name,menu_type,controller_name,action_name')->where(['status'=>1,'menu_category'=>'menu_category_front','pid'=>0])->order('order_no asc')->select();
        $this->assign('menus',$menus);


        $detect = new \Org\Net\Mobile_Detect();
         if($detect->isMobile()){
             $this->redirect('/mobile');
//            echo 'mobile test';exit;
         }
//        //判断用户登陆状态
          $UID = session("Match_User_Id");
         if($UID){
             $telData = m("user")->where("id = '{$UID}'")->find();
            if($telData){
                $isShow = true;
            }else{
                $isShow = false;
            }

         }else{
             $isShow = false;
         }

         $this->assign('isShow',$isShow);
         $this->assign('userName',$telData['true_name']);
	}


}
