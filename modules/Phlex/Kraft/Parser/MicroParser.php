<?php namespace Phlex\Kraft\Parser;

class MicroParser extends Parser{

	protected $tagDelimeters;
	protected $stateStack = array();
	protected $eachStack = array();
	protected $errorPrefix = 'KRAFT Micro ERROR';

	const STATE_IF = 1;
	const STATE_ELSEIF = 2;
	const STATE_ELSE = 3;
	const STATE_EACHAS = 4;
	const STATE_EACH = 5;
	const STATE_FOR = 6;
	const STATE_AS = 7;
	const STATE_COMPARE_CASE = 8;
	const STATE_COMPARE_DEFAULT = 9;


	function __construct($delimeters = array('{','}')){
		$this->tagDelimeters = $delimeters;
	}

	function parse($template_str){

		// Az értelmező ki-be kapcsolása
		$template_str = str_replace($this->tagDelimeters[0].'OFF'.$this->tagDelimeters[1], '<script microphp="off">', $template_str);
		$template_str = str_replace($this->tagDelimeters[0].'ON'.$this->tagDelimeters[1], '</script><!--microphp="on"-->', $template_str);

		// BLOCK COMMENT
		$num_of_comment_tags = preg_match_all('('.preg_quote($this->tagDelimeters[0]).'\/\*'.preg_quote($this->tagDelimeters[1]).'.*?'.preg_quote($this->tagDelimeters[0]).'\*\/'.preg_quote($this->tagDelimeters[1]).')msi', $template_str, $matches);
		for($i=0 ; $i<$num_of_comment_tags; $i++) $template_str = str_replace($matches[0][$i], '', $template_str);

		// Style és Script tag-ek törlése a kódból
		$template_str_work = preg_replace(
			array(
				'@<style[^>]*?>.*?</style>@siu',
				'@<script[^>]*?>.*?</script>@siu',
			),
			array('<?php //dummy ?>', '<?php //dummy ?>'),
			$template_str);
		$tokens = token_get_all($template_str_work);
		// valamiért a tokenizer a T_INLINE_HTML
		// szegmenseket szétvagdossa, ezeket össze kell fésülni
		$index = 0;
		$token_array = array('');
		foreach($tokens as $token){
			if($token[0] == T_INLINE_HTML)$token_array[$index] .=  $token[1];
			else{
				if($token_array[$index] != '')$index++;
				$token_array[$index] = '';
			}
		}

		foreach($token_array as $key => $token){
			$html_segment = $token;
			$num_of_tags = preg_match_all('('.preg_quote($this->tagDelimeters[0]).'(.+?)'.preg_quote($this->tagDelimeters[1]).')', $html_segment, $tags);

			for($i = 0; $i<$num_of_tags; $i++){

				$tag = trim($tags[1][$i]);

				($r = $this->tag_comment($tag)) or
				($r = $this->tag_end($tag)) or
				($r = $this->tag_if($tag)) or
				($r = $this->tag_elseif($tag)) or
				($r = $this->tag_else($tag)) or
				($r = $this->tag_echo($tag)) or
				($r = $this->tag_echo_var($tag)) or
				($r = $this->tag_each_as($tag)) or
				($r = $this->tag_each($tag)) or
				($r = $this->tag_as($tag)) or
				($r = $this->tag_for($tag)) or
				($r = $this->tag_compare($tag)) or
				($r = $this->tag_compare_to($tag)) or
				($r = $this->tag_compare_default($tag)) or
				false
				;

				if($r !== false){
					if($r === true) $r = '';
					$html_segment = str_replace($tags[0][$i], $r, $html_segment);
				}
			}

			$template_str = self::strReplaceFirst($token, $html_segment, $template_str);
		}

		$template_str = str_replace('<script microphp="off">', '', $template_str);
		$template_str = str_replace('</script><!--microphp="on"-->', '', $template_str);
		return $template_str;
	}
	function SSNotEmpty(){
		return (bool) count($this->stateStack);
	}
	function SSCurrent($states){
		return in_array(end($this->stateStack), func_get_args());
	}
	function SSHas($state){
		return in_array($state, $this->stateStack);
	}

	function SSPush($state){
		array_push($this->stateStack, $state);
	}
	function SSPop(){
		array_pop($this->stateStack);
	}
	function SSReplace($state){
		$this->SSPop();
		$this->SSPush($state);
	}

	private static function strReplaceFirst($search, $replace, $subject) {
		if(!$search)return $subject;
		$pos = strpos($subject, $search);
		if ($pos !== false) $subject = substr_replace($subject, $replace, $pos, strlen($search));
		return $subject;
	}




	/* END */

	function tag_end($tag){
		if($tag == '.' or $tag == 'end'){
			if(!$this->SSNotEmpty()) $this->error('Unexpected [BLOCK CLOSE]', E_USER_WARNING);
			$this->SSPop();
			return '<?php } ?>';
		}
		return false;
	}

	/* COMMENT */

	function tag_comment($tag){
		$firstLetter = substr($tag,0,1);
		if($firstLetter == '#'){
			return true;
		}
		return false;
	}

	/* IF */

	function tag_if($tag){
		$firstLetter = substr($tag,0,1);
		if($firstLetter == '?'){
			$this->SSPush(self::STATE_IF);
			if(substr($tag,1,1) == '?'){
				return '<?php if( isset('.$this->parseVar(substr($tag,2)).') ) {?> ';
			}else return '<?php if( '.trim(substr($tag,1)).' ) {?> ';
		}
		return false;
	}

