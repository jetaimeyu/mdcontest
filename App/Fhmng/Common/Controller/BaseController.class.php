<?php
/**日志
 * 模块：BaseController
 * 创建人:李宝振
 * 创建时间：2016-07-25
 * 描述：用户管理
 *
 * 修改人：
 * 修改时间：
 * 修改描述：所有的后台controller必须继承此controller，
 * 用于公共部分的赋值，以及session的判断
 *
 * */
namespace Fhmng\Common\Controller;

use Think\Controller;
class BaseController extends Controller{

    //是否是超管
    protected $is_admin = null;
    //系统设置的过期时间
    protected $session_time_out = null;
    //用户session
    protected $session_user = null;
    //权限session串
    protected $session_authStr = null;
    //访问的模块名称
    protected $visit_modelName = null;
    //访问的控制器名称
    protected $visit_controllerName = null;
    //访问的方法名称
    protected $visit_ActionName = null;
    
    //子类继承BaseController时，ThinkPHP会自动执行该方法
    protected function _initialize(){
        $this->visit_modelName = MODULE_NAME;
        $this->visit_controllerName = CONTROLLER_NAME;
        $this->visit_ActionName = ACTION_NAME;
        
        //系统设置的过期时间
        $this->session_time_out = session("session_time_out");
        
        //判断是否含有用户Session
        session(['name'=>'aeci']);
        $this->session_user = session("session_user");
        
        $login_time = session("session_login_time");
        $now_time = time();
        $this->is_admin = $this->session_user['is_admin'];
       
        //判断是否超时
        if(($now_time-$login_time)>$this->session_time_out||!$this->session_user){
            session("session_user",null);
            session("session_login_time",null);
            session("session_time_out",null);
            
            if(IS_AJAX){
                $error["sessiontimeout"]=true;
                $this->ajaxReturn($error);
            }else{
                $this->redirect("Fhmng/Aeciverify/wersdf");
            }
        }else{
            session("session_login_time",time());
            $this->assign("session_user",$this->session_user);
        }
        
//         if(!$this->session_user){
//             //$this->error("非法访问",U("Login/login"));
//             //$this->redirect("MessagePage/session_error");
//             if(IS_AJAX){
//                 $error["sessiontimeout"]=true;
//                 $this->ajaxReturn($error);
//             }else{
//                 $this->redirect("MessagePage/session_error");
//             }
            
//         }
//         $this->assign("session_user",$this->session_user);
        
        //初始化
        $this->init();
    }
    
    /**
     * 初始化
     * */
    private function init(){
        //获取用户权限session串
        if(!session("authStr")){
            $uid = $this->session_user["id"];
            //如果是admin账号则权限串赋值为all，表示全部权限
            if($this->is_admin){
                $authStr ="all";
                session("authStr",$authStr);
            }else{
                //根据用户id取得权限串
                $authStr = $this->getUserAuthStr($uid);
                session("authStr",$authStr);
            }
        }
        $this->session_authStr = session("authStr");
        
    }
    
    
    /**
     * @access 取得用户权限串
     * @param $uid int 用户id
     * @return 用户权限串，即功能id串
     * */
    private function getUserAuthStr($uid){
        $roleUserViewModel = D("RoleUserView");
        //查询条件
        //角色可用
        $where["role_status"]=1;
        $where["uid"] = $uid;
    
        $groups = $roleUserViewModel->where($where)->select();
        //权限串
        $rules  = array();
        foreach ($groups as $g) {
            $rules = array_merge($rules, explode(',', trim($g['function_str'])));
        }

        $rules = array_unique($rules);

        return implode(",", $rules);
    }
    
    /**
     * @access检查页面权限
     * @param $AuthCode varchar 权限编码
     *
     * */
    protected function checkPageAuth($AuthCode){
        if($this->session_authStr!="all"){
            $funcModel = M("Function");
            $funcid = $funcModel->getByFuncCode($AuthCode)["id"];
            $findStr = ",".$funcid.",";
            //检查功能菜单是否在权限内
            $index = strpos(",".$this->session_authStr.",", $findStr);
            if($index===false){
                $this->redirect("Fhmng/MessagePage/no_auth");
            }
        }
    }
    /*更新某个字段(常用作假删、启用、禁用)*/
    protected function updateField($table,$where,$field,$value){
        $res = M($table)->where($where)->setField($field,$value);
        if($res !== false){
            return ['success'=>true,'message'=>'操作成功'];
        }else{
            return ['success'=>false,'message'=>'操作失败'];
        }
    }

