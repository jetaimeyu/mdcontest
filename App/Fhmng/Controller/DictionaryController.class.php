<?php
/**日志
 * 模块：字典管理
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
class DictionaryController extends BaseController{
    
    /**
     * 字典管理页面
     * */
    public function dictionaryIndex(){
        //检查权限
        $this->checkPageAuth("AUTH_DICTIONARY");
        $this->display();
    }
    
    /**
     * 根据类型编码获取字典列表
     * @param $typeCode 字典类型编码
     * @return 字典列表
     * */
    public function getDicsByTypeCode($typeCode){
        $dicTypeModel = M("DictionaryType");
        $where["status"]=1;
        $where["type_code"]=$typeCode;
        $dicType = $dicTypeModel->where($where)->select();
        if($dicType){
            $dicModel = M("Dictionary");
            $where2["type_code"]=$typeCode;
            $where2["status"]=1;
            
            $dics = $dicModel->where($where2)->order("order_no asc")->select();
            return $dics;
            
        }else{
            return null;
        }
    }
   
    /**
     * 根据字典键获字典
     * @param $dic_key 键
     * @return 字典
     * */
    public function getDictByKey($dic_key){
        $dicModel = M("Dictionary");
        $where["status"]=1;
        $where["dic_key"]=$dic_key;
        
        $dic = $dicModel->where($where)->find();
        return $dic;
    }
    
    /**
     * 取得字典treegride列表
     * */
    public function getDictionaryTreeList(){
        $type_code = I("type_code");
        $pid_key = I("id");
        if(!$pid_key){
            $pid_key= "0";
        }
        
        $dictionaryModel = M("Dictionary");
        $where["type_code"] = $type_code;
        $where["pid_key"] =$pid_key;
        $dics = $dictionaryModel->where($where)->order("order_no asc")->select();
        
        //选择的字典类型下的所有的字典项
        $where2["type_code"] = $type_code;
        $totalDics = $dictionaryModel->where($where2)->order("order_no asc")->select();
        
        $treeArr = $this->getDictionaryTreeGridData($dics,$pid_key,$totalDics);
        $this->ajaxReturn($treeArr);
    
    }
    
    /**
     *@access 递归得到treegird树json
     *@param $store array 字典数据源
     *@param $pid_key int 父字典key
     *@param $$totalDics array 字典内所有的集合
     * */
    public function getDictionaryTreeGridData($store,$pid_key="0",$totalDics) {
        $returnArray = array();
        foreach ($store as $key=>$value){
            if($value["pid_key"]==$pid_key){
                //主键
                $item["id"] = $value["id"];
                $item["dic_key"] = $value["dic_key"];
                $item["dic_value"] = $value["dic_value"];
                $item["pid_key"] = $value["pid_key"];
                $item["type_code"] = $value["type_code"];
                $item["status"] = $value["status"];
                $item["order_no"] = $value["order_no"];
                $item["description"] = $value["description"];
                if($this->hasChild($totalDics, $value["dic_key"])){
                    $item["state"] = "closed";
                }else{
                    $item["state"] = "open";
                }
               
                array_push($returnArray, $item);
                unset($item);
            }
        }
        return $returnArray;
    }
    
    /**
     * 判断是否含有子节点
     * */
    public function hasChild($totalStore,$dic_key){
        $tag = false;
        foreach ($totalStore as $key=>$value){
            if($dic_key==$value["pid_key"]){
                $tag = true;
                break;
            }
        }
        return $tag;
    }
    
    /**
     * 添加字典页面
     * */
    public function addDictionaryPage(){
        $type_code = I("type_code");
        $this->assign("type_code",$type_code);
        $this->display();
    }
    
    /**
     * @access 取得上级字典树
     **/
    public function getPidDictionaryTree(){
        $hasRoot = false;
        //前台页面tree定义的valueField为id，所以此处接收的是id，其实就是dic_key字段
        $pid_key = I("id");
        if(!$pid_key){
            $pid_key = "0";
            $hasRoot = true;
        }
        $dictionaryModel = M("Dictionary");
       
        $dic_key = I("dic_key");
        //编辑时，去除本身
        if($dic_key){
            $where["dic_key"]=array("NEQ",$dic_key);
        }
        $type_code = I("type_code");
        $where["type_code"] = $type_code;
        $where["pid_key"] = $pid_key;
        $dics = $dictionaryModel->where($where)->order("order_no asc")->select();
        
        $where2["type_code"] = $type_code;
        //该字典类型下所有的字典项
        $totalDics = $dictionaryModel->where($where2)->order("order_no asc")->select();
        
      
        //获取树json串
        $treeArr = $this->getDictionaryTreeData($dics,$pid_key,$hasRoot,$totalDics);
        $store = json_encode($treeArr);
        $this->ajaxReturn($treeArr);
    }
    
    /**
     *@access 递归得到字典树json
     *@param $store 数组 功能数据源
     *@param $pid_key int 父字典id
     *@param $hasRootNode boolean 是否含有根节点
     * */
    public function getDictionaryTreeData($store,$pid_key="0",$hasRootNode=false,$totalDics) {
        $returnArray = array();
        if($hasRootNode){
            $root["id"]="0";
            $root["text"]="顶级字典";
            $root["state"] = "open";
            $root["iconCls"] = "icon-extend-deproot";
            array_push($returnArray, $root);
            unset($root);
        }
    
        foreach ($store as $key=>$value){
            if($value["pid_key"]==$pid_key){
                //主键
                $item["id"] = $value["dic_key"];
                $item["text"] = $value["dic_value"];
                
                if($this->hasChild($totalDics, $value["dic_key"])){
                    $item["state"] = "closed";
                }else{
                    $item["state"] = "open";
                }
                
                array_push($returnArray, $item);
                unset($item);
            }
        }
        return $returnArray;
    }
    
    /**
     * 添加字典
     * */
    public function addDictionary(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            //字典键
            $dic_key= trim(I("dic_key"));
            if($this->isExistDicKey($dic_key)){
                $result["success"]=false;
                $result["message"]="该字典键已存在";
                $result["validateCode"]="DICKEY_CODE_EXIST";
            }else{
                $dic_value = trim(I("dic_value"));
                $pid_key = I("pid_key");
                $type_code = I("type_code");
                $status = I("status");
                $order_no = I("order_no");
                $description = trim(I("description"));
        
                $dicModel = M("Dictionary");
                $data["dic_key"] = $dic_key;
                $data["dic_value"] = $dic_value;
                $data["pid_key"] = $pid_key;
                $data["type_code"] = $type_code;
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
        
                $res = $dicModel->add($data);
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
     * 编辑字典页面
     * */
    public function editDictionaryPage(){
        $id = I(id);
        $dictionaryModel = M("Dictionary");
        $dic = $dictionaryModel->find($id);
        
        $where["dic_key"] = $dic["pid_key"];
        $parentDic = $dictionaryModel->where($where)->select();
        if($parentDic){
            $parentValue =$parentDic[0]["dic_value"];
        }else{
            $parentValue = "顶级字典";
        }
        $this->assign("parentValue",$parentValue);
        $this->assign("dic",$dic);
        
        $type_code = I("type_code");
        $this->assign("type_code",$type_code);
        
        $this->display();
    }
    
    /**
     * 编辑字典
     * */
    public function updateDictionary(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            $dic_id = I("dic_id");
            $dic_key = trim(I("dic_key"));
            $dic_value = trim(I("dic_value"));
            $pid_key = I("pid_key");
            $status = I("status");
            $order_no = I("order_no");
            $description = trim(I("description"));
        
            $dicModel = M("Dictionary");
            $where["id"] = $dic_id;
            //取得原键值
            $orgDic_Key = $dicModel->where($where)->getField("dic_key");
        
            //判断功能编码是否修改过
            if($orgDic_Key!=$dic_key){
                if($this->isExistDicKey($dic_key)){
                    $result["success"] = false;
                    $result["message"]="该字典键已存在";
                    $result["validateCode"]="DIC_KEY_EXIST";
                }else{
                    $data["id"] = $dic_id;
                    $data["dic_key"] = $dic_key;
                    $data["dic_value"] = $dic_value;
                    $data["pid_key"] = $pid_key;
                    $data["status"] = $status;
                    $data["order_no"] = $order_no;
                    $data["description"] = $description;
                     
                    $res = $dicModel->save($data);
                    if($res!==false){
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $dic_id;
                $data["dic_key"] = $dic_key;
                $data["dic_value"] = $dic_value;
                $data["pid_key"] = $pid_key;
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
                 
                $res = $dicModel->save($data);
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
     * 前台页面验证是否存字典键
     * */
    public function validDickey(){
        $dic_key = trim(I("dic_key"));
    
        if($this->isExistDicKey($dic_key)){
            $result["isExist"] = true;
        }else{
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    /**
     * 前台编辑页面验证是否存在该类型编码
     * */
    public function validDicKeyByUpdate(){
        $dic_id = I("dic_id");
        $dic_key = trim(I("dic_key"));
    
        $dicionaryModel = M("Dictionary");
        $where["id"] = $dic_id;
        //取得原来的key
        $orgDicKey = $dicionaryModel->where($where)->getField("dic_key");
    
        //判断是否修改过
        if($orgDicKey!=$dic_key){
            $isExist = $this->isExistDicKey($dic_key);
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
    public function isExistDicKey($dic_key){
        $dicionaryModel = M("Dictionary");
        $dicKey = $dicionaryModel->getByDicKey($dic_key);
        if($dicKey){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 删除功能
     * */
    public function delDic(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id=I("id");
            $dic_key = trim(I("dic_key"));
            
            //判断该部门下是否含有子节点
            if($this->exsitChildDic($dic_key)){
                $result["success"] = false;
                $result["validateCode"] ="Exist_Child_Dic";
                $result["message"] = "该功能下含子节点，不允许删除";
            }else{
                $dicModel = M('Dictionary');
                $where["id"] = array("in",$id);
                $res = $dicModel->where($where) ->delete();
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
     * 判断是否含有子节点
     * @param $dic_key 字典键
     * @return boolean
     * */
    private function exsitChildDic($dic_key){
        $dicModel = M("Dictionary");
        $where["pid_key"]=$dic_key;
    
        $count = $dicModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     *  设置状态
     * */
    public function setDicStatus(){
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
    
            $dicModel = M("Dictionary");
            $data["status"] = $status;
            $where["id"] = array("in",$id);
    
            $res = $dicModel->where($where)->save($data);
    
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
    
}