	/* ELSE IF */

	function tag_elseif($tag){
		$firstLetter = substr($tag,0,1);
		if($firstLetter == ':' and strlen($tag)>1){
			if(!$this->SSCurrent(static::STATE_IF, static::STATE_ELSEIF)) $this->error('Unexpected [ELSE IF]', E_USER_WARNING);
			$this->SSReplace(self::STATE_ELSEIF);
			if(substr($tag,1,1) == '?'){
				return '<?php }else if( '.$this->parseVar(substr($tag,2)).' ) {?> ';
			}else return '<?php } else if( '.trim(substr($tag,1)).' ) {?> ';
		}
		return false;
	}

	/* ELSE */

	function tag_else($tag){
		$firstLetter = substr($tag,0,1);
		if($firstLetter == ':'){
			if(!$this->SSCurrent(static::STATE_IF, static::STATE_ELSEIF)) $this->error('Unexpected [ELSE]', E_USER_WARNING);
			$this->SSReplace(self::STATE_ELSE);
			return '<?php } else {?> ';
		}
		return false;
	}

	/* ECHO VAR */

	function tag_echo_var($tag){
		$firstLetter = substr($tag,0,1);
		if(($firstLetter == '@' or $firstLetter == '.' or $firstLetter == '$') and strlen($tag)>1){
			$var = $this->parseVar($tag);
			return '<?php echo '.$var.';?>';
		}
		return false;
	}

	/* ECHO */

	function tag_echo($tag){
		$firstLetter = substr($tag,0,1);
		if(($firstLetter == '=') and strlen($tag)>1){
			return '<?php echo '.substr($tag,1).';?>';
		}
		return false;
	}

	/* EACH AS */

	function tag_each_as($tag){
		if(preg_match("(^each\s+(.*?)\s+as\s+((.*?)(\s+at\s+(.*?))?)(\s+count\s+((.*?)(\s+from\s+([0-9]*))?))?$)", $tag, $matches)){
			$this->SSPush(self::STATE_EACHAS);
			// 1: array; 3: value; 5: key; 8: index; 10: from
			$array = $this->parseVar($matches[1]);
			$value = ($matches[5]?$matches[5].'=>':'').$matches[3];
			$index = array_key_exists(8, $matches) ? $matches[8] : null;
			$from = array_key_exists(10, $matches) ? $matches[10] : 0;
			return '<?php '.($index?$index.'='.($from-1).'; ':'').'if(is_array('.$array.') and count('.$array.')) foreach('.$array.' as '.$value.'){'.($index?' '.$index.'++;':'').' ?> ';
		}
		return false;
	}

	/* EACH */

	function tag_each($tag){
		if(preg_match("(^each\s+(.*?)$)", $tag, $matches) and !preg_match("(^each\s+(.*?)(\s+as\s+.*?)$)", $tag)){
			$this->SSPush(self::STATE_EACH);
			// 1: array;
			$array = $this->parseVar($matches[1]);
			array_push($this->eachStack, $array);
			return '<?php if(is_array('.$array.') and count('.$array.')) { ?> ';
		}
		return false;
	}

	/* AS */

	function tag_as($tag){
		if(preg_match("(^as\s+((.*?)(\s+at\s+(.*?))?)(\s+count\s+((.*?)(\s+from\s+([0-9]*))?))?$)", $tag, $matches)){

			if(!$this->SSHas(static::STATE_EACH) or !count($this->eachStack)) $this->error('Unexpected [AS]', E_USER_WARNING);
			$this->SSPush(self::STATE_AS);

			// 2: value; 4: key; 7: index; 9: from
			$array = array_pop($this->eachStack);
			$value = ($matches[4]?$matches[4].'=>':'').$matches[2];
			$index = isset($matches[7])?$matches[7]:null;
			$from = isset($matches[9])?$matches[9]:0;
			return '<?php '.($index?$index.'='.($from-1).'; ':'').' foreach('.$array.' as '.$value.'){'.($index?' '.$index.'++;':'').' ?> ';
		}
		return false;
	}

	/* FOR */

	function tag_for($tag){
		if(preg_match('(for\s+(.*?)$)', $tag, $matches)){
			$this->SSPush(self::STATE_FOR);
			return '<?php for('.$matches[1].'){?>';
		}
		return false;
	}

	/* COMPARE */

	function tag_compare($tag){
		if(preg_match('(compare\s+(.*?)\s+to\s+(.*?)$)', $tag, $matches)){
			$this->SSPush(self::STATE_COMPARE_CASE);
			return '<?php switch('.$matches[1].'){ case '.$matches[2].': ?>';
		}
		return false;
	}

	/* COMPARE TO */

	function tag_compare_to($tag){
		if(preg_match('(to\s+(.*?)$)', $tag, $matches)){
			if(!$this->SSCurrent(static::STATE_COMPARE_CASE)) $this->error('Unexpected [TO]', E_USER_WARNING);
			return '<?php break; case '.$matches[1].': ?>';
		}
		return false;
	}

	/* COMPARE DEFAULT */

	function tag_compare_default($tag){
		if($tag == 'default'){
			if(!$this->SSCurrent(static::STATE_COMPARE_CASE)) $this->error('Unexpected [DEFAULT]', E_USER_WARNING);
			$this->SSReplace(self::STATE_COMPARE_DEFAULT);
			return '<?php break; default: ?>';
		}
		return false;
	}
}


