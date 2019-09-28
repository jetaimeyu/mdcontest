<?php
/*404错误页*/
namespace Home\Controller;
use Home\Common\Controller\BaseController;

class ErrorController extends BaseController {
    public function index()
    {
       header('HTTP/1.1 404 Not Found'); 

        $detect = new \Org\Net\Mobile_Detect();
        if($detect->isMobile()){
            $this->display('Mobile@Public:error');
        }else{
            $this->display('Public/error');
        }
    }
  
}