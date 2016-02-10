<?php namespace Phlex\Deployer;

class Worker {

	protected $source;
	protected $destination;
	protected $mode;
	protected $path;

	function __construct($source, $destination, $mode, $pattern){

		if(substr($source,0,2) == '//') $source = \Phlex\Config::getInstance()->pathRoot.substr($source, 2);
		$this->source = $source.'/';

		if(substr($destination,0,2) == '//') $destination = \Phlex\Config::getInstance()->pathRoot.substr($destination, 2);
		$this->destination = $destination.'/';

		$this->mode = $mode;

		if(!is_array($pattern)) $pattern = array($pattern);
		$this->pattern = $pattern;
	}

	function __invoke(){
		$this->deploy(null);
	}

	protected function deploy($dir = null){

		if($dir == null){
			// TODO: purgálni, ha overwrite

		}else{
			// TODO: figyelni al-deployokra
		}

		$sourceDir = realpath($this->source.$dir).'/';
		$destinationDir = $this->destination.$dir.'/';
		if(!is_dir($destinationDir)) @mkdir($destinationDir, 0777, true);
		$destinationDir = realpath($destinationDir).'/';

		$directories = glob($sourceDir.'*',GLOB_ONLYDIR);
		foreach($directories as $directory){
			if(substr($dir,0,1) != '.') $this->deploy($dir.'/'.basename($directory));
		}

		foreach($this->pattern as $pattern){
			$files = glob($sourceDir.$pattern, GLOB_BRACE);
			foreach($files as $file){
				$file = basename($file);
				copy($sourceDir.$file, $destinationDir.$file);
			}
		}


		$files = glob($destinationDir . "*");
		if(!$files){
			@rmdir($destinationDir);
		}
	}

}