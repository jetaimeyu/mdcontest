<?php 
    /**
     * 动态管理
     */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class DynamicController extends  BaseController{
    /**
     * 首页
     */
    public function  index(){
        $this->checkPageAuth("DTGL");
        $this->display();
    }
    
    
    /**
     * 获取列表数据
     */
    public function getList(){
        $page=I("page",1);
        $rows = I("rows",10);  //每页显示的条数
        $param=I('post.searchValue');
        $sort=I('post.sort');
        $order=I('post.order');
        if(!empty($param)){
            $where['name']=['like','%'.$param.'%'];
        }
        $where['online_dynamic.status']=['neq',3];
        $total=M('online_dynamic')
            ->join('LEFT JOIN online_expert on online_expert.id=online_dynamic.uid')
            ->where($where)
            ->field('online_dynamic.*,online_expert.name,online_expert.status as stu')
        ->count();
        if(!empty($sort)){
            $res=M('online_dynamic')
            ->join('LEFT JOIN online_expert on online_expert.id=online_dynamic.uid')
            ->where($where)
            ->field('online_dynamic.*,online_expert.name,online_expert.status as stu')
            ->order($sort.'  '.$order)
            ->page($page,$rows)
            ->select();
           
        }else{
            $res=M('online_dynamic')
            ->join('LEFT JOIN online_expert on online_expert.id=online_dynamic.uid')
            ->where($where)
            ->field('online_dynamic.*,online_expert.name,online_expert.status as stu')
            ->page($page,$rows)
            ->select();
        }
  
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    
    
    /**
     * 查看详情
     */
    
    public function detail(){
        $id=I('id');
        if(!empty($id)){
            $data=M('online_dynamic')
            ->join('LEFT JOIN online_expert on online_expert.id=online_dynamic.uid')
            ->field('online_dynamic.*,online_expert.name,online_expert.status as stu')->where(array('online_dynamic.id'=>$id))
            ->find();
        }
        
        $this->assign('data',$data);
        $this->display();
    }
    
    
    /**
     * 删除   审核 启用 禁用 状态修改
     */ 
    public function  updateStu(){
        $post = I('post.');
        $value = $post['status'];
        $table = $post['table'];
        $where['id'] =['IN',$post['id']];
        $res = $this->updateField($table,$where,'status',$value);
        $this->ajaxReturn($res);
        
    }
}























?>