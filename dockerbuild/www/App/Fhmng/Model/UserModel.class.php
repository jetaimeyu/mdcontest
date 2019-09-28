<?php
namespace Fhmng\Model;

use Think\Model\RelationModel;
class UserModel extends RelationModel{
    protected $_link = array(
        //  "auth_list"=>array(
        //     "mapping_type"=>self::MANY_TO_MANY,
        //     "relation_table"=>"user_meeting",
        //     'class_name'    => 'enter_auth',
        //     "foreign_key"=>"admin_id",
        //     'condition'=>"status = 1",
        //     "relation_foreign_key"=>"auth_id",
        //     'mapping_fields'=>'id,auth_name,meeting_id,is_edit,is_view',
        // ),
       
        "role_list"=>array(
            "mapping_type"=>self::MANY_TO_MANY,
            "relation_table"=>"role_user",
            'class_name'    => 'role',
            'condition'=>"status = 1",
            "foreign_key"=>"uid",
            "relation_foreign_key"=>"rid",
            'mapping_fields'=>'id,role_name',
        ),
        "Dep"=>array(
            "mapping_type"=>self::BELONGS_TO,
            "foreign_key"=>"did",
            "mapping_fields"=>"dep_name",
            "as_fields"=>"dep_name:dep_name"
        )
    );
}