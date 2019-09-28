<?php
/**日志
 * 模块：登录模块
 * 创建人:李宝振
 * 创建时间：2016-07-11
 * 模块权限code:
 * 描述：登录
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Think\Controller;
class AeciverifyController extends Controller{
    //验证码配置
    public  $config = array(
            'fontSize'=>80,// 验证码字体大小    
            'length'=>4,// 验证码位数    
            'useNoise'=>FALSE, // 关闭验证码杂点
            'useImgBg '=>TRUE,
    		'useCurve'=>FALSE,//是否使用混淆曲线 默认为true
            'reset'=>false,//验证成功后是否重置，即验证成功后清空sessioin，如果为true，则一旦验证成功，第二次验证的话就会验证不通过
     );
    
    /**
     * 登录页面
     * */
    public function wersdf(){
        $this->display('Login/index');
    }
    
    /**
     * 验证码
     * */
    public function verifyCode(){
        $Verify = new \Think\Verify($this->config);
        $Verify->fontttf = '4.ttf';
        // 设置验证码字符为纯数字
        $Verify->codeSet = '0123456789';
        $Verify->entry();
    }
    
    /**
     *前台验证验证码
     * */
    public function checkVerifyCode(){
        $result["success"] = false;
        $code = I("code");
        if($this->check_verify($code)){
            $result["success"] = true;
        }

        //临时
        $result["success"] = true;
        $this->ajaxReturn($result);
    }
 
    /**
     * 验证验证码
     * */
    public function check_verify($code){
        $verify = new \Think\Verify($this->config);
        return $verify->check($code);
        // return true; //临时
    }
    
    /**
     * @access 登录
     * */
    public function doverify(){
        //判断是否是POST提交数据
        if (IS_POST) {
            $login_name = trim(I("un"));
            $pwd = trim(I("sec"));
            $verifyCode = I("verifyCode");
            if($this->check_verify($verifyCode)){
                $userModel = M("User");
                $where["login_name"] = $login_name;
                $where["is_user"] = 1;
                $where["pwd"] = md5($pwd);
                //状态：0:禁用，1：启用 , 3删除
                $where["status"] = ['in','0,1'];
                $user  = $userModel->where($where)->find();
                if($user){
                    if($user["status"] != 1){
                        $return["success"]=false;
                        $return["message"]="该账号已锁定，请联系后台管理员";
                    }else{
                        session_regenerate_id(true);
                        //存储用户session
                        session("session_user",$user);
                        //登录时间
                        
                        session("session_login_time",time());
                        $return["success"]=true;
                        $return["message"]="登录成功";
                        //添加登录日志
                        $loginLogModel = M("LoginLog");
                        $data["uid"] = $user["id"];
                        $data["login_name"]=$user["login_name"];
                        $data["true_name"]=$user["true_name"];
                        $data["login_ip"]= get_real_client_ip();
                        $data["login_time"] = date("Y-m-d H:i:s");
                        $loginLogModel->add($data);
                        
                        //取得系统会话时间
                        $sessionTimeOut = getSysConfigValue("session_time_out");
                        if($sessionTimeOut){
                            //session过期时间，单位是分钟
                            $sessionTimeOut = $sessionTimeOut*60;
                            session("session_time_out",$sessionTimeOut);
                        }else{
                            //没有设置默认为30分钟
                            $sessionTimeOut = 30*60;
                            session("session_time_out",$sessionTimeOut);
                        }
                        
                    }
                }else{
                    $return["success"]=false;
                    $return["message"]="用户名或者密码错误";
                }
            }else{
                $return["success"]=false;
                $return["message"]="验证码错误";
            }
            
            
            $this->ajaxReturn($return);
        }
    }
    
    /**
     * 根据配合参数获取配置
     * @param $configCode 配置参数
     * */
    private function _getSysConfigValue($configCode){
        $sysConfigModel = M("SysConfig");
        $where["config_code"] = $configCode;
        $config = $sysConfigModel->where($where)->select();
    
        return $config;
    }
    
    

    /**
     * 清空运行缓存
     * @return boolean
     */
    function clear(){
        $ret=clear_runtime();
        if($ret)
            $this -> ajaxReturn(array('success'=>1,'message'=>'清除成功！'));
        else
            $this -> ajaxReturn(array('success'=>0,'message'=>'清除失败！'));
    }
    
}