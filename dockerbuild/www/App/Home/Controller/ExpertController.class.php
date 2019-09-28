<?php
/*专家团队*/
namespace Home\Controller;
use Home\Common\Controller\BaseController;

class ExpertController extends BaseController {
    public function index()
    {
     //设置页面标题
     $menuinfo = M('menu')->where(['menu_code'=>'ZJTD','status'=>1])->field("id,website_title")->find();
//     $this->assign('webtitle',$menuinfo['website_title']?$menuinfo['website_title']:$this->webtitle);
     //专家页面数据
     $map['m.pid'] = $menuinfo['id'];
     $map['m.status'] = 1;
     $map['c.status'] = 1;
     $map['c.content_img'] = array('neq',"");
     $list = m('menu')->alias('m')
         ->join('content c ON  m.id= c.menu_id')
         ->where($map)
         ->order('m.order_no,m.id,c.order_no,c.id')
         ->field("m.menu_name,c.title,c.menu_id,c.content_img,c.summary")
         ->select();
     $listData =[];
        foreach($list as $k=>$v){
            $listData[$v['menu_id']][]=$v;
     }
      $this->assign('listData',$listData);
      $this->display();
    }
  
}