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
use app\community\controller\BaseController;

class IndexController extends BaseController {

    /**
     * 首页
     * @return type
     */
    public function index() {    
        
        $articleModel = new CommunityArticleModel();
        $categoryModel = new CommunityCategoryModel();
        $userModel = new UserModel(); 
        
        // 置顶文章 
        $topField = [ 'a.is_top' => 1];
        $topArts = $articleModel->getArticlesByField($topField);    
        
        // 右侧边栏分类
        $rightCate = $categoryModel->where([ 'risshow' => 1 ])->find(); 
        
        // 右侧边栏文章s 
        $join = [
            ['__COMMUNITY_CATEGORY_ARTICLE__ b', 'a.id = b.post_id'] 
        ];
        $where = [ "b.category_id" => $rightCate['id'] ];
        $rightArts = $articleModel->alias('a')
            ->join($join) 
            ->where($where)
            ->order('a.update_time', 'DESC')
            ->limit(5) 
            ->select();  
         
        // 回帖周榜
        $replyTop = $userModel->maxComments(); 
 
        $this->assign('replyTop', $replyTop);
        $this->assign('topArts', $topArts); 
        $this->assign('rightCate', $rightCate);
        $this->assign('rightArts', $rightArts);
         
        cmf_user_action('test');
        return $this->fetch(':index');
    }
    
    /**
     * ajax文章
     * @return int
     */
    public function ajaxarticles() {
        $param = $this->request->param();   
        $articleModel = new CommunityArticleModel();
        
        // 全部文章
        $allArts = $articleModel->getArticles($param);
  
        $this->assign('allArts', $allArts);  
        return $this->fetch(':ajaxarticles');
    }

}
