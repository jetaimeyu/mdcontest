<?php

namespace Fhmng\Controller;

use Fhmng\Common\Controller\BaseController;
class UserController extends BaseController{
    /**
     *  用户首页
     * */
    public function userIndex(){
        // //检查权限
        $this->checkPageAuth("AUTH_USER");
        
        $this->display();
    }
    /**
     *  取得用户数据
     * */
    public function getUserList(){
       
        //用户状态
        $userStatus = I("userStatus");
        //部门id
        $did = I('did');
        //查询的字段
        $queryField = I("queryField");
        //查询值
        $queryStr = I("queryStr");
        $where['is_user'] = 1;
        if($userStatus=="1"||$userStatus=="0"){
            $where["user.status"] = $userStatus;
        }else{
            $where['user.status'] = array('neq',3);
        }
        $where['user.login_name'] = array('neq','admin');
        if($did){
            $ids = A('Dep')->getTreeID($did);
            $where['did'] = array('in',$ids);
        }
        if(!empty($queryStr)){
            //全部
            if($queryField=="ALL"){
                $where2["user.login_name"]=array('LIKE','%'. $queryStr .'%');
                $where2["user.true_name"]=array('LIKE','%'. $queryStr .'%');
                $where2["_logic"]="OR";
            }else if($queryField=="login_name"){
                $where2["user.login_name"]=array('LIKE','%'. $queryStr .'%');
            }else if($queryField=="true_name"){
                $where2["user.true_name"]=array('LIKE','%'. $queryStr .'%');
            }
        }
        
        if($where&&$where2){
            $where["_complex"] = $where2;
        }else if ($where2){
            $where = $where2;
        }
        //页码
        $pageIndex = I("post.page",1,intval);
        //每页显示的条数
        $pageSize = I("post.rows",20,intval);
        $data =D('user')->relation(true)
        ->where($where)->page($pageIndex,$pageSize)->order("create_time desc")->select();

        $count = M('user')->where($where)->count();
        $result=['rows'=>$data,'total'=>$count];
        $this->ajaxReturn($result);
    }
    /**
     *  取得用户数据
     * */
    public function getUserList1(){
        //用户状态
        
        if($userStatus=="1"||$userStatus=="0"){
            $where["status"] = $userStatus;
        }
        
        if(!empty($queryStr)){
            //全部
            if($queryField=="ALL"){
                $where2["login_name"]=array('LIKE','%'. $queryStr .'%');
                $where2["true_name"]=array('LIKE','%'. $queryStr .'%');
                $where2["_logic"]="OR";
            }else if($queryField=="login_name"){
                $where2["login_name"]=array('LIKE','%'. $queryStr .'%');
            }else if($queryField=="true_name"){
                $where2["true_name"]=array('LIKE','%'. $queryStr .'%');
            }
        }
        
        if($where&&$where2){
            $where["_complex"] = $where2;
        }else if ($where2){
            $where = $where2;
        }
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
        
        $userModel = D("User");
        $total = $userModel->relation(true)->where($where)->count();
        $res = $userModel->order("create_time desc")->where($where)->page($page,$rows)->relation(true)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    
    /**
     *  设置用户状态
     * */
    public function setUserStatus(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //用户ids字符串,逗号隔开
            $ids = I("ids");
            //用户状态,0：禁用，1:启用,3:删除
            $status = I("post.status",0,intval);
            
            $userModel = M("User");
            $data["status"] = $status;
            $where["id"] = array("in",$ids);
            
            $res = $userModel->where($where)->save($data);
            
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
     *  删除用户
     * */
    public function delUsers(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //用户ids字符串,逗号隔开
            $ids = I("ids");
            $userModel = M("User");
            //开启事务
            $userModel->startTrans();
            //删除用户            
            $where["id"] = array("in",$ids);
            $res = $userModel->where($where)->delete();
            
            //删除用户对应的角色关联表内的数据
            $roleUserModel = M("RoleUser");
            $where2["uid"]=array("in",$ids);
            $res2=$roleUserModel->where($where2)->delete();
            
            if($res!==false&&$res2!==false){
                $userModel->commit();
                $result["success"] = true;
                $result["message"]="操作成功";
            }else{
                $userModel->rollback();
                $result["success"] = false;
                $result["message"]="操作失败";
            }
        }
        $this->ajaxReturn($result);
    }
 
    
    /**
     * 添加用户
     * */
    public function addUser(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        
        if(IS_POST){
            //登录名
            $login_name = trim(I("login_name"));
            $user_id = trim(I("user_id"));
            //后台验证登录名是否存在
            if($this->isExistLoginName($login_name,$user_id)){
                $result["success"] = false;
                $result["validateCode"] = "loginNameExist";
                $result["message"] = "该登录名已存在";
            }else{
                //密码
                $pwd = trim(I("post.pwd"));
                //部门id
                $depid = I("post.did");
                
                //状态,0:禁用，1:启用
                $status = I("post.status");
                
                $userModel = M("User");
                $data["login_name"] = $login_name;
                $data["true_name"] = I('post.true_name');
                $data["telephone"] = I('post.telephone');
                $data["adress"] = I('post.adress');
                $data["pwd"] =md5($pwd) ;
                $data['headimgurl'] = '/Public/home/images/default.jpg';
                $data["did"] = $depid;
                $data["status"] = $status;
                $data["budget"] = I("post.budget");
                $data["value_chain"] = I("post.value_chain");
                $data['is_user'] = 1; //是否是用户 1 后台管理员，2 前台用户
                $data["create_time"] = date("Y-m-d H:i:s");
                $uid = $userModel->add($data);

               //添加角色
                $role = I('post.role');
                $user_role = [];
                foreach ($role as $key=> $role_id) {
                    $user_role[$key]['rid'] = $role_id;
                    $user_role[$key]['uid'] = $uid;
                }
                M('role_user')->addAll($user_role);
               
                $result["success"] = true;
                $result["message"]="操作成功";

            }
        }else{
            $result["success"] = false;
            $result["message"]="非法操作";
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 判断登录名是否已经存在
     * @access private
     * @param string $login_name 登录名
     * @return boolean
     * */
    private function isExistLoginName($login_name,$user_id = ''){
        $userModel = M("User");
        $where["login_name"] = $login_name;
        $where['status'] = ['neq',3];
        if($user_id){
            $where['id'] = array('neq',$user_id);
        }
        $res = $userModel->where($where)->count();
        
        if($res>0){
            return true;
        }else{
            return false;
        }
    }
     /**
     * 前台判断登录名是否已经存在
     * @access public
     * @return boolean
     * */
    public function exsitLoginName(){
        $login_name= trim(I("post.login_name"));
        $user_id= trim(I("post.user_id"));
        $isExist = $this->isExistLoginName($login_name,$user_id);
        if($isExist){
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
    public function exsitLoginNameByUpdate(){
        $id = I("id");
        $login_name= trim(I("login_name"));
        
        $userModel = M("User");
        $where["id"] = $id;
        //取得原来的登录名
        $orgLoginName = $userModel->where($where)->getField("login_name");
        
        //判断登录名是否修改过
        if($orgLoginName!=$login_name){
            $isExist = $this->isExistLoginName($login_name);
            if($isExist){
                $result["isExist"] = true;
            }else{
                $result["isExist"] = false;
            }
            
        }else{
            //如果没有被修改过，则认为该用户名不存在
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 修改用户页面
     * */
    public function editUserPage(){
        //用户id
        $id = I("get.id");
        $userModel = M("User");
        $user = $userModel->find($id);
        
        
        $this->assign("user",$user);
        $this->display();
    }
    /**
     * 添加用户页面
     * */
    function addUserPage(){
        
        $role_list = A('Fhmng/Role')->getRoleListCheck();
        $this->assign('role_list',$role_list);
        $this->display();
    }
    /**
     * 更新用户
     * */
    public function updateUser(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        if(IS_POST){
            //用户id
            $id=I("id");
            //登录名
            $login_name = trim(I("login_name"));
            //姓名
            $true_name = trim(I("true_name"));
            //部门id
            $depid = I("did");
           
            //性别
            $sex = I("sex");
            //邮箱
            $email = trim(I("email"));
            //手机
            $phone = trim(I("phone"));
            //状态
            $status = I("status");
            //描述
            $description = trim(I("description"));
            
            $userModel = M("User");
            $where["id"] = $id;
            //取得原来的登录名
            $orgLoginName = $userModel->where($where)->getField("login_name");
            //判断登录名是否修改过
            if($orgLoginName!=$login_name){
                if($this->isExistLoginName($login_name)){
                    $result["success"] = false;
                    $result["validateCode"] = "loginNameExist";
                    $result["message"] = "该登录名已存在";
                }else{
                    $data["id"] = $id;
                    $data["login_name"] = $login_name;
                    $data["true_name"] = $true_name;
                    $data["telephone"] = trim(I('post.telephone'));
                    $data["adress"] =trim(I('post.adress'));
                    $data["did"] = $depid;
                    $data["budget"] = I("post.budget");
                    $data["value_chain"] = I("post.value_chain");

                    $data["status"] = $status;
                    $data["description"] = $description;
                    $res = $userModel->save($data);
                    if($res!==false){
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $id;
                $data["true_name"] = $true_name;
                $data["telephone"] = trim(I('post.telephone'));
                $data["adress"] =trim(I('post.adress'));
                $data["did"] = $depid;
                $data["status"] = $status;
                $data["description"] = $description;
                $data["budget"] = I("post.budget");
                $data["value_chain"] = I("post.value_chain");
                
                $res = $userModel->save($data);
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
     * 重设用户密码页面
     * */
    public function resetUserPwdPage(){
        $id=I("id");
        $userModel = M("User");
        $user = $userModel->find($id);
        $this->assign("user",$user);
        $this->display();
    }
    
    /**
     * 重设用户密码
     * */
    public function resetUserPwd(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //用户id
            $id = I("id");
            $pwd = trim(I("pwd"));
        
            $userModel = M("User");
            $data["id"] = $id;
            $data["pwd"] = md5($pwd);
            $res = $userModel->save($data);
        
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
     * 修改个人密码页面
     * */
    public function modifyPersonalPwdPage(){
        $id=I("id");
        $userModel = M("User");
        $user = $userModel->find($id);
        $this->assign("user",$user);
        $this->display();
    }
    
    /**
     * 修改个人密码
     * */
    public function modifyPersonalPwd(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        
        if(IS_POST){
            //原密码
            $orgpwd = trim(I("orgpwd"));
            //用户id
            $uid = I("id");
            //后台验证原密码是否正确
            if(!$this->checkOrgPwd($uid,$orgpwd)){
                $result["success"] = false;
                $result["validateCode"] = "orgpwderror";
                $result["message"] = "原密码错误";
            }else{
                //密码
                $pwd = trim(I("pwd"));
        
                $userModel = M("User");
                $data["pwd"] =md5($pwd) ;
                $data["id"] =$uid;
        
                $res = $userModel->save($data);
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
     * 前台验证用户原密码是否正确
     * */
    public function validOrgPwd(){
        $result["correct"] = false;
        //用户id
        $uid = I("uid");
        //原密码
        $orgpwd = I("orgpwd");
        
       if($this->checkOrgPwd($uid, $orgpwd)){
           $result["correct"] = true;
       }
       $this->ajaxReturn($result);
    }
    
    /**
     * 验证用户名和密码是否正确
     * @access public
     * @return boolean
     * */
    public function checkOrgPwd($uid,$orgpwd){
        $userModel = M("User");
        $where["id"]=$uid;
        $where["pwd"]=md5($orgpwd);
        $count = $userModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 修改个人信息页面
     * */
    public function modifyPersonalInfoPage(){
        $id=I("id");
        $userModel = M("User");
        $user = $userModel->find($id);
        $this->assign("user",$user);
        $this->display();
    }
    /**
     * 修改个人信息
     * */
    public function modifyPersonalInfo(){
        $result["success"] = false;
        $result["message"]="";
        
        if(IS_POST){
            $post = I('post.');
            //手机
            $phone = trim($post['phone']);
            //邮箱

            //用户id
            $uid = $post['id'];
            
            $userModel = M("User");
            $data["telephone"] = $phone;
            // $data["email"] =$email;
            $data["id"] = $uid;
            $data['headimgurl'] = $post['headimgurl'];
            $res = $userModel->save($data);
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
    
    public function uploadImg(){
      //原图片地址
        $res = upload_img('Filedata','avatar');
        $this->ajaxReturn($res);
        
    }
}