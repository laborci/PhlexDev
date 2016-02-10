<?php namespace Phlex\Kraft\Response;

class JsonResponse extends Response{

	function answer(){
		header('Content-Type: application/json');
		parent::answer();
	}

	function render(){
		return json_encode($this->data);
	}
}