<?php
/**
 * 获取客户端ip
 */
function get_real_client_ip($type = 0)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($_SERVER['HTTP_X_REAL_IP']) {//nginx 代理模式下，获取客户端真实IP
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if ($ip == "::1") {
        $ip = "127.0.0.1";
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}


/**
 *生成订单号
 *lj
 */
function getOrdercode()
{
    // $mid = session('session_user')['current_mid'];
    $obj = M('serial_number');
    $date = date('Ymd');
    M()->startTrans();
    $today = $obj->lock(true)->where(['time' => $date])->find();
    if ($today) {
        $number = $today['number'] + 1;
        $obj->where(['time' => $date])->save(['number' => $number]);
    } else {
        $number = 1;
        $data['number'] = 1;
        $data['time'] = $date;
        $obj->add($data);
    }
    M()->commit();
    $ordercode = trim(date('Ymd') . $number);
    $len = 13 - strlen($ordercode);
    if ($len > 0) {
        $exp = str_repeat('0', $len);
        $ordercode = trim(date('Ymd') . $exp . $number);
    }
    return $ordercode;
}


/**
 * xml反序列化
 */
function xml_decode($xmlstr)
{
    $obj = simplexml_load_string($xmlstr);
    $json = json_encode($obj, true);
    $arr = json_decode($json, true);
    return $arr;
}

/*生成短链接*/
function makeshorturl($url)
{
    //u6.gg短网址
    return file_get_contents('http://u6.gg/api.php?url=' . $url);
    // var_dump($return);exit;
    // return file_get_contents('http://msurl.cn/short_url.text?url='.$url);
    //百度短网址
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://dwz.cn/create.php");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = array('url' => $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $strRes = curl_exec($ch);
    curl_close($ch);
    $arrResponse = json_decode($strRes, true);
    if ($arrResponse['status'] == 0) {
        /**错误处理*/
        echo iconv('UTF-8', 'GBK', $arrResponse['err_msg']) . "\n";
    }
    /** tinyurl */
    return $arrResponse['tinyurl'];
}

/**
 * 邮件发送函数
 */
function sendMail($to, $to_name, $title, $content)
{
    Vendor('PHPmailer.PHPMailerAutoload');
    $user = M('dictionary')->where(['dic_key' => ['in', 's_email,s_password,email_name'], 'status' => 1])->getField('dic_key,dic_value');
    $user_email_name = $user['email_name'];
    $password = $user['s_password'];
    $user_email = $user['s_email'];
    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host = 'smtp.163.com'; //smtp服务器的名称
    $mail->SMTPAuth = TRUE; //启用smtp认证
    $mail->Mailer = "SMTP";
    $mail->Port = "25"; //smtp服务器端口号为25
    $mail->Username = $user_email; //你的邮箱名
    $mail->Password = $password; //邮箱密码要填写授权码，而不是密码
    $mail->From = $user_email; //发件人地址（也就是你的邮箱地址）
    $mail->FromName = $user_email_name; //发件人姓名
    $mail->AddAddress($to, $to_name);
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(TRUE); // 是否HTML格式邮件
    $mail->CharSet = 'utf-8'; //设置邮件编码
    $mail->Subject = $title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
    //保存发送日志
    $messageObj = M('message_log');
    $data['phone'] = $to;
    $data['msg'] = $title;
    $data['send_time'] = date('Y-m-d H:i:s');
    $messageObj->add($data);
    $res = $mail->Send();
    return ($res);
}

/**
 * 模拟请求
 * @param string $url 地址
 * @param string $method 请求方式 get/post
 * @param unknown $params 参数
 */
function httpRequest($url, $method, $params = array(), $https = false)
{
    if (trim($url) == '' || !in_array($method, array('get', 'post')) || !is_array($params)) {
        return false;
    }
    switch ($method) {
        case 'get':
            $str = '?';
            foreach ($params as $k => $v) {
                $str .= $k . '=' . urlencode($v) . '&';
            }
            $str = substr($str, 0, -1);
            $url .= $str;
            $result = file_get_contents($url);
            break;
        case 'post':
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            if ($https) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            }
            $result = curl_exec($curl);
            curl_close($curl);
            break;
    }
    return $result;
}


/**
 * get请求
 * @date: 2018-6-4 下午6:17:53
 * @author: zhucy
 */
function httpGetRequest($url, $headers)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $rs = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($rs, true);
    return $result;
}

/**
 * 235云通讯法师那个短信--营销
 * @param phone 电话号码 如果需要发送多个手机号码 请用英文逗号","隔开
 * @param msg 短信内容
 */
