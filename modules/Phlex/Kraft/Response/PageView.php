<?php namespace Phlex\Kraft\Response;

class PageView extends HtmlView{

	protected $js = array();
	protected $css = array();
	protected $serverVars = array();

	/**
	 * @var static
	 */
	static protected $instance;

	public static function factory($template = null, $data = array()) {
		if(static::$instance !== null) trigger_error('Multiple PageResponse created', E_USER_ERROR);
		static::$instance = parent::factory($template, $data);
		return static::$instance;
	}


	/**
	 * @return PageView
	 */
	public function getRoot() {
		return $this;
	}


	static public function addServerVar($key, $value){
		if(static::$instance === null) trigger_error('addServerVar called while page not defined',E_USER_ERROR);
		static::$instance->serverVars[$key] = $value;
	}
	static function addJSInclude($src){
		if(static::$instance === null) trigger_error('addJSInclude called while page not defined',E_USER_ERROR);
		static::$instance->js[] = $src;
	}
	static function addCSSInclude($src){
		if(static::$instance === null) trigger_error('addCSSInclude called while page not defined',E_USER_ERROR);
		static::$instance->css[] = $src;
	}



	protected function importJS(){
		$ret = '';
		$sources = array_unique($this->js);
		foreach($sources as $source){
			$ret .= '<script src="'.$source.'"></script>'."\n";
		}
		return $ret;
	}
	protected function importCSS(){
		$ret = '';
		$sources = array_unique($this->css);
		foreach($sources as $source){
			$ret .= '<link rel="stylesheet" href="'.$source.'" />'."\n";
		}
		return $ret;
	}
	protected function renderServerVars(){
		$serverVars = $this->serverVars;
		if(count($serverVars)){
			return '<script>var serverVars = '.json_encode($serverVars).';</script>'."\n";
		}else{
			return '<script>var serverVars = {};</script>'."\n";
		}
	}

	function render($template = null) {
		$rendered = parent::render($template);
		$rendered = str_replace('@import-js', $this->importJS(), $rendered);
		$rendered = str_replace('@import-css', $this->importCSS(), $rendered);
		$rendered = str_replace('@servervars', $this->renderServerVars(), $rendered);
		return $rendered;
	}

}