    /*启用或禁用
    *前台ajax方式传递 id,table,value
    */
    public function enableOrdisable(){
        if(IS_AJAX){
            $post = I('post.');
            $value = $post['status'];
            $table = $post['table'];
            $where['id'] = ['IN',$post['id']];
            $res = $this->updateField($table,$where,'status',$value);
            $this->ajaxReturn($res);
        }
    }
     /**
        *保存一条记录（save或add适用）
        * @param $table 表名
        * @param $data  要保存的数据
        * @param $addCallback  添加记录后的回调方法,参数为插入的新记录的id
        * @param $saveCallback  更新记录后的回调方法,参数为已更新的记录的id
        *说明：根据$data中有无id来判断是save还是add
      */
    protected function saveRow($table,$data,$addCallback = '',$saveCallback = ''){
        if(!$data['id'])
        unset($data['id']);
        $model = D($table);
        $model->create($data);
        if($model->id){
            $res =  $model->save();
            if($res !== false && $saveCallback){
                $saveCallback($model->id);
            }
        }else{
            $res =  $model->add();
            if($res !== false && $addCallback){
                $addCallback($res);
            }
        }
        if($res !== false){
            return ['success'=>true,'message'=>'操作成功'];
        }else{
            return ['success'=>false,'message'=>'操作失败'];
        }
    }


    /*
    * 传递数组参数例如 array(
    *                 'table'=>'user',//默认当前控制器名称
    *                 'dir'=>'EditModel',//默认为Model
    *                 'where'=>array(
    *                     'eq_uid'=>1,
    *                     'neq_status'=>3
    *                    ),
    *                 'order'=>"id desc,age asc"
    *                 )
      返回格式 array('rows'=>$data,'total'=>$count);
    */
 
    protected function get_datas($config = [],$post = null){
        //表名
        $table = $config['table']?:CONTROLLER_NAME;
        $join = $config['join']?:'';
        //模型所在层 默认Model
        $dir = $config['dir']?:'Model';
        //是否分页
        if(isset($config['page'])){
            $ifpage = $config['page'];
        }else{
            $ifpage = true;
        }
        if(!$post)
        $post = I('post.');
        //当前页
        $page = $post['page']?:1;
        unset($post['page']);
        //每页的数量
        $size = $post['rows']?:10;
        unset($post['rows']);
        $sort = $post['sort'];
        $order = $post['order'];
        unset($post['sort']);
        unset($post['order']);
        $where = [];
        $condArray = ['eq','like','neq','lt','elt','gt','egt','in'];
        if(is_array($config['where'])){
            $post = array_merge($config['where'],$post);
        }
        foreach ($post as $field=>$value) {
            if($field == '_string'){
                $where['_string'] = $value;
            }else{
                //例： like_field_name 
                $fieldArray = explode('_', $field);//array('like','field','name')
                if(in_array($fieldArray[0], $condArray)){
                    $field = array_diff($fieldArray,array($fieldArray[0]));
                    $field = implode('_',$field);//field_name
                    if($fieldArray[0] == 'like' && $value){
                        $value = "%\\$value%";
                    }else if($fieldArray[0] == 'like'){
                        continue;
                    }
                    $where[$field] = [$fieldArray[0],$value];

                }else{
                    $where[$field] = $value;
                }
           }
        }
        $field = $config['field']?:'*';
        if($sort){
            $order = $sort . ' ' . $order;
            $order .= $config['order']?','.$config['order']:',id asc';
        }else{
            $order .= $config['order']?:'id asc';
        }
        $model  = D($table,$dir);
        $count = $model->where($where)->count();
        if($ifpage){
            if($join){
                $data  = $model->where($where)->page($page,$size)->join($join)->field($field)->order($order)->select();
            }else{
                $data  = $model->where($where)->page($page,$size)->field($field)->order($order)->select();
            }
            $retdata = ['rows'=>$data,'total'=>$count];
        }else{
            $retdata  = $model->where($where)->field($field)->order($order)->select();
        }
        return $retdata;
    }
    /**
     * 退出登录
     * */
    public function loginOut(){
        //清空session
        session('session_user',null);
        session('authStr',null);
        $this->redirect('/aeci');
    }


   
    /**
    *生成订单号
    *lj
    */
    function getOrdercode(){
        // $mid = session('session_user')['current_mid'];
        $sql = "SELECT next_serial_num() as num";
        $num = M()->query($sql);
        $ordercode = trim(date('Ymd').$num[0]['num']);
        $len = 13 - strlen($ordercode);
        if($len > 0 ){
            $exp =  str_repeat('0',$len);
            $ordercode = trim(date('Ymd').$exp.$num[0]['num']);
        }
        return $ordercode;
    } 