function sendMessMark($phone, $msg)
{
    //创建一个类
    $clapi = new \Org\Net\ChuanglanSmsApi();

    /**
     * 调用短信方法
     * 如果需要发送多个手机号码 请用英文逗号","隔开
     * 签名需在平台审核通过后 在短信内容前面添加
     */
    $result = $clapi->sendSMSMark($phone, $msg);

    //返回值处理返回数组
    $result = $clapi->execResult($result);
    //返回的状态码
    $statusStr = array(
        '0' => '发送成功',
        '101' => '无此用户',
        '102' => '密码错',
        '103' => '提交过快',
        '104' => '系统忙',
        '105' => '敏感短信',
        '106' => '消息长度错',
        '107' => '错误的手机号码',
        '108' => '手机号码个数错',
        '109' => '无发送额度',
        '110' => '不在发送时间内',
        '111' => '超出该账户当月发送额度限制',
        '112' => '无此产品',
        '113' => 'extno格式错',
        '115' => '自动审核驳回',
        '116' => '签名不合法，未带签名',
        '117' => 'IP地址认证错',
        '118' => '用户没有相应的发送权限',
        '119' => '用户已过期',
        '120' => '内容不是白名单',
        '121' => '必填参数。是否需要状态报告，取值true或false',
        '122' => '5分钟内相同账号提交相同消息内容过多',
        '123' => '发送类型错误(账号发送短信接口权限)',
        '124' => '白模板匹配错误',
        '125' => '驳回模板匹配错误',
        '128' => '内容解码失败'
    );
    if ($result[1] === '0') {
        return true;
    } else {
        echo $statusStr[$result[1]];
    }
}

/**
 * 根据配合参数获取配置
 * @param string $configCode 配置参数编码
 * @return 配置值
 * */
function getSysConfigValue($configCode)
{
    if ($ret) {
        return $ret;
    }
    $sysConfigModel = M("SysConfig");
    $where["config_code"] = $configCode;
    $config = $sysConfigModel->where($where)->select();
    if ($config && $config[0]["config_value"]) {
        return $config[0]["config_value"];
    }
}

/**
 * 根据type_code获取字典子项
 * @param string $typecode 字典类型编码
 * @return array 子项集合
 * *@author liuxing <lxmp@foxmail.com>
 */
function getDicValue($typeCode)
{

    $dicModel = M('dictionary');
    $where['type_code'] = $typeCode;
    $where['status'] = 1;
    $where['pid_key'] = 0;
    $config = $dicModel->cache(true)->where($where)->getField('dic_value,id');
    return $config;
}


/**
 * 根据唯一菜单编码，获取该菜单下的文章列表
 * @param $menu_code 唯一的菜单编码
 * @param $limits 条数
 * @param $fields 字段字符串，例如：id,title,content
 * @param $strorder 排序  0 是否置顶 排序号及发布时间 1 发布时间倒叙 2访问量
 * @return 文章列表
 * */
function getArticleListByMenuCode($menu_code, $limits, $fields, $strorder)
{
    $contents = null;
    $contentViewModel = D("Home/ContentView");

    if ($menu_code) {
        $where["menu_code"] = $menu_code;
    } else {
        $where["menu_category"] = 'menu_category_front';
    }
    //文章状态，0：待审核，1：审核通过
    $where["status"] = 1;
    $where["expiry_date"] = array(
        array("EXP", "IS NULL"),
        array("EGT", date("Y-m-d H:i:s")),
        "OR"
    );
    if ($strorder == '1') {
        $strorder = 'create_time desc';
    } else if ($strorder == '2') {
        $strorder = 'visits desc';
    } else {
        $strorder = 'is_top desc,order_no desc,create_time desc';
    }
    if ($limits) {
        $contents = $contentViewModel->field($fields)->where($where)->limit($limits)->order($strorder)->select();
    } else {
        $contents = $contentViewModel->field($fields)->where($where)->order($strorder)->select();
    }

    return $contents;
}

/**
 *  根据菜单分类获取下属的菜单idlist  1,2,3 格式
 * */
function getMenucodebyCategory($category)
{
    $menucodeModel = M("Menu");
    $where['menu_category'] = $category;
    $menucode = $menucodeModel->field('id,menu_code')->where($where)->select();
    $count = count($menucode, 0);
    for ($i = 0; $i < $count; $i += 1) {
        if ($i == $count - 1) {
            $codestr .= $menucode[$i]['id'];
        } else {
            $codestr .= $menucode[$i]['id'] . ',';
        }
    }
    return $codestr;
}

/**
 * 获取悬浮窗,取得状态可用排序最靠前的一个
 * */
function getFloatWindow()
{
    $floatWindowModel = M("FloatWindow");
    $where["status"] = 1;
    $floatWindow = $floatWindowModel->where($where)->limit(1)->order("order_no desc")->select();
    if ($floatWindow) {
        return $floatWindow[0];
    }
    return null;
}

/**
 * 获取文章自定义扩展字段的值
 * @param $content_id 文章id
 * @param $field_name 字段名称
 * @return 扩展值
 * */
function getContentExtendFieldValue($content_id, $field_name)
{
    $contentModel = M("Content");
    $content = $contentModel->find($content_id);
    if ($content) {
        $json = $content["extends"];
        $fieldValue = getExtendJsonValue($json, $field_name);
    }
    return $fieldValue;
}

/**
 *根据json字段名取得json串中的值
 * @param $json json串
 * @param $field_name 字段名
 * @return json值
 * */
function getExtendJsonValue($json, $field_name)
{
    if (strpos($field_name, "extend_") !== 0) {
        $field_name = "extend_" . $field_name;
    }
    $returnValue = "";
    $extendObj = json_decode($json, true);
    foreach ($extendObj as $key => $value) {
        $jsonName = $key;
        $jsonValue = $value;
        if ($jsonName == $field_name) {
            $returnValue = $jsonValue;
            break;
        }
    }
    return $returnValue;
}

/**
 * 删除目录及目录下所有文件或删除指定文件
 * @param str $path 待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
 * @return bool 返回删除状态
 * */
