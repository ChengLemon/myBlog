<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\community\model;
 
use think\Model; 
use traits\model\SoftDelete; 
use app\admin\model\RouteModel;
use tree\Tree;
use think\Db;
use app\community\model\CommunityTagModel;
use app\community\model\CommunityCommentModel;

class CommunityArticleModel extends Model
{ 
    protected $pk = 'id';
    protected $name = 'community_article'; 
    
    // 时间格式
    protected $dateFormat = 'Y-m-d H:i:s';
    
    // 类型转换
    protected $type = [
        'more' => 'array',
        'update_time' => 'timestamp', 
        'published_time' => 'timestamp',
        'create_time' => 'timestamp',
    ];

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true; 
    
    /**
     * 本周热议
     */
    public function getHots($nums = 0, $limit = 5) {
        $startTime = mktime(0, 0 , 0, date("m"), date("d")-date("w")+1, date("Y"));
        $endTime = time();
        
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0,
            'a.published_time' => [['>= time', $startTime], ['<= time', $endTime]] // 一周
        ];  
        
        $join = [ 
            ['__COMMUNITY_COMMENT__ d', 'd.object_id = a.id', 'left']
        ];

        $field = 'a.*,count(d.id) as comms'; 
 
        $articles = $this->alias('a')->field($field)
            ->join($join) 
            ->where($where)
            ->order('a.update_time', 'DESC')
            ->limit($limit)
            ->group('a.id') 
            ->having('count(d.id) >= ' . $nums)  
            ->select();  

