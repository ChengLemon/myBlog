<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\wx\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController; 
use plugins\wx\model\PluginWxModel;
use think\Db;
use cmf\lib\Plugin;

class IndexController extends PluginBaseController
{

    public function index()
    {
        // 微信登录
        $res = config('wxlogin'); 
        $plugin = $this->getPlugin();
        $config = $plugin->getConfig();  
        $callback = urlencode(cmf_plugin_url("wx://Index/loginCallBack"));
        $state = md5(uniqid(rand(), true)); 
        session('wx_state', $state);
 
        $url = "https://open.weixin.qq.com/connect/qrconnect?appid={$config['appid']}&redirect_uri={$callback}&response_type=code&scope=snsapi_login&state={$state}#wechat_redirect";
        header("location: {$url}");
    }
    
    /**
     * 授权回调
     * http://cheng.com/plugin/wx/index/loginCallBack.html?code=0317a2c31ccd5eadf1a7a8fffd4a7dbf&state=123
     */
    public function loginCallBack() { 
        if (input('get.state') != $_SESSION["wx_state"]) {
            exit("5001");
        } 
        
        $plugin = $this->getPlugin();
        $config = $plugin->getConfig();         
        
        $appid = $config['appid'];
        $appSecret = $config['appsecret'];
        $code = input('get.code');
        
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
         
        $data = curl($url);
        
        $infoUrl = "https://api.weixin.qq.com/sns/userinfo?access_token={$data['access_token']}&openid={$data['openid']}&lang=zh_CN";
        
        $userInfo = curl($infoUrl); 
        print_r($userInfo);
    }

}
