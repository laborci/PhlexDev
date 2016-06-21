<?php namespace Phlex\Kraft\Response;

class HtmlView extends Response{

	/**
	 * @var HtmlView
	 */
	protected $parent;
	protected $template;
	protected $renderer;
	protected $root = null;

	function __get($name){
		switch($name){
			case 'root': return $this->getRoot(); break;
		}
		return null;
	}

	/**
	 * @return PageView
	 */
	public function getRoot(){
		return $this->parent->getRoot();
	}

	/**
	 * @param null  $template
	 * @param array $data
	 *
	 * @return static
	 */
	public static function factory($template = null, $data = array()) {
		$response = new static();
		$response->template = $template;
		$response->data = $data;
		return $response;
	}

	function setTemplate($template){
		$this->template = $template;
		return $this;
	}

	function setRenderer($renderer){
		$this->renderer = $renderer;
		return $this;
	}

	function render($template = null){
		ob_start();
		if($this->renderer){
			call_user_func($this->renderer, $this);
		} else {
			if ($template == null) $template = $this->template;
			$env = \Phlex\Env\Environment::instance();
			$file = getenv('root').$env['kraft']['template-path'] . $template . '.' . $env['kraft']['ext'];
			if (file_exists($file)) include $file;
			else trigger_error('Template not found: "' . $file . '""', E_USER_ERROR);
		}

		return ob_get_clean();
	}

}