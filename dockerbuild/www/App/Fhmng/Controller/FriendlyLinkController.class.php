<?php
/**日志
 * 模块：友情链接
 * 创建人:李宝振
 * 创建时间：2016-09-01
 * 模块权限code:AUTH_FRIENDLY_LINK
 * 描述：友情链接管理
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */

namespace Fhmng\Controller;

use Fhmng\Common\Controller\BaseController;
class FriendlyLinkController extends BaseController{
    /**
     * 友情链接首页
     * */
    public function friendlyLinkIndex(){
        //检查权限
        $this->checkPageAuth("AUTH_FRIENDLY_LINK");
        $this->display();
    }
    
    /**
     * 获取友情链接数据
     * */
    public function getFriendLinkData(){
        //状态
        $status = I("status");
        //查询值
        $queryStr = I("queryStr");
        
        if($status=="1"||$status=="0"){
            $where["status"] = $status;
        }
        
        if(!empty($queryStr)){
            $where["link_title"]=array('LIKE','%'. $queryStr .'%');
        }
        
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
        
        $friendlyLinkModel = M("FriendlyLink");
        $total = $friendlyLinkModel->where($where)->count();
        $res = $friendlyLinkModel->order("order_no asc,create_time desc")->where($where)->page($page,$rows)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    
    /**
     * 友情链接添加页面
     * */
    public function addFriendlyLinkPage(){
        $this->display();
    }
    
    /**
     * 添加友情链接
     * */
    public function addFriendlyLink(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            //链接标题
            $link_title = trim(I("link_title"));
            //链接网址
            $link_url = trim(I("link_url"));
            //logo
            $link_logo = trim(I("link_logo"));
            //状态
            $status = I("status");
            //排序号
            $order_no = I("order_no");
            //描述
            $description = trim(I("description"));
            
            $friendlyLinkModel = M("FriendlyLink");
            $data["link_title"] = $link_title;
            $data["link_url"] = $link_url;
            $data["link_logo"] = $link_logo;
            $data["order_no"] = $order_no;
            $data["status"] = $status;
            $data["create_time"] = date("Y-m-d H:i:s");
            $data["description"] = $description;
            $res = $friendlyLinkModel->add($data);
            if($res!==false){
                $result["success"] = true;
            }else{
                $result["success"] = false;
                $result["message"]="添加失败";
            }
        }else{
            $result["success"]=false;
            $result["message"]="非法操作";
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 编辑页面
     * */
    public function editFriendlyLinkPage(){
        $id = I("id");
        $friendlyLinkModel = M("FriendlyLink");
        $friendlyLink = $friendlyLinkModel->find($id);
        $this->assign("friendlyLink",$friendlyLink);
        $this->display();
    }
    
    /**
     * 更新友情链接页面
     * */
    public function updateFriendlyLink(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        if(IS_POST){
            //主键id
            $id=I("id");
            //链接标题
            $link_title = trim(I("link_title"));
            //链接网址
            $link_url = trim(I("link_url"));
            //logo
            $link_logo = trim(I("link_logo"));
            //状态
            $status = I("status");
            //排序号
            $order_no = I("order_no");
            //描述
            $description = trim(I("description"));
            
            $friendlyLinkModel = M("FriendlyLink");
            $data["id"] = $id;
            $data["link_title"] = $link_title;
            $data["link_url"] = $link_url;
            $data["link_logo"] = $link_logo;
            $data["order_no"] = $order_no;
            $data["status"] = $status;
            $data["create_time"] = date("Y-m-d H:i:s");
            $data["description"] = $description;
            
            $res = $friendlyLinkModel->save($data);
            if($res!==false){
                $result["success"] = true;
            }else{
                $result["success"] = false;
                $result["message"]="修改失败";
            }
        }else{
            $result["success"] = false;
            $result["message"]="非法操作";
        }
        
        $this->ajaxReturn($result);
    }
    
    /**
     *  设置状态
     * */
    public function setStatus(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //角色id字符串,逗号隔开
            $ids = I("ids");
            //用户状态,0：禁用，1:启用
            $status = I("status");
            if($status&&$status==1){
                $status=1;
            }else{
                $status=0;
            }
    
            $friendlyLinkModel = M("FriendlyLink");
            $data["status"] = $status;
            $where["id"] = array("in",$ids);
    
            $res = $friendlyLinkModel->where($where)->save($data);
    
            if($res!==false){
                $result["success"] = true;
                $result["message"]="操作成功";
            }else{
                $result["success"] = false;
                $result["message"]="操作失败";
            }
        }
        $this->ajaxReturn($result);
    }
    
    /**
     *  删除链接
     * */
    public function delLinks(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //用户ids字符串,逗号隔开
            $ids = I("ids");
            $friendlyLinkModel = M("FriendlyLink");
            //删除记录之前要清空图片
           $idsArray = explode(",", $ids);
           foreach ($idsArray as $itemId){
               $friendLink = $friendlyLinkModel->find($itemId);
               $link_logo = $friendLink["link_logo"];
               if($link_logo){
                   $link_logo = "./Uploads/".$link_logo;
                   removeFiles($link_logo);
               }
           }
            
            $where["id"] = array("in",$ids);
            $res = $friendlyLinkModel->where($where)->delete();
            if($res!==false){
                $result["success"] = true;
                $result["message"]="操作成功";
            }else{
                $result["success"] = false;
                $result["message"]="操作失败";
            }
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 上传logo
     * */
    public function uploadLogo(){
        //原图片地址
        $orgPath = I("path");
        //如果含有则删除
        if($orgPath){
            $orgPath= "./Uploads/".$orgPath;
            $res = removeFiles($orgPath);
            //如果删除成功，则清除表中的link_logo字段
            if($res){
                $id = I("id");
                if($id){
                    $FriendlyLinkModel = M("FriendlyLink");
                    $data["id"]=$id;
                    $data["link_logo"]="";
                    $FriendlyLinkModel->save($data);
                }
            }
        }
        $upload = new \Think\Upload();// 实例化上传类
        //文件上传的最大文件大小（以字节为单位），0为不限大小
        $upload->maxSize=3*1024*1024 ;
        //文件上传保存的根路径
        $upload->rootPath="./Uploads/";
        //文件上传的保存路径（相对于根路径）
        $upload->savePath = '/friendlylink/'; 
        //允许上传的文件后缀（留空为不限制），使用数组或者逗号分隔的字符串设置，默认为空
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        //自动使用子目录保存上传文件 默认为true
        $upload->autoSub = false;
        //子目录创建方式，采用数组或者字符串方式定义
        $upload->subName = array('date','Ymd');
        //上传文件的保存规则，支持数组和字符串方式定义
        $upload->saveName = array('uniqid','');
        // 上传文件     
        $info = $upload->upload();
        $result["success"] = false;
        $result["message"] = "";
        if(!$info) {
            $result["success"] = false;
            $result["message"] = "上传失败";
        }else{
            $result["success"] = true;
            $result["message"] = "上传成功";
            $result["savefile"] = $info["Filedata"]["savepath"].$info["Filedata"]["savename"];
        }
        //$result["info"]=$info;
        $this->ajaxReturn($result);
    }
    
    /**
     * 删除logo
     * */
    public function removeLogo(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            $path = I("path");
            $path="./Uploads/".$path;
            $res = removeFiles($path);
            if($res){
                $id = I("id");
                //如果含有id，说明是编辑页面，则对应的link_logo字段要清空
                if($id){
                    $FriendlyLinkModel = M("FriendlyLink");
                    $data["id"]=$id;
                    $data["link_logo"]="";
                    $FriendlyLinkModel->save($data);
                }
                $result["success"] = true;
                $result["message"]="删除图片成功!";
            }else{
                $result["success"] = false;
                $result["message"]="删除图片失败!";
            }
        }
        $this->ajaxReturn($result);
    }
    
}