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
use app\admin\model\RouteModel;
use tree\Tree;
use think\Db;
use app\community\model\CommunityTagModel;

class CommunityCommentModel extends Model
{ 
    protected $pk = 'id';
    protected $name = 'community_comment'; 
    
    // 时间格式
    protected $dateFormat = 'Y-m-d H:i:s';
    
    // 类型转换
    protected $type = [
        'more' => 'array',
        'delete_time' => 'timestamp',  
        'create_time' => 'timestamp',
    ];

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
 
 
}