<?php
/**日志
 * 模块：系统配置
 * 创建人:李宝振
 * 创建时间：2016-08-31
 * 模块权限code:AUTH_SYSCONFIG
 * 描述：
 *
 * 修改人：
 * 修改时间：
 * 修改描述：
 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class SysConfigController extends BaseController{
    
    /**
     * 系统配置首页
     * */
    public function sysConfigIndex(){
        //检查权限
        $this->checkPageAuth("AUTH_SYSCONFIG");
        $this->display();
    }
    
    /**
     * 根据配合参数获取配置
     * */
    public function getSysConfigValue($configCode){
        $sysConfigModel = M("SysConfig");
        $where["config_code"] = $configCode;
        $config = $sysConfigModel->where($where)->select();
        
        return $config;
    }
    
    /**
     * 获取系统配置
     * */
    public function getConfigData(){
        $sysConfigModel = M("SysConfig");
        $sysConfig = $sysConfigModel->where($where)->order("group_order desc,order_no asc")->select();
        $returnArray=array();
        foreach ($sysConfig as $key=>$value){
            $item["id"]=$value["id"];
            $item["name"]=$value["config_name"];
            $item["value"]=$value["config_value"];
            if($value['control_type'] == 'combobox'){
                $item["value"]=$this->getComboboxValue($value["config_value"])['dic_value'];
            }
            $item["config_code"]=$value["config_code"];
            $item["description"]=$value["description"];
            $item["group"]=$value["group"];
            $item["width"]=100;
            $item["editor"]=$this->_getControl($value["control_type"],$value["config_code"]);
            array_push($returnArray, $item);
            unset($item);
        }
        $this->ajaxReturn($returnArray);
    }
    
    /**
     * 获取控件
     * @param $controlType控件类型,例如：textbox,combobox
     * @param $configCode 参数编码
     * */
    private function _getControl($controlType,$configCode){
        $control["type"]=strtolower($controlType);
        if(strtolower($controlType)=="textbox"){
            
        }else if(strtolower($controlType)=="combobox"){
            $url = 
            $control["options"]= array(
                "valueField"=>"dic_key",
                "textField"=>"dic_value",
                "panelHeight"=>'auto',
                "editable"=>false,
                "url"=>U("Fhmng/SysConfig/getComboboxStore"),
                "queryParams"=>array(
                    "configCode"=>$configCode
                )
            );
        }
        return $control;
    }
    
    /**
     * 获取combobox的下拉值
     * */
    public function getComboboxStore(){
        //配置参数编码，如果要取得字典表里的数据，则设置此编码与字典类型编码一致即可
        $configCode = I("configCode");
        $dics = A("Fhmng/Dictionary")->getDicsByTypeCode($configCode);
        
        $this->ajaxReturn($dics);
    }
    public function getComboboxValue($dic_key){
        $value = A("Fhmng/Dictionary")->getDictByKey($dic_key);
        return $value;
    }
    /**
     * 保存数据
     * */
    public function saveConfigData(){
        S('version_no',null);
        $result["success"] = false;
        $result["message"] = "";
        if(IS_POST){
            $changes = $_POST['changes'];
            $changeArray = json_decode($changes);
            $sysConfigModel = M("SysConfig");
            foreach ($changeArray as $key=>$value){
                $data["id"] = $value->id;
                $data["config_value"] = $value->value;
                if($value->config_code == 'version_no'){
                    $sysConfigModel->where(['config_code'=>'version_no'])->save(['config_value'=>$value->value]);
                }
                $sysConfigModel->save($data);
            }
            $result["success"] = true;
            $result["message"] = "保存成功";
            
        }else{
            $result["success"] = false;
            $result["message"] = "非法操作";
        }
        $this->ajaxReturn($result);
    }
}