<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\portal\controller;

use think\Controller;
use think\Request;

class TestController extends Controller {

    public function testDisplay() {
 
        // $admin = new \app\admin\annotation\AdminMenuAnnotation();
        // $admin->test();
        
        $title = "HElloWORld";
        $content = "<div style='color:red'>{$title}</div>";
        return $this->display($content);
    }
    
    /**
     * 请求信息
     */
    public function getReq() { 
        
        // 获取当前域名 http://cheng.com
        echo $this->request->domain();
        echo '<br/>';
        
        // 获取当前入口文件 /index.php
        echo $this->request->baseFile();
        echo '<br/>';

        // 获取当前URL地址 不含域名 /portal/test/getReq
        echo $this->request->url();
        echo '<br/>';

        // 获取包含域名的完整URL地址 http://cheng.com/portal/test/getReq
        echo $this->request->url(true);
        echo '<br/>';

        // 获取当前URL地址 不含QUERY_STRING  /portal/test/getReq
        echo $this->request->baseUrl();
        echo '<br/>';

        // 获取URL访问的ROOT地址
        echo $this->request->root();
        echo '<br/>';
 
        // 获取URL访问的ROOT地址包含域名  http://cheng.com
        echo $this->request->root(true);
        echo '<br/>';

        // 获取URL地址中的PATH_INFO信息 portal/test/getReq
        echo $this->request->pathinfo();
        echo '<br/>';
        
        // 获取URL地址中的PATH_INFO信息 不含后缀 portal/test/getReq
        echo $this->request->path();
        echo '<br/>';

        // 获取URL地址中的后缀信息 html
        echo $this->request->ext();
        echo '<br/>';
 
        // 获取当前应用(模块) portal
        echo $this->request->module();
        echo '<br/>';

        // 获取当前控制器 Test
        echo $this->request->controller();
        echo '<br/>';

        // 获取当前操作名称 getreq
        echo $this->request->action();
        echo '<br/>';
 
        // 获取当前请求方法 GET
        echo $this->request->method();
        echo '<br/>';

        // 获取当前请求访问地址 xml
        echo $this->request->type();
        echo '<br/>';

        // 获取当前访问者 ip地址 127.0.0.1
        echo $this->request->ip();
        echo '<br/>';

        // 获取当前访问者 真实ip地址(防止代理) 127.0.0.1
        echo $this->request->ip(0, true);
        echo '<br/>';
    }

    /**
     * 输入变量
     */
    public function getParam() {
        $name = $this->request->param('name');
        $params = $this->request->param(); // 获取所有参数
        // print_r($params);
        $this->request->isCli();
        $header = $this->request->header();
        print_r($header);
        exit;
    }
    
    public function totest() {
        cmf_user_action('test');
    }
    
    public function test() {
        
    }
}
