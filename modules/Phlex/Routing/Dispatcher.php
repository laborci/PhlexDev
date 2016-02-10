<?php namespace Phlex\Routing {

	use Phlex\Request\Request;

	class Dispatcher{

		private $domain;

		function __construct(Request $request){
			$this->domain = $request->host;
		}

		function __invoke($globPatterns, \Closure $handler){
			$this->match($globPatterns, $handler);
		}

		/**
		 * @param string $globPatterns (1 or many arguments)
		 * @param \Closure $handler (last argument)
		 */
		function match($globPatterns, \Closure $handler){
			/** @var \Closure $handler */

			$args = func_get_args();
			$handler = array_pop($args);
			$conds = $args;

			foreach ($conds as $cond) {
				if(fnmatch($cond, $this->domain)){
					$handler();
					die();
				}
			}
		}
	}
}

