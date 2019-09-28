<?php
return array(
	//'配置项'=>'配置值'
    'MODULE_ALLOW_LIST'    => array('Home','Fhmng','Mobile'),
	'DEFAULT_MODULE'=>'Home',
    //数据库配置

    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '47.93.198.167', // 服务器地址
    'DB_NAME'   => 'hybm', // 数据库名
    'DB_USER'   => 'yuyuyu', // 用户名
    'DB_PWD'    => '123123', // 密码
    'ERROR_PAGE' =>'/Home/Error/index', //错误提示页
    'DB_PORT'   => 3306, // 端口
    'DB_PREFIX' => '', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集
    'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
    //开启Trace调试
    'SHOW_PAGE_TRACE'=>FALSE,
    //URL模式：0：普通模式，1：PATHINFO模式（默认）,2:REWRITE模式（以去掉URL地址里面的入口文件index.php）
    //3:兼容模式
	'URL_CASE_INSENSITIVE' => true,
    'URL_MODEL' =>2,
	'UPLOAD_PATH'=>'./Uploads/',//上传文件保存目录
	//开启伪静态
	'URL_ROUTER_ON'   => true,
	'URL_ROUTE_RULES'=>array(
        'index$'=>'Home/Index/index',
        'game$'=>'Home/Game/index',
        'aboutus$'=>'Home/Aboutus/index',
        'download$'=>'Home/Download/index',
        'expert$'=>'Home/Expert/index',
        'login$'=>'Home/Login/index',
        'personinfo$'=>'Home/Personinfo/index',
        'register$'=>'Home/Register/index',
        'subject$'=>'Home/Subject/index',
        'works$'=>'Home/Works/index',
	    'checkPictureVerify' =>  'Mobile/Index/check_verify',
	    'staticVerifyCode' =>  'Mobile/Index/verify',
	    'sendVerify' => 'Mobile/Index/sendVerify',
	    'isPhoneRegister' => 'Mobile/Index/isPhoneRegister',
        'addressSelect' =>'Mobile/Index/addressSelect',
        'getSubmit' => 'Mobile/Index/getSubmit',
        'submitsuccess' => 'Mobile/Index/submitSuccess',
        'checkPhoneVerify' => 'Mobile/Index/checkPhoneVerify',
        'submit' => 'Mobile/Index/submit',
        'detail' => 'Mobile/Index/detail',
        'mobile' => 'Mobile/Index/index',
        'aeci' => 'Fhmng/AeciAdmin/aeci',
        'p1'=>'Fhmng/Aeciverify/doverify',
        'verify'=>'Fhmng/Aeciverify/checkVerifyCode',
        'verifycode'=>'Fhmng/Aeciverify/verifyCode',
	),
    'LOG_RECORD' => true, // 开启日志记录
    'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR', // 只记录EMERG ALERT CRIT ERR 错误 check_verify
  //post 或者 get 保存数据去除空格
    'DEFAULT_FILTER'        => 'trim,htmlspecialchars',
    'DB_PARAMS'    =>    array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),
    //缴费
    'SERVER'=>$_SERVER['HTTP_HOST'],//获取域名


    'SERVER'=>$_SERVER ['HTTP_HOST'],//获取域名

	'__ROOT__' => __ROOT__, //  当前网站地址
	'__APP__' => __APP__, //  当前应用地址
	'__MODULE__' => __MODULE__,
	'__ACTION__' => __ACTION__, //  当前操作地址
	'__SELF__' => __SELF__, //  当前页面地址
	'__CONTROLLER__'=> __CONTROLLER__,
	'__URL__' => __CONTROLLER__,
	'__PUBLIC__' => __ROOT__.'/Public',//  站点公共目录
	//'__UPLOADS__' => __ROOT__.'/Uploads',//  站点公共上传目录
    'TMPL_PARSE_STRING'  =>array(
        '__UPLOADS__' => __ROOT__.'/Uploads', // 增加新的上传路径替换规则
        '__LOCALPUBLIC__'=>__ROOT__.'/Public'
    ),
     'SESSION_EXPIRE'=>'7200',
        'SESSION_OPTIONS'=> array(
            'expire'=>'7200'
        ),
   'MD_API1' => 'http://58.56.29.195:8031',
   'MD_API45' => 'http://58.56.29.195:8045',
    // 'MD_API1' => 'http://api.maidiyun.com',
    // 'MD_API45' => 'http://api45.maidiyun.com',
    'VerifyOvertime' => 600,  //验证码有效时长，单位 秒
    'VerifyLimit' => 10,  //验证码每天发送数量
    // 'SESSION_TYPE'=>'Memcache',
    // 'MEMCACHE_HOST'=>'172.16.231.131',
    // 'MEMCACHE_PORT'=>'11211',
    // 'SESSION_TIMEOUT'=>1,
    // 'DATA_CACHE_TYPE' => 'Memcache',
    // 'MEMCACHE_HOST'  => 'tcp://172.16.231.131:11211',
    // 'DATA_CACHE_TIME' => '7200',
);
