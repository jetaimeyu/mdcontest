<?php
/**日志
 * 描述：用户登录视图
 * 创建人:李宝振
 * 创建时间：2016-08-12
 * */
namespace Fhmng\Model;

use Think\Model\ViewModel;
class LoginLogViewModel extends ViewModel{
    public $viewFields = array(
        "LoginLog"=>array("id","uid","login_ip","login_time","_type"=>"LEFT"),
        "User"=>array("login_name","true_name","_on"=>"LoginLog.uid=User.id")
    );
}