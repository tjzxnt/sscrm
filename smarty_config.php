<?php
	return array(
        'enabled' => TRUE, // 开启Smarty
        'config' =>array(
            'template_dir' => APP_PATH . '/tpl', // 模板存放的目录
            'compile_dir' => APP_PATH . '/tmp', // 编译的临时目录
            'cache_dir' => APP_PATH . '/tmp', // 缓存的临时目录
            'left_delimiter' => '<{',  // smarty左限定符
            'right_delimiter' => '}>', // smarty右限定符
        ),
        'engine_name' => 'Smarty', // 模板引擎的类名称，默认为Smarty
	    'engine_path' => SP_PATH . '/Drivers/Smarty/Smarty.class.php',
	    'auto_display' => TRUE, // 是否使用自动输出模板功能
	    'auto_display_sep' => '/', // 自动输出模板的拼装模式，/为按目录方式拼装，_为按下划线方式，以此类推
		'auto_display_suffix' => '.html', // 自动输出模板的后缀名
		'debugging' => false, // smarty是否开启调试
	);
?>