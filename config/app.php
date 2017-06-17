<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY', '34wf5e854$@f4023c11288a3d98b664'),

    'cipher' => 'AES-256-CBC',

	'debug' =>  env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

	'timezone' => 'PRC',

    'locale' => env('APP_LOCALE', 'en'),
    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

	'image_url' => 'http://图片域名/qrcode',

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
	
	'grcode_key_encrypt' => '32f5#$$@f4wqc31sd88dd==23',//二维码图片读取加密串

	'picture'=>[
		"size"=>3145728,
		"type"=>['image/gif','image/png','image/jpeg','image/bmp','image/x-png'],
		"ext"=>['gif','jpg','png','bmp']
	],


	//平台版本配置
	"app_platform"	=>	[


		//商户平台
		[
			"appid"		=> 1001,
			"appkey"		=>"Q#1d3s",
			"privatekey"	=>"88_rsa_private_key.pem",
			"publickey"		=>"88_rsa_public_key.pem",
		],

		

	],


];
