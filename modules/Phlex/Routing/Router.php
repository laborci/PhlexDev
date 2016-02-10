<?php namespace Phlex\Routing;

use Phlex\Request\Request;

class Router {

	private $request;

	const METHOD_POST = Request::METHOD_POST;
	const METHOD_GET = Request::METHOD_GET;
	const METHOD_DELETE = Request::METHOD_DELETE;
	const METHOD_PUT = Request::METHOD_PUT;
	const METHOD_ANY = 'ANY';

	private $method = Router::METHOD_GET;

	function __invoke($conditions, $handler = null) {
		if ($handler === null) {
			$method = $conditions;
			$this->method = $method;
		} else {
			$conditions = func_get_args();
			$handler = array_pop($conditions);
			$this->route($this->method, $conditions, $handler);
		}
	}

	function onMethod($method, \Closure $handler){
		$this($method);
		$handler($this);
	}
	function on_GET(\Closure $handler){$this->onMethod(Router::METHOD_GET, $handler);}
	function on_POST(\Closure $handler){$this->onMethod(Router::METHOD_POST, $handler);}
	function on_PUT(\Closure $handler){$this->onMethod(Router::METHOD_PUT, $handler);}
	function on_DELETE(\Closure $handler){$this->onMethod(Router::METHOD_DELETE, $handler);}
	function on_ANY(\Closure $handler){$this->onMethod(Router::METHOD_ANY, $handler);}

	function __construct(Request $request){
		$this->request = $request;
	}

	public function get($conditions, $handler){
		$conditions = func_get_args();
		$handler = array_pop($conditions);
		$this->route(Router::METHOD_GET, $conditions, $handler);
		return $this;
	}
	public function post($conditions, $handler){
		$conditions = func_get_args();
		$handler = array_pop($conditions);
		$this->route(Router::METHOD_POST, $conditions, $handler);
		return $this;
	}
	public function delete($conditions, $handler){
		$conditions = func_get_args();
		$handler = array_pop($conditions);
		$this->route(Router::METHOD_DELETE, $conditions, $handler);
		return $this;
	}
	public function put($conditions, $handler){
		$conditions = func_get_args();
		$handler = array_pop($conditions);
		$this->route(Router::METHOD_PUT, $conditions, $handler);
		return $this;
	}
	public function any($conditions, $handler){
		$conditions = func_get_args();
		$handler = array_pop($conditions);
		$this->route(Router::METHOD_ANY, $conditions, $handler);
		return $this;
	}

	protected function route($method, array $conditions, $handler){
		if($method == Router::METHOD_ANY) $methods = array(Request::METHOD_PUT, Request::METHOD_DELETE, Request::METHOD_GET, Request::METHOD_POST);
		elseif(is_array($method)) $methods = $method;
		else $methods = array($method);

		if(!in_array($this->request->method, $methods)) return false;
		if(!$this->test($conditions)) return false;
		$response = $handler();
		if($response === false) return false;
		$response->answer();
		die();
	}

	protected function test($conditions){
		foreach($conditions as $condition){

			if (
				is_string($condition) and (
					$condition[0] == '/' ?
						fnmatch($condition, $this->request->path) :
						preg_match($condition, $this->request->path)
				) or (
					is_callable($condition) && $condition($this->request)
				)
			) return true;

			/*
			if(is_string($condition)){
				if($condition[0] == '/'){
					if(fnmatch($condition, $this->request->path)) return true;
				}
				else if (preg_match($condition, $this->request->path)) return true;
			}
			if(is_callable($condition) && $condition($this->request)) return true;
			*/
		}
		return false;
	}
}