        return $articles;
    }
    
    /**
     * 根据类型获取文章
     * @param type $type
     */
    public function getArticlesByField($field = '', $limit = 5) {
        
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0
        ]; 
        
        if( !empty($field) ) {
            $where = array_merge($where, $field);
        } 
        
        $join = [
            ['__USER__ u', 'a.user_id = u.id'], 
            ['__COMMUNITY_COMMENT__ d', 'd.object_id = a.id', 'left']
        ];

        $field = 'a.*,u.user_login,u.user_nickname,u.user_email,u.avatar,count(d.id) as comms'; 
 
        $articles = $this->alias('a')->field($field)
            ->join($join) 
            ->where($where)
            ->order('a.update_time', 'DESC')
            ->limit($limit)
            ->group('a.id')
            ->select(); 

        return $articles;
    }

    /**
     * 获取符合条件的文章
     * @param type $filter        过滤条件
     * @param type $isPagination  是否分页
     * @return type
     */
    public function getArticles($filter = '', $isPagination = false) {
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0
        ];

        $join = [
            ['__USER__ u', 'a.user_id = u.id'],
            ['__COMMUNITY_CATEGORY_ARTICLE__ b', 'a.id = b.post_id'],
            ['__COMMUNITY_CATEGORY__ c', 'c.id = b.category_id'],
            ['__COMMUNITY_COMMENT__ d', 'd.object_id = a.id', 'left']
        ];

        $field = 'a.*,b.id AS post_category_id,b.list_order,c.name,b.category_id,u.user_login,u.user_nickname,u.user_email,u.avatar,count(d.id) as comms';

        // 分类搜索
        $category = empty($filter['categoryId']) ? 0 : intval($filter['categoryId']);
        if (!empty($category)) {
            $where['b.category_id'] = ['eq', $category]; 
        }

        // 关键字搜索
        $keyword = empty($filter['keyword']) ? '' : trim($filter['keyword']);
        if (!empty($keyword)) {
            $where['a.post_title'] = ['like', "%$keyword%"];
        } 
        
        // 是否完结
        $isdone = empty($filter['isdone']) ?  '' : $filter['isdone']; 
        if(!empty($isdone)) {
            $where['a.isdone'] = $isdone;
        }
            
        // 热议or最新
        $sorts = empty($filter['sorts']) ? '' : $filter['sorts']; 
        switch ($sorts) {
            case 'hots':
                $sorts = "comms desc";
                break;
            default :
                $sorts = "a.update_time desc";
                break;
        } 
        
        $communityArticleModel = new CommunityArticleModel();
        $articles        = $communityArticleModel->alias('a')->field($field)
            ->join($join)
            ->where($where)
            ->group('a.id') 
            ->order($sorts);
        
        if($isPagination) {
            $articles = $articles->paginate(2);
        } else {
            $articles = $articles->limit(25)->select();
        }
            
        return $articles;
    }
    
    /**
     * 获取某新闻详情
     * @param type $id
     * @return type
     */
    public function getArticle($id) {
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0,
            'a.id' => $id
        ];

        $join = [ 
            ['__COMMUNITY_COMMENT__ d', 'd.object_id = a.id', 'left'],
            ['__USER__ u', 'u.id = a.user_id']
        ];

        $field = 'a.*, a.id as articleId, count(d.id) as comms, u.*, u.id as uid'; 
        
        $data = [];
        $communityArticleModel = new CommunityArticleModel();
        $detail = $communityArticleModel->find($id);
        $article        = $communityArticleModel->alias('a')->field($field)
            ->join($join)
            ->where($where) 
            ->find(); 
        
        if(!empty($article) ) { 
            $data['article'] = $article;
            
            // 浏览量+1 
            $communityArticleModel->where('id', $id)->setInc('post_hits'); 
            
            // categories
            $data['postCategories'] = $detail->categories()->alias('c')->column('c.name', 'c.id'); 
            
            // tags
            $data['postTags'] = $detail->tags()->alias('t')->column('t.name', 't.id'); 
            
            // 评论
            $data['comments'] = $communityArticleModel->getComments($id);  
        } 
        
        return $data;
    }
    
    /**
     * 关联 user表
     * @return $this
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id')->setEagerlyType(1);
    }
    
    /**
     * 关联 community_comment表
     * @return type
     */
    public function comments()
    {
        return $this->hasMany('CommunityCommentModel', 'object_id');
    }

    /**
     * 获取某文章的所有评论及评论的用户信息
     * @param type $id 文章id
     * @return type
     */
    public function getComments($id) {
        $where = [
            'c.create_time' => ['>=', 0],
            'c.delete_time' => 0,
            'c.object_id' => $id
        ];

//        $join = [ 
//            ['__USER__ u', 'u.id = c.user_id', 'left'] 
//        ];
        $join = [
            ['__COMMUNITY_COMMENT__ cc', 'cc.id = c.parent_id', 'left']
        ];

//        $field = 'c.*, u.user_nickname, u.avatar '; 
        $field = 'c.*, cc.full_name as parent_name'; 
            
        $communityCommentModel = new CommunityCommentModel();
        $comments        = $communityCommentModel->alias('c')->field($field) 
            ->join($join)
            ->where($where) 
            ->order('c.accepted asc, c.create_time desc')
            ->select();
            
        return $comments;
    }
    
    /**
     * 关联分类表
     */
    public function categories()
    { 
        return $this->belongsToMany('CommunityCategoryModel', 'community_category_article', 'category_id', 'post_id');
    }
    
    /**
     * 关联标签表
     */
    public function tags() {
        return $this->belongsToMany('CommunityTagModel', 'community_tag_article', 'tag_id', 'post_id');
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getPostContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function setPostContentAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }

    /**
     * published_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setPublishedTimeAttr($value)
    {
        return strtotime($value);
    }
    
    /**
     * 前台添加文章
     * @param array $data 文章数据
     * @param array|string $categories 文章分类 id
     * @return $this
     */
    public function homeAddArticle($data, $categories)
    {
        $data['user_id'] = cmf_get_current_user_id() ? cmf_get_current_user_id() : cmf_get_current_admin_id();

        $this->allowField(true)->data($data, true)->isUpdate(false)->save();

        if (is_string($categories)) {
            $categories = explode(',', $categories);
        }

        $this->categories()->save($categories); 

        $keywords = explode(' ', $data['tags']);

        $this->addTags($keywords, $this->id);

        return $this;

    }

    /**
     * 后台管理添加文章
     * @param array $data 文章数据
     * @param array|string $categories 文章分类 id
     * @return $this
     */
    public function adminAddArticle($data, $categories)
    {
        $data['user_id'] = cmf_get_current_admin_id();

        if (!empty($data['more']['thumbnail'])) {
            $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
        }

        $this->allowField(true)->data($data, true)->isUpdate(false)->save();

        if (is_string($categories)) {
            $categories = explode(',', $categories);
        }

        $this->categories()->save($categories);

        $data['post_keywords'] = str_replace('，', ',', $data['post_keywords']);

        $keywords = explode(',', $data['post_keywords']);

        $this->addTags($keywords, $this->id);

        return $this;

    }

    /**
     * 后台管理编辑文章
     * @param array $data 文章数据
     * @param array|string $categories 文章分类 id
     * @return $this
     */
    public function adminEditArticle($data, $categories)
    {

        unset($data['user_id']);

        if (!empty($data['more']['thumbnail'])) {
            $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
        }

        $data['post_status'] = empty($data['post_status']) ? 0 : 1;
        $data['is_top']      = empty($data['is_top']) ? 0 : 1;
        $data['recommended'] = empty($data['recommended']) ? 0 : 1;

        $this->allowField(true)->isUpdate(true)->data($data, true)->save();

        if (is_string($categories)) {
            $categories = explode(',', $categories);
        }

        $oldCategoryIds        = $this->categories()->column('category_id');
        $sameCategoryIds       = array_intersect($categories, $oldCategoryIds);
        $needDeleteCategoryIds = array_diff($oldCategoryIds, $sameCategoryIds);
        $newCategoryIds        = array_diff($categories, $sameCategoryIds);

        if (!empty($needDeleteCategoryIds)) {
            $this->categories()->detach($needDeleteCategoryIds);
        }

        if (!empty($newCategoryIds)) {
            $this->categories()->attach(array_values($newCategoryIds));
        }


        $data['post_keywords'] = str_replace('，', ',', $data['post_keywords']);

        $keywords = explode(',', $data['post_keywords']);

        $this->addTags($keywords, $data['id']);

        return $this;

    }

    public function addTags($keywords, $articleId)
    {
        $portalTagModel = new CommunityTagModel();

        $tagIds = [];

        $data = [];

        if (!empty($keywords)) {

            $oldTagIds = Db::name('community_tag_article')->where('post_id', $articleId)->column('tag_id');

            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword)) {
                    $findTag = $portalTagModel->where('name', $keyword)->find();
                    if (empty($findTag)) {
                        $tagId = $portalTagModel->insertGetId([
                            'name' => $keyword
                        ]);
                    } else {
                        $tagId = $findTag['id'];
                    }

                    if (!in_array($tagId, $oldTagIds)) {
                        array_push($data, ['tag_id' => $tagId, 'post_id' => $articleId]);
                    }

                    array_push($tagIds, $tagId);

                }
            } 

            if (empty($tagIds) && !empty($oldTagIds)) {
                Db::name('community_tag_article')->where('post_id', $articleId)->delete();
            }

            $sameTagIds = array_intersect($oldTagIds, $tagIds);

            $shouldDeleteTagIds = array_diff($oldTagIds, $sameTagIds);

            if (!empty($shouldDeleteTagIds)) {
                Db::name('community_tag_article')->where(['post_id' => $articleId, 'tag_id' => ['in', $shouldDeleteTagIds]])->delete();
            }

            if (!empty($data)) {
                Db::name('community_tag_article')->insertAll($data);
            }


        } else {
            Db::name('community_tag_article')->where('post_id', $articleId)->delete();
        }
    }

    public function adminDeletePage($data)
    {

        if (isset($data['id'])) {
            $id = $data['id']; //获取删除id

            $res = $this->where(['id' => $id])->find();

            if ($res) {
                $res = json_decode(json_encode($res), true); //转换为数组

                $recycleData = [
                    'object_id'   => $res['id'],
                    'create_time' => time(),
                    'table_name'  => 'community_article#page',
                    'name'        => $res['post_title'],

                ];

                Db::startTrans(); //开启事务
                $transStatus = false;
                try {
                    Db::name('community_article')->where(['id' => $id])->update([
                        'delete_time' => time()
                    ]);
                    Db::name('recycle_bin')->insert($recycleData);

                    $transStatus = true;
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {

                    // 回滚事务
                    Db::rollback();
                }
                return $transStatus;


            } else {
                return false;
            }
        } elseif (isset($data['ids'])) {
            $ids = $data['ids'];

            $res = $this->where(['id' => ['in', $ids]])
                ->select();

            if ($res) {
                $res = json_decode(json_encode($res), true);
                foreach ($res as $key => $value) {
                    $recycleData[$key]['object_id']   = $value['id'];
                    $recycleData[$key]['create_time'] = time();
                    $recycleData[$key]['table_name']  = 'portal_post';
                    $recycleData[$key]['name']        = $value['post_title'];

                }

                Db::startTrans(); //开启事务
                $transStatus = false;
                try {
                    Db::name('community_article')->where(['id' => ['in', $ids]])
                        ->update([
                            'delete_time' => time()
                        ]);


                    Db::name('recycle_bin')->insertAll($recycleData);

                    $transStatus = true;
                    // 提交事务
                    Db::commit();

                } catch (\Exception $e) {

                    // 回滚事务
                    Db::rollback();


                }
                return $transStatus;


            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * 后台管理添加页面
     * @param array $data 页面数据
     * @return $this
     */
    public function adminAddPage($data)
    {
        $data['user_id'] = cmf_get_current_admin_id();

        if (!empty($data['more']['thumbnail'])) {
            $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
        }

        $data['post_status'] = empty($data['post_status']) ? 0 : 1;
        $data['post_type']   = 2;
        $this->allowField(true)->data($data, true)->save();

        return $this;

    }

    /**
     * 后台管理编辑页面
     * @param array $data 页面数据
     * @return $this
     */
    public function adminEditPage($data)
    {
        $data['user_id'] = cmf_get_current_admin_id();

        if (!empty($data['more']['thumbnail'])) {
            $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
        }

        $data['post_status'] = empty($data['post_status']) ? 0 : 1;
        $data['post_type']   = 2;
        $this->allowField(true)->isUpdate(true)->data($data, true)->save();

        $routeModel = new RouteModel();
        $routeModel->setRoute($data['post_alias'], 'community/Page/index', ['id' => $data['id']], 2, 5000);

        $routeModel->getRoutes(true);
        return $this;
    }
 
}