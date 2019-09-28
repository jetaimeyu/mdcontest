<?php
/**日志
 * 模块：模型字段管理
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
class FieldsController extends BaseController{
    /**
     * 根据模型id获取字段配置信息
     * */
    public function getFieldsByModelId($model_id){
        $fieldModel = M("Fields");
        $where["model_id"] = $model_id;
        $fields = $fieldModel->where($where)->order("order_no asc")->select();
        return $fields;
    }
    
    /**
     * 取得模型字段数据列表
     * */
    public function getFieldsList(){
        //模型id
        $model_id = I("model_id");
        //状态
        $status = I("status");
        //查询值
        $queryStr = I("queryStr");
        
        $where["model_id"] = $model_id;
        if($status=="1"||$status=="0"){
            $where["status"] = $status;
        }
        if(!empty($queryStr)){
            $where["field_alias"]=array('LIKE','%'. $queryStr .'%');
        }
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
        
        $fieldsViewModel = D("FieldsView");
        $total = $fieldsViewModel->where($where)->count();
        $res = $fieldsViewModel->order("order_no asc,create_time desc")->where($where)->page($page,$rows)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    
    /**
     * 从字典表里取得字段的类型
     * */
    public function getFieldsType(){
        $dics = A("Fhmng/Dictionary")->getDicsByTypeCode("field_type");
        $this->ajaxReturn($dics);
    }
    
    /**
     * 新增字段页面
     * */
    public function addFieldPage(){
        $model_id = I("model_id");
        $this->assign("model_id",$model_id);
        $this->display();
    }
    
    /**
     * 添加字段
     * */
    public function addField(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        //模型id
        $model_id = I("model_id");
        //字段名称
        $field_name = trim(I("field_name"));
        if(IS_POST){
            if($this->existFieldName($model_id, $field_name)){
                $result["success"] = false;
                $result["validateCode"] = "FIELD_NAME_EXIST";
                $result["message"]="该模型下，该字段名称已存在";
            }else{
                //字段别名
                $field_alias = trim(I("field_alias"));
                
                //字段类型
                $field_type = I("field_type");
                //字典类型编码
                $dic_type_code = I("dic_type_code");
                //状态,0:禁用，1:启用
                $status = I("status");
                //排序号
                $order_no = I("order_no");
                //描述
                $description = trim(I("description"));
                
                $fieldsModel = M("Fields");
                $data["model_id"] = $model_id;
                $data["field_alias"] = $field_alias;
                $data["field_name"] = $field_name;
                $data["field_type"] = $field_type;
                if($dic_type_code&&$field_type=="combobox"){
                    $data["dic_type_code"] = $dic_type_code;
                }else{
                    $data["dic_type_code"]=null;
                }
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
                $data["create_time"] = date("Y-m-d H:i:s");
                
                $res = $fieldsModel->add($data);
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
     * 编辑字段页面
     * */
    public function editFieldPage(){
        $id = I("id");
        $field = M("Fields")->find($id);
        $this->assign("field",$field);
        $this->display();
    }
    
    /**
     * 更新字段
     * */
    public function updateField(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        if(IS_POST){
            //主键
            $id = I("id");
            //模型id
            $model_id = I("model_id");
            //字段名称
            $field_name = trim(I("field_name"));
             //字段别名
            $field_alias = trim(I("field_alias"));
            
            //字段类型
            $field_type = I("field_type");
            //字典类型编码
            $dic_type_code = I("dic_type_code");
            
            //状态,0:禁用，1:启用
            $status = I("status");
            //排序号
            $order_no = I("order_no");
            //描述
            $description = trim(I("description"));
        
            $fieldsModel = M("Fields");
            $where["id"] = $id;
            //取得原来字段名称
            $orgFieldName= $fieldsModel->where($where)->getField("field_name");
            //判断是否修改过
            if($orgFieldName!=$field_name){
                if($this->existFieldName($field_name)){
                    $result["success"] = false;
                    $result["validateCode"] = "FIELD_NAME_EXIST";
                    $result["message"] = "该模型下，该字段名称已存在";
                }else{
                    $data["id"] = $id;
                    $data["field_name"] = $field_name;
                    $data["field_alias"] = $field_alias;
                    $data["field_type"] = $field_type;
                    $data["dic_type_code"] = $dic_type_code;
                    $data["status"] = $status;
                    $data["order_no"] = $order_no;
                    $data["description"] = $description;
                     
                    $res = $fieldsModel->save($data);
                    if($res!==false){
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $id;
                $data["field_name"] = $field_name;
                $data["field_alias"] = $field_alias;
                $data["field_type"] = $field_type;
                $data["dic_type_code"] = $dic_type_code;
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
                 
                $res = $fieldsModel->save($data);
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
     * 同一个模型下验证是否存在字段名称
     * @access public
     * @param $model_id 模型id
     * @param $field_name 字段名称
     * @return boolean
     * */
    public function existFieldName($model_id,$field_name){
        $fieldsModel = M("Fields");
        $where["model_id"] = $model_id;
        $where["field_name"] = $field_name;
        $field = $fieldsModel->where($where)->select();
    
        if($field){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 前台页面验证是否存在字段名称
     * */
    public function validFieldNameExist(){
        $model_id= I("model_id");
        $field_name = trim(I("field_name"));
    
        if($this->existFieldName($model_id,$field_name)){
            $result["isExist"] = true;
        }else{
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 更新页面前台判断字段编码是否已存在
     * @access public
     * @return boolean
     * */
    public function validFieldNameExistByUpdate(){
        //字段主键
        $id = I("id");
        //模型id
        $model_id = I("model_id");
        //字段名称
        $field_name = trim(I("field_name"));
        
        $fieldsModel = M("Fields");
        $where["id"] = $id;
        $where["model_id"] = $model_id;
        //取得原来的字段名称
        $orgFieldlName = $fieldsModel->where($where)->getField("field_name");
    
        //判断是否修改过
        if($orgFieldlName!=$field_name){
            $isExist = $this->existFieldName($model_id,$field_name);
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
     * 删除字段
     * */
    public function delField(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id = I("id");
            $fieldsModel = M('Fields');
            $where["id"] = array("in",$id);
            $res = $fieldsModel->where($where) ->delete();
    
            if($res!==false){
                $result["success"] = true;
                $result["message"] = "操作成功";
            }else{
                $result["success"] = false;
                $result["message"] = "操作失败";
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
            //id字符串,逗号隔开
            $ids = I("ids");
            //状态,0：禁用，1:启用
            $status = I("status");
            if($status&&$status==1){
                $status=1;
            }else{
                $status=0;
            }
    
            $fieldModel = M("Fields");
            $data["status"] = $status;
            $where["id"] = array("in",$ids);
    
            $res = $fieldModel->where($where)->save($data);
    
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
     * 取得字典类型列表
     * */
    public function getDicTypes(){
        $dicTypes = A("Fhmng/DictionaryType")->getDicTypes();
        $this->ajaxReturn($dicTypes);
    }
}