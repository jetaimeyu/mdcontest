<?php
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class FunctionController extends BaseController{
    public function functionIndex(){
         //检查权限
        $this->checkPageAuth("AUTH_FUNCTION");
        $this->display();
    }
    
    /**
     * 添加功能页面
     * */
    public function addFuncPage(){
        $this->display();
    }
    
    /**
     * 添加功能
     * */
    public function addFunc(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            //功能编码
            $func_code= trim(I("func_code"));
            if($func_code&&$this->isExistFuncCode($func_code)){
                $result["success"]=false;
                $result["message"]="该功能编码已存在";
                $result["validateCode"]="FUNCTION_CODE_EXIST";
            }else{
                //功能名称
                $func_name = trim(I("func_name"));
                //功能连接
                $func_url = trim(I("func_url"));
                //上级部门
                $func_pid = I("func_pid");
                //状态
                $status = I("status");
                //类型：1：菜单，2：功能
                $type = I("type");
                //排序号
                $order_no = I("order_no");
                //描述
                $description = trim(I("description"));
        
                $funcModel = M("Function");
                $data["func_name"] = $func_name;
                $data["func_code"] = $func_code;
                $data["func_url"] = $func_url;
                $data["status"] = $status;
                $data["pid"] = $func_pid;
                $data["type"] = $type;
                $data["order_no"] = $order_no;
                $data["create_time"] = date("Y-m-d H:i:s");
                $data["description"] = $description;
        
                $res = $funcModel->add($data);
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
     * 编辑功能页面
     * */
    public function editFuncPage(){
        //功能id
        $id = I("id");
        $funcModel = M("Function");
        $func = $funcModel->find($id);
        $this->assign("func",$func);
        $this->display();
    }
    
    /**
     * 更新功能
     * */
    public function updateFunc(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            $func_id = I("func_id");
            $func_name = trim(I("func_name"));
            $func_code = trim(I("func_code"));
            $func_url = trim(I("func_url"));
            $pid = I("func_pid");
            $status = I("status");
            $type = I("type");
            $order_no = I("order_no");
            $description = trim(I("description"));
        
            $funcModel = M("Function");
            $where["id"] = $func_id;
            //取得原功能编码
            $orgFunc_code = $funcModel->where($where)->getField("func_code");
        
            //判断功能编码是否修改过
            if($func_code&&$orgFunc_code!=$func_code){
                if($this->isExistFuncCode($func_code)){
                    $result["success"] = false;
                    $result["message"]="该功能编码已存在";
                    $result["validateCode"]="FUNC_CODE_EXIST";
                }else{
                    $data["id"] = $func_id;
                    $data["func_name"] = $func_name;
                    $data["func_code"] = $func_code;
                    $data["func_url"] = $func_url;
                    $data["status"] = $status;
                    $data["pid"] = $pid;
                    $data["type"] = $type;
                    $data["order_no"] = $order_no;
                    $data["description"] = $description;
                     
                    $res = $funcModel->save($data);
                    if($res!==false){
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $func_id;
                $data["func_name"] = $func_name;
                $data["func_code"] = $func_code;
                $data["func_url"] = $func_url;
                $data["status"] = $status;
                $data["pid"] = $pid;
                $data["type"] = $type;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
                 
                $res = $funcModel->save($data);
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
     * 取得功能treegride列表
     * */
    public function getFunctionTreeList(){
        $funcModel = M("Function");
        $deps = $funcModel->order("order_no asc")->select();
        $treeArr = $this->getFunctionTreeGridData($deps,0);
        $this->ajaxReturn($treeArr);
    
    }
    
    /**
     *@access 递归得到treegird树json
     *@param $store 数组功能数据源
     *@param $pid int 父部门id
     * */
    public function getFunctionTreeGridData($store,$pid="0") {
        $returnArray = array();
        foreach ($store as $key=>$value){
            if($value["pid"]==$pid){
                //主键
                $item["id"] = $value["id"];
                //部门名称
                $item["func_name"] = $value["func_name"];
                $item["func_code"] = $value["func_code"];
                $item["func_url"] = $value["func_url"];
                $item["pid"] = $value["pid"];
                $item["status"] = $value["status"];
                $item["type"] = $value["type"];
                $item["order_no"] = $value["order_no"];
                $item["create_time"] = $value["create_time"];
                $item["description"] = $value["description"];
                $item["state"] = "open";
                $item["children"] = $this->getFunctionTreeGridData($store,$value["id"]);
                if (!$item["children"]) {
                    unset($item["children"]);
                    $item["iconCls"]="icon-extend-module";
                }
                array_push($returnArray, $item);
                unset($item);
            }
        }
        return $returnArray;
    }
    
    /**
     * 取得上级功能树
     **/
    public function getPidFunctionTree(){
        $id = I("id");
        //编辑时，去除本身
        if($id){
            $where["id"]=array("NEQ",$id);
        }
        $funcModel = M("Function");
        $funcs = $funcModel->where($where)->order("order_no asc")->select();
        //获取部门树json串
        $treeArr = $this->getFunctionTreeData($funcs,0,true);
        $store = json_encode($treeArr);
        $this->ajaxReturn($treeArr);
    }
    
    /**
     *递归得到功能树json
     *@param $store 数组 功能数据源
     *@param $pid int 父功能id
     *@param $hasRootNode boolean 是否含有根节点
     * */
    public function getFunctionTreeData($store,$pid="0",$hasRootNode=false) {
        $returnArray = array();
        if($hasRootNode){
            $root["id"]="0";
            $root["text"]="顶级功能";
            $root["state"] = "open";
            $root["iconCls"] = "icon-extend-deproot";
            array_push($returnArray, $root);
            unset($root);
        }
    
        foreach ($store as $key=>$value){
            if($value["pid"]==$pid){
                //主键
                $item["id"] = $value["id"];
                //功能名称
                $item["text"] = $value["func_name"];
                $item["state"] = "open";
                $item["children"] = $this->getFunctionTreeData($store,$value["id"],false);
                if (!$item["children"]) {
                    unset($item["children"]);
                    $item["iconCls"]="icon-extend-module";
                }
                array_push($returnArray, $item);
                unset($item);
            }
        }
        return $returnArray;
    }
    
    /**
     * 前台页面验证是否存在该功能权限码
     * */
    public function validFuncCode(){
        $func_code = trim(I("func_code"));
        
        if($this->isExistFuncCode($func_code)){
            $result["isExist"] = true;
        }else{
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    /**
     * 前台编辑页面验证是否存在该功能权限码
     * */
    public function validFuncCodeByUpdate(){
        $func_id = I("func_id");
        $func_code= trim(I("func_code"));
        
        $funcModel = M("Function");
        $where["id"] = $func_id;
        //取得原来的功能编码
        $orgFuncCode = $funcModel->where($where)->getField("func_code");
        
        //判断是否修改过
        if($orgFuncCode&&$orgFuncCode!=$func_code){
            $isExist = $this->isExistFuncCode($func_code);
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
     * 是否存在功能权限编码
     * */
    public function isExistFuncCode($func_code){
        $funcModel = M("Function");
        $func = $funcModel->getByFuncCode($func_code);
        if($func){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 删除功能
     * */
    public function delFunc(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id = I("id");
            //判断该功能下是否含有子功能
            if($this->exsitChildFunc($id)){
                $result["success"] = false;
                $result["validateCode"] ="Exist_Child_Dep";
                $result["message"] = "该功能下含子功能，不允许删除";
            }else{
                $funcModel = M('Function');
                $where["id"] = array("in",$id);
                $res = $funcModel->where($where) ->delete();
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
     * 判断是否含有子功能
     * @param $func_id 功能id
     * @return boolean
     * */
    private function exsitChildFunc($func_id){
        $funcModel = M("Function");
        $where["pid"]=$func_id;
    
        $count = $funcModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     *  设置功能状态
     * */
    public function setFuncStatus(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //部门id
            $id = I("id");
            //状态,0：禁用，1:启用
            $status = I("status");
            if($status&&$status==1){
                $status=1;
            }else{
                $status=0;
            }
    
            $funcModel = M("Function");
            $data["status"] = $status;
            $where["id"] = array("in",$id);
    
            $res = $funcModel->where($where)->save($data);
    
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
     * 取得菜单
     * liuj-ad
    **/
    // function getMenuList($pid){
    //     $functionModel = M("Function");
    //     //系统菜单
    //     $wh['func_code'] = 'SYESTEM_COFG';
    //     $syspid = $functionModel->where($wh)->getField('id');
    //     //状态：0:禁用，1:启用
    //     $where["status"]=1;
    //     //功能类型,1：菜单，2：功能
    //     $where["type"]=1;
    //     if(isset($pid)){
    //         $where["pid"]=$pid;
    //     }
    //     $functions = $functionModel->where($where)->order("order_no asc")->select();
    //     $menus = [];
    //     //admin 可以访问系统管理
    //     $admin = session('session_user')['login_name'];
    //     if($admin == 'admin'){
    //         return $functions;
    //     }else{
    //         foreach ($functions as $key => $value) {
    //             if(($value['pid'] != $syspid) && ($value['id'] != $syspid)) {   
    //                 array_push($menus,$value);
    //             }  
    //         }
    //         return $menus;
    //     }
    // }
     /**
     * 取得菜单
     * */
    public function getMenu($pid=null){
        $functionModel = M("Function");
        //状态：0:禁用，1:启用
        $where["status"]=1;
        //功能类型,1：菜单，2：功能
        $where["type"]=1;
        if(isset($pid)){
            $where["pid"]=$pid;
        }
        $functions = $functionModel->where($where)->order("order_no asc")->select();
        //用户权限串
        $authStr = $this->session_authStr;
        //all：表示拥有全部权限
        if($authStr=="all"){
            return $functions;
        }else{
            $menus = array();
            foreach ($functions as $menu){
                $findStr = ",".$menu["id"].",";
                //检查功能菜单是否在权限内
                $index = strpos(",".$authStr.",", $findStr);
                if($index!==false){
                    array_push($menus, $menu);
                }
            }
            
            return $menus;
        }
    }
    
    /**
     * 取得所有的功能
     * 
     * */
    public function getAllFunctions(){
        $functionModel = M("Function");
        //状态：0:禁用，1:启用
        $where["status"]=1;
        //功能类型,1：菜单，2：功能
//         $where["type"]=1;
        
        $functions = $functionModel->where($where)->order("order_no asc")->select();
        
        return $functions;
    }
}