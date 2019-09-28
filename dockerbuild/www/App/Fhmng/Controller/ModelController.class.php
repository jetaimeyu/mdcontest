<?php
/**日志
 * 模块：模型管理
 * 创建人:李宝振
 * 创建时间：2016-09-06
 * 模块权限code:AUTH_MODEL
 * 描述：
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class ModelController extends BaseController{
    /**
     * 模型管理首页
     * */
    public function modelIndex(){
        $this->display();
    }
    
    /**
     * 取得模型数据
     * */
    public function getModelList(){
        //状态
        $status = I("status");
        //查询值
        $queryStr = I("queryStr");
        if($status=="1"||$status=="0"){
            $where["status"] = $status;
        }
        if(!empty($queryStr)){
            $where["model_name"]=array('LIKE','%'. $queryStr .'%');
        }
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
        
        $modelModel = M("Model");
        $total = $modelModel->where($where)->count();
        $res = $modelModel->order("order_no asc,create_time desc")->where($where)->page($page,$rows)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    
    /**
     * 添加模型页面
     * */
    public function addModelPage(){
        $this->display();
    }
    
    /**
     * 添加模型
     * */
    public function addModel(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        
        if(IS_POST){
            //模型编码
            $model_code = trim(I("model_code"));
            //后台验证是否存在
            if($this->existModelCode($model_code)){
                $result["success"] = false;
                $result["validateCode"] = "MODEL_CODE_EXIST";
                $result["message"] = "该模型编码已存在";
            }else{
                //模型名称
                $model_name = trim(I("model_name"));
                //状态,0:禁用，1:启用
                $status = I("status");
                //排序号
                $order_no = I("order_no");
                //描述
                $description = trim(I("description"));
        
                $modelModel = M("Model");
                $data["model_name"] = $model_name;
                $data["model_code"] = $model_code;
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
                $data["create_time"] = date("Y-m-d H:i:s");
                $res = $modelModel->add($data);
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
     * 模型编辑页面
     * */
    public function  editModelPage(){
        $id = I("id");
        $model = M("Model")->find($id);
        $this->assign("model",$model);
        $this->display();
    }
    
    /**
     * 更新模型
     * */
    public function updateModel(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        if(IS_POST){
            //id
            $id=I("id");
            //模型名称
            $model_name = trim(I("model_name"));
            //模型编码
            $model_code = trim(I("model_code"));
            //状态
            $status = I("status");
            //排序号
            $order_no = I("order_no");
            //描述
            $description = trim(I("description"));
        
            $modelModel = M("Model");
            $where["id"] = $id;
            //取得原来模型编码
            $orgModelCode= $modelModel->where($where)->getField("model_code");
            //判断是否修改过
            if($orgModelCode!=$model_code){
                if($this->existModelCode($model_code)){
                    $result["success"] = false;
                    $result["validateCode"] = "MODEL_CODE_EXIST";
                    $result["message"] = "该模型编码已存在";
                }else{
                    $data["id"] = $id;
                    $data["model_name"] = $model_name;
                    $data["model_code"] = $model_code;
                    $data["status"] = $status;
                    $data["order_no"] = $order_no;
                    $data["description"] = $description;
                     
                    $res = $modelModel->save($data);
                    if($res!==false){
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $id;
                $data["model_name"] = $model_name;
                $data["model_code"] = $model_code;
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
                $res = $modelModel->save($data);
                if($res!==false){
                    $result["success"] = true;
                }else{
                    $result["success"] = false;
                    $result["message"]="修改失败";
                }
            }
        
        }else{
            $result["success"] = false;
            $result["message"]="非法操作";
        }
        
        $this->ajaxReturn($result);
    }
    
    /**
     * 是否存在模型编码
     * @access public
     * @return boolean
     * */
    public function existModelCode($model_code){
        $modelModel = M("Model");
        $model = $modelModel->getByModelCode($model_code);
    
        if($model){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 前台页面验证是否存在模型编码
     * */
    public function validModelCode(){
        $model_code = trim(I("model_code"));
    
        if($this->existModelCode($model_code)){
            $result["isExist"] = true;
        }else{
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 更新页面前台判断模型编码是否已存在
     * @access public
     * @return boolean
     * */
    public function validModelCodeByUpdate(){
        $id = I("id");
        $model_code= trim(I("model_code"));
    
        $modelModel = M("Model");
        $where["id"] = $id;
        //取得原来的模型编码
        $orgModelCode = $modelModel->where($where)->getField("model_code");
    
        //判断是否修改过
        if($orgModelCode!=$model_code){
            $isExist = $this->existModelCode($model_code);
            if($isExist){
                $result["isExist"] = true;
            }else{
                $result["isExist"] = false;
            }
    
        }else{
            //如果没有被修改过，则认为不存在
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    
    /**
     *  删除模型
     * */
    public function delModel(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id = I("id");
            if($this->existFields($id)){
                $result["success"] = false;
                $result["validateCode"] ="MODEL_Exist_FIELDS";
                $result["message"] = "该模型下含有字段设置，不允许删除";
            }else if($this->hasRefrence($id)){
                $result["success"] = false;
                $result["validateCode"] ="MODEL_HAS_REFRENCE";
                $result["message"] = "该模型已被引用，不允许删除";
            }else{
                $modelModel = M('Model');
                $where["id"] = array("in",$id);
                $res = $modelModel->where($where) ->delete();
    
                if($res!==false){
                    $result["success"] = true;
                    $result["message"] = "操作成功";
                }else{
                    $result["success"] = false;
                    $result["message"] = "操作失败";
                }
            }
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
    
            $modelModel = M("Model");
            $data["status"] = $status;
            $where["id"] = array("in",$ids);
    
            $res = $modelModel->where($where)->save($data);
    
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
     * 判断模型下是否含有字段
     * */
    public function existFields($model_id){
        $fieldModel = M("Fields");
        $where["model_id"]=$model_id;
        $fields = $fieldModel->where($where)->count();
        if($fields>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 判断模型是否被引用
     * */
    public function hasRefrence($model_id){
        $menuModel = M("Menu");
        $where["model_id"] = $model_id;
        $count = $menuModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 取得模型下拉框的数据源
     * */
    public function getModelComboStore(){
        $modelModel = M("Model");
        $where["status"]=1;
        $models = $modelModel->field("id,model_name")->where($where)->order("order_no asc")->select();
        $this->ajaxReturn($models) ;
    }
}