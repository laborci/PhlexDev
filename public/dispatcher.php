<?php
ob_start();

//$GLOBALS['__time'] = microtime(true); register_shutdown_function(function(){ echo('X-Run Time: '.(microtime(true) - $GLOBALS['__time'])); });

require_once '../modules/autoload.php';
require_once '../application/autoload.php';

$env = \Phlex\Env\Environment::instance();

if($env['dev-mode']){
	// system('php '.$env['root'].'phlex.php config -q');
	system('php '.$env['root'].'phlex.php build -q -f');
	\Phlex\Debug::setup();
}

$dispatcher = new Phlex\Routing\Dispatcher(\Phlex\Request\Request::getCurrent());

$dispatcher('www.*', function(){ $app = new \App\www\App(); $app->route(); });
$dispatcher('admin.*', function(){ /*dummy*/ });
$dispatcher('*', function(){
	header('Request Method: GET');
	header('Location:'.'http://www.'.$_SERVER['SERVER_NAME']);
	die();
});
