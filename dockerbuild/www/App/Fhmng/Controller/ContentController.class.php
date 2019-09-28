<?php
/**日志
 * 模块：文章内容管理
 * 创建人:李宝振
 * 创建时间：2016-09-08
 * 模块权限code:AUTH_CONTENT
 * 描述：
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class ContentController extends BaseController{
    /**
     * 文章内容首页
     * */
    public function contentIndex(){
        //检查权限
        $this->checkPageAuth("AUTH_CONTENT");
        //文章是否需要审核,需要审核：need_audit,不需要审核:no_audit
        $content_is_check = getSysConfigValue("content_check");
        if(!$content_is_check){
            $content_is_check="no_audit";
        }
        $this->assign("content_is_check",$content_is_check);
        $this->display();
    }
    
    /**
     * 获取文章数据列表
     * */
    public function getContentList(){
        //菜单id
        $menu_id = I("menu_id");
        $menu_code = M("menu")->where(['id'=>$menu_id])->getField('menu_code');
        if($menu_code == 'FHXW' || $menu_code == 'HYBG' || $menu_code == '12DY'|| $menu_code == '13DY' || $menu_code == '14DY' || $menu_code == '15DY' || $menu_code == '16DY' || $menu_code == '17DY' || $menu_code == '18DY'){
            $menuid_arr  =  M('menu')->where(['menu_code'=>$menu_code])->getField('id',true);
        }else{
            $menuid_arr = $menu_id;
        }
        //状态
        $status = I("status");
        //查询值
        $queryStr = I("queryStr");
        
        $where["menu_id"] = ['in',$menuid_arr];
        // if($status=="0"||$status=="1"||$status=="2"||$status=="3"){
        //     $where["status"] = $status;
        // }
        $where["status"] = ['neq',3];
        if(!empty($queryStr)){
            $where["title"]=array('LIKE','%'. $queryStr .'%');
        }
        
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
        
        $contentModel = D("Content");
        $total = $contentModel->where($where)->count();
        $res = $contentModel->order("is_top desc,order_no asc,id")->where($where)->page($page,$rows)->select();
        
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    
    /**
     * 文章内容添加页面
     * */
    public function addContentPage(){
        $menu_id = I("menu_id");
       
        
        $wheremenu['id']=$menu_id;
        $menuModel = M("Menu");
        $menu = $menuModel->where($wheremenu)->select();
        
        $this->assign("menu_id",$menu_id);
        $this->assign("menu_name",$menu[0]['menu_name']);
        $this->assign("menu_code",$menu[0]['menu_code']);
        
        $htmlText = $this->getModelFieldsConfig($menu_id);
        $this->assign("fieldsConfig",$htmlText);
        
        //文章是否需要审核,需要审核：need_audit,不需要审核:no_audit
        $content_is_check = getSysConfigValue("content_check");
        if(!$content_is_check){
            $content_is_check="no_audit";
        }
        $this->assign("content_is_check",$content_is_check);
        
        $this->display();   
    }
    
    /**
     * 根据菜单id获取菜单关联模型的字段配置
     * @param $menu_id 菜单id
     * @return 拼接的html字符串
     * */
    public function getModelFieldsConfig($menu_id,$json=null){
        $htmlText = "<div title='模型扩展字段' style='padding-top:10px'>";
        $htmlText.="<table>";
        //获取模型id
        $menu = M("Menu")->find($menu_id);
        $model_id = $menu["model_id"];
        //如果配置了模型，则取得模型的字段配置
        $trText = "";
        if($model_id){
            $fields = A("Fhmng/Fields")->getFieldsByModelId($model_id);
            foreach ($fields as $key=>$value){
                //前台提交时用于区分扩展字段
                $value["field_name"] = "extend_".$value["field_name"];
                $trText.="<tr>";
                $trText.="<td class='text_right' style='width:80px'>";
                $trText.=$value["field_alias"]."：";
                $trText.="</td>";
                //控件值
                $controlValue ="";
                if($json){
                    //根据json串和字段名，获取值
                    $controlValue = getExtendJsonValue($json, $value["field_name"]);
                }
                if( ($value["field_type"]=="textbox") || ($value["field_type"]=="emailbox")|| ($value["field_type"]=="idcardbox")|| ($value["field_type"]=="phonebox") ){
                    $trText.="<td>";
                    $trText.="<input class='easyui-textbox' id='".$value["field_name"]."' name='". $value["field_name"] ."' style='width:600px' value='".$controlValue."'/>";
                    $trText.="</td>";
                  
                }else if(($value["field_type"]=="numberspinner") || ($value["field_type"]=="moneybox") ){
                    $trText.="<td>";
                    $trText.="<input class='easyui-numberspinner' id='".$value["field_name"]."' name='". $value["field_name"] ."' style='width:145px' data-options='min:0,max:999999,editable:true";
                    if($controlValue){
                        $trText.=",value:\"".$controlValue."\"";
                    }
                    $trText.="'/>";
                    $trText.="</td>";
                }else if($value["field_type"]=="textarea"){
                    $trText.="<td>";
                    $trText.="<textarea style='width:800px;height:80px' class='easyui-validatebox text-area' id='".$value["field_name"]."' name='". $value["field_name"] ."'>";
                    if($controlValue){
                        $trText.=$controlValue;
                    }
                    $trText.="</textarea>";
                    $trText.="</td>";
                }else if($value["field_type"]=="datebox"){
                    $trText.="<td>";
                    $trText.="<input  class='easyui-datebox' id='".$value["field_name"]."' name='". $value["field_name"] ."' value='".$controlValue."'/>";
                    $trText.="</td>";
                }else if($value["field_type"]=="datetimebox"){
                    $trText.="<td>";
                    $trText.="<input  class='easyui-datetimebox' id='".$value["field_name"]."' name='". $value["field_name"] ."' style='width:150px' value='".$controlValue."'/>";
                    $trText.="</td>";
                }else if($value["field_type"]=="combobox"){
                    $trText.="<td>";
                    $trText.="<input  class='easyui-combobox' id='".$value["field_name"]."' name='". $value["field_name"] ."' data-options='panelHeight:\"auto\",valueField:\"dic_key\",textField:\"dic_value\",url:\"".U("Content/getComboStore")."\",queryParams:{type_code:\"".$value["dic_type_code"]."\"}";
                    if($controlValue){
                        $trText.=",value:\"".$controlValue."\"";
                    }
                    $trText.="'/>";
                    $trText.="</td>";
                }else if($value["field_type"]=="checkbox"){
                    $arr = explode(',', $controlValue);
                    $dic_type_code = $value["dic_type_code"];
                    $list = $this->getRadioList($dic_type_code);

                    $trText.="<td>";
                    foreach ($list as $k=> $v ){
                        $trText.="<input   id='".$value["field_name"]."' name='". $value["field_name"] ."[]' type='checkbox' value='".$v['dic_value']."' ";
                       if(in_array($v['dic_value'],$arr)){
                        $trText .= "checked='true'";
                       }
                        $trText.="'/>" . $v['dic_value'];
                    }
                    $trText.="</td>";
                }else if($value["field_type"]=="radio"){
                    $trText.="<td>";
                    $dic_type_code = $value["dic_type_code"];
                    $list = $this->getRadioList($dic_type_code);
                    $trText.="<td>";
                    foreach ($list as $k=> $v ){
                        $trText.="<input   id='".$value["field_name"]."' name='". $value["field_name"] ."' type='radio' value='".$v['dic_value']."' ";
                       if($controlValue == $v['dic_value']){
                            $trText.="checked='true'";
                        }
                        $trText.="'/>" . $v['dic_value'];
                    }
                    $trText.="</td>";
                }/* else if($value["field_type"]=="webeditor"){
                    $trText.="<td>";
                    //$trText.="<script id='container1' name='". $value["field_name"] ."' style='width:800px;height:300px;' type='text/plain'/>";
                    //$trText.="<script src='a.js'/>";
                    $trText.="</td>";
                } */
                $trText.="</tr>";
            }
        }
        if($trText){
            $htmlText.=$trText;
            $htmlText.="</table>";
            $htmlText.="</div>";
        }else{
            $htmlText="";
        }
       
        return $htmlText;
    }
    
    /**
     * 在字典中取得配置字段combo的下拉值
     * */
    public function getComboStore(){
        $dic_type_code = I("type_code");
        $dics = A("Fhmng/Dictionary")->getDicsByTypeCode($dic_type_code);
        $this->ajaxReturn($dics);
    }
    /**
     * 在字典中取得配置字段checkbox和radio的值
     * */
    public function getRadioList($dic_type_code){
        $dics = A("Fhmng/Dictionary")->getDicsByTypeCode($dic_type_code);
        return $dics;
    }
    /**
     * 添加文章
     * */
    public function addContent(){
        $result["success"]=false;
        $result["message"]="";
        $result["validateCode"]="";
        
        if(IS_POST){
            //栏目菜单id
            $menu_id = I("smenu_id");
            //文章标题
            $title = trim(I("title"));
            //一级副标题
            $sub_title1 = trim(I("sub_title1"));
            //二级副标题
            $sub_title2 = trim(I("sub_title2"));
            //文章内容
            // $content =  htmlspecialchars_decode(html_entity_decode(trim(I("content"))));
            $content = I('content');
            //摘要
            $summary = trim(I("summary"));
            //状态
            $status = I("status");
            //排序号
            $order_no = I("order_no");
            
            //网页标题
            $website_title = trim(I("website_title"));
            //网页关键字
            $key_word = trim(I("key_word"));
            //网站描述
            $description = trim(I("description"));
            
            //tags标签
            $tags = trim(I("tags"));
            //作者
            $author = trim(I("author"));
            //来源
            $source = trim(I("source"));
            //转向链接
            $link_url = trim(I("link_url"));
            //文章编码 content_code
            $content_code=trim(I("content_code"));
            //文章文号
            $content_wh = trim(I("content_wh"));
            //访问次数
            $visits = I("visits");
            //是否置顶
            $is_top = I("is_top");
            //发布时间
            $publish_time = I("publish_time");
            //过期时间
            $expiry_date = I("expiry_date");
            //文章图片
            $content_img = I("content_img");
            
            $params = I('.');
            $extendObj = null;
            foreach ($params as $key=>$value){
                $paramName = $key;
                if(!is_array($value)){
                    //防止中文乱码，先用urlencode处理一下
                    //然后再json_encode，输出结果的时候在用函数urldecode()转回来
                    $paramValue = urlencode($value);
                
                    $pos = strpos($paramName, "extend_");
                    if($pos!==false){
                        $extendObj[$paramName] = $paramValue;
                    }
                }else{
                    $paramValue = urlencode(implode(',', $value));
                
                    $pos = strpos($paramName, "extend_");
                    if($pos!==false){
                        $extendObj[$paramName] = $paramValue;
                    }
                }
            }
            
            $extendJson = urldecode(json_encode($extendObj));
            //判断文章编码是否唯一
            if($content_code&&$content_code!=""){
               $exist=$this->_existContentCode($content_code);
               if($exist==true){
                   $result["success"]=false;
                   $result["message"]="已存在此文章编码";
                   $this->ajaxReturn($result);
                   return;
               }
            }
            $contentModel = M("Content");
            $data["menu_id"] = $menu_id;
            $data["title"] = $title;
            $data["sub_title1"] = $sub_title1;
            $data["sub_title2"] = $sub_title2;
            $data["content"] = $content;
            $data["summary"] = $summary;
            $data["status"] = 1;
            $data["order_no"] = $order_no;
            $data["website_title"] = $website_title;
            $data["key_word"] = $key_word;
            $data["description"] = $description;
            $data["tags"] = $tags;
            $data["author"] = $author;
            $data["source"] = $source;
            $data["link_url"] = $link_url;
            $data["visits"] = $visits;
            $data["is_top"] = $is_top;
            $data["content_code"]=$content_code;
            $data["content_wh"]=$content_wh;
            $data['share_img'] = I('post.share_img');
            if($publish_time){
                $data["publish_time"] = $publish_time;
            }else{
                $data["publish_time"] = date("Y-m-d H:i:s");
            }
            if($expiry_date){
                $data["expiry_date"] = $expiry_date;
            }
            $data["content_img"] = $content_img;
            $data["create_time"] = date("Y-m-d H:i:s");
            $data["update_time"] = date("Y-m-d H:i:s");
            // var_dump($extendJson);exit;
            if($extendJson){
                $data["extends"] = $extendJson;
            }
            //开启事物
            $contentModel->startTrans();
            $res = $contentModel->add($data);
            $res2=1;
            //如果状态为审核通过 则需要添加审核记录
            // if($status==1){
            //     //审批记录
            //     $contentAuditModel = M("ContentAudit");
            //     $data2["audit_person"] = $this->session_user["true_name"];
            //     $data2["audit_time"] = date("Y-m-d H:i:s");
            //     if($status==1){
            //         $data2["audit_result"] = "审核通过";
            //     }else if($status==3){
            //         $data2["audit_result"] = "审核未通过";
            //     }
            //     $data2["audit_opinion"] ="";
            //     $data2["content_id"] = $res;
            //     $res2 = $contentAuditModel->add($data2);
            // }
            if($res!==false&&$res2!==false){
                $contentModel->commit();
                $result["success"] = true;
            }else{
                $contentModel->rollback();
                $result["success"] = false;
                $result["message"]="添加失败";
            }
        }else{
            $result["success"]=false;
            $result["message"]="非法操作";
        }
        $this->ajaxReturn($result);
    }
    /**
     * 判断文章编码是否存在*/
    public function _existContentCode($content_code){
        $contentModel = M("Content");
        $where['content_code']=$content_code;
        $where['status'] = ['neq',3];
        $count=$contentModel->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    } 
    
    /**
     * 修改文章
     * */
    public function editContentPage(){
        $id = I("id");
        $contentModel = M("Content");
        $content = $contentModel->find($id);
        
        $sql=$contentModel->getLastSql();
        
        $this->assign("content",$content);
        
        $menu_id = $content["menu_id"];
        $htmlText = $this->getModelFieldsConfig($menu_id,$content["extends"]);
        $this->assign("fieldsConfig",$htmlText);
       
        $wheremenu['id']=$menu_id;
        $menuModel = M("Menu");
        $menu = $menuModel->where($wheremenu)->select();
        
        $this->assign("menu_id",$menu_id);
        $this->assign("menu_name",$menu[0]['menu_name']);
        $this->assign("menu_code",$menu[0]['menu_code']);
        //文章是否需要审核,需要审核：need_audit,不需要审核:no_audit
        $content_is_check = getSysConfigValue("content_check");
        if(!$content_is_check){
            $content_is_check="no_audit";
        }
        $this->assign("content_is_check",$content_is_check);
        
        $this->display();
    }
    
    /**
     * 更新文章
     * */
    public function updateContent(){
        $result["success"] = false;
        $result["validateCode"] = "normal";
        $result["message"]="";
        if(IS_POST){
            //栏目菜单id
            $id = I("id");
            //文章标题
            $title = trim(I("title"));
            //一级副标题
            $sub_title1 = trim(I("sub_title1"));
            //二级副标题
            $sub_title2 = trim(I("sub_title2"));
            //文章内容
            // $content =  htmlspecialchars_decode(html_entity_decode(trim(I("content"))));
            $content = I('content');
            //摘要
            $summary = trim(I("summary"));
            //状态，0：待审核，1：审核通过，2：草稿
            //$status = I("status");
            
            /*--需要审核则前台只能设置为草稿或者待审核，此处不用处理了
            //文章是否需要审核,需要审核：need_audit,不需要审核:no_audit
            $content_is_check = getSysConfigValue("content_check");
            if(!$content_is_check){
                $content_is_check="no_audit";
            }
            //如果文章需要审核,则编辑的时候，审核状态设置为待审核
            if($content_is_check=="need_audit"){
                $status = 0;
            }*/
            
            //排序号
            $order_no = I("order_no");
            
             //网页标题
            $website_title = trim(I("website_title"));
            //网页关键字
            $key_word = trim(I("key_word"));
            //网站描述
            $description = trim(I("description"));
            
            //tags标签
            $tags = trim(I("tags"));
            //作者
            $author = trim(I("author"));
            //来源
            $source = trim(I("source"));
            //转向链接
            $link_url = trim(I("link_url"));
            //文章编码 content_code
            $content_code=trim(I("content_code"));
            //文章文号
            $content_wh = trim(I("content_wh"));
            //访问次数
            $visits = I("visits");
            //是否置顶
            $is_top = I("is_top");
            //发布时间
            $publish_time = I("publish_time");
            //过期时间
            $expiry_date = I("expiry_date");
            //文章图片
            $content_img = I("content_img");
            $menu_id = I("menu_id");
            $params = I('');
            $extendObj = null;
            foreach ($params as $key=>$value){
                $paramName = $key;
                if(!is_array($value)){
                    //防止中文乱码，先用urlencode处理一下
                    //然后再json_encode，输出结果的时候在用函数urldecode()转回来
                    $paramValue = urlencode($value);
                
                    $pos = strpos($paramName, "extend_");
                    if($pos!==false){
                        $extendObj[$paramName] = $paramValue;
                    }
                }else{
                    $paramValue = urlencode(implode(',', $value));
                
                    $pos = strpos($paramName, "extend_");
                    if($pos!==false){
                        $extendObj[$paramName] = $paramValue;
                    }
                }
            }
            $extendJson = urldecode(json_encode($extendObj));
            
            $contentModel = M("Content");
            
            //判断文章编码是否唯一
            if($content_code&&$content_code!=""){
                $sourceContent=$contentModel->find($id);
                if($content_code!=$sourceContent['content_code']){
                    $exist=$this->_existContentCode($content_code);
                    if($exist==true){
                        $result["success"]=false;
                        $result["message"]="已存在此文章编码";
                        $this->ajaxReturn($result);
                        return;
                    }  
                } 
            }
           
            $data["id"] = $id;
            $data["title"] = $title;
            $data["sub_title1"] = $sub_title1;
            $data["sub_title2"] = $sub_title2;
            $data["content"] = $content;
            $data["summary"] = $summary;
            //2016年11月8日 修改后 状态设置为草稿
            //$data["status"] = 2; 2016年11月29日 先暂时隐藏掉
            $data["order_no"] = $order_no;
            
            $data["website_title"] = $website_title;
            $data["key_word"] = $key_word;
            $data["description"] = $description;
            
            $data["tags"] = $tags;
            $data["author"] = $author;
            $data["source"] = $source;
            $data["link_url"] = $link_url;
            $data["visits"] = $visits;
            $data["is_top"] = $is_top;
            $data["content_code"]=$content_code;
            $data["content_wh"]=$content_wh;
            $data['share_img'] = I('post.share_img');
            $data['menu_id']=$menu_id;
            if($publish_time){
                $data["publish_time"] = $publish_time;
            }
            
            if($expiry_date){
                $data["expiry_date"] = $expiry_date;
            }else{
                $data["expiry_date"]=null;
            }
            $data["content_img"] = $content_img;
            $data["update_time"] = date("Y-m-d H:i:s");
             
            if($extendJson){
                $data["extends"] = $extendJson;
            }
            
            $res = $contentModel->save($data);
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
    
    /**
     * 删除文章
     * */
    public function delContent(){
        $result["success"] = false;
        $result["validateCode"] ="";
        $result["message"] = "";
        if(IS_POST){
            $id = I("id");
            $contentModel = M('Content');
            //删除文章之前判断是否含有图片
            $content = $contentModel->find($id);
            if($content["content_img"]){
                $content_img = "./Uploads/".$content["content_img"];
                removeFiles($content_img);
            }
            
            $where["id"] = array("in",$id);
            $res = $contentModel->where($where) ->delete();
        
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
            //状态，0:待审核，1：启用，2：禁用，3：删除
            $status = I("status");
            $contentModel = M("Content");
            $data["status"] = $status;
            $where["id"] = array("in",$ids);
            $res = $contentModel->where($where)->save($data);
    
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
     * 上传图片
     * */
    public function uploadImgShare(){
    	//原图片地址
    	$orgPath = I("path");
    	//如果含有则删除
    	if($orgPath){
    		$orgPath= "./Uploads/".$orgPath;
    		$res = removeFiles($orgPath);
    		//如果删除成功，则清除表中的content_img字段
    		if($res){
    			$id = I("id");
    			if($id){
    				$contentModel = M("Content");
    				$data['id']=$id;
    				$data['share_img']="";
    				$contentModel->save($data);
    			}
    		}
    	}
    	$res = upload_img('Filedata','content');
    	$this->ajaxReturn($res);

    }
    function upload_pic(){
        //原图片地址
        $res = upload_img('pic','content');
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
        $path = I('path');
        $res =  removeImg($path);
        if($res){
            $id = I('id');
             //如果含有id，说明是编辑页面，则对应的link_logo字段要清空
            if($id){
                    $contentModel = M('Content');
                    $data['id']=$id;
                    $data['content_img']='';
                    $contentModel->save($data);
            } 
            $this->ajaxReturn(['success'=>true,'message'=>'删除成功']);
        }else{
            $this->ajaxReturn(['success'=>false,'message'=>'图片删除失败']);
        }
    }    
    /**
     * 取得菜单
     * */
    public function getMenu(){
        
        $menuModel = M("Menu");
        $where["status"]=1;
        $menus = $menuModel->where($where)->order("order_no asc")->select();
        
        //在字典表里取得菜单类别
        $dics = A("Fhmng/Dictionary")->getDicsByTypeCode("menu_category");
        $returnArray = array();
        foreach ($dics as  $key=>$value){
            //主键
            $item["id"] = "menu_category_".$value["id"];
            //菜单名称
            $item["text"] = $value["dic_value"];
            $item["menu_category"] = $value["dic_key"];
            $item["state"] = "open";
            $item["children"] = $this->getTreeJson($menus,0,$value["dic_key"]);
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
     *递归得到菜单树json
     *@param $store array 菜单数据源
     *@param $pid int 父菜单id
     *@param $menu_category 菜单类别
     * */
    public function getTreeJson($store,$pid="0",$menu_category) {
        $returnArray = array();
    
        foreach ($store as $key=>$value){
            if($value["pid"]==$pid&&$value["menu_category"]==$menu_category){
                //主键
                $item["id"] = $value["id"];
                //菜单名称
                $item["text"] = $value["menu_name"];
                $item["menu_category"] = $menu_category;
                $item["state"] = "open";
                $item["children"] = $this->getTreeJson($store,$value["id"],$value["menu_category"]);
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
     * 文章审核
     * */
    public function contentCheck(){
        $this->display();
    }
    
    /**
     * 获取审核文章数据列表
     * */
    public function getCheckContentList(){
        //菜单id
        $menu_id = I("menu_id");
        //状态
        $status = I("status");
        //查询值
        $queryStr = I("queryStr");
        //如果点击了某菜单，则查询相应菜单的文章，没有则默认查询全部
        if($menu_id){
            $where["menu_id"] = $menu_id;
        }
        //状态，0:待审核，1：审核通过，2：草稿，3：审核不通过
        if($status=="0"||$status=="1"||$status=="3"){
            $where["status"] = $status;
        }else{
            $where["status"] = array("NEQ",2);
        }
    
        if(!empty($queryStr)){
            $where["title"]=array('LIKE','%'. $queryStr .'%');
        }
    
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
    
        $contentModel = D("Content");
        $total = $contentModel->where($where)->count();
        $res = $contentModel->order("is_top desc,order_no asc,id desc")->where($where)->page($page,$rows)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    
    /**
     * 取得文章的审核记录
     * */
    public function getAuditData(){
        //文章id
        $content_id = I("content_id");
        $where["content_id"] = $content_id;
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
    
        $contentAuditModel = D("ContentAudit");
        $total = $contentAuditModel->where($where)->count();
        $res = $contentAuditModel->order("audit_time desc")->where($where)->page($page,$rows)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
    }
    /**
     * 审核修改为子表模式 2016年11月7日
     * */
    public function contentAuditPage(){
        $id = I("id");
        $this->assign("id",$id);
        $this->display();
    }
    /**
     * 审核2016年11月7日
     * */
    public function contentAudit(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //文章id
            $id = I("id");
            //状态，0:待审核，1：审核通过，2：草稿，3：审核不通过
            $status = I("status");
            //审核意见
            $audit_opinion = I("audit_opinion");
    
            //修改状态
            $contentModel = M("Content");
            $content=$contentModel->find($id);
            $data["id"] = $id;
            $data["status"] = $status;
    
            //开始事务
            $contentModel->startTrans();
            $res = $contentModel->save($data);
    
            //审批记录
            $contentAuditModel = M("ContentAudit");
            $data2["audit_person"] = $this->session_user["true_name"];
            $data2["audit_time"] = date("Y-m-d H:i:s");
            if($status==1){
                $data2["audit_result"] = "审核通过";
            }else if($status==3){
                $data2["audit_result"] = "审核未通过";
            }
            $data2["audit_opinion"] = $audit_opinion;
            $data2["content_id"] = $id;
            $res2 = $contentAuditModel->add($data2);
    
            if($res!==false&&$res2!==false){
                //提交事务
                $contentModel->commit();
                $result["success"] = true;
                $result["message"]="操作成功";
            }else{
                $contentModel->rollback();
                $result["success"] = false;
                $result["message"]="操作失败";
            }
        }else{
            $result["success"] = false;
            $result["message"]="非法操作";
        }
        $this->ajaxReturn($result);
    }
}