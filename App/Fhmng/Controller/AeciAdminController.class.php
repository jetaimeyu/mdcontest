<?php
/**日志
 * 模块：首页
 * 创建人:
 * 创建时间：
 * 模块权限code:
 * 描述：首页
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class AeciAdminController extends BaseController {
    /**
     * 首页
     * */
    public function aeci(){
        //取得系统列表
        $menus = A('Fhmng/Function')->getMenu(0);
        $uid = session('session_user')['id'];
        $this->assign("menus",$menus);
        $this->display('/Index/index');
    }
    /**
     * 欢迎页
     * */
    public function welcome(){
        $this->display('/Index/welcome');
    }
    
    /**
     * 菜单
     * 
     * */
    public function menus(){
        //系统菜单id，即菜单pid为0的菜单
        $systemid = I("systemid");
        //取得权限内所有的功能菜单
        $allMenus = A('Fhmng/Function')->getMenu();
        
        //获取过滤属于该系统的功能菜单
        $childMenus = $this->getChildenMenu($allMenus,$systemid);
        #$var_dump($childMenus);
        $this->assign("systemid",$systemid);
        $this->assign("childMenus",$childMenus);
        $this->display('/Index/menus');
    }
    
    /**
     *  递归取得子菜单
     * @param $allMenus 数组 所有菜单集合
     * @param $pid int 父id
     * */
    private function getChildenMenu($allMenus,$pid="-1"){
        $returnArray = array();
        foreach ($allMenus as $key=>$value){
            if($value["pid"]==$pid){
                array_push($returnArray,$value);
                $childMenus = $this->getChildenMenu($allMenus,$value["id"]);
               $returnArray=array_merge($returnArray,$childMenus);
            }
        }
        return $returnArray;
    }
}