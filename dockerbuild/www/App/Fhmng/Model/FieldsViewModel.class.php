<?php
/**日志
 * 描述：模型字段视图
 * 创建人:李宝振
 * 创建时间：2016-09-07
 * */
namespace Fhmng\Model;
use Think\Model\ViewModel;
class FieldsViewModel extends ViewModel{
    public $viewFields = array(
        "Fields"=>array("id","model_id","field_name","field_alias","field_type",
                        "order_no","status","create_time","description","_type"=>"LEFT"),
        "Dictionary"=>array("dic_value"=>"field_type_name","_on"=>"Fields.field_type=Dictionary.dic_key")
    );
}