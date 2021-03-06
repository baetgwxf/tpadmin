<?php
// +----------------------------------------------------------------------
// | tpadmin [a web admin based ThinkPHP5]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 tianpian All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: tianpian <tianpian0805@gmail.com>
// +----------------------------------------------------------------------

//------------------------
// 角色模型
//-------------------------

namespace app\admin\model;

use think\Model;

class AdminRole extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
}