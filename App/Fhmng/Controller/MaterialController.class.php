<?php
/**日志
 * 模块：关系管理
 * 创建人:liuj-ad
 * 创建时间：2018-08-08
 * 描述：
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class MaterialController extends BaseController{
  
    public function index(){
        $this->checkPageAuth('Material');
        $this->display();
    }
    function getlist(){
      $post = I('post.');
      $page = $post['page'];
      $rows = $post['rows'];
      $list = M("material")->page($page,$rows)->select();
     
      $count = M("material")->count();
      $this->ajaxReturn(['rows'=>$list,'total'=>$count]);
    } 
   
    //保存
    function editContent(){
      $obj = M('material');
      if(IS_GET){
        $id = I('get.id',0,intval);
        if($id){
          $data = $obj->find($id);
        }
        $this->assign('data',$data);
        $this->display();
      }elseif(IS_POST){
        $post = I('post.');
       
        if($post['id']){
           $res =$obj->save($post);
        }else{
           $res = $obj->add($post);
        }
        if($res !== false){
          $this->ajaxReturn(['success'=>true]);
        }else{
          $this->ajaxReturn(['success'=>false]);
        }
      }
    }
    //启用，禁用，删除
    function del(){
     $res = M("material")->where(['id'=>I('post.id',0,intval)])->delete();
     if($res !== false){
        $this->ajaxReturn(['success'=>true]);
      }else{
        $this->ajaxReturn(['success'=>false]);
      }
    }
 
      /**
     * 上传附件
     * */
    public function uploadpdf(){
        $res = upload_file('pdf','Material');
        if($res['status'] == 1){
          $this->ajaxReturn(['success'=>true,'url'=>$res['file']['old'],'name'=>$res['file']['name']]);
        }else{
          $this->ajaxReturn(['success'=>false,'message'=>$res['info']]);
        }
    }
    function upload_pic(){
      //原图片地址
      $res = upload_img('pic','Material');
      if(!$res['status']) {
          $result["success"] = false;
          $result["message"] = "上传失败";
      }else{
          $result["success"] = true;
          $result["message"] = "上传成功";
          $result["savefile"] = $res['file']['old'];
      }
      $this->ajaxReturn($result);
   }
   /**
     * 删除图片
     * */
    public function removeImg(){
        $path = I('post.path');
        $id = I('post.id');
        if(!$id){
            $res =  removeImg($path);
        }else{
            $res = true;
        }
        if($res){
            $this->ajaxReturn(['success'=>true,'message'=>'删除成功']);
        }else{
            $this->ajaxReturn(['success'=>false,'message'=>'删除失败']);
        }
    }   
}