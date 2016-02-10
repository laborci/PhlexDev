<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 09/02/16
 * Time: 06:35
 */

namespace Phlex\Kraft\Parser;


class DirectiveParser {

	public $ctNamespace = 'CustomTag';

	public function parse($string){
		$lines = explode("\n", $string);
		foreach($lines as $idx=>$line){
			$line = trim($line);
			if(false and $line == "@import-scripts"){
				$lines[$idx] = '<?php \Phlex\Kraft\Response\PageResponse::importJS(); ?>';
			}elseif(false and $line == "@import-styles"){
				$lines[$idx] = '<?php \Phlex\Kraft\Response\PageResponse::importCSS(); ?>';
			}elseif(substr($line,0,7) == "@style:"){
				$css = trim(substr($line,7));
				$lines[$idx] = '<?php \Phlex\Kraft\Response\PageResponse::addCSSInclude("'.$css.'"); ?>';
			}elseif(substr($line,0,8) == "@script:"){
				$js = trim(substr($line,8));
				$lines[$idx] = '<?php \Phlex\Kraft\Response\PageResponse::addJSInclude("'.$js.'"); ?>';
			}elseif(substr($line,0,6) == "@ctns:"){
				$ctns = trim(substr($line,6));
				$lines[$idx] = '';
				$this->ctNamespace = $ctns;
			}if(false and $line == "@servervars"){
				$lines[$idx] = '<?php \Phlex\Kraft\Response\PageResponse::renderServerVars(); ?>';

			}
			if(!$line)unset($lines[$idx]);
		}

		return trim(join("\n", $lines));
	}
}
