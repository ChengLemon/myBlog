<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\community\controller;
 
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminSetController 社区管理设置
 * @package app\community\controller
 */
class AdminSetController extends AdminBaseController
{
    public function setting() {   

        $this->assign(cmf_get_community_option('post_settings'));     
        return $this->fetch();
        
    }
    
    public function setPost() {
        if ($this->request->isPost()) { 

            $options = $this->request->param('options/a');
            cmf_set_community_option('post_settings', $options); 

            $this->success("保存成功！", '');

        }
    }
    
    
}