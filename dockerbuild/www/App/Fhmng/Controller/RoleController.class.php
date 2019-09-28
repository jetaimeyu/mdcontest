<?php
/**日志
 * 模块：角色管理
 * 创建人:李宝振
 * 创建时间：2016-08-03
 * 模块权限code:AUTH_ROLE
 * 描述：角色管理
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class RoleController extends BaseController{
    public function roleFrameIndex(){
        //检查权限
        $this->checkPageAuth("AUTH_ROLE");
        $this->display();
    }
    
    /**
     *  角色首页
     * */
    public function roleIndex(){
        //检查权限
        $this->checkPageAuth("AUTH_ROLE");
        
        $this->display();
    }
    
    /**
     *  取得角色数据
     * */
    public function getRoleList(){
        //状态
        $roleStatus = I("roleStatus");
       
        //查询值
        $queryStr = I("queryStr");
    
        if($roleStatus=="1"||$roleStatus=="0"){
            $where["status"] = $roleStatus;
        }
    
        if(!empty($queryStr)){
            $where["role_name"]=array('LIKE','%'. $queryStr .'%');
        }
  
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
    
        $roleModel = D("Role");
        $total = $roleModel->where($where)->count();
        $res = $roleModel->order("create_time asc")->where($where)->page($page,$rows)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    
    
    /**
     *  删除角色
     * */
    public function delRole(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id = I("id");
            if($this->existUsers($id)){
                $result["success"] = false;
                $result["validateCode"] ="Role_Exist_User";
                $result["message"] = "该角色下含有用户，不允许删除";
            }else{
                $roleModel = M('Role');
                $where["id"] = array("in",$id);
                $res = $roleModel->where($where) ->delete();
                
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
     *  设置角色状态
     * */
    public function setRoleStatus(){
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
    
            $roleModel = M("Role");
            $data["status"] = $status;
            $where["id"] = array("in",$ids);
    
            $res = $roleModel->where($where)->save($data);
    
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
     * 判断某一角色下是否含有用户
     * @access private
     * @param $roleid int 角色id
     * @return boolean
     * */
    private function existUsers($roleid){
        $roleUserModel = M("RoleUser");
        $where["rid"]=$roleid;
        $res = $roleUserModel->where($where)->count();
        if($res>0){
            return true;
        }else{
            return false;
        }
    }
    
    
    /**
     * 设置用户角色页面
     * */
    public function setUserRolePage(){
        $admin_id =trim(I('get.admin_id'));

        $roleModel = M("Role");
        $where["status"]=1;
        //可用的角色列表
        $roles = $roleModel->where($where)->select();
        
        //根据用户id查询出用户所含有的角色id串
        $roleids = $this->getUserRoleIdStr($admin_id);
        
        $this->assign("roles",$roles);
        $this->assign("roleids",$roleids);
        $this->display();
    }
    
    /**
     * 根据用户id，查询所具有的角色id串
     * @access private
     * @param uid 用户id int
     * @return string
     * */
    private function getUserRoleIdStr($uid){
        $roleUserModel = M("RoleUser");
        $where["uid"]=$uid;
        $roleUser = $roleUserModel->where($where)->select();
    
        $roleids ="";
        foreach($roleUser as $item){
            if(!$roleids){
                $roleids = $item["rid"];
            }else{
                $roleids=$roleids.",".$item["rid"];
            }
        }
        return $roleids;
    }
    
    /**
     * 设置用户角色
     * */
    public function setUserRole() {
        $result["success"] = false;
        if(IS_POST){
            //角色id串
            $roleids = I("roleids");
            //用户id
            $uid = I("uid");
    
            $roleArr = explode(",", $roleids);
            //去除数据中的空元素
            $roleArr = array_filter($roleArr);
    
            $roleUserModel = M("RoleUser");
    
            //先清除该用户所有的角色，然后在添加选中的
            $where["uid"]=$uid;
            $roleUserModel->where($where)->delete();
    
            foreach ($roleArr as $roleid){
                $data["uid"] = $uid;
                $data["rid"]=$roleid;
    
                $roleUserModel->add($data);
            }
            $result["success"] = true;
        }
    
        $this->ajaxReturn($result);
    }
    
    /**
     * 是否存在角色名
     * @access public
     * @return boolean
     * */
    public function existRoleName($role_name){
        $roleModel = M("Role");
        $role = $roleModel->getByRoleName($role_name);
        
        if($role){
            return true;   
        }else{
            return false;
        }
    }
    
    /**
     * 添加角色页面
     * */
    public function addRolePage(){
        $this->display();
    }
    
    /**
     * 前台页面验证是否存在角色名称
     * */
    public function validRoleName(){
        $role_name = trim(I("role_name"));
        
        if($this->existRoleName($role_name)){
            $result["isExist"] = true;
        }else{
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 更新页面前台判断角色名是否已经存在
     * @access public
     * @return boolean
     * */
    public function validRoleNameByUpdate(){
        $id = I("id");
        $role_name= trim(I("role_name"));
    
        $roleModel = M("Role");
        $where["id"] = $id;
        //取得原来的角色名
        $orgRoleName = $roleModel->where($where)->getField("role_name");
    
        //判断是否修改过
        if($orgRoleName!=$role_name){
            $isExist = $this->existRoleName($role_name);
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
     * 添加角色
     * */
    public function addRole(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        
        if(IS_POST){
            //角色名
            $role_name = trim(I("role_name"));
            //后台验证是否存在
            if($this->existRoleName($role_name)){
                $result["success"] = false;
                $result["validateCode"] = "loginNameExist";
                $result["message"] = "该角色名称已存在";
            }else{
                //状态,0:禁用，1:启用
                $status = I("status");
                //描述
                $description = trim(I("description"));
        
                $roleModel = M("Role");
                $data["role_name"] = $role_name;
                $data["status"] = $status;
                if($description){
                    $data["description"] = $description;
                }
                $data["create_time"] = date("Y-m-d H:i:s");
        
                $res = $roleModel->add($data);
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
     * 编辑角色页面
     * */
    public function editRolePage(){
        $id = I("id");
        $roleModel = M("Role");
        $role = $roleModel->find($id);
        $this->assign("role",$role);
        $this->display();
    }
    /**
     * 更新角色
     * */
    public function updateRole(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        if(IS_POST){
            //角色id
            $id=I("id");
            //角色名称
            $role_name = trim(I("role_name"));
            //状态
            $status = I("status");
            //描述
            $description = trim(I("description"));
        
            $roleModel = M("Role");
            $where["id"] = $id;
            //取得原来的角色名
            $orgRoleName = $roleModel->where($where)->getField("role_name");
            //判断角色名是否修改过
            if($orgRoleName!=$role_name){
                if($this->existRoleName($role_name)){
                    $result["success"] = false;
                    $result["validateCode"] = "roleNameExist";
                    $result["message"] = "该角色名称已存在";
                }else{
                    $data["id"] = $id;
                    $data["role_name"] = $role_name;
                    $data["status"] = $status;
                    $data["description"] = $description;
                     
                    $res = $roleModel->save($data);
                    if($res!==false){
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $id;
                $data["role_name"] = $role_name;
                $data["status"] = $status;
                $data["description"] = $description;
                 
                $res = $roleModel->save($data);
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
     * 角色人员页面
     * */
    public function roleUserPage(){
        //检查权限
        $this->checkPageAuth("AUTH_ROLE");
        
        $this->display();
    }
    
    /**
     * 角色人员列表
     * */
    public function getRoleUserList() {
        //查询值
        $queryStr = I("queryStr");
        //角色id
        $roleid = I("roleid");
        $where['rid']=$roleid;
        $where['login_name']=array('neq','admin');
        if(!empty($queryStr)){
            $wh["true_name"]=array('LIKE','%'. "\\$queryStr" .'%');
            $wh["login_name"]=array('LIKE','%'. "\\$queryStr" .'%');
            $wh['_logic'] = 'OR';
            $where['_complex'] = $wh;
        }
        
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
        
        $roleUserViewModel = D("RoleUserView");
        $total = $roleUserViewModel->where($where)->count();
        $res = $roleUserViewModel->where($where)->page($page,$rows)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result,"json");
    }
    
    /**
     * 删除角色人员
     * */
    public function delRoleUser(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            //角色id
            $roleid = I("roleid");
            //人员id  2016年10月14日 修改成批量删除
            $ids = I("ids");
            
            $roleUserModel = M("RoleUser");
            $where["rid"]=$roleid;
            $where["uid"] = array("in",$ids);
            
            $res = $roleUserModel->where($where)->delete();
            if($res!==false){
                $result["success"] = true;
                $result["message"] = "操作成功";
            }else{
                $result["success"] = false;
                $result["message"] = "操作失败";
            }
        }else{
            $result["success"] = false;
            $result["message"] = "非法操作";
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 设置角色人员页面
     * */
    public function setRoleUserPage(){
        //检查权限
        $this->checkPageAuth("RoleUserPage");
        $this->display();
    }
    
    /**
     * 设置角色人员
     * */
    public function setRoleUser(){
        $result["success"] = false;
        if(IS_POST){
            //用户id串
            $uidStr = I("uidStr");
            //角色id
            $roleid = I("roleid");
            $uidArr = explode(",", $uidStr);
            //去除数据中的空元素
            $uidArr = array_filter($uidArr);
        
            $roleUserModel = M("RoleUser");
            $del['rid'] = $roleid;
            foreach ($uidArr as $key=>$uid){
                if(!$this->existRoleUser($uid,$roleid)){
                    $data["uid"] = $uid;
                    $data["rid"]=$roleid;
        
                    $roleUserModel->add($data);
                }
            }
            //删除去掉的
            
            // $oldids = $roleUserModel -> where(array('rid'=>$roleid))->field('uid')->select();
            // $oldidArr = [];
            // foreach ($oldids as $key => $value) {
            //     $oldidArr[] = $value['uid'];
            // }
            // $del_ids = array_diff($oldidArr, $uidArr);
            // if($del_ids){
            //     $del['rid'] = $roleid;
            //     $del['uid'] = array('in',$del_ids);
            //     $roleUserModel->where($del)->delete();
            // }

            $result["success"] = true;
        }
        
        $this->ajaxReturn($result);
    }
    
    /**
     * 根据用户id以及角色id判断是否存在该记录
     * @access private
     * @param uid int 用户id
     * @param group_id int 角色id
     * @return boolean
     * */
    private function existRoleUser($uid,$roleid){
        $roleUserModel = M("RoleUser");
        $where["uid"]=$uid;
        $where["rid"]=$roleid;
        $res = $roleUserModel->where($where)->count();
    
        if($res>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 取得用户列表
     * */
    public function getUserList(){
        //角色id
        $roleid = I('select_roleid',0,intval);
        //查询值
        $queryStr = I('queryStr');
        $where["status"]=1;
        $where['login_name']=array('neq','admin');
        $where['is_user'] = 1; //后台管理员
        //此角色下已有的人员id
        $uids = M('role_user')->field('uid')->where(array('rid'=>$roleid))->select();
        $uidArr = [];
        foreach ($uids as $key => $value) {
            $uidArr[] = $value['uid'];
        }
        //不显示此角色下的原有人员
        if($uidArr){
            $where['id'] = array('not in',$uidArr);
        }

        if($queryStr){
            $condition['login_name'] = array('like','%'."\\$queryStr".'%');  
            $condition['true_name'] = array('like','%'."\\$queryStr".'%');  
            $condition['_logic'] = 'OR';
            $where['_complex'] = $condition;
        }
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
        $userModel = M("User");
        $total = $userModel->where($where)->count();
        $res = $userModel->order("create_time desc")->where($where)->page($page,$rows)->select();
        $role_user = M('role_user')->where(array('rid'=>$roleid))->select();

        $users = [];
        foreach ($role_user as $key => $value) {
            $users[] = $value['uid'];
        }
        foreach ($res as  &$value) {
            if(in_array($value['id'], $users)){
                $value['checked'] = true;
            }
        }

        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result,"json");
    }
    
    /**
     * 设置角色功能页面
     * */
    public function setRoleFunctionPage(){
        $roleid = I("roleid");
        $this->assign("roleid",$roleid);
        $this->display();
    }
    
    /**
     * 设置角色功能
     * */
    public function setRoleFunction(){
        $result["success"] = false;
        if(IS_POST){
            //功能id串
            $funcStr = I("funcStr");
            //角色id
            $roleid = I("roleid");
           
            $roleModel = M("Role");
            $data["id"] = $roleid;
            $data["function_str"] = $funcStr;
            $res=$roleModel->save($data);
            
            if($res!==false){
                $result["success"] = true;
            }
        }
        
        $this->ajaxReturn($result);
    }
    
    /**
     * 取得功能树
     * */
    public function getFunctionTree(){
        //角色id
        $roleid = I("roleid");
        
        $roleModel = M("Role");
        $role = $roleModel->getById($roleid);
        $selectedFuncStr = $role["function_str"];
        //取得功能数组
        $funcArray = A("Fhmng/Function")->getAllFunctions();
        
        //获取功能树json串
        $treeArr = $this->getTree($funcArray,$selectedFuncStr,0);
        $this->ajaxReturn($treeArr);
    }
    
    /**
     * 取得功能树
     * @access private
     * @param $store 需要递归的数据源
     * @param $selectedFuncStr 选中的树节点id串
     * @param $pid 父级ID
     * @return 递归后的数组
     * */
    private function getTree($store,$selectedFuncStr,$pid="-1"){
        $returnArray = array();
        foreach ($store as $key=>$value) {
            if($value["pid"]==$pid){
                //获取子节点
                #$store[$key]["children"] = $this->getTree($store, $store[$key]["id"]);
                $item["id"] = $value["id"];
                //显示标题
                $item["text"] = $value["func_name"];
                //判断是否含有默认选中的树节点
                if($selectedFuncStr){
                    $queryStr = ",".$selectedFuncStr.",";
                    $findStr = ",".$value["id"].",";
                    $res = strpos($queryStr,$findStr);
                    if($res!==false){
                        $item["checked"] = true;
                    }else{
                        $item["checked"] = false;
                    }
                }
                 
                //默认是否被展开
                $item["state"]="open";
                $item["children"] = $this->getTree($store,$selectedFuncStr,$value["id"]);
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
    function getRoleListCheck(){
        $list = M('role')->where('status = 1')->select();
        return $list;
    }
}