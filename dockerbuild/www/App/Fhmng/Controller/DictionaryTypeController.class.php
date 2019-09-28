<?php
/**日志
 * 模块：字典类型管理
 * 创建人:李宝振
 * 创建时间：2016-08-18
 * 模块权限code:AUTH_DICTIONARY
 * 描述：
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;

use Fhmng\Common\Controller\BaseController;
class DictionaryTypeController extends BaseController{
    //=====================start==========================================
    /**
     * 取得字典类型
     * */
    public function getDicTypes(){
        $DicionaryTypeModel = M("DictionaryType");
        $where["status"]=1;
        $dictionaryTypes = $DicionaryTypeModel->where($where)->select();
        return $dictionaryTypes;
    }
    //=====================end==========================================
    
    /**
     * 新增字典类型页面
     * */
    public function addDicTypePage(){
        $this->display();
    }
    
    /**
     * 字典类型列表
     * */
    public function getDicTypeList(){
        $DicionaryTypeModel = M("DictionaryType");
        $dictionaryTypes = $DicionaryTypeModel->select();
    
        $this->ajaxReturn($dictionaryTypes);
    }
    
    /**
     * 添加字典类型
     * */
    public function addDicType(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            //类型编码
            $type_code= trim(I("type_code"));
            if($this->isExistTypeCode($type_code)){
                $result["success"]=false;
                $result["message"]="该类型编码已存在";
                $result["validateCode"]="TYPE_CODE_EXIST";
            }else{
                //类型名称
                $type_name = trim(I("type_name"));
                //状态
                $status = I("status");
        
                $dictionartTypeModel = M("DictionaryType");
                $data["type_name"] = $type_name;
                $data["type_code"] = $type_code;
                $data["status"] = $status;
               
        
                $res = $dictionartTypeModel->add($data);
                if($res!==false){
                    $result["success"] = true;
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
     * 字典类型编辑页面
     * */
    public function editDicTypePage(){
        $id=I("id");
        $dicTypeModel = M("DictionaryType");
        $dicType = $dicTypeModel->find($id);
        $this->assign("dicType",$dicType);
        $this->display();
    }
    
    /**
     * 更新功能
     * */
    public function updateDicType(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
    
        if(IS_POST){
            $type_id = I("type_id");
            $type_code = trim(I("type_code"));
            $type_name = trim(I("type_name"));
            $status = I("status");
            
            $dicTypeModel = M("DictionaryType");
            $where["id"] = $type_id;
            //取得原功能编码
            $orgType_Code = $dicTypeModel->where($where)->getField("type_code");
    
            //判断类型编码是否修改过
            if($orgType_Code!=$type_code){
                if($this->isExistTypeCode($type_code)){
                    $result["success"] = false;
                    $result["message"]="该类型编码已存在";
                    $result["validateCode"]="TYPE_CODE_EXIST";
                }else{
                    //根据字典表原来的type_code更新字典表
                    $dicModel = M("Dictionary");
                    //开启事务
                    $dicModel->startTrans();
                    $dicWhere["type_code"]=$orgType_Code;
                    $dicData["type_code"]=$type_code;
                    $res1=$dicModel->where($dicWhere)->save($dicData);
                    
                    $data["id"] = $type_id;
                    $data["type_name"] = $type_name;
                    $data["type_code"] = $type_code;
                    $data["status"] = $status;
                     
                    $res = $dicTypeModel->save($data);
                    if($res!==false){
                        $dicModel->commit();
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $type_id;
                $data["type_name"] = $type_name;
                $data["type_code"] = $type_code;
                $data["status"] = $status;
                 
                $res = $dicTypeModel->save($data);
                if($res!==false){
                    $result["success"] = true;
                }else{
                    $result["success"] = false;
                    $result["message"]="修改失败";
                }
            }
    
        }else{
            $result["success"] = false;
            $result["message"] = "非法操作";
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 删除功能
     * */
    public function delDicType(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id = I("id");
            $type_code = trim(I("type_code"));
            //判断该类型下是否含有字典项
            if($this->exsitDictionary($type_code)){
                $result["success"] = false;
                $result["validateCode"] ="Exist_Child_Dep";
                $result["message"] = "该类型下含有字典项，不允许删除";
            }else{
                $dicTypeModel = M('DictionaryType');
                $where["id"] = array("in",$id);
                $res = $dicTypeModel->where($where) ->delete();
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
     *  设置字典类型状态
     * */
    public function setDicTypeStatus(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //部门id
            $id = I("id");
            //用户状态,0：禁用，1:启用
            $status = I("status");
            if($status&&$status==1){
                $status=1;
            }else{
                $status=0;
            }
    
            $dicTypeModel = M("DictionaryType");
            $data["status"] = $status;
            $where["id"] = array("in",$id);
    
            $res = $dicTypeModel->where($where)->save($data);
    
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
     *根据字典类型编码判断是否含有字典项
     * */
    public function exsitDictionary($type_code){
        $dictionaryModel = M("Dictionary");
        $where["type_code"] = $type_code;
        
        $res = $dictionaryModel->where($where)->count();
        if($res>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 前台页面验证是否存在该类型编码
     * */
    public function validDicTypeCode(){
        $type_code = trim(I("type_code"));
    
        if($this->isExistTypeCode($type_code)){
            $result["isExist"] = true;
        }else{
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    /**
     * 前台编辑页面验证是否存在该类型编码
     * */
    public function validTypeCodeByUpdate(){
        $type_id = I("type_id");
        $type_code= trim(I("type_code"));
    
        $dicionaryTypeModel = M("DictionaryType");
        $where["id"] = $type_id;
        //取得原来的类型编码
        $orgTypeCode = $dicionaryTypeModel->where($where)->getField("type_code");
    
        //判断是否修改过
        if($orgTypeCode!=$type_code){
            $isExist = $this->isExistTypeCode($type_code);
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
     * 是否存在类型编码
     * */
    public function isExistTypeCode($type_code){
        $type_code = strtolower($type_code);
        $dicionaryTypeModel = M("DictionaryType");
        $dicType = $dicionaryTypeModel->getByTypeCode($type_code);
        if($dicType){
            return true;
        }else{
            return false;
        }
    }
}