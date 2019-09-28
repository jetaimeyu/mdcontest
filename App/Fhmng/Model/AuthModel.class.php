<?php
namespace Fhmng\Model;

use Think\Model\RelationModel;
class AuthModel extends RelationModel{
    protected $_link = array(
        "model_list"=>array(
            "mapping_type"=>self::HAS_MANY,
            'class_name'    => 'form_model',
            "parent_key"=>"value",
            "foreign_key"=>'id',
            'mapping_fields'=>'id,name',
        ),

        // "Dep"=>array(
        //     "mapping_type"=>self::BELONGS_TO,
        //     "foreign_key"=>"dep_id",
        //     "mapping_fields"=>"dep_name",
        //     "as_fields"=>"dep_name:dep_name"
        // )
    );
}