 /**
 * 导出数据为excel表格
 *@param $data    一个二维数组,结构如同从数据库查出来的数组
 *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
 *@param $filename 下载的文件名
 *@examlpe
 *$stu = M ('User');
 *$arr = $stu -> select();
 *exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
 */
protected function exportexcel($data=array(),$headArr=array(),$fileName='report'){
        Vendor("PHPExcel\PHPExcel");
        //对数据进行检验
        if(empty($data) || !is_array($data)){
            die("data must be a array");
        }
        $date = date("Ymd",time());
        $fileName .= "_{$date}.xls";
        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objActSheet = $objPHPExcel->getActiveSheet();
        //设置表头
        $key = ord("A");
        foreach($headArr as $v){
            $colum = chr($key);
            $objActSheet->setCellValue($colum.'1', $v);
            $objActSheet->getStyle($colum.'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $key += 1;
        }
        $column = 2;
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->getColumnDimension($j)->setAutoSize(true);
                $objActSheet->setCellValue($j.$column, strip_tags(htmlspecialchars_decode($value)));
                $span++;
            }
            $column++;
        }
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        // $objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->getProperties()->setTitle( "Office 2007 XLSX Test Document" );
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: applicationnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
    /**
     * 导入excel
     */
    public function implodeexcel($file='', $sheet=0,$fieldName=[]){
        Vendor("PHPExcel.PHPExcel");
        $file = iconv("UTF-8", "GB2312", $file);   //转码
        if(empty($file) OR !file_exists($file)) {
            die('file not exists!');
        }
        $objReader = \PHPExcel_IOFactory::createReaderForFile($file);
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        $obj = $objReader->load($file);  //建立excel对象
        $currSheet = $obj->getSheet($sheet);   //获取指定的sheet表

        $columnH = $currSheet->getHighestColumn();   //取得最大的列号
        $columnCnt = array_search($columnH, $cellName);
        $rowCnt = $currSheet->getHighestRow();   //获取总行数
        $data = array();
        for($_row=1; $_row<=$rowCnt; $_row++){  //读取内容
            for($_column=0; $_column<=$columnCnt; $_column++){
                $cellId = $cellName[$_column].$_row;
                $cellValue = $currSheet->getCell($cellId)->getValue();
                //$cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
                if($cellValue instanceof \PHPExcel_RichText){   //富文本转换字符串
                    $cellValue = $cellValue->__toString();
                }
                if($fieldName){
                    $data[$_row][$fieldName[$_column]] = $cellValue;
                }else{
                    $data[$_row][$cellName[$_column]] = $cellValue;
                }

            }
        }

        return $data;

    }
    //清空文件夹函数和清空文件夹后删除空文件夹函数的处理
    function deldir($path){
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                //排除目录中的.和..
                if($val !="." && $val !=".."){
                    //如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        //子目录中操作删除文件夹和文件
                        $this->deldir($path.$val.'/');
                        //目录清空后删除空文件夹
                        @rmdir($path.$val.'/');
                    }else{
                        //如果是文件直接删除
                        unlink($path.$val);
                    }
                }
            }
        }
    }
}