<?php
/**日志
 * 模块：登录日志管理
 * 创建人:李宝振
 * 创建时间：2016-08-12
 * 模块权限code:AUTH_LOGINLOG
 * 描述：
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class UserLogController extends BaseController{
	/**
	 * 意见领袖馆登录
	 */
	public function exprtLogPage(){
		$this->display();
	}
	/**
	 * 取得意见领袖登录日志列表
	 * */
	public function getexprtLogList(){
		//登录名
		$login_name = I("login_name");
		//登录开始时间
		$startLoginTime = I("startLoginTime");
		//登录结束时间
		$endLoginTime = I("endLoginTime");
		 
		if($login_name){
			$where["login_name"]=array("LIKE","%".$login_name."%");
		}
	
		$timeCondition=array();
		if($startLoginTime){
			array_push($timeCondition, array("EGT",$startLoginTime));
		}
		if($endLoginTime){
			array_push($timeCondition, array("ELT",$endLoginTime));
		}
	
		if($timeCondition){
			$where["login_time"] = $timeCondition;
		}
		$where["type"] = 2;
		//页码
		$page = I("page");
		//每页显示的条数
		$rows = I("rows");
	
		//$loginLogViewModel = D("LoginLogView");
		$loginLogModel= M("user_log");
		$total = $loginLogModel->where($where)->count();
		$res = $loginLogModel->order("login_time desc")->where($where)->page($page,$rows)->select();
		$result["total"] = $total;
		$result["rows"] = $res;
	
		$this->ajaxReturn($result);
	}
    /**
     * 论坛登录日志页面
     * */
    public function loginLogPage(){
        $this->display();
    }
    
    /**
     * 取得登录日志列表
     * */
    public function getLogList(){
        //登录名
        $login_name = I("login_name");
        //登录开始时间
        $startLoginTime = I("startLoginTime");
        //登录结束时间
        $endLoginTime = I("endLoginTime");
       
        if($login_name){
            $where["login_name"]=array("LIKE","%".$login_name."%");
        }
        
        $timeCondition=array();
        if($startLoginTime){
            array_push($timeCondition, array("EGT",$startLoginTime));
        }
        if($endLoginTime){
            array_push($timeCondition, array("ELT",$endLoginTime));
        }
        
        if($timeCondition){
            $where["login_time"] = $timeCondition;
        }
        $where["type"] = 1;
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
        
        //$loginLogViewModel = D("LoginLogView");
        $loginLogModel= M("user_log");
        $total = $loginLogModel->where($where)->count();
        $res = $loginLogModel->order("login_time desc")->where($where)->page($page,$rows)->select();
        $result["total"] = $total;
        $result["rows"] = $res;
        
        $this->ajaxReturn($result);
    }
}