<?php
//数组转换成字符串
function arr2str ($arr)
{
	$result = '';
	foreach ($arr as $key=>$val)
	{
		if($key != 'ticket'){
			$result = $result.'/'.$key.'/'.$val;
		}
	}
	return $result;
}
 

