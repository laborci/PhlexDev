<?php namespace Phlex\Kraft\Parser;

class CustomTagParser extends Parser{

	protected $errorPrefix = 'KRAFT ViewTag ERROR';
	protected $ctns;


	public function parse($source, $ctns = '.CustomTag'){
		$this->ctns = $ctns;
		$tags = $this->findTagsByNameSpace('ct',$source);

		foreach($tags as $tag){
			if($tag->type == 'close'){
				$objectCode = $this->createCustomTagCloseCall();

			}else{
				$objectCode = $this->createCustomTagCall($tag->tag, $tag->attributes);
			}
			$source = str_replace($tag->source, $objectCode, $source);
		}
		return $source;
	}

	protected function createCustomTagCall($tag, $attributes){
		if($tag[0] != '.') $tag = $this->ctns.'.'.$tag;
		$class = str_replace('.','\\',$tag);
		$attributes = $this->parseKraftAttributes($attributes);
		return '<?php '.$class.'::factory(array('.$attributes.'), $this)->renderTag(); ?>';
	}

	protected function createCustomTagCloseCall(){
		return '<?php Phlex\Kraft\CustomTag::pullFromStack()->renderCloseTag(); ?>';
	}

	protected function parseKraftAttributes($attributes){
		$pairs = array();
		foreach($attributes as $key=>$value){
			$pair = $this->parseKraftAttribute($key, $value);
			$pairs[] = '"'.$pair['key'].'" => '.$pair['value'];
		}
		return join(', ', $pairs);
	}
}
