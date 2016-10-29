<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/29
 * Time: 7:46
 */
return
[
        'host' => 'slog.thinkphp.cn',//websocket服务器地址，默认localhost
        'optimize' => false,//是否显示利于优化的参数，如果运行时间，消耗内存等，默认为false
        'show_included_files' => false,//是否显示本次程序运行加载了哪些文件，默认为false
        'error_handler' => false,//是否接管程序错误，将程序错误显示在console中，默认为false
        //日志强制记录到配置的client_id
        'force_client_ids' => ['slog_d192bd'],
        //限制允许读取日志的client_id
        'allow_client_ids' => ['slog_d192bd'],
];