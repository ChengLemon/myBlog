<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\community\controller;

use think\Controller;
use cmf\controller\HomeBaseController;

class TestController extends HomeBaseController {

    public function test() {  
        cmf_user_action('testset');
        
    }
 

}
