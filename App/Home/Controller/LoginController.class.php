<?php
/**
 * Created by PhpStorm.
 * User: "李明冬"
 * Date: 2018/8/6
 * Time: 15:46
 */

namespace Home\Controller;


use Home\Common\Controller\BaseController;
use Home\Model\UserModel;
use Think\Verify;

class LoginController extends BaseController
{
    public function index()
    {
        if(session('Match_User_Id')){
        $this->redirect("/personinfo");
        return;
        }
        $this -> display();
    }
    public function loginVer()
    {
        $data = I("post.");
        $getType = new UserModel();
        $getLoginType =  $getType->getUserData($data);
        if($getLoginType){
            $msg = ['State'=>1,"Msg"=>"登陆成功","Data"=>1];

        }else{
            $msg = ['State'=>1,"Msg"=>"登陆失败","Data"=>0];
        }
        $this->ajaxReturn($msg);

    }
    //获取验证妈
    public function verify()
    {
        $config = array(
            "expire"=>30,
            "length" =>4,
            'imageW'=>210,
            'imageH'=>60,
            'useImgBg'=>false,
            'fontttf' => '4.ttf',
            'fontSize'=>30,
            'useCurve'=>true,
            'useNoise'=>false
        );
        $Verify = new Verify($config);
        $Verify->entry();
    }
    //验证验证码
    public function check_verify($code,$id = ""){
        $verify = new Verify();
        $res = $verify->check($code,$id);
        $this->ajaxReturn($res,'json');
    }
    public function signout()
    {
        session('Match_User_Id',null);
        $this->redirect("/");
    }

}