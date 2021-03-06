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
// 自定义异常处理
//-------------------------

use think\exception\Handle;
use think\exception\HttpException;
use think\Request;

class TpException extends Handle
{
    public function render(\Exception $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            http_response_code($statusCode);
        }
        //可以在此交由系统处理
        if (Request::instance()->isAjax()) {
            $error_code = $this->getCode($e) ?: 1;
            return ajax_return_adv_error($this->getMessage($e), $error_code);
        }
        return parent::render($e);
    }

}