function removeFiles($path, $delDir = FALSE)
{
    if (is_array($path)) {
        foreach ($path as $subPath) {
            delDirAndFile($subPath, $delDir);
        }
    }
    if (is_dir($path)) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
                }
            }
            closedir($handle);
            if ($delDir) {
                return rmdir($path);
            }
        }
    } else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
    clearstatcache();
}

/**
 ** 截取中文字符串
 **/
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
    } else {
        $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
        $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
        $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
        $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    $fix = '';
    if (strlen($slice) < strlen($str)) {
        $fix = '...';
    }
    return $suffix ? $slice . $fix : $slice;
}

/**
 * 取得面包屑
 * */
function getBreadCrumb($menu_code)
{
    $menuModel = M("Menu");
    $where["menu_code"] = $menu_code;

    $menu = $menuModel->where($where)->select();
    if ($menu) {
        return $menu[0]["menu_name"];
    }
    return null;
}

function ajaxReturn($obj, $success, $message)
{
    $ret['success'] = $success;
    $ret['message'] = $message;
    $obj->ajaxReturn($ret);
}

/**
 * 文件下载
 * @param: string file_url文件连接
 * @param: string new_name 下载文件名称
 * *@author liuj-ad
 */
function download_file($file_url, $new_name = '')
{

    $file_name = basename($file_url);
    $file_type = explode('.', $file_name);
    $file_type = end($file_type);
    $file_name = trim($new_name == '') ? $file_name : urlencode($new_name);
    //中文显示问题
    $file_name = iconv("utf-8", "gb2312", $file_name);
    if (!file_exists($file_url)) {//检测文件是否存在
        echo "文件不存在！";
        die();
    }
    $file = fopen($file_url, 'r'); //打开文件
    $size = filesize($file_url);
    header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length:" . $size);
    header("Content-Disposition: attachment; filename*=utf-8'zh_cn'" . $file_name);
    readfile($file_url);


}

/**
 * 打包zip下载
 * @param 文件名称 $file_name string
 * @param 需要打包文件数组 $files array('url'=>'文件地址','name'=>'文件名字')
 */
function export_zip($file_name = '', $files = [])
{
    $zip = new \ZipArchive;
    //压缩文件名
    $filename = $file_name ? $file_name . date("YmdHi", time()) . '.zip' : 'download' . date("YmdHi", time()) . '.zip';
    $dir = './export';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    $saveurl = $dir . '/' . $filename;

    //新建zip压缩包
    $res = $zip->open($saveurl, \ZipArchive::CREATE);
    foreach ($files as $key => $value) {
        $zip->addFile($value['url'], $value['name']);
    }
    //打包zip
    $zip->close();
    $saveurl = iconv('utf-8', 'gb2312', $saveurl);
    // header('Content-Type:text/html;charset=gb2312');
    header('Content-disposition:attachment;filename=' . basename($filename));
    header('Content-length:' . filesize($saveurl));
    readfile($saveurl);
    unlink($saveurl);
}

/**
 * 导出excel
 * @param string $title
 * @param array $data
 * @param string $fileName
 * @param string $savePath
 * @param boolean $isDown
 * @author 刘佳
 */
