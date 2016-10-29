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
// 公共函数
//-------------------------

use think\Session;
use think\Response;
use think\Request;
use think\Url;

/**
 * CURLFILE 兼容性处理 php < 5.5
 * 一定不要修改、删除，否则 curl 可能无法上传文件
 */
if (!function_exists('curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '')
    {
        return "@$filename;filename="
        . ($postname ?: basename($filename))
        . ($mimetype ? ";type=$mimetype" : '');
    }
}

/**
 * flash message
 *
 * flash("?KEY") 判断是否存在flash message KEY 返回bool值
 * flash("KEY") 获取flash message，存在返回具体值，不存在返回null
 * flash("KEY","VALUE") 设置flash message
 * @param string $key
 * @param bool|string $value
 * @return bool|mixed|null
 */
function flash($key, $value = false)
{
    $prefix = 'flash_';
    // 判断是否存在flash message
    if ('?' == substr($key, 0, 1)) {
        return Session::has($prefix . substr($key, 1));
    } else {
        $flash_key = $prefix . $key;
        if (false === $value) {
            // 获取flash
            $ret = Session::pull($flash_key);

            return null === $ret ? null : unserialize($ret);
        } else {
            // 设置flash
            return Session::set($flash_key, serialize($value));
        }
    }
}

/**
 * 表格排序筛选
 * @param string $name  单元格名称
 * @param string $field 排序字段
 * @return string
 */
function sort_by($name, $field = '')
{
    $sort = Request::instance()->param('_sort');
    $param = Request::instance()->get();
    $param['_sort'] = ($sort == 'asc' ? 'desc' : 'asc');
    $param['_order'] = $field;
    $url = Url::build(Request::instance()->action(), $param);

    return Request::instance()->param('_order') == $field ?
        "<a href='{$url}' title='点击排序' class='sorting-box sorting-{$sort}'>{$name}</a>" :
        "<a href='{$url}' title='点击排序' class='sorting-box sorting'>{$name}</a>";
}

/**
 * 用于高亮搜索关键词
 * @param string $string 原文本
 * @param string $needle 关键词
 * @param string $class  span标签class名
 * @return mixed
 */
function high_light($string, $needle = '', $class = 'c-red')
{
    return $needle !== '' ? str_replace($needle, "<span class='{$class}'>" . $needle . "</span>", $string) : $string;
}

/**
 * 用于显示状态操作按钮
 * @param int $status        0|1|-1状态
 * @param int $id            对象id
 * @param string $field      字段，默认id
 * @param string $controller 默认当前控制器
 * @return string
 */
function show_status($status, $id, $field = 'id', $controller = '')
{
    $controller === '' && $controller = Request::instance()->controller();
    switch ($status) {
        // 恢复
        case 0 :
            $ret = '<a href="javascript:;" onclick="ajax_req(\'' . Url::build($controller . '/resume', [$field => $id]) . '\',{},change_status,[this,\'resume\'])" class="label label-success radius" title="点击恢复">恢复</a>';
            break;
        // 禁用
        case 1 :
            $ret = '<a href="javascript:;" onclick="ajax_req(\'' . Url::build($controller . '/forbid', [$field => $id]) . '\',{},change_status,[this,\'forbid\'])" class="label label-warning radius" title="点击禁用">禁用</a>';
            break;
        // 还原
        case -1 :
            $ret = '<a href="javascript:;" onclick="ajax_req(\'' . Url::build($controller . '/recycle', [$field => $id]) . '\')" class="label label-secondary radius" title="点击还原">还原</a>';
            break;
    }

    return $ret;
}

/**
 * 显示状态
 * @param int $status     0|1|-1
 * @param bool $imageShow true只显示图标|false只显示文字
 * @return string
 */
function get_status($status, $imageShow = true)
{
    switch ($status) {
        case 0 :
            $showText = '禁用';
            $showImg = '<i class="Hui-iconfont c-warning status" title="禁用">&#xe631;</i>';
            break;
        case -1 :
            $showText = '删除';
            $showImg = '<i class="Hui-iconfont c-danger status" title="删除">&#xe6e2;</i>';
            break;
        case 1 :
        default :
            $showText = '正常';
            $showImg = '<i class="Hui-iconfont c-success status" title="正常">&#xe615;</i>';

    }

    return ($imageShow === true) ? $showImg : $showText;
}

/**
 * 框架内部默认ajax返回
 * @param string $msg      提示信息
 * @param string $redirect 重定向类型 current|parent|''
 * @param string $alert    父层弹框信息
 * @param bool $close      是否关闭当前层
 * @param string $url      重定向地址
 * @param string $data     附加数据
 * @param int $code        错误码
 * @param array $extend    扩展数据
 */
function ajax_return_adv($msg = '操作成功', $redirect = 'parent', $alert = '', $close = false, $url = '', $data = '', $code = 0, $extend = [])
{
    $extend['opt'] = [
        'alert'    => $alert,
        'close'    => $close,
        'redirect' => $redirect,
        'url'      => $url,
    ];

    return ajax_return($data, $msg, $code, $extend);
}

/**
 * 返回错误json信息
 */
function ajax_return_adv_error($msg = '', $code = 1, $redirect = '', $alert = '', $close = false, $url = '', $data = '', $extend = [])
{
    return ajax_return_adv($msg, $alert, $close, $redirect, $url, $data, $code, $extend);
}

/**
 * ajax数据返回，规范格式
 * @param array $data   返回的数据，默认空数组
 * @param string $msg   信息
 * @param int $code     错误码，0-未出现错误|其他出现错误
 * @param array $extend 扩展数据
 */
function ajax_return($data = [], $msg = "", $code = 0, $extend = [])
{
    $ret = ["code" => $code, "msg" => $msg, "data" => $data];
    $ret = array_merge($ret, $extend);

    return Response::create($ret, 'json');
}

/**
 * 返回标准错误json信息
 */
function ajax_return_error($msg = "出现错误", $code = 1, $data = [], $extend = [])
{
    return ajax_return($data, $msg, $code, $extend);
}

/**
 * 从二维数组中取出自己要的KEY值
 * @param  array $arrData
 * @param string $key
 * @param $im true 返回逗号分隔
 * @return array
 */
function filter_value($arrData, $key, $im = false)
{
    $re = [];
    foreach ($arrData as $k => $v) {
        if (isset($v[$key])) $re[] = $v[$key];
    }
    if (!empty($re)) {
        $re = array_flip(array_flip($re));
        sort($re);
    }

    return $im ? implode(',', $re) : $re;
}

/**
 * 重设键，转为array(key=>array())
 * @param array $arr
 * @param string $key
 * @return array
 */
function reset_by_key($arr, $key)
{
    $re = [];
    foreach ($arr as $v) {
        $re[$v[$key]] = $v;
    }

    return $re;
}

/**
 * 节点遍历
 * @param $list
 * @param string $pk
 * @param string $pid
 * @param string $child
 * @param int $root
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = [];
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = [];
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }

    return $tree;
}

/**
 * 统一密码加密方式，如需变动直接修改此处
 * @param $password
 * @return string
 */
function password_hash_tp($password)
{
    return hash("md5", trim($password));
}

/**
 * 生成随机字符串
 * @param string $prefix
 * @return string
 */
function get_random($prefix = '')
{
    return $prefix . base_convert(time() * 1000, 10, 36) . "_" . base_convert(microtime(), 10, 36) . uniqid();
}

/**
 * 获取自定义配置
 * @param string|int $name 配置项的key或者value，传key返回value，传value返回key
 * @param string $conf
 * @param bool $key        传递的是否是配置键名，默认是，则返回配置信息
 * @return int|string
 */
function get_conf($name, $conf, $key = true)
{
    $arr = config("conf." . $conf);
    if ($key) return $arr[$name];
    foreach ($arr as $k => $v) {
        if ($v == $name) {
            return $k;
        }
    }
}


/**
 * 打印调试信息到前台
 * @param $log
 * @return mixed
 */
if (!function_exists('dump_log')) {
    function dump_log($log){
        \Conlose::config(config('socketlog'));
        return \Conlose::log($log);//自定义日志的样式，第三个参数为css样式;
    }
}