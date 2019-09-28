<?php
/**
* 达人堂内容管理  已禁用
* @date: 2017-6-28 下午4:39:45
* @author: zhucy
*/
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class OnlineController extends BaseController {
    
    public function contentIndex(){
       //检查权限
       $this->checkPageAuth("online_content");
       $this->display();
    }
    //获取会议列表
    function getContents(){
      $config =array(
        'table'=>'online_content',
        'where'=>['neq_status'=>3],
        'order'=>'id desc'
        );
      $return = $this->get_datas($config);
      $this->ajaxReturn($return);
    }
    
    //编辑会议
    function editContent(){
        if(IS_GET){
            $id = I('get.id');
            if($id){
               $result =  M('online_content')->find($id);
               $this->assign('content',$result); 
            }
            $this->display();
        }else if (IS_POST){
           $adminid = session('session_user')['id'];
           $data = I('post.');
           $res = $this->saveRow('online_content',$data);
          
           $this->ajaxReturn($res);
        }
    }
    //启用，禁用，删除
    function setStatus(){
        $ids = I('post.ids');
        $status = I('post.status');
        if($ids){
            $wh['id'] = array('in',$ids);
           $res =  M('online_content')->where($wh)->save(array('status'=>$status));
           if($res !== false){
            $this->ajaxReturn(array('success'=>true,'message'=>'操作成功'));
           }else{
            $this->ajaxReturn(array('success'=>false,'message'=>'操作失败'));
           }
        }
    }
    
    //上传图片
    function upload_pic(){
    
    	$res = upload_img('pic','online_content_img');
    
    	if($res['status']){
    		$img_url = $res['file']['old'];
    		$this->ajaxReturn(['success'=>true,'domain'=>C('QINIU_DOMAIN'),'file'=>$img_url,'message'=>'上传图片成功']);
    	}else{
    		$this->ajaxReturn(['success'=>false,'message'=>'上传图片失败']);
    	}
    }
    //删除图片
    function removeImg(){
    	//删除七牛
    	$path = I('post.path');
    	$res1 = removeImg($path);
    	//删除数据库
    	$id = I('post.id');
    	$res2 = true;
    	if($id){
    		$res2 = M('online_content')->where(['id'=>$id])->save(['img'=>'']);
    	}
    	if($res1 && ($res2 !== false)){
    		$this->ajaxReturn(['success'=>true,'message'=>'删除图片成功']);
    	}else{
    		$this->ajaxReturn(['success'=>false,'message'=>'删除图片失败']);
    	}
    }
}