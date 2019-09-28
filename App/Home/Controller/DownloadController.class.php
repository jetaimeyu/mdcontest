<?php
/*作品展示*/
namespace Home\Controller;
use Home\Common\Controller\BaseController;

class DownloadController extends BaseController {
    public function index()
    {
    //设置页面标题
//    $menuinfo = M('menu')->where(['menu_code'=>'SJXZ','status'=>1])->field("id,website_title")->find();
//    $this->assign('webtitle',$menuinfo['website_title']?$menuinfo['website_title']:$this->webtitle);
     $downData = m("material")->order("order_no")->select();
     $downDataList = [];
     foreach($downData as $k=>$v){
         $downDataList[$v['type']][] = $v;
     }
     foreach($downDataList as $k=>&$v){
         if($k==1){
             $v = array_chunk($v,5);
         }
     }
     $this->assign('downDataList',$downDataList);
     $this->display();
    }
  
}