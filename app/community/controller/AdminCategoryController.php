<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\community\controller;

use cmf\controller\AdminBaseController;
use app\community\model\CommunityCategoryModel;
use app\admin\model\ThemeModel;
use app\admin\model\RouteModel;
use think\Db;

class AdminCategoryController extends AdminBaseController {

    /**
     * 社区分类列表
     * @adminMenu(
     *     'name'   => '社区分类管理',
     *     'parent' => 'community/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '社区分类列表',
     *     'param'  => ''
     * )
     */
    public function index() { 
        $communityCategoryModel = new CommunityCategoryModel();
        $categoryTree        = $communityCategoryModel->adminCategoryTableTree();

        $this->assign('category_tree', $categoryTree);
        return $this->fetch(); 
    }
    
    /**
     * 社区分类排序
     * @adminMenu(
     *     'name'   => '社区分类排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '社区分类排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('community_category'));
        $this->success("排序更新成功！", '');
    }
    
    /**
     * 添加社区分类
     * @adminMenu(
     *     'name'   => '添加社区分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加社区分类',
     *     'param'  => ''
     * )
     */
    public function add() {
        $parentId            = $this->request->param('parent', 0, 'intval');
        $communityCategoryModel = new CommunityCategoryModel();
        $categoriesTree      = $communityCategoryModel->adminCategoryTree($parentId);

        $themeModel        = new ThemeModel();
        $listThemeFiles    = $themeModel->getActionThemeFiles('community/List/index');
        $articleThemeFiles = $themeModel->getActionThemeFiles('community/Article/index');

        $this->assign('list_theme_files', $listThemeFiles);
        $this->assign('article_theme_files', $articleThemeFiles);
        $this->assign('categories_tree', $categoriesTree);
        return $this->fetch();
    }

    /**
     * 添加社区分类提交
     * @adminMenu(
     *     'name'   => '添加社区分类提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加社区分类提交',
     *     'param'  => ''
     * )
     */
    public function addPost() {
        $communityCategoryModel = new CommunityCategoryModel();

        $data = $this->request->param();

        $result = $this->validate($data, 'CommunityCategory');

        if ($result !== true) {
            $this->error($result);
        }

        $result = $communityCategoryModel->addCategory($data);

        if ($result === false) {
            $this->error('添加失败!');
        }

        $this->success('添加成功!', url('AdminCategory/index'));
    }
    
    /**
     * 删除社区分类
     * @adminMenu(
     *     'name'   => '删除社区分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除社区分类',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $communityCategoryModel = new CommunityCategoryModel();
        $id                  = $this->request->param('id');
        //获取删除的内容
        $findCategory = $communityCategoryModel->where('id', $id)->find();

        if (empty($findCategory)) {
            $this->error('分类不存在!');
        }

        $categoryChildrenCount = $communityCategoryModel->where('parent_id', $id)->count();

        if ($categoryChildrenCount > 0) {
            $this->error('此分类有子类无法删除!');
        }

        $categoryPostCount = db('community_category_article')->where('category_id', $id)->count();

        if ($categoryPostCount > 0) {
            $this->error('此分类有文章无法删除!');
        }

        $data   = [
            'object_id'   => $findCategory['id'],
            'create_time' => time(),
            'table_name'  => 'community_category',
            'name'        => $findCategory['name']
        ];
        $result = $communityCategoryModel
            ->where('id', $id)
            ->update(['delete_time' => time()]);
        if ($result) {
            db('recycleBin')->insert($data);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
        }
    }
    
    /**
     * 编辑社区分类
     * @adminMenu(
     *     'name'   => '编辑社区分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑社区分类',
     *     'param'  => ''
     * )
     */
    public function edit() {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $category = CommunityCategoryModel::get($id)->toArray();

            $communityCategoryModel = new CommunityCategoryModel();
            $categoriesTree      = $communityCategoryModel->adminCategoryTree($category['parent_id'], $id);

            $themeModel        = new ThemeModel();
            $listThemeFiles    = $themeModel->getActionThemeFiles('community/List/index');
            $articleThemeFiles = $themeModel->getActionThemeFiles('community/Article/index');

             $routeModel = new RouteModel();
             $alias      = $routeModel->getUrl('community/List/index', ['id' => $id]);
             $category['alias'] = $alias;
            
            $this->assign($category);
            $this->assign('list_theme_files', $listThemeFiles);
            $this->assign('article_theme_files', $articleThemeFiles);
            $this->assign('categories_tree', $categoriesTree);
            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }
    }
     
    /**
     * 编辑社区分类提交
     * @adminMenu(
     *     'name'   => '编辑社区分类提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑社区分类提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data = $this->request->param();
 
        $result = $this->validate($data, 'CommunityCategory');

        if ($result !== true) {
            $this->error($result);
        }

        $communityCategoryModel = new CommunityCategoryModel();

        $result = $communityCategoryModel->editCategory($data);

        if ($result === false) {
            $this->error('保存失败!', url('AdminCategory/index'));
        }

        $this->success('保存成功!', url('AdminCategory/index'));
    }
    
    /**
     * 文章分类选择对话框
     * @adminMenu(
     *     'name'   => '文章分类选择对话框',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章分类选择对话框',
     *     'param'  => ''
     * )
     */
    public function select()
    {
        $ids                 = $this->request->param('ids');
        $selectedIds         = explode(',', $ids);
        $communityCategoryModel = new CommunityCategoryModel();

        $tpl = <<<tpl
<tr class='data-item-tr'>
    <td>
        <input type='checkbox' class='js-check' data-yid='js-check-y' data-xid='js-check-x' name='ids[]'
               value='\$id' data-name='\$name' \$checked>
    </td>
    <td>\$id</td>
    <td>\$spacer <a href='\$url' target='_blank'>\$name</a></td>
</tr>
tpl;

        $categoryTree = $communityCategoryModel->adminCategoryTableTree($selectedIds, $tpl);

        $where      = ['delete_time' => 0];
        $categories = $communityCategoryModel->where($where)->select();

        $this->assign('categories', $categories);
        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);
        return $this->fetch();
    }
}
