<?php
/**
 * Created by PhpStorm.
 * User: "李明冬"
 * Date: 2018/8/6
 * Time: 16:07
 */

namespace Home\Controller;


use Home\Common\Controller\BaseController;
use Home\Model\UserModel;
use Think\Verify;

class RqupasswordController extends BaseController
{
    public function index()
    {
        $this -> display();
    }
    public function check_verify($code,$id = ""){
        $verify = new Verify();
        $res = $verify->check($code,$id);
        $this->ajaxReturn($res,'json');
     }
    public function verify()
    {
        $config = array(
            "expire"=>300,
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
    public function setUserPwd()
    {
        $data = I("post.");
        $setPwd = new UserModel();
        $backdata =  $setPwd->setUserPwd($data);
        if($backdata) {
            $msg = ['State' => 1, "Msg" => "修改成功", "Data" => 1];
        }else{
            $msg = ['State' => 1, "Msg" => "修改失败", "Data" => 0];
        }
        $this->ajaxReturn($msg);
    }

//    验证手机号是否是注册的
    public function valiTel($tel)
    {
        $telData = m("user")->where("telephone = '{$tel}'")->find();

        if($telData){
            $msg = ['State'=>1,"Msg"=>"该手机号已经注册","Data"=>1];
        }else{
            $msg = ['State'=>1,"Msg"=>"该手机号还未注册，请注册!","Data"=>0];
        }
        $this->ajaxReturn($msg);
    }
    public function getPhonevali($tel){
        $res =  sendVerify($tel);
        $this->ajaxReturn($res);
    }
    public function checkPhonevali($tel,$val)
    {
        $res = checkAndUserVerify($tel,$val);
        $this->ajaxReturn($res);
    }
}