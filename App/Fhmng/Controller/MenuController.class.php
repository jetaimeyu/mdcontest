<?php
/**日志
 * 模块：菜单管理
 * 创建人:李宝振
 * 创建时间：2016-09-03
 * 模块权限code:AUTH_MENU
 * 描述：
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class MenuController extends BaseController{
    /**
     * 菜单首页
     * */
    public function index() {
        //检查权限
        $this->checkPageAuth("AUTH_MENU");
        $this->display();
    }
    
    /**
     * 获取一级菜单列表
     */
    public function getList()
    {
    
        $where['status'] = ['neq',3];
        $where['pid'] = 0;
        $data = M('home_menu')->where($where)->select();
        $this->ajaxReturn($data);
    }
    /**
     * 编辑一级菜单
     */
    public function editOne()
    {
    
        if(IS_GET){
            // 如果是get请求，查询数据，渲染页面
            $id = I('get.id');
            if($id){
                $data = M('home_menu')->find($id);
                $this->assign('data',$data);
            }
            $this->display();
        }else if(IS_POST){
            // post请求，保存数据
            $data = I('post.');
            $data['pid'] = 0;
            $data['create_time'] = date('Y-m-d H:i:s');
            $res = $this->saveRow('home_menu',$data);
            $this->ajaxReturn($res);
        }
    }
    
    /**
     * 删除菜单
     */ 
    public function del()
    {
        $id  = I('post.id');
        if($id){
            $res = M('home_menu')->where(['id'=>$id])->setField('status',3);
            if($res !== false){
                $this->ajaxReturn(['success'=>true,'message'=>'删除成功']);
            }else{
                $this->ajaxReturn(['success'=>false,'message'=>'删除失败']);
            }
        }
    }
    //检查关键字
    function checkKey(){
        $data = I('post.');
    
        $where['status'] = ['neq',3];
        $wh['menu_code'] = $data['key'];
        if($data['id']){
            $wh['id'] = ['neq',$data['id']];
        }
        $res = M('home_menu')->where($wh)->count();
        if($res){
            $this->ajaxReturn(['isExist'=>true]);
        }else{
            $this->ajaxReturn(['isExist'=>false]);
        }
    }
    /**
     * 获取二级菜单
     */
    public function getContent()
    {
        $data = I('post.');
        $wh['status'] = ['neq',3];
        $wh['pid'] = $data['pid'];
        if($data['searchValue']){
            $wh['menu_name'] = ['like','%'.$data['searchValue'].'%'];
        }
        $list = M('home_menu')->where($wh)->page($data['page'],$data['rows'])->select();
        $count  = M('hoem_menu')->where($wh)->count();
        $this->ajaxReturn(['rows'=>$list,'total'=>$count]);
    }
    
    /**
     * 编辑二级菜单
     */
    public function editContent()
    {
        if(IS_GET){
            $pid = I('get.pid');
            $id = I('get.id');
            //有id则是编辑
            if($id){
                $data = M('home_menu')->find($id);
                $this->data = $data;
            }
            $this->display();
        }else if(IS_POST){
            $data = I('post.');
            $data['create_time'] = date('Y-m-d H:i:s');
            $res = $this->saveRow('home_menu',$data);
            $this->ajaxReturn($res);
    
        }
    }

	/*************************原菜单********************************/
    /**
     * 菜单首页
     * */
    public function menuIndex() {
        //检查权限
        $this->checkPageAuth("AUTH_MENU");
        $this->display();
    }
    
    /**
     * 取得菜单treegride列表
     * */
    public function getMenuTreeList() {
        
        $menuViewModel = D("MenuView");
        $menus = $menuViewModel->order("order_no asc,id asc")->select();
        
        //在字典表里取得菜单类别
        $dics = A("Fhmng/Dictionary")->getDicsByTypeCode("menu_category");
        $returnArray = array();
        foreach ($dics as  $key=>$value){
            //主键
            $item["id"] = "menu_category_".$value["id"];
            $item["model_id"] =  "";
            //外键表的模型名称
            $item["model_id_name"] =  "";
            $item["menu_name"] = $value["dic_value"];
            $item["menu_code"] =  "";
            $item["menu_type"] =  "";
            $item["model_name"] =  "";
            $item["controller_name"] =  "";
            $item["action_name"] =  "";
            $item["url_params"] =  "";
            $item["menu_category"] =  "";
            $item["menu_category_name"] =  "";
            $item["menu_url"] =  "";
            $item["url_target"] =  "";
            $item["menu_pic"] =  "";
            $item["status"] =  "-999";
            $item["pid"] =  "";
            $item["order_no"] =  "";
            $item["website_title"] =  "";
            $item["website_title"] =  "";
            $item["key_word"] =  "";
            $item["description"] =  "";
            $item["create_time"] =  "";
            $item["remark"] = "";
            $item["state"] = "open";
            $item["children"] = $this->getMenuTreeGridData($menus,0,$value["dic_key"]);
            $item["iconCls"]="icon-extend-bluetreefolder";
            if (!$item["children"]) {
                unset($item["children"]);
            }
            array_push($returnArray, $item);
            unset($item);
        }
        $this->ajaxReturn($returnArray);
    }
    
    /**
     * 递归得到treegird树json
     *@param $store 数组 菜单数据源
     *@param $pid int 父菜单id
     *@param $menu_category 菜单类别
     * */
    public function getMenuTreeGridData($store,$pid="0",$menu_category) {
        $returnArray = array();
        foreach ($store as $key=>$value){
            if($value["pid"]==$pid&&$value["menu_category"]==$menu_category){
                //主键
                $item["id"] = $value["id"];
                $item["model_id"] = $value["model_id"];
                //外键表的模型名称
                $item["model_id_name"] = $value["model_id_name"];
                $item["menu_name"] = $value["menu_name"];
                $item["menu_code"] = $value["menu_code"];
                $item["menu_type"] = $value["menu_type"];
                //模块名称
                $item["model_name"] = $value["model_name"];
                $item["controller_name"] = $value["controller_name"];
                $item["action_name"] = $value["action_name"];
                $item["url_params"] = $value["url_params"];
                $item["menu_category"] = $value["menu_category"];
                $item["menu_category_name"] = $value["menu_category_name"];
                $item["menu_url"] = $value["menu_url"];
                $item["url_target"] = $value["url_target"];
                $item["menu_pic"] = $value["menu_pic"];
                $item["status"] = $value["status"];
                $item["pid"] = $value["pid"];
                $item["order_no"] = $value["order_no"];
                $item["website_title"] = $value["website_title"];
                $item["website_title"] = $value["website_title"];
                $item["key_word"] = $value["key_word"];
                $item["description"] = $value["description"];
                $item["create_time"] = $value["create_time"];
                $item["remark"] = $value["remark"];
                $item["state"] = "open";
                $item["children"] = $this->getMenuTreeGridData($store,$value["id"],$value["menu_category"]);
                if (!$item["children"]) {
                    unset($item["children"]);
                    $item["iconCls"]="icon-extend-menu";
                }
                array_push($returnArray, $item);
                unset($item);
            }
        }
        return $returnArray;
    }
    
    /**
     * 添加菜单页面
     * */
    public function addMenuPage(){
        $this->display();
    }
    
    /**
     * 添加菜单
     * */
    public function addMenu(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
    
        if(IS_POST){
            //功能编码
            $menu_code= trim(I("menu_code"));
            if($menu_code&&$this->isExistMenuCode($menu_code)){
                $result["success"]=false;
                $result["message"]="该菜单编码已存在";
                $result["validateCode"]="MENU_CODE_EXIST";
            }else{
                
                //菜单名称
                $menu_name = trim(I("menu_name"));
                $englishname = trim(I('post.englishname'));
                //菜单类型
                $menu_type = I("menu_type");
                //模型名称
                $model_name = trim(I("model_name"));
                //控制器
                $controller_name = trim(I("controller_name"));
                //动作名称
                $action_name = trim(I("action_name"));
                //参数
                $url_params = trim(I("url_params"));
                
                //菜单类别
                $menu_category = I("menu_category");
                //菜单网址
                $menu_url = trim(I("menu_url"));
                //打开方式
                $url_target = I("url_target");
                //菜单图片
                $menu_pic = I("menu_pic");
                //状态
                $status = I("status");
                //父级菜单
                $menu_pid = I("menu_pid");
                //模型id
                $model_id = I("model_id");
                //排序号
                $order_no = I("order_no");
                //备注
                $remark = trim(I("remark"));
                //网页标题
                $website_title = trim(I("website_title"));
                //关键字
                $key_word = trim(I("key_word"));
                //网页描述
                $description = trim(I("description"));
                
                $menuModel = M("Menu");
                $data["menu_name"] = $menu_name;
                $data["menu_code"] = $menu_code;
                $data["menu_type"] = $menu_type;
                $data["model_name"] = $model_name;
                $data["controller_name"] = $controller_name;
                $data["action_name"] = $action_name;
                $data["url_params"] = $url_params;
                $data["menu_category"] = $menu_category;
                $data["menu_url"] = $menu_url;
                $data["url_target"] = $url_target;
                $data['englishname'] = $englishname;
                $data["menu_pic"] = $menu_pic;
                $data["status"] = $status;
                $data["pid"] = $menu_pid;
                if($model_id){
                    $data["model_id"] = $model_id;
                }
                $data["order_no"] = $order_no;
                $data["remark"] = $remark;
                $data["website_title"] = $website_title;
                $data["key_word"] = $key_word;
                $data["description"] = $description;
                $data["create_time"] = date("Y-m-d H:i:s");
    
                $res = $menuModel->add($data);
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
     * 修改页面
     * */
    public function editMenuPage(){
        $id = I("id");
        $menModel = M("Menu");
        $menu = $menModel->find($id);
        $this->assign("menu",$menu);
        $this->display();
    }
    
    /**
     * 更新菜单
     * */
    public function updateMenu(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            $menu_id = I("menu_id");
            //菜单名称
            $menu_name = trim(I("menu_name"));
            $englishname = trim(I('post.englishname'));
            //菜单类型
            $menu_type = I("menu_type");
            //模型名称
            $model_name = trim(I("model_name"));
            //控制器
            $controller_name = trim(I("controller_name"));
            //动作名称
            $action_name = trim(I("action_name"));
            //参数
            $url_params = trim(I("url_params"));
            
            //菜单编码
            $menu_code = trim(I("menu_code"));
            //菜单类别
            $menu_category = I("menu_category");
            //菜单网址
            $menu_url = trim(I("menu_url"));
            //打开方式
            $url_target = I("url_target");
            //菜单图片
            $menu_pic = I("menu_pic");
            //状态
            $status = I("status");
            //父级菜单
            $menu_pid = I("menu_pid");
            //模型id
            $model_id = I("model_id");
            //排序号
            $order_no = I("order_no");
            //备注
            $remark = trim(I("remark"));
            //网页标题
            $website_title = trim(I("website_title"));
            //关键字
            $key_word = trim(I("key_word"));
            //网页描述
            $description = trim(I("description"));
        
            $menuModel = M("Menu");
            $where["id"] = $menu_id;
            //取得原功能编码
            $orgMenu_code = $menuModel->where($where)->getField("menu_code");
        
            //判断功能编码是否修改过
            if($menu_code&&$orgMenu_code!=$menu_code){
                if($this->isExistMenuCode($menu_code)){
                    $result["success"] = false;
                    $result["message"]="该菜单编码已存在";
                    $result["validateCode"]="MENU_CODE_EXIST";
                }else{
                    $data["id"] = $menu_id;
                    $data["menu_name"] = $menu_name;
                    $data["menu_code"] = $menu_code;
                    
                    $data["menu_type"] = $menu_type;
                    $data["model_name"] = $model_name;
                    $data["controller_name"] = $controller_name;
                    $data["action_name"] = $action_name;
                    $data["url_params"] = $url_params;
                    
                    $data["menu_category"] = $menu_category;
                    $data["menu_url"] = $menu_url;
                    $data["url_target"] = $url_target;
                    $data["menu_pic"] = $menu_pic;
                    $data["status"] = $status;
                    $data['englishname'] = $englishname;
                    $data["pid"] = $menu_pid;
                    if($model_id){
                        $data["model_id"] = $model_id;
                    }else{
                        $data["model_id"] = array('exp','null');
                    }
                    $data["order_no"] = $order_no;
                    $data["remark"] = $remark;
                    $data["website_title"] = $website_title;
                    $data["key_word"] = $key_word;
                    $data["description"] = $description;
                     
                    $res = $menuModel->save($data);
                    if($res!==false){
                        $result["success"] = true;
                    }else{
                        $result["success"] = false;
                        $result["message"]="修改失败";
                    }
                }
            }else{
                $data["id"] = $menu_id;
                $data["menu_name"] = $menu_name;
                $data["menu_code"] = $menu_code;
                
                $data["menu_type"] = $menu_type;
                $data["model_name"] = $model_name;
                $data["controller_name"] = $controller_name;
                $data["action_name"] = $action_name;
                $data["url_params"] = $url_params;
                
                $data["menu_category"] = $menu_category;
                $data["menu_url"] = $menu_url;
                $data["url_target"] = $url_target;
                $data["menu_pic"] = $menu_pic;
                $data["status"] = $status;
                $data["pid"] = $menu_pid;
                if($model_id){
                    $data["model_id"] = $model_id;
                }else{
                    $data["model_id"] = array('exp','null');
                }
                $data["order_no"] = $order_no;
                $data["remark"] = $remark;
                $data["website_title"] = $website_title;
                $data["key_word"] = $key_word;
                $data["description"] = $description;
                $data['englishname'] = $englishname;
                $res = $menuModel->save($data);
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
     * 前台页面验证是否存在该菜单编码
     * */
    public function validMenuCode(){
        $menu_code = trim(I("menu_code"));
    
        if($this->isExistMenuCode($menu_code)){
            $result["isExist"] = true;
        }else{
            $result["isExist"] = false;
        }
        $this->ajaxReturn($result);
    }
    /**
     * 前台编辑页面验证是否存在该菜单编码
     * */
    public function validMenuCodeByUpdate(){
        $menu_id = I("menu_id");
        $menu_code = trim(I("menu_code"));
    
        $menuModel = M("Menu");
        $where["id"] = $menu_id;
        //取得原来的功能编码
        $orgMenuCode = $menuModel->where($where)->getField("menu_code");
    
        //判断是否修改过
        if($orgMenuCode&&$orgMenuCode!=$menu_code){
            $isExist = $this->isExistMenuCode($menu_code);
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
     * 是否存在菜单编码
     * */
    public function isExistMenuCode($menu_code){
        $menuModel = M("Menu");

        $menu = $menuModel->getByMenuCode($menu_code);
        if($menu){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 取得所属分类
     */
    public function getMenuCategory(){
        $dics = A("Fhmng/Dictionary")->getDicsByTypeCode("menu_category");
        $this->ajaxReturn($dics);
    }
    
    /**
     * 取得菜单树
     * */
    public function getMenuTree(){
        $menuModel = M("Menu");
        $where["status"]=1;
        $menus = $menuModel->where($where)->order("order_no asc")->select();
        $treeArr = $this->getMenuTreeData($menus,0,false);
        return $treeArr;
    }
    
    /**
     * 取得上级菜单树
     **/
    public function getPidMenuTree(){
        $id = I("id");
        $dic_key = I('get.dic_key');
        //编辑时，去除本身
        if($id){
            $where["id"]=array("NEQ",$id);
        }
        if($dic_key){
            $where['menu_category'] = $dic_key;
        }
        $menuModel = M("Menu");
        $menus = $menuModel->where($where)->order("order_no asc")->select();
        //获取部门树json串
        $treeArr = $this->getMenuTreeData($menus,0,true);
        $store = json_encode($treeArr);
        $this->ajaxReturn($treeArr);
    }
    
    /**
     *递归得到菜单树json
     *@param $store array 菜单数据源
     *@param $pid int 父菜单id
     *@param $hasRootNode boolean 是否含有根节点
     * */
    public function getMenuTreeData($store,$pid="0",$hasRootNode=false) {
        $returnArray = array();
        if($hasRootNode){
            $root["id"]="0";
            $root["text"]="顶级菜单";
            $root["state"] = "open";
            $root["iconCls"] = "icon-extend-deproot";
            array_push($returnArray, $root);
            unset($root);
        }
    
        foreach ($store as $key=>$value){
            if($value["pid"]==$pid){
                //主键
                $item["id"] = $value["id"];
                //菜单名称
                $item["text"] = $value["menu_name"];
                $item["state"] = "open";
                $item["children"] = $this->getMenuTreeData($store,$value["id"],false);
                if (!$item["children"]) {
                    unset($item["children"]);
                    $item["iconCls"]="icon-extend-menu";
                }
                array_push($returnArray, $item);
                unset($item);
            }
        }
        return $returnArray;
    }
    
    /**
     * 删除菜单
     * */
    public function delMenu(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id = I("id");
            //判断该菜单下是否含有子菜单
            if($this->_exsitChildMenu($id)){
                $result["success"] = false;
                $result["validateCode"] ="Exist_Child_Dep";
                $result["message"] = "该菜单下含子菜单，不允许删除";
            }else if($this->_exsitContent($id)){
                $result["success"] = false;
                $result["validateCode"] ="Exist_CONTENT";
                $result["message"] = "该菜单下含文章，不允许删除";
            }else{
                $menuModel = M("Menu");
                
                //删除记录之前要清空图片
                $menu = $menuModel->find($id);
                 $menu_pic= $menu["menu_pic"];
                if($menu_pic){
                    $menu_pic = "./Uploads/".$menu_pic;
                    removeFiles($menu_pic);
                }
               
                $where["id"] = array("in",$id);
                $res = $menuModel->where($where) ->delete();
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
     * 判断是否含有子菜单
     * @param $menu_id 菜单id
     * @return boolean
     * */
    private function _exsitChildMenu($menu_id){
        $menuModel = M("Menu");
        $where["pid"]=$menu_id;
    
        $count = $menuModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 判断该菜单下是否存在文章
     * */
    private function _exsitContent($menu_id){
        $contentModel = M("Content");
        $where["menu_id"]=$menu_id;
        
        $count = $contentModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    /**
     *  设置功能状态
     * */
    public function setStatus(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            $id = I("id");
            //状态,0：禁用，1:启用
            $status = I("status");
            if($status&&$status==1){
                $status=1;
            }else{
                $status=0;
            }
    
            $menuModel = M("Menu");
            $data["status"] = $status;
            $where["id"] = array("in",$id);
    
            $res = $menuModel->where($where)->save($data);
    
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
     * 上传菜单图片
     * */
    public function uploadPic(){
        //原图片地址
        $orgPath = I("path");
        //如果含有则删除
        if($orgPath){
            $orgPath= "./Uploads/".$orgPath;
            $res = removeFiles($orgPath);
            //如果删除成功，则清除表中的menu_pic字段
            if($res){
                $id = I("id");
                if($id){
                    $menuModel = M("Menu");
                    $data["id"]=$id;
                    $data["menu_pic"]="";
                    $menuModel->save($data);
                }
            }
        }
        $upload = new \Think\Upload();// 实例化上传类
        //文件上传的最大文件大小（以字节为单位），0为不限大小
        $upload->maxSize=3*1024*1024 ;
        //文件上传保存的根路径
        $upload->rootPath="./Uploads/";
        //文件上传的保存路径（相对于根路径）
        $upload->savePath = '/menu/';
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
     * 删除菜单图片
     * */
    public function removePic(){
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
                    $menuModel = M("Menu");
                    $data["id"]=$id;
                    $data["menu_pic"]="";
                    $menuModel->save($data);
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