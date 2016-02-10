<?php namespace Phlex\Request {


	class PathData extends Data {

		private $pathSegments;

		function __construct($pathSegments){
			parent::__construct($pathSegments);
			$this->pathSegments = $pathSegments;
		}

		function map($keys){
			if(!is_array($keys)) $keys = func_get_args();

			$count = (count($this->pathSegments) < count($keys)) ? count($this->pathSegments) : count($keys);

			for($i=0; $i<$count; $i++) if($keys[$i]){
				$this[$keys[$i]] = $this->pathSegments[$i];
			}
			return $this;
		}
	}
}
