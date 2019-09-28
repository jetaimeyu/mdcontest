<?php
/**
 * Created by PhpStorm.
 * User: "李明冬"
 * Date: 2018/8/6
 * Time: 15:16
 */

namespace Home\Controller;
use Home\Model\UserModel;
use Think\Verify;


use Home\Common\Controller\BaseController;

class RegisterController extends BaseController
{
    public function index()
    {
        $this->display();
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
    public function ajaxSubmit()
    {
        $data = I("post.");
        $res =  $this->submitBackstageVail($data['telephone'],$data['Phonevali']);
        if($res !== true){
            $this->ajaxReturn($res);
            return;
        }
        unset ($data['Phonevali']);
        $submit = new UserModel();
        $backdata =  $submit->addUserData($data);
        $type =  checkPhoneToMD($data['telephone']);
        if($backdata){
            $msg = ['State'=>1,"Msg"=>"注册成功","Data"=>1];
            if($type['State'] == 2){
                //新用户注册成功
                $msg = ['State'=>2,"Msg"=>"注册成功，该账号也可用于登陆今日制造，密码默认为该手机号","Data"=>1];
            }elseif($type['State'] == 1){
                //迈迪设计宝已有账号
                $msg = ['State'=>1,"Msg"=>"注册成功","Data"=>1];
            }else{
                //迈迪设计宝注册失败

            }
        }else{
            $msg = ['State'=>0,"Msg"=>"注册失败","Data"=>0];
        }
        $this->ajaxReturn($msg);

    }
    public function valiTel($tel)
    {
        $telData = m("user")->where("telephone = '{$tel}'")->find();
        if($telData){
            $msg = ['State'=>2,"Msg"=>"该手机号已经注册","Data"=>0];
        }else{
            $msg = ['State'=>1,"Msg"=>"该手机号可以注册","Data"=>1];
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
   public function submitBackstageVail($tel,$val)
   {
       $telData = m("user")->where("telephone = '{$tel}'")->find();
       if($telData){
           $msg =['State'=>0,"Data"=>"该手机号已经被注册","Type"=>1];
           return $msg;
       }else{
           $res = checkAndUserVerify($tel,$val);
           if($res['State'] != 1){
               $resData = ['State'=>0,"Data"=>"请输入正确的验证码","Type"=>5];
               return $resData;
           }
       }
       return true;
   }
}
