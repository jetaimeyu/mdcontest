<?php
/**日志
 * 描述：角色用户视图
 * 创建人:李宝振
 * 创建时间：2016-08-09
 * */
namespace Fhmng\Model;
use Think\Model\ViewModel;
class RoleUserViewModel extends ViewModel{
    public $viewFields = array(
        "RoleUser"=>array("uid","rid","_type"=>"LEFT"),
        "User"=>array("login_name","true_name","telephone","_on"=>"RoleUser.uid=User.id and User.status = 1","_type"=>"LEFT"),
        "Role"=>array("role_name","status"=>"role_status","function_str","_on"=>"RoleUser.rid=Role.id")
    );
}