function exportExcel($title = array(), $data = array(), $fileName = '', $isDown = false, $savePath = './')
{
    vendor('PHPExcel.PHPExcel');
    $obj = new \PHPExcel();
    //横向单元格标识
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

    $obj->getActiveSheet(0)->setTitle('sheet1');   //设置sheet名称
    $_row = 1;   //设置纵向单元格标识
    if ($title) {
        $_cnt = count($title);
        // $obj->getActiveSheet(0)->mergeCells('A'.$_row.':'.$cellName[$_cnt-1].$_row);   //合并单元格
        // $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, '数据导出：'.date('Y-m-d H:i:s'));  //设置合并后的单元格内容
        // $_row++;
        $i = 0;
        foreach ($title AS $v) {   //设置列标题
            $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $_row, $v);
            $i++;
        }
        $_row++;
    }
    //填写数据
    if ($data) {
        $i = 0;
        foreach ($data AS $_v) {
            $j = 0;
            foreach ($_v AS $_cell) {
                $obj->getActiveSheet(0)->setCellValueExplicit($cellName[$j] . ($i + $_row), $_cell, \PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
            $i++;
        }
    }

    //文件名处理
    if (!$fileName) {
        $fileName = date("YmdHi");
    } else {
        $fileName .= date("YmdHi");
    }

    $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
    if ($isDown) {   //网页下载
        header('pragma:public');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWrite->save('php://output');
        exit;
    }
    $_savePath = $savePath . '/' . $fileName . '.xlsx';
    // $_savePath = iconv("utf-8", "gb2312", $_savePath);   //转码
    $objWrite->save($_savePath);

    return $savePath;
}

/**
 * csv导出
 * @param unknown $file_name
 * @param unknown $head_arr
 * @param unknown $data_arr
 */
function export_csv($file_name, $head_arr, $data)
{
    $date = date("YmdHi");
    $file_name .= "{$date}.csv";
    $file_name = iconv("utf-8", "gb2312", $file_name);
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Pragma: public");
    header("Expires: 0");
    header("Content-Disposition: attachment;filename=\"$file_name\"");
    header('Cache-Control: max-age=0');
    $fp = fopen('php://output', 'a');
    fwrite($fp, chr(0XEF) . chr(0xBB) . chr(0XBF));
    foreach ($head_arr as $i => $v) {
// 		$head[$i] = $v;							//服务器上使用这一句
        //$v = "\t{$v}";
        $head[$i] = $v;//本机使用这一句
    }
    fputcsv($fp, $head);
    foreach ($data as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
    exit;
}

function rmdirr($dirname)
{
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }
    $dir->close();
    return rmdir($dirname);
}


/**
 * 上传图片本地
 * @param string $fileinput 上传文件input name名称
 * @param unknown $filename 文件名
 * @author 刘佳  2018-08-02
 */
function upload_img($fileinput, $savepath = null)
{
    //上传设置
    $savepath = $savepath ? './Uploads/' . $savepath . '/' : './Uploads/';
    $uploadConfig['maxSize'] = 31457280;
    $uploadConfig['exts'] = array('jpg', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'jpeg', 'JPEG');
    $uploadConfig['rootPath'] = './';
    $uploadConfig['savePath'] = $savepath;
    $upload = new \Think\Upload ($uploadConfig);
    //上传
    $info = $upload->uploadOne($_FILES[$fileinput]);
    if (!$info) {
        $result = array('status' => 0, 'info' => $upload->getError());
    } else {
        $result['status'] = 1;
        $result['file']['old'] = str_replace('./Uploads', '', $info['savepath'] . $info['savename']);
        $result['file']['name'] = $info['name'];
    }
    return $result;
}

/**
 * 上传文件本地
 * @param string $fileinput 上传文件input name名称
 * @param unknown $filename 文件名
 * @author 刘佳  2018-08-02
 */
function upload_file($fileinput, $savepath = null)
{
    //上传设置
    $savepath = $savepath ? './Uploads/' . $savepath . '/' : './Uploads/';
    $uploadConfig['maxSize'] = 52418800;
    // $uploadConfig['exts']=array('pdf','doc', 'docx');
    $uploadConfig['rootPath'] = './';
    $uploadConfig['savePath'] = $savepath;
    $upload = new \Think\Upload ($uploadConfig);
    //上传
    $info = $upload->uploadOne($_FILES[$fileinput]);
    if (!$info) {
        $result = array('status' => 0, 'info' => $upload->getError());
    } else {
        $result['status'] = 1;
        $result['file']['old'] = str_replace('./Uploads', '', $info['savepath'] . $info['savename']);
        $result['file']['name'] = $info['name'];
    }
    return $result;
}

/**
 * 删除本地图片
 * @author 刘佳 2018-08-02
 */
function removeImg($path)
{
    if (file_exists('.' . $path)) {
        $res = unlink('.' . $path);
    } else {
        $res = true;
    }
    if ($res) {
        return true;
    } else {
        return false;
    }
}

/**
 * 上传图片七牛
 * @param string $fileinput 上传文件input name名称
 * @param unknown $filename 文件名
 * @param unknown $size 剪裁图大小（数组'width','height'）
 * @param unknown $thumb 缩略图大小（数组'width','height'）
 * @return multitype:number NULL 路径(数组'old'=>原图,'cut'=>'剪裁图','thumb'=>'缩略图')
 * @author 刘星  2017-03-08 14:44:15
 */
function upload_img_qn($fileinput, $savepath = null, $size = array(), $thumb = array())
{
    //七牛设置
    $uploadConfig = C('UPLOAD_SITEIMG_QINIU');
    $uploadConfig['maxSize'] = 31457280;
    $uploadConfig['exts'] = array('jpg', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'jpeg', 'JPEG');
    $uploadConfig['rootPath'] = './';
    $uploadConfig['savePath'] = $savepath ? $savepath . '/' : './';
    $uploadQN = new \Think\Upload ($uploadConfig);
    //上传
    $infoQN = $uploadQN->uploadOne($_FILES[$fileinput]);
    if (!$infoQN) {
        $result = array('status' => 0, 'info' => $uploadQN->getError());
    } else {
        $result['status'] = 1;
        $result['file']['old'] = str_replace('/', '_', $infoQN['name']) . '?imageslim';
        //剪裁
        if (!empty($size)) {
            $result['file']['cut'] = $result['file']['old'] . '?imageView2';
            $result['file']['cut'] .= '/2/w/' . $size['width'] . '/h/' . $size['height'];
        }

        //缩略
        if (!empty($thumb)) {
            $result['file']['thumb'] = $result['file']['old'] . '?imageView2';
            $result['file']['thumb'] .= '/0/w/' . $thumb['width'] . '/h/' . $thumb['height'];
        }
    }
    return $result;
}

/**
 * 上传图片
 * @param string $fileinput 上传文件input name名称
 * @param unknown $filename 文件名
 * @param unknown $size 剪裁图大小（数组'width','height'）
 * @param unknown $thumb 缩略图大小（数组'width','height'）
 * @return multitype:number NULL 路径(数组'old'=>原图,'cut'=>'剪裁图','thumb'=>'缩略图')
 * @author 刘星  2017-03-08 14:44:15
 */
function upload_img_mobile($fileinput, $savepath = null, $size = array(), $thumb = array())
{
    //七牛设置
    $uploadConfig = C('UPLOAD_SITEIMG_QINIU');
    $uploadConfig['maxSize'] = 31457280;
    $uploadConfig['exts'] = array('jpg', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'jpeg', 'JPEG', '');
    $uploadConfig['rootPath'] = './';
    $uploadConfig['savePath'] = $savepath ? $savepath . '/' : './';
    $uploadQN = new \Think\Upload ($uploadConfig);
    //上传
    $infoQN = $uploadQN->uploadOne($_FILES[$fileinput]);
    if (!$infoQN) {
        $result = array('status' => 0, 'info' => $uploadQN->getError());
    } else {
        $result['status'] = 1;
        $result['file']['old'] = str_replace('/', '_', $infoQN['name']) . '?imageslim';
        //剪裁
        if (!empty($size)) {
            $result['file']['cut'] = $result['file']['old'] . '?imageView2';
            $result['file']['cut'] .= '/2/w/' . $size['width'] . '/h/' . $size['height'];
        }

        //缩略
        if (!empty($thumb)) {
            $result['file']['thumb'] = $result['file']['old'] . '?imageView2';
            $result['file']['thumb'] .= '/0/w/' . $thumb['width'] . '/h/' . $thumb['height'];
        }
    }
    return $result;
}


/**
 * 上传附件
 * @param unknown $fileinput
 * @param unknown $filename
 * @return Ambigous <multitype:number multitype:string  , multitype:number string >
 */
function upload_fil($fileinput, $filename)
{
    //七牛设置
    $uploadConfig = C('UPLOAD_SITEIMG_QINIU');
    $uploadConfig['maxSize'] = 31457280;
    $uploadConfig['exts'] = array('txt', 'docx', 'xlsx', 'xls', 'rar', 'zip', 'swf', 'flash');
    $uploadConfig['rootPath'] = './';
    $uploadQN = new \Think\Upload ($uploadConfig);

    // 上传文件
    $infoQN = $uploadQN->uploadOne($_FILES[$fileinput]);

    if (!$infoQN) {// 上传错误提示错误信息
        $result = array('status' => 0, 'info' => $uploadQN->getError());
    } else {// 上传成功
        $result['status'] = 1;
        $result['file']['old'] = $infoQN['url'];
    }

    return $result;
}

/**
 * 删除七牛图片
 * @author 刘星
 */
function removeImg_qn($path)
{
    $qiniuObj = new \Think\Upload\Driver\Qiniu\QiniuStorage(C('UPLOAD_SITEIMG_QINIU')['driverConfig']);
    $path = str_replace('?imageslim', '', $path);
    $res = $qiniuObj->del($path);
    if (!$res) {
        return true;
    } else {
        return false;
    }
}

/**
 * 敏感词过滤公共调用方法
 *
 */
function filterbad($words)
{
    require APP_PATH . 'Common/Common/badword.src.php';
    $badword = explode("|", $content);//explode()函数以","为标识符进行拆分
    $badword1 = array_combine($badword, array_fill(0, count($badword), '*'));
    $str = strtr($words, $badword1);
    return $str;
}

/**
 * 截取字符
 */
function cutStr($str, $n = 20, $stripTags = true)
{
    if (!$str) {
        return false;
    }
    $qian = array(" ", "　", "\t", "\n", "\r", "_ueditor_page_break_tag_");
    $hou = array("", "", "", "", "", "");
    if ($stripTags) {
        $strip_str = str_replace($qian, $hou, strip_tags(htmlspecialchars_decode($str)));
        $r = abslength($strip_str) > $n ? '...' : '';
        return mb_substr($strip_str, 0, $n, 'utf-8') . $r;
    } else {
        $strip_str = str_replace($qian, $hou, htmlspecialchars_decode($str));
        return mb_substr($strip_str, 0, $n, 'utf-8');
    }
}

function abslength($str)
{
    if (empty($str)) {
        return 0;
    }
    if (function_exists('mb_strlen')) {
        return mb_strlen($str, 'utf-8');
    } else {
        preg_match_all("/./u", $str, $ar);
        return count($ar[0]);
    }
}

/**
 * 表情解析
 * @param unknown $content
 */
function get_smohan_face($content)
{
    $public = C('__PUBLIC__');
    $content = preg_replace('/\[委屈\]/', "<img src='" . $public . "/home/images/smohan/faces/wq.gif' addFacesPic='[委屈]' alt='委屈' title='委屈'/>", $content);
    $content = preg_replace('/\[呵呵\]/', "<img src='" . $public . "/home/images/smohan/faces/smilea.gif' addFacesPic='[呵呵]' alt='呵呵' title='呵呵'/>", $content);
    $content = preg_replace('/\[嘻嘻\]/', "<img src='" . $public . "/home/images/smohan/faces/tootha.gif' addFacesPic='[嘻嘻]' alt='嘻嘻' title='嘻嘻'/>", $content);
    $content = preg_replace('/\[哈哈\]/', "<img src='" . $public . "/home/images/smohan/faces/laugh.gif' addFacesPic='[哈哈]' alt='哈哈' title='哈哈'/>", $content);
    $content = preg_replace('/\[可爱\]/', "<img src='" . $public . "/home/images/smohan/faces/tza.gif' addFacesPic='[可爱]' alt='可爱' title='可爱'/>", $content);
    $content = preg_replace('/\[可怜\]/', "<img src='" . $public . "/home/images/smohan/faces/kl.gif' addFacesPic='[可怜]' alt='可怜' title='可怜'/>", $content);
    $content = preg_replace('/\[挖鼻屎\]/', "<img src='" . $public . "/home/images/smohan/faces/kbsa.gif' addFacesPic='[挖鼻屎]' alt='挖鼻屎' title='挖鼻屎'/>", $content);
    $content = preg_replace('/\[吃惊\]/', "<img src='" . $public . "/home/images/smohan/faces/cj.gif' addFacesPic='[吃惊]' alt='吃惊' title='吃惊'/>", $content);
    $content = preg_replace('/\[害羞\]/', "<img src='" . $public . "/home/images/smohan/faces/shamea.gif' addFacesPic='[害羞]' alt='害羞' title='害羞'/>", $content);
    $content = preg_replace('/\[挤眼\]/', "<img src='" . $public . "/home/images/smohan/faces/zy.gif' addFacesPic='[挤眼]' alt='挤眼' title='挤眼'/>", $content);
    $content = preg_replace('/\[闭嘴\]/', "<img src='" . $public . "/home/images/smohan/faces/bz.gif' addFacesPic='[闭嘴]' alt='闭嘴' title='闭嘴'/>", $content);
    $content = preg_replace('/\[鄙视\]/', "<img src='" . $public . "/home/images/smohan/faces/bs2.gif' addFacesPic='[鄙视]' alt='鄙视' title='鄙视'/>", $content);
    $content = preg_replace('/\[爱你\]/', "<img src='" . $public . "/home/images/smohan/faces/lovea.gif' addFacesPic='[爱你]' alt='爱你' title='爱你'/>", $content);
    $content = preg_replace('/\[泪\]/', "<img src='" . $public . "/home/images/smohan/faces/sada.gif' addFacesPic='[泪]' alt='泪' title='泪'/>", $content);
    $content = preg_replace('/\[偷笑\]/', "<img src='" . $public . "/home/images/smohan/faces/heia.gif' addFacesPic='[偷笑]' alt='偷笑' title='偷笑'/>", $content);
    $content = preg_replace('/\[亲亲\]/', "<img src='" . $public . "/home/images/smohan/faces/qq.gif' addFacesPic='[亲亲]' alt='亲亲' title='亲亲'/>", $content);
    $content = preg_replace('/\[生病\]/', "<img src='" . $public . "/home/images/smohan/faces/sb.gif' addFacesPic='[生病]' alt='生病' title='生病'/>", $content);
    $content = preg_replace('/\[太开心\]/', "<img src='" . $public . "/home/images/smohan/faces/mb.gif' addFacesPic='[太开心]' alt='太开心' title='太开心'/>", $content);
    $content = preg_replace('/\[懒得理你\]/', "<img src='" . $public . "/home/images/smohan/faces/ldln.gif' addFacesPic='[懒得理你]' alt='懒得理你' title='懒得理你'/>", $content);
    $content = preg_replace('/\[右哼哼\]/', "<img src='" . $public . "/home/images/smohan/faces/yhh.gif' addFacesPic='[右哼哼]' alt='右哼哼' title='右哼哼'/>", $content);
    $content = preg_replace('/\[左哼哼\]/', "<img src='" . $public . "/home/images/smohan/faces/zhh.gif' addFacesPic='[左哼哼]' alt='左哼哼' title='左哼哼'/>", $content);
    $content = preg_replace('/\[嘘\]/', "<img src='" . $public . "/home/images/smohan/faces/x.gif' addFacesPic='[嘘]' alt='嘘' title='嘘'/>", $content);
    $content = preg_replace('/\[衰\]/', "<img src='" . $public . "/home/images/smohan/faces/cry.gif' addFacesPic='[衰]' alt='衰' title='衰'/>", $content);
    $content = preg_replace('/\[吐\]/', "<img src='" . $public . "/home/images/smohan/faces/t.gif' addFacesPic='[吐]' alt='吐' title='吐'/>", $content);
    $content = preg_replace('/\[打哈气\]/', "<img src='" . $public . "/home/images/smohan/faces/k.gif' addFacesPic='[打哈气]' alt='打哈气' title='打哈气'/>", $content);
    $content = preg_replace('/\[抱抱\]/', "<img src='" . $public . "/home/images/smohan/faces/bba.gif' addFacesPic='[抱抱]' alt='抱抱' title='抱抱'/>", $content);
    $content = preg_replace('/\[怒\]/', "<img src='" . $public . "/home/images/smohan/faces/angrya.gif' addFacesPic='[怒]' alt='怒' title='怒'/>", $content);
    $content = preg_replace('/\[疑问\]/', "<img src='" . $public . "/home/images/smohan/faces/yw.gif' addFacesPic='[疑问]' alt='疑问' title='疑问'/>", $content);
    $content = preg_replace('/\[馋嘴\]/', "<img src='" . $public . "/home/images/smohan/faces/cza.gif' addFacesPic='[馋嘴]' alt='馋嘴' title='馋嘴'/>", $content);
    $content = preg_replace('/\[拜拜\]/', "<img src='" . $public . "/home/images/smohan/faces/88.gif' addFacesPic='[拜拜]' alt='拜拜' title='拜拜'/>", $content);
    $content = preg_replace('/\[思考\]/', "<img src='" . $public . "/home/images/smohan/faces/sk.gif' addFacesPic='[思考]' alt='思考' title='思考'/>", $content);
    $content = preg_replace('/\[汗\]/', "<img src='" . $public . "/home/images/smohan/faces/sweata.gif' addFacesPic='[汗]' alt='汗' title='汗'/>", $content);
    $content = preg_replace('/\[困\]/', "<img src='" . $public . "/home/images/smohan/faces/sleepya.gif' addFacesPic='[困]' alt='困' title='困'/>", $content);
    $content = preg_replace('/\[睡觉\]/', "<img src='" . $public . "/home/images/smohan/faces/sleepa.gif' addFacesPic='[睡觉]' alt='睡觉' title='睡觉'/>", $content);
    $content = preg_replace('/\[钱\]/', "<img src='" . $public . "/home/images/smohan/faces/money.gif' addFacesPic='[钱]' alt='钱' title='钱'/>", $content);
    $content = preg_replace('/\[失望\]/', "<img src='" . $public . "/home/images/smohan/faces/sw.gif' addFacesPic='[失望]' alt='失望' title='失望'/>", $content);
    $content = preg_replace('/\[酷\]/', "<img src='" . $public . "/home/images/smohan/faces/cool.gif' addFacesPic='[酷]' alt='酷' title='酷'/>", $content);
    $content = preg_replace('/\[花心\]/', "<img src='" . $public . "/home/images/smohan/faces/hsa.gif' addFacesPic='[花心]' alt='花心' title='花心'/>", $content);
    $content = preg_replace('/\[哼\]/', "<img src='" . $public . "/home/images/smohan/faces/hatea.gif' addFacesPic='[哼]' alt='哼' title='哼''/>", $content);
    $content = preg_replace('/\[鼓掌\]/', "<img src='" . $public . "/home/images/smohan/faces/gza.gif' addFacesPic='[鼓掌]' alt='鼓掌' title='鼓掌'/>", $content);
    $content = preg_replace('/\[晕\]/', "<img src='" . $public . "/home/images/smohan/faces/dizzya.gif' addFacesPic='[晕]' alt='晕' title='晕'/>", $content);
    $content = preg_replace('/\[悲伤\]/', "<img src='" . $public . "/home/images/smohan/faces/bs.gif' addFacesPic='[悲伤]' alt='悲伤' title='悲伤'/>", $content);
    $content = preg_replace('/\[抓狂\]/', "<img src='" . $public . "/home/images/smohan/faces/crazya.gif' addFacesPic='[抓狂]' alt='抓狂' title='抓狂'/>", $content);
    $content = preg_replace('/\[黑线\]/', "<img src='" . $public . "/home/images/smohan/faces/h.gif' addFacesPic='[黑线]' alt='黑线' title='黑线''/>", $content);
    $content = preg_replace('/\[阴险\]/', "<img src='" . $public . "/home/images/smohan/faces/yx.gif' addFacesPic='[阴险]' alt='阴险' title='阴险'/>", $content);
    $content = preg_replace('/\[怒骂\]/', "<img src='" . $public . "/home/images/smohan/faces/nm.gif' addFacesPic='[怒骂]' alt='怒骂' title='怒骂'/>", $content);
    $content = preg_replace('/\[心\]/', "<img src='" . $public . "/home/images/smohan/faces/hearta.gif' addFacesPic='[心]' alt='心' title='心'/>", $content);
    $content = preg_replace('/\[伤心\]/', "<img src='" . $public . "/home/images/smohan/faces/unheart.gif' addFacesPic='[伤心]' alt='伤心' title='伤心'/>", $content);
    return $content;
}


/**
 * 手机号隐藏处理
 * @param unknown $tel
 * @return string
 */
function get_hide_tel($tel)
{
    if (preg_match('/^1[34578]{1}\d{9}$/', $tel)) {
        $hide_tel = substr($tel, 0, 3);
        $hide_tel .= '****';
        $hide_tel .= substr($tel, 7, 4);
    } else {
        $hide_tel = $tel;
    }
    return $hide_tel;
}


/**
 * 创建随机字符串
 * @param number $length
 * @return string
 */
function createNonStr($length = 16)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    mt_srand((double)microtime() * 1000000 * getmypid());
    $str = "";
    while (strlen($str) < $length)
        $str .= substr($chars, (mt_rand() % strlen($chars)), 1);
    return $str;
}


