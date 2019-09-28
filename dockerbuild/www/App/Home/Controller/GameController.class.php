<?php
/*大赛简介*/
namespace Home\Controller;
use Home\Common\Controller\BaseController;

class GameController extends BaseController {
    public function index()
    {
      // //子菜单
      // $pid = M('menu')->where(['menu_code'=>'DSJJ','status'=>1])->getField('id');
      // $ds_menus = M('menu')->where(['pid'=>$pid,'status'=>1])->field('id,menu_name')->select();
      // //内容
      // $id_arr = array_column($ds_menus,'id');
      // $list = M('content')->where(['menu_id'=>['in',$id_arr],'status'=>1])->order('order_no')->field('id,menu_id,title,content')->select();
     
      
      //修改为菜单和内容一体
      $pid = M('menu')->cache(true)->where(['menu_code'=>'DSJJ','status'=>1])->getField('id');
      $list = M('content')->cache(true)->where(['menu_id'=>$pid,'status'=>1])->order("is_top desc,order_no asc,id")->field('id,title,content')->select();

      $this->assign('list',$list);
      $this->display();
    }
  
}