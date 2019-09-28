<?php
/*关于我们*/
namespace Home\Controller;
use Home\Common\Controller\BaseController;

class AboutusController extends BaseController {
    public function index()
    {
     //修改为菜单和内容一体
     $pid = M('menu')->cache(true)->where(['menu_code'=>'GYWM','status'=>1])->getField('id');
     $list = M('content')->cache(true)->where(['menu_id'=>$pid,'status'=>1])->order("is_top desc,order_no asc,id ")->field('id,title,content_code,content')->select();
    
      $this->assign('list',$list);
      $this->display();
    }
  
}