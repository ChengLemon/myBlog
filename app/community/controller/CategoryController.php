<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\community\controller;

use cmf\controller\HomeBaseController;
use app\community\model\CommunityCategoryModel;
use app\community\model\CommunityArticleModel;

class CategoryController extends HomeBaseController {

    public function index() {
        $param = $this->request->param();
        $id = $this->request->param('categoryId', 0, 'intval');  
        
        $cateModel = new CommunityCategoryModel();
        $articalModel = new CommunityArticleModel();
 
        $articles = $articalModel->getArticles($param, true);
 
        $category = $cateModel->where('id', $id)->where('status', 1)->find(); 
       
        $this->assign('category', $category); 
        $this->assign('articles', $articles); 
 
        $listTpl = empty($category['list_tpl']) ? 'list' : $category['list_tpl'];
 
        return $this->fetch('/category/' . $listTpl);
    }

}
