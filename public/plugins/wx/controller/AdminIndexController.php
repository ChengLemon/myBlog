<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\wx\controller; //Demo插件英文名，改成你的插件英文就行了

use think\Db;
use cmf\controller\PluginBaseController;
use plugins\wx\model\PluginWxModel;

class AdminIndexController extends PluginBaseController
{

    function _initialize()
    {
        $adminId = cmf_get_current_admin_id();//获取后台管理员id，可判断是否登录
        if (!empty($adminId)) {
            $this->assign("admin_id", $adminId);
        } else {
            //TODO no login
            $this->error('未登录');
        }
    }

    function index()
    {
        $users = Db::name("user")->limit(0, 5)->select();
        $result = PluginWxModel::all(); 
        print_r($result); 
        exit;

        $this->assign("users", $users);


        $this->assign("users", $users);

        return $this->fetch('/admin_index');
    }

}
