<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016073000124206",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEowIBAAKCAQEA1DQBitk5RMX6El298DoMTP33DlZi0GfEE/cBDOsleUUsbOaQ
B1GwUDB6jgdJIPijtk2puDoDCT1xqKOAZG/IdwwAP9/JrOt4Axu7z2GDuO/N3+4y
z1BInT4WVCmsyIV2bd6nbGDR2Z9MIFAmrYH1Jmjj38VfuFNbwbDsj2Q5QLHvSB8U
Lt6Rm62NaRwNrlFB8fZlWcwIUW2iQcF5FwZioCrVgoKgw/JEJJKFpC5alynzzJzW
XUjyP9c52q5+4ENXxFYcZTP07q/Ow84kLqsVGv372AZkF0bHoOLlXnXJ7rD048ao
KDDY1hUR34RL8fjW28RX7IoHPo2uhPJXG52GVwIDAQABAoIBAGt05m8Mbx6vbmIM
jFlw27fFR21AO4uzcCx4AVUsYm6Vo9iFrNu39yO0WOtBLzv+DNENZOuAnPb54Wt3
WFYD8SyvrBSsW3EwUDaqvaFXjR+Cd+t0sNAgpJTT0vTNocxtITqj7H5KTRn5u9AF
JoDodV788J/pT/6EX8umrzQxmXhzdiU/0+lIeXQpJqsdagrB/vfrrWfHhCxL8lzm
nqS20RM3QRTcq79ZSJ/LGF4TNEMQE0x4+Jn6ro6AzE0juSVGmgr3Qj9uQZNOwzPe
TFuoX3ddkBNvGpbc4OUhKsJxvt1th8muw/3aP9ZSBATWjKmgNbih37XcFKJGowZA
Ql/3ypECgYEA808a76w9BZ4RcIOY/2fZQ/UkYukypgK9WafYdGlrP81WeD2np9GW
hlXjGnHw1QVchRkwtehCx4psRlq/xTZy3b4/G3KPrHlIJryANqYJr9tC9o3z5BgL
9+Xd8l5QBVrD2qAmHAzJjI5hfDJYx0/sTGH04wC0U9l1ahtxzc0QDukCgYEA30WL
roKsojmOqz8B7qOuySvgrcTZv7VSFaMLJ9fUARcv8OdZjWBxUzV5gVtbx9hR7EdB
qVEfH/BNfFgbDAJDcwfYpqDqy6DmmXe0+7w7Bhuw72uqLXGHuHUvbDA5YcESh1I1
tH/xs9eO+NjVc1422UoiVPMGlGfSyzpqG3mgIz8CgYEAkBlDnLrnkRIixf5KeMlq
dcMT/7iZFJT+y8CKg7eDm0/jbGcnik6o1Xq8fAcLWT5Jo1Jd9P5PvpoOskRA2235
7bYk7f6VdNKHltmBHdyMVCiJqjg8P9S2EeWD421T+zfcUvkSWP6Bx8rzlXjD95cw
HbTOMjtSkW5zCZqjdf2DCzECgYBD4Wv/SYFc7OzgSY631BHu3aU4j5G4RFNJesGI
gojVMAyGTIAgiILLzoU7e+AimTUHBf8DkENLPY8BT/QhKYsCLQ+EUYYxFKEnZYqj
wm2bhM29bNlXaZ3eydn6JYs8miuc8rSbRQ7iI5Y3OA1lPu6kD3LyqtSESDCBUygx
ZD/4lwKBgFpvbLUEh52GSD8+MlyhgTQk5GBWIcHOBz8LRBJU+v1sXPVWuFmbwpZg
N9u/Ou6OIZsa7ggF5TOOoPfxJx61WdKXoXQnOJZ0RIK+vKPxIOEmZwoIu1Po+CX8
MPBhMs9KRSas7kEpKmJx8oxsFn67hrM76DF4jdtqv855n/dqnm9M",
		
		//异步通知地址
		'notify_url' => "http://工程公网访问地址/alipay.trade.wap.pay-PHP-UTF-8/notify_url.php",
		
		//同步跳转
		'return_url' => "http://172.16.231.114/alipay.trade.wap.pay-PHP-UTF-8/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1DQBitk5RMX6El298DoM
TP33DlZi0GfEE/cBDOsleUUsbOaQB1GwUDB6jgdJIPijtk2puDoDCT1xqKOAZG/I
dwwAP9/JrOt4Axu7z2GDuO/N3+4yz1BInT4WVCmsyIV2bd6nbGDR2Z9MIFAmrYH1
Jmjj38VfuFNbwbDsj2Q5QLHvSB8ULt6Rm62NaRwNrlFB8fZlWcwIUW2iQcF5FwZi
oCrVgoKgw/JEJJKFpC5alynzzJzWXUjyP9c52q5+4ENXxFYcZTP07q/Ow84kLqsV
Gv372AZkF0bHoOLlXnXJ7rD048aoKDDY1hUR34RL8fjW28RX7IoHPo2uhPJXG52G
VwIDAQAB",
		
	
);