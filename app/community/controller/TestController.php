<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\community\controller;

use think\Controller;
use cmf\controller\HomeBaseController;
use app\community\controller\BaseController;

class TestController extends BaseController {

    public function test() {  
        cmf_user_action('testset');
        
    }
 

}