/**
 * 清空运行缓存
 * @return boolean
 * scandir: 返回一个数组，其中包含指定路径中的文件和目录
 */
function clear_runtime()
{
    // $memcache_obj = memcache_connect('10.127.48.209', 11211);

    // memcache_flush($memcache_obj);
    $dirs = array();
    $noneed_clear = array(".", "..");
    $rootdirs = array_diff(scandir(RUNTIME_PATH), $noneed_clear);//通过排除“.”或者“..” 只 获取文件列表数组
    foreach ($rootdirs as $dir) {
        if ($dir != "." && $dir != "..") {
            $dir = RUNTIME_PATH . $dir;
            if (is_dir($dir)) {
                array_push($dirs, $dir);
                $tmprootdirs = scandir($dir);
                foreach ($tmprootdirs as $tdir) {
                    if ($tdir != "." && $tdir != "..") {
                        $tdir = $dir . '/' . $tdir;
                        if (is_dir($tdir)) {
                            array_push($dirs, $tdir);
                        }
                    }
                }
            }
        }
    }
    // $dirtool=new \Common\Lib\Util\Dir();
    $dirtool = new \Org\Util\Dir();
    $return = true;
    foreach ($dirs as $dir) {
        $res = $dirtool->del($dir);
        if ($res === false)
            $return = false;
    }
    return $return;
}


