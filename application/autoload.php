<?php

spl_autoload_register( function($class){
	if(substr($class, 0, 4) == 'App\\'){
		$class = substr($class, 4);
		$file = dirname( __FILE__ ).'/App/'.str_replace('\\', '/', $class) . '.php';
		if(file_exists($file)) require_once $file;
	}
});

spl_autoload_register( function($class){
	if(substr($class, 0, 10) == 'CustomTag\\'){
		$class = substr($class, 10);
		$file = dirname( __FILE__ ).'/CustomTag/'.str_replace('\\', '/', $class) . '.php';
		if(file_exists($file)) require_once $file;
	}
});

spl_autoload_register( function($class){
	$file = dirname(__FILE__).'/entities/'.$class.'.php';
	if(file_exists($file)) require_once $file;
});

spl_autoload_register( function($class){
	$file = dirname(__FILE__).'/.entityModels/'.$class.'.php';
	if(file_exists($file)) require_once $file;
});