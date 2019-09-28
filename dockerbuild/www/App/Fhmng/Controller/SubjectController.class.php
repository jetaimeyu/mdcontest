<?php
/**日志
 * 模块：比赛题目
 * 创建人:刘佳
 * 创建时间：2018-08-03
 * 模块权限code:AUTH_SUB
 * */
namespace Fhmng\Controller;

use Fhmng\Common\Controller\BaseController;
class SubjectController extends BaseController{
    
    /**
     * 页面
     * */
    /***题目 start**/
    public function subjectindex(){
        //检查权限
        $this->checkPageAuth("AUTH_SUB");
        $this->display();
    }
    function geList(){
        $search = I('post.');
        $data = M('subject')->where($wh)->order('order_no')->page($search['page'],$search['rows'])->select();
        $count = M('subject')->where($wh)->count();
        $return = ['rows'=>$data,'total'=>$count];
        
        $this->ajaxReturn($return);
    }
    function getSubContentList(){
        $post = I('post.');
        $wh['sid'] = $post['sid'];
        $data = M('subject_content')->where($wh)->order('order_no')->page($post['page'],$post['rows'])->select();
        $count = M('subject_content')->where($wh)->count();
        $return = ['rows'=>$data,'total'=>$count];
        
        $this->ajaxReturn($return);
    }
    function addSubject(){
        if(IS_GET){
            $this->display();
        }else if(IS_POST){
            $post  = I('post.');
            $res = M('subject')->add($post);
            if($res !== false){
                $result=[
                    'success'=>true,
                    'message'=>'操作成功'
                ];
            }else{
                $result=[
                    'success'=>false,
                    'message'=>'操作失败'
                ];
            }
            $this->ajaxReturn($result);
        }
    }
    function editSubject(){
        if(IS_GET){
            $id = I('get.id');
            $data = M('subject')->find($id);
            $this->assign('data',$data);
            $this->display();
        }else if(IS_POST){
            $post  = I('post.');
            $res = M('subject')->save($post);
            if($res !== false){
                $result=[
                    'success'=>true,
                    'message'=>'操作成功'
                ];
            }else{
                $result=[
                    'success'=>false,
                    'message'=>'操作失败'
                ];
            }
            $this->ajaxReturn($result);
        }
    }
   function delSubject(){
       $post = I('post.');
       $res = M('subject')->where(['id'=>$post['id']])->delete();
       if($res !== false){
            $result=[
                'success'=>true,
                'message'=>'操作成功'
            ];
        }else{
            $result=[
                'success'=>false,
                'message'=>'操作失败'
            ];
        }
        $this->ajaxReturn($result);
    }
    function setStatus(){
        $post = I('post.');
        $res = M('subject')->save($post);
        if($res !== false){
            $result=[
                'success'=>true,
                'message'=>'操作成功'
            ];
        }else{
            $result=[
                'success'=>false,
                'message'=>'操作失败'
            ];
        }
        $this->ajaxReturn($result);
    }
    /***题目 end**/

    /**流程 start**/
    function processList(){
        $sid = I('get.sid',0,intval);
        $this->assign('sid',$sid);
        $this->display();
    }
    function getProcessList(){
        $search = I('post.');
        $sid = I('get.sid',0,intval);
        $data = M('subject_process')->where(['sid'=>$sid])->order('pdate')->page($search['page'],$search['rows'])->select();
        $count = M('subject_process')->where(['sid'=>$sid])->count();
        $return = ['rows'=>$data,'total'=>$count];
        
        $this->ajaxReturn($return);
    }
    function addProcess(){
        if(IS_GET){
            $sid = I('get.sid',0,intval);
            $this->assign('sid',$sid);
            $this->display();
        }else if(IS_POST){
            $post  = I('post.');
            $res = M('subject_process')->add($post);
            if($res !== false){
                $result=[
                    'success'=>true,
                    'message'=>'操作成功'
                ];
            }else{
                $result=[
                    'success'=>false,
                    'message'=>'操作失败'
                ];
            }
            $this->ajaxReturn($result);
        }
    }
    function editProcess(){
        if(IS_GET){
            $id = I('get.id');
            $data = M('subject_process')->find($id);
            $this->assign('data',$data);
            $this->display();
        }else if(IS_POST){
            $post  = I('post.');
            $res = M('subject_process')->save($post);
            if($res !== false){
                $result=[
                    'success'=>true,
                    'message'=>'操作成功'
                ];
            }else{
                $result=[
                    'success'=>false,
                    'message'=>'操作失败'
                ];
            }
            $this->ajaxReturn($result);
        }
    }
    function delProcess(){
        $post = I('post.');
        $res = M('subject_process')->where(['id'=>$post['id']])->delete();
        if($res !== false){
             $result=[
                 'success'=>true,
                 'message'=>'操作成功'
             ];
         }else{
             $result=[
                 'success'=>false,
                 'message'=>'操作失败'
             ];
         }
         $this->ajaxReturn($result);
     }
    /**流程 end**/

    /**简介 start**/
    function addContent(){
        if(IS_GET){
            $sid = I('get.sid',0,intval);
            $this->assign('sid',$sid);
            $this->display();
        }else if(IS_POST){
            $post  = I('post.');
            $res = M('subject_content')->add($post);
            if($res !== false){
                $result=[
                    'success'=>true,
                    'message'=>'操作成功'
                ];
            }else{
                $result=[
                    'success'=>false,
                    'message'=>'操作失败'
                ];
            }
            $this->ajaxReturn($result);
        }
    }
    function editContent(){
        if(IS_GET){
            $id = I('get.id');
            $data = M('subject_content')->find($id);
            $this->assign('data',$data);
            $this->display();
        }else if(IS_POST){
            $post  = I('post.');
            $res = M('subject_content')->save($post);
            if($res !== false){
                $result=[
                    'success'=>true,
                    'message'=>'操作成功'
                ];
            }else{
                $result=[
                    'success'=>false,
                    'message'=>'操作失败'
                ];
            }
            $this->ajaxReturn($result);
        }
    }
    function delContent(){
        $post = I('post.');
        $res = M('subject_content')->where(['id'=>$post['id']])->delete();
        if($res !== false){
             $result=[
                 'success'=>true,
                 'message'=>'操作成功'
             ];
         }else{
             $result=[
                 'success'=>false,
                 'message'=>'操作失败'
             ];
         }
         $this->ajaxReturn($result);
     }
     function setContentStatus(){
        $post = I('post.');
        $res = M('subject_content')->save($post);
        if($res !== false){
             $result=[
                 'success'=>true,
                 'message'=>'操作成功'
             ];
         }else{
             $result=[
                 'success'=>false,
                 'message'=>'操作失败'
             ];
         }
         $this->ajaxReturn($result);
     }
     /*简介 end*/
     function upload_pic(){
        //原图片地址
        $res = upload_img('pic','subject');
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
        $id = I('id');
        if(!$id){
            $res =  removeImg($path);
        }else{
            $res = true;
        }
        if($res){
            $this->ajaxReturn(['success'=>true,'message'=>'删除成功']);
        }else{
            $this->ajaxReturn(['success'=>false,'message'=>'图片删除失败']);
        }
    }   
  
}