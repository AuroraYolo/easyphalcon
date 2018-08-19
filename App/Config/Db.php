<?php
namespace App\Config;

return [
    'db' => [
        // 是否记录执行的mysql语句
        'logged'           => true,
        // 记录执行时间超过0秒的mysql语句
        'max_execute_time' => 0,
        // 比较时间到小数点后几位
        'scale'            => 5,
    ]
];