/*********************************************************************************************************
 ***************************************** 迈迪设计大赛新增方法 *******************************************
 **********************************************************************************************************/

/**
 * 创建迈迪账号
 * @return State： 0  保存失败   1 访问成功，该手机号已有账户  2 访问成功，为手机号创建账户，迈迪的密码为该手机号；
 */
function checkPhoneToMD($phone)
{
    $url = C('MD_API45') . "/api/v1.0/User/LoginUserByPhone?phone=" . $phone;
    $result = httpGetRequest($url, '');
    if ($result['State'] == 1 && count($result['Data']) > 0) {
        //$result['Data'][0]['IsNew']  0 表示已有账户  1表示新账户
        return array('State' => $result['Data'][0]['IsNew'] + 1, 'Data' => $result['Data'][0]);
    } else {
        //保存错误日志，方便后期同步错误的账号数据
        return array('State' => 0, 'msg' => $result);
    }

}

/**
 * 创建纯数字随机字符串
 * @param number $length
 * @return string
 */
function createNonStrNumber($length = 16)
{
    $chars = '0123456789';
    mt_srand((double)microtime() * 1000000 * getmypid());
    $str = "";
    while (strlen($str) < $length)
        $str .= substr($chars, (mt_rand() % strlen($chars)), 1);
    return $str;
}


