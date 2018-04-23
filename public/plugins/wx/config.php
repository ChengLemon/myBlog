<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
return [
    'appid'     => [ // 在后台插件配置表单中的键名 ,会是config[text]
        'title' => 'appid', // 表单的label标题
        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '', // 表单的默认值
        'tip'   => '微信公众号的appid' //表单的帮助提示
    ],
    'appsecret' => [ // 在后台插件配置表单中的键名 ,会是config[password]
        'title' => 'appsecret',
        'type'  => 'text',
        'value' => '',
        'tip'   => '微信公众号的appsecret'
    ] 
];
					