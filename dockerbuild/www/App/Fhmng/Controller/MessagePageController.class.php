<?php
namespace  Fhmng\Controller;
use Think\Controller;
class MessagePageController extends Controller{
    /**
     * 没有权限错误提示页面
     * */
    public function no_auth(){
        $this->display();
    }
    
    /**
     * 404页面
     * */
    public function Page_404(){
        $this->display();
    }
    
    public function session_error(){
        $this->display();
    }
}