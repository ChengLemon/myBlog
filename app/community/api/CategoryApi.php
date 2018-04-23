<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\community\api; 

use app\community\model\CommunityCategoryModel;

class CategoryApi {

    /**
     * 分类列表 用于模板设计
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
        $cateModel = new CommunityCategoryModel();

        // $where = ['delete_time' => 0];

        if (!empty($param['keyword'])) {
            $where['name'] = [ 'like', "%{$param['keyword']}%"];
        }

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        return $cateModel->where($where)->select();
    }
        
    public function nav() {
        $cateModel = new CommunityCategoryModel();
        $where = [ 'delete_time' => 0 ];
        $categories = $cateModel->where($where)->select();

        $return = [
            'rule'  => [
                'action' => 'community/Category/index',
                'param'  => [
                    'id' => 'id'
                ]
            ],//url规则
            'items' => $categories //每个子项item里必须包括id,name,如果想表示层级关系请加上 parent_id
        ];

        return $return;
        
    }

}
