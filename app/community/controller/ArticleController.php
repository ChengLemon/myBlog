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
use app\community\validate\AdminArticleValidate;
use app\community\model\CommunityCommentModel;
use think\captcha\Captcha;
use think\Env;

class ArticleController extends HomeBaseController {

    public function add() { 
        return $this->fetch('/article/add');
    }

    public function addPost() { 
        
        if ($this->request->isPost()) {  
            // 检查验证码
            $data = $this->request->param();  

            $communityArticleModel = new CommunityArticleModel();

            $communityArticleModel->homeAddArticle($data, $data['categories']);

            $data['post']['id'] = $communityArticleModel->id;

            // 插件，保存文章后
            /**$hookParam = [
                'is_add' => true,
                'article' => $data['post']
            ];
            hook('portal_admin_after_save_article', $hookParam);**/

            return 'success';
        }
    }
    
    /**
     * 检测验证码
     * @return boolean
     */
    public function checkCode() {
        $code = $this->request->param('code'); 
        if (cmf_captcha_check($code)) {
            return 'success';
        }
        return 'error';
    }
    
    public function detail() { 
        $id = $this->request->param('id', 0, 'intval');
        
        $communityArticleModel = new CommunityArticleModel();
        $post            = $communityArticleModel->getArticle($id);
         
  
        if(empty($id) || empty($post)) {
            $this->error('这个帖子貌似不存在');
        }  
        
        $this->assign('data', $post); 
        
        return $this->fetch('/article/detail');
    }
    
    public function reply() {
        $data = $this->request->param();
        $articleId = $data['object_id'];
        $commentModel = model('CommunityComment');
        $commentModel->data($data);
        $commentModel->save();
        // $result = db('community_comment')->insertGetId($data);
        if(empty( $commentModel->id )) {
            $this->error('评论失败！');
        } else {
            $this->redirect('article/detail', [ 'id' => $articleId ]);
        }
    }

}

 