/**
 * 发送验证码
 * @return State： 0 接口调用失败   1 数量超出限制   2 成功
 *         Data: 验证码
 *         msg: 错误原因
 */
function sendVerify($phone)
{

    $verifyMsg = M('verifymsg');
    $where['key'] = array('eq', $phone);
    $where['send_time'] = array('gt', date('Y-m-d'));
    $count = $verifyMsg->where($where)->count('id');

    //数量超出则不允许发送
    if ($count >= C(VerifyLimit)) {
        return array('State' => 1, 'msg' => '超出每日发送数量');
    }
    //判断数据库中是否存在其他的验证码
    $what['key'] = array('eq', $phone);
    $what['status'] = 0;
    $what['send_time'] = array('gt', date('Y-m-d H:i:s', time() - C('VerifyOvertime')));
    $verifyMsg->where($what)->setField('status', 1);
    //用于研发测试阶段生成验证码,需要真实发送短信时注释该部分
//  $data['key']  = $phone;
//  $data['send_time']  =  date('Y-m-d H:i:s', time());
//  $data['verify']  = createNonStrNumber(6);
//  $verifyMsg->add($data);
//  return array('State' =>2,'Data'=>$data['verify']);


    //调用迈迪网接口发送验证码短信并返回验证码   （验证码由迈迪网生成）
    $url = C('MD_API1') . "/u/validRegPhone?phone=" . $phone;
    $result = httpGetRequest($url, '');

    if ($result['State'] == 1 && count($result['Data']) > 0) {
        $data['key'] = $phone;
        $data['send_time'] = date('Y-m-d H:i:s', time());
        $data['verify'] = $result['Data'];
        $verifyMsg->add($data);
        return array('State' => 2, 'Data' => $result['Data']);
    } else {
        //保存错误日志，方便后期追查错误
        return array('State' => 0, 'msg' => $result);
    }
}

