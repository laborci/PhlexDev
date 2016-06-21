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
	if(substr($class, 0, 11) == 'EntityBase\\'){
		$class = substr($class, 11);
		$file = getenv('root').'.entitybase/'.str_replace('\\', '/', $class) . '.php';
		if(file_exists($file)) require_once $file;
	}elseif(substr($class, 0, 12) == 'EntityModel\\'){
		$class = substr($class, 12);
		$file = dirname( __FILE__ ).'/EntityModel/'.str_replace('\\', '/', $class) . '.php';
		if(file_exists($file)) require_once $file;
	}elseif(substr($class, 0, 17) == 'EntityRepository\\'){
		$class = substr($class, 17);
		$file = dirname( __FILE__ ).'/EntityRepository/'.str_replace('\\', '/', $class) . '.php';
		if(file_exists($file)) require_once $file;
	}elseif(substr($class, 0, 7) == 'Entity\\'){
		$class = substr($class, 7);
		$file = dirname( __FILE__ ).'/Entity/'.str_replace('\\', '/', $class) . '.php';
		if(file_exists($file)) require_once $file;
	}
});