<?php
/**日志
 * 模块：首页banner管理
 * 创建人:刘佳
 * 创建时间：2018-08-03
 * 模块权限code:AUTH_BANNER

 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class BannerController extends BaseController{
    /**
     * banner首页
     * */
    public function bannerIndex(){
        $this->checkPageAuth("AUTH_BANNER");
        $this->display();
    }
    
    /**ffs
     * 获取banner数据
     * */
    public function getBannerData(){
        $search = I('post.');
        $wh['title'] = ['like','%'.$search['like_title'].'%'];
        if($search['status'] != '-1' && $search['status']){
            $wh['status'] = $search['status'];
        }
        $data = M('banner')->where($wh)->order('order_no')->page($search['page'],$search['rows'])->select();
        $count = M('banner')->where($wh)->count();
        $return = ['rows'=>$data,'total'=>$count];
        
        $this->ajaxReturn($return);
    }
    
    /**
     *  添加banner页面
     * */
    public function addBannerPage(){
        $this->display();
    }
    
    /**
     * 添加banner
     * */
    public function addBanner(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            //图片
            $banner_img = trim(I("banner_img"));
        
            if(!$banner_img){
                $result["success"]=false;
                $result["message"]="请上传Banner图片";
                $result["validateCode"]="BANNER_IMG_NOTEXIST";
            }else{
                //标题
                $title = trim(I("title"));
                //链接网址
                $link_url = trim(I("link_url"));
                //状态
                $status = I("status");
        
                //排序号
                $order_no = I("order_no");
                //描述
                $description = trim(I("description"));
        
                $bannerModel = M("Banner");
                $data["title"] = $title;
                $data["link_url"] = $link_url;
                $data["banner_img"] = $banner_img;
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
                $res = $bannerModel->add($data);
                if($res!==false){
                    $result["success"] = true;
                    $result["message"]="添加成功";
                }else{
                    $result["success"] = false;
                    $result["message"]="添加失败";
                }
            }
        }else{
            $result["success"]=false;
            $result["message"]="非法操作";
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 编辑banner页面
     * */
    public function editBannerPage(){
        $id = I("id");
        $bannerModel = M("Banner");
        $banner = $bannerModel->find($id);
        $this->assign("banner",$banner);
        $this->display();
    }
    
    /**
     * 更新banner
     * */
    public function updateBanner(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        if(IS_POST){
            //主键id
            $id=I("id");
            //banner图片
            $banner_img = trim(I("banner_img"));
            //标题
            $title = trim(I("title"));
            //链接网址
            $link_url = trim(I("link_url"));
            //状态
            $status = I("status");
        
            //排序号
            $order_no = I("order_no");
            //描述
            $description = trim(I("description"));
        
            if(!$banner_img){
                $result["success"]=false;
                $result["message"]="请上传Banner图片";
                $result["validateCode"]="BANNER_IMG_NOTEXIST";
            }else{
                $bannerModel = M("Banner");
                $data["id"] = $id;
                $data["title"] = $title;
                $data["link_url"] = $link_url;
                $data["banner_img"] = $banner_img;
                $data["order_no"] = $order_no;
                $data["status"] = $status;
                $data["description"] = $description;
                $res = $bannerModel->save($data);
                if($res!==false){
                    $result["success"] = true;
                }else{
                    $result["success"] = false;
                    $result["message"]="添加失败";
                }
            }
        
        }else{
            $result["success"] = false;
            $result["message"]="非法操作";
        }
        
        $this->ajaxReturn($result);
    }
    
    /**
     *  删除
     * */
    public function delBanner(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //用户ids字符串,逗号隔开
            $ids = I("ids");
            $bannerModel = M("Banner");
            //删除记录之前要清空图片
            $idsArray = explode(",", $ids);
            foreach ($idsArray as $itemId){
                $banner = $bannerModel->find($itemId);
                $banner_img = $banner["banner_img"];
                if($banner_img){
                    $banner_img = "./Uploads/".$banner_img;
                    removeImg($banner_img);
                }
            }
    
            $where["id"] = array("in",$ids);
            $res = $bannerModel->where($where)->delete();
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
     *  设置状态
     * */

    public function setStatus(){
        if(IS_POST){
            $table = 'Banner';
            $ids = I("post.ids");
            $where["id"] = array("in",$ids);
            $field = 'status';
            $value = I('post.status');
            $res = $this->updateField($table,$where,$field,$value);
            $this->ajaxReturn($res);
        }
    }
    
  
    
    function upload_pic(){
        //原图片地址
        $res = upload_img('pic','banner');
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