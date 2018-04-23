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

class UserModel extends Model
{
    protected $pk = 'id';
    
    // 时间格式
    protected $dateFormat = 'Y-m-d H:i:s';
    
    // 类型转换
    protected $type = [
        'more' => 'array',
        'last_login_time' => 'timestamp',  
        'create_time' => 'timestamp',
        'delete_time' => 'timestamp'
    ];

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 回帖周榜
     * 用户头像、昵称、id
     */
    public function maxComments($nums = 0, $limit = 20) {
        $startTime = mktime(0, 0 , 0, date("m"), date("d")-date("w")+1, date("Y"));
        $endTime = time();
        
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0, 
            'b.delete_time' => 0,
            'b.create_time' => [['>= time', $startTime], ['<= time', $endTime]] // 一周
        ];   
        
        $join = [ 
            ['__COMMUNITY_COMMENT__ b', 'b.user_id = a.id', 'left']
        ];

        $field = 'a.*,count(b.id) as comms'; 
 
        $list = $this->alias('a')->field($field)
            ->join($join) 
            ->where($where)
            ->order('b.create_time', 'DESC')
            ->limit($limit)
            ->group('a.id')   
            ->select();  

        return $list;
    }

}