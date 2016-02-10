<?php namespace Phlex\Kraft\Parser;

use Phlex\Config\Environment;

class TemplateHandler {

	/**
	 * @var MicroParser
	 */
	protected $microParser;
	/**
	 * @var CustomTagParser
	 */
	protected $viewTagParser;

	function parse($source=null, $destination=null, $force = false){

		$env = Environment::instance();

		if($source == null) $source = $env['kraft']['template-source'];
		if($destination == null) $destination = $env['kraft']['template'];
		if(!$source or !$destination) return;
		$source = $source.'/';
		$destination = $destination.'/';

		$src = array_diff(scandir($source), array('.', '..'));
		if(!is_dir($destination)) mkdir($destination);
		$dest = array_diff(scandir($destination), array('.', '..'));
		$dest = array_fill_keys($dest, false);

		foreach($src as $item) {
			if (is_dir($source.$item)) {
				$this->parse($source.$item, $destination.$item, $force);
			}else if ($force || !file_exists($destination.$item) || filemtime($source.$item) > filemtime($destination.$item)){
				$this->parseFile($source.$item, $destination.$item);
			}
			$dest[$item] = true;
		}
		foreach($dest as $item=>$state) if(!$state){
			(is_dir($destination.$item)) ? static::delTree($destination.$item) : unlink($destination.$item);
		}
	}

	protected function parseFile($source, $destination){
		$file = file_get_contents($source);


		$microParser = new MicroParser();
		$customTagParser = new CustomTagParser();
		$directiveParser = new DirectiveParser();

		$file = $directiveParser->parse($file);
		$file = $microParser->parse($file);
		$file = $customTagParser->parse($file, $directiveParser->ctNamespace);

		file_put_contents($destination, $file);
	}

	public static function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? static::delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
}