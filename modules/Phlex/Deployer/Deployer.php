<?php namespace Phlex\Deployer;

class Deployer{

	protected $deployPoints = array();

	function __construct(){
	}

	function __invoke($config = null){
		if(is_string($config)){
			$this->autoDeploy($config);
		}elseif(is_array($config)){
			$this->deploy($config);
		}
	}

	protected function autoDeploy($root){
		$this->scanDeployPoints($root.'/');
		foreach($this->deployPoints as $config){
			$this->deploy($config);
		}
	}

	protected function deploy($config){
		$worker = new Worker($config['source'], $config['destination'], $config['mode'], $config['pattern']);
		$worker();
	}

	protected function scanDeployPoints($dir){
		$dir = realpath($dir).'/';

		if(file_exists($dir.'/.deploy.json')){
			$cnf = json_decode(file_get_contents($dir.'.deploy.json'), true);
			if($cnf !== array_values($cnf)){
				if(!isset($cnf['source'])) $cnf['source'] = $dir;
				$this->deployPoints[] = $cnf;
			}else{
				foreach($cnf as $c){
					if(!isset($c['source'])) $c['source'] = $dir;
					$this->deployPoints[] = $c;
				}
			}
			return;
		}

		$entries = array_diff(scandir($dir), array('..', '.'));
		foreach($entries as $entry){
			$cDir = $dir.'/'.$entry;
			if(substr($entry,0,1) != '.' and is_dir($cDir)){
				$this->scanDeployPoints($cDir);
			}
		}
	}
}