/**
 * 判断并使用验证码
 * @return State： 0 失败(验证码错误、已失效、已使用)    1 成功
 *         msg: 错误原因
 */
function checkAndUserVerify($phone, $ver)
{
    $verifyMsg = M('verifymsg');
    $where['key'] = array('eq', $phone);
    $where['verify'] = array('eq', $ver);
    $where['send_time'] = array('gt', date('Y-m-d H:i:s', time() - C('VerifyOvertime')));
    $verRow = $verifyMsg->where($where)->find();

    if ($verRow) {
        if ($verRow['status'] == 0) {
            //修改状态，该验证码已使用
            $verRow['status'] = 1;
            $verRow['change_time'] = date('Y-m-d H:i:s', time());
            $verifyMsg->save($verRow);
            return array('State' => 1, 'msg' => 'OK');
        } else {
            return array('State' => 0, 'msg' => '验证码已经被使用');
        }
    } else {
        return array('State' => 0, 'msg' => '验证码错误或者验证码已失效');
    }
}

/**
 * 报名成功后发送短信
 */
function sendSmsForBM($phone)
{

    include "TopSdk.php";
    // date_default_timezone_set('Asia/Shanghai');
    $client = new ClusterTopClient("23331158", "402ace97d8c0da7c5db2a6852f8b03ac");
    $client->gatewayUrl = "http://gw.api.taobao.com/router/rest";
    $req = new AlibabaAliqinFcSmsNumSendRequest;
    $req->setExtend("-1");//接收短信的用户编号，未登录用户为-1
    $req->setSmsType("normal");//固定
    $req->setSmsFreeSignName("迈迪网");//固定
    $req->setRecNum($phone);//接收短信的电话号码
    $req->setSmsTemplateCode("SMS_174278611");//固定

    //发送短信
    $result = $client->execute($req);

    //保存短信发送记录到数据库
    $data['phone'] = $phone;
    $data['send_time'] = date('Y-m-d H:i:s', time());
    $data['msg'] = json_encode($result);
    M('message_log')->add($data);
}
