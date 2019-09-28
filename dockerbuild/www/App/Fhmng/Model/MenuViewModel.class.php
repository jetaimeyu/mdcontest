<?php
/**日志
 * 描述：前台网站菜单视图
 * 创建人:李宝振
 * 创建时间：2016-09-07
 * */
namespace Fhmng\Model;
use Think\Model\ViewModel;
class MenuViewModel extends ViewModel{
    public $viewFields = array(
        "Menu"=>array(
            "*",
            "_type"=>"LEFT"
        ),
        "Dictionary"=>array(
            "dic_value"=>"menu_category_name",
            "_on"=>"Menu.menu_category=Dictionary.dic_key",
            "_type"=>"LEFT"
        ),
        "Model"=>array(
            "model_name"=>"model_id_name",
            "_on"=>"Menu.model_id=Model.id"
        )
    );
}