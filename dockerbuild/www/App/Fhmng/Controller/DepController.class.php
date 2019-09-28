<?php
/**日志
 * 模块：部门
 * 创建人:李宝振
 * 创建时间：2016-07-29
 * 模块权限code:AUTH_DEP
 * 描述：
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
use Org\Util\ArrayList;
class DepController extends BaseController{
    /**
     * @access 部门列表
     * */
    public function depIndex(){
        // //检查权限
        $this->checkPageAuth("AUTH_DEP");
        $this->display();
    }
    
    /**
     * 部门添加页面
     * */
    public function addDepPage(){
        $this->display();
    }
    
    /**
     * 添加部门
     * */
    public function addDep(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            //部门名称
            $dep_name= trim(I("dep_name"));
            if($this->isExistDepName($dep_name)){
                $result["success"]=false;
                $result["message"]="该部门名称已存在！";
                $result["validateCode"]="DEP_NAME_EXIST";
            }else{
                //上级部门
                $dep_pid = I("dep_pid");
                
                //状态
                $status = I("status");
                //排序号
                $order_no = I("order_no");
                //描述
                $description = trim(I("description"));
                
                $depModel = M("Dep");
                $data["dep_name"] = $dep_name;
                $data["pid"] = $dep_pid;
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["create_time"] = date("Y-m-d H:i:s");
                $data["description"] = $description;
                $data["is_del"] = 0;
                
                $res = $depModel->add($data);
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
     * 编辑页面
     * */
    public function editDepPage(){
        //部门id
        $id = I("id");
        $depModel = M("Dep");
        $dep = $depModel->find($id);
        $this->assign("dep",$dep);
        $this->display();
    }
    
    /**
     * 编辑部门
     * */
    public function updateDep(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            $dep_id = I("dep_id");
            $dep_name = trim(I("dep_name"));
            $pid = I("dep_pid");
            $status = I("status");
            $order_no = I("order_no");
            $description = trim(I("description"));
            
            $depModel = M("Dep");
            $where["id"] = $dep_id;
            //原部门名称
            $orgDep_name = $depModel->where($where)->getField("dep_name");
            
            //判断部门名是否修改过
            if($orgDep_name!=$dep_name){
                if($this->isExistDepName($dep_name)){
                    $result["success"] = false;
                    $result["message"]="该部门名称已存在！";
                    $result["validateCode"]="DEP_NAME_EXIST";
                }else{
                    $data["id"] = $dep_id;
                    $data["dep_name"] = $dep_name;
                    $data["pid"] = $pid;
                    $data["status"] = $status;
                    $data["order_no"] = $order_no;
                    $data["description"] = $description;
                     
                    $res = $depModel->save($data);
                    if($res!==false){
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $dep_id;
                $data["dep_name"] = $dep_name;
                $data["pid"] = $pid;
                $data["status"] = $status;
                $data["order_no"] = $order_no;
                $data["description"] = $description;
                 
                $res = $depModel->save($data);
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
     * 前台页面验证是否存在角色名称
     * */
    public function validDepName(){
        $dep_name = trim(I("dep_name"));
    
        if($this->isExistDepName($dep_name)){
            $result["isExist"] = true;
        }else{
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    
    
    /**
     * 更新页面前台判断登录名是否已经存在
     * @access public
     * @return boolean
     * */
    public function validDepNameByUpdate(){
        $dep_id = I("dep_id");
        $dep_name = trim(I("dep_name"));
    
        $depModel = M("Dep");
        $where["id"] = $dep_id;
        //取得原来的部门名称
        $orgDepName = $depModel->where($where)->getField("dep_name");
    
        //判断是否修改过
        if($orgDepName!=$dep_name){
            $isExist = $this->isExistDepName($dep_name);
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
     * 是否存在部门名称
     * */
    public function isExistDepName($dep_name){
        $depModel = M("Dep");
        $wh['dep_name'] = $dep_name;
        $wh['status'] = 1;
        $wh['is_del'] = 0;
        
        $dep = $depModel->where($wh)->count();
        if($dep){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 取得部门treegride列表
     * */
    public function getDepTreeList(){
        $depModel = M("Dep");
        $where['is_del'] = 0;
        
        $deps = $depModel->order("order_no asc")->where($where)->select();
        $treeArr = $this->getDepTreeGridData($deps,0);
        $this->ajaxReturn($treeArr);
        
    }
    /**
     * 取得部门treegride列表
     * */
    function getDepTreeListSelect(){
        $depModel = M("Dep");
        $where['status'] = 1;
        $where['is_del'] = 0;
        
        
        $deps = $depModel->order("order_no asc")->where($where)->select();

        $treeArr = $this->getDepTreeGridData($deps,0);
        $all['id'] = 0;
        $all['text'] = '全部';
        array_unshift($treeArr,$all);
        $this->ajaxReturn($treeArr);
    }
    /**
     * 取得部门treegride列表
     * */
    function getDepTreeListDepart(){
        $depModel = M("Dep");
        $where['status'] = 1;
        $where['is_del'] = 0;
        
        $deps = $depModel->order("order_no asc")->where($where)->select();

        $treeArr = $this->getDepTreeGridData($deps,0);
        $all['id'] = '';
        $all['text'] = '无';
        array_unshift($treeArr,$all);
        $this->ajaxReturn($treeArr);
    }
    /**
     *@access 递归得到treegird树json
     *@param $store 数组 部门数据源
     *@param $pid int 父部门id
     * */
    public function getDepTreeGridData($store,$pid="0") {
        $returnArray = array();
        foreach ($store as $key=>$value){
            if($value["pid"]==$pid  ){
                //主键
                $item["id"] = $value["id"];
                //部门名称
                $item["dep_name"] = $value["dep_name"];
                $item["text"] = $value["dep_name"];
                $item["meeting_name"] = $value["meeting_name"];
                $item["pid"] = $value["pid"];
                $item["status"] = $value["status"];
                $item["order_no"] = $value["order_no"];
                $item["create_time"] = $value["create_time"];
                $item["description"] = $value["description"];
                $item["total_price"] = $value["total_price"];
                $item["state"] = "open";
                $item["children"] = $this->getDepTreeGridData($store,$value["id"]);
                if (!$item["children"]) {
                    unset($item["children"]);
                }
                array_push($returnArray, $item);
                unset($item);
            }
        }
        return $returnArray;
    }
    

    /**
     * @access 取得上级部门树
     **/
    public function getPidDepTree(){
        $id = I("id");
        //编辑时，去除本身
        if($id){
            $where["id"]=array("NEQ",$id);
        }
      
        $depModel = M("Dep");
        $where["status"]=1;
        $where["is_del"]=0;
        $deps = $depModel->where($where)->order("order_no asc")->select();
        //获取部门树json串
        $treeArr = $this->getDepTreeData($deps,0,true);
        $store = json_encode($treeArr);
        $this->ajaxReturn($treeArr);
    }
    
    /**
     * @access 取得部门树
     * @param $dep_id int 部门id
     **/
    public function getDepTree(){
        $dep_id = I("get.dep_id");
        $depModel = M("Dep");
        $where["status"]=1;
        $where["is_del"]=0;
        
        // if($dep_id){
        //     $where["id"]=$dep_id;
        //     $where["_logic"]= "OR";
        // }
        $deps = $depModel->where($where)->order("order_no asc")->select();
        //获取部门树json串
        $treeArr = $this->getDepTreeData($deps,0,false,$dep_id);
        $store = json_encode($treeArr);
        // print_r($treeArr);exit;
        $this->ajaxReturn($treeArr);
    }
   
    /**
     *@access 递归得到部门树json
     *@param $store 数组 部门数据源
     *@param $pid int 父部门id
     *@param $hasRootNode boolean 是否含有根节点
     * */
    public function getDepTreeData($store,$pid="0",$hasRootNode=false,$dep_id = 0) {
        $returnArray = array();
        if($hasRootNode){
            $root["id"]="0";
            $root["text"]="顶级部门";
            $root["state"] = "open";
            $root["iconCls"] = "icon-extend-deproot";
            array_push($returnArray, $root);
            unset($root);
        }
        
        foreach ($store as $key=>$value){
        if($value["pid"]==$pid){
                //主键
                $item["id"] = $value["id"];
                //部门名称
                $item["text"] = $value["dep_name"];
                $item["state"] = "open";
                if($dep_id && $value['id'] == $dep_id){
                    $item['checked'] = 'checked';
                }
                $item["children"] = $this->getDepTreeData($store,$value["id"],false,$dep_id);
                if (!$item["children"]) {
                    unset($item["children"]);
                }
                array_push($returnArray, $item);
                unset($item);
            }
        }
        return $returnArray;
    }
    
    /**
     * 删除部门
     * */
    public function delDep(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id = I("id");
            //获取此部门下的所有 id ，全部更改状态
            $ids = $this -> getTreeID($id);
            $wh['did'] = array('in',$ids);
            $wh['status'] = 1;
            $count = M('user')->where($wh)->count();
            if($count > 0){
                $result["success"] = false;
                $result["message"]="此部门下有相关人员，请处理后再操作";
                $this->ajaxReturn($result);
            }
            //判断此部门下是否有费用归属
            $is_free = $this->exsitFreePriceDep($id);
            if($is_free){
                $result["success"] = false;
                $result["validateCode"] ="Exist_Price_Belong";
                $result["orders"] =$is_free;
                $result["message"]="此部门下有订单的费用归属，请处理后再操作";
                $this->ajaxReturn($result);
            }
            //判断该部门下是否含有子部门
            if($this->exsitChildDep($id)){
                $result["success"] = false;
                $result["validateCode"] ="Exist_Child_Dep";
                $result["message"] = "该部门下含子部门，不允许删除";
            }else{
                //判断该部门下是否含有人员
                if($this->existDepUser($id)){
                    $result["success"] = false;
                    $result["validateCode"] ="Exist_Dep_User";
                    $result["message"] = "该部门下含有人员，不允许删除";
                }else{
                    $depModel = M('Dep');
                    $where["id"] = array("in",$id);
                    $res = $depModel->where($where) ->save(array('is_del'=>1));
                    if($res!==false){
                        $result["success"] = true;
                        $result["message"] = "操作成功";
                    }else{
                        $result["success"] = false;
                        $result["message"] = "操作失败";
                    }
                }
            }
        }
        $this->ajaxReturn($result);
    }
   
    /**
     * 判断是否含有子部门
     * @param $dep_id 部门id
     * @return boolean
     * */
    private function exsitChildDep($dep_id){
        $depModel = M("Dep");
        $where["pid"]=$dep_id;
        $where["is_del"]=0;
        $count = $depModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 判断部门下是否含有人员
     * @param $dep_id 部门id
     * @return boolean
     * */
    private function existDepUser($dep_id){
        $userModel = M("User");
        $where["did"]=$dep_id;
        $where['status'] = 1;
        $where['is_del'] = 0;
        $count = $userModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     *  设置部门状态
     * */
    public function setDepStatus(){
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
    
            $depModel = M("Dep");

            //获取此部门下的所有 id ，全部更改状态
            $ids = $this -> getTreeID($id);
            $wh['did'] = array('in',$ids);
            $wh['status'] = 1;
            $wh['is_del'] = 0;
            $count = M('user')->where($wh)->count();
            if(($count > 0) && ($status == 0)){
                $result["success"] = false;
                $result["message"]="此部门下有相关人员，请处理后再操作";
                $this->ajaxReturn($result);
            }
            if($status == 0){
                 //判断此部门下是否有费用归属
                $is_free = $this->exsitFreePriceDep($id);
                if($is_free){
                    $result["success"] = false;
                    $result["validateCode"] ="Exist_Price_Belong";
                    $result["orders"] =$is_free;
                    $result["message"]="此部门下有订单的费用归属，请处理后再操作";
                    $this->ajaxReturn($result);
                }
            }
            $data["status"] = $status;
            if($status == 0){
                //如果是禁用，则禁用其下所有部门
                $where["id"] = array("in",$ids);
            }else{
                //如果是启用，则先检查上层部门，如果上层部门有禁用的，先启用上层部门
                $fids = $this -> checkFID($id);
                $f_status = true;
                foreach ($fids as $key => $value) {
                    if(($value['id'] != $id) && $value['status'] == 0){
                        $f_status = false;
                        break;
                    }
                }
                if($f_status){
                    $where["id"] = $id;
                }else{
                    $result["success"] = false;
                    $result["message"]="请先启用上层部门";
                    $this->ajaxReturn($result);
                }
            }
            $where['_logic'] = 'or';

            $res = $depModel->where($where)->save($data);

            
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
    //取得当前部门下的所有子部门
    function getTreeID($pid){
        static $ids = [];
        $ids[] = $pid;
        $model = M('dep');
        $result = $model->where(array('pid'=>$pid,'is_del'=>0))->field('id')->select();
        foreach ($result as $key => &$value) {
            $this->getTreeID($value['id']);
        }
        return $ids;
    }
    function checkFID($id){
        static $fids = [];
        // static $check_id = '';
        // $check_id = $id;
        $model = M('dep');
        $fid = $model->field('id,pid,status,is_del')->find($id);
        $fids[] = $fid;
        // if(( $fid['status'] == 0 ) && ($id != $check_id)){
        //     // return $fids;
        //     return $fid;
        //     return false;
        // }

        if($fid['pid']){
            $this->checkFID($fid['pid']);
        }
        return $fids;
        // return true;
        
    }
    function checkStatus(){
        $dep_id = I('get.dep_id',0,intval);
        $dep = M('dep')->find($dep_id);
        if($dep['status'] == 1){
            $this->ajaxReturn(true);
        }else{
            $this->ajaxReturn(false);
        }
    }
}