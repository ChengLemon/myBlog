<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\community\controller;

use think\Controller;
use cmf\controller\HomeBaseController;
use app\community\service\ApiService;
use app\community\model\CommunityArticleModel;
use app\community\model\CommunityCategoryModel;
use app\community\model\UserModel;
use think\View;

class BaseController extends HomeBaseController {
    
    public function _initialize() { 
        parent::_initialize();
        $articleModel = new CommunityArticleModel();
        
        // 本周热议
        $nums = 0; // 暂定0
        $hotsArts = $articleModel->getHots($nums);
        View::share('hotsArts', $hotsArts); 
    }
}
