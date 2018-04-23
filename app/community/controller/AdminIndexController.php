<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\community\controller;

use cmf\controller\AdminBaseController;

class AdminIndexController extends AdminBaseController {

    public function index() { 
        return $this->fetch();
    }

}
