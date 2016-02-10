<?php namespace Phlex\Tool;

	abstract class PxString {
		public static function isEmail($string) { return (bool)filter_var($string, FILTER_VALIDATE_EMAIL); }

		public static function formatJson($json) {
			if(!is_string($json)) $json = json_encode($json);
			$result = '';
			$pos = 0;
			$strLen = strlen($json);
			$indentStr = "\t";
			$newLine = "\n";
			$prevChar = '';
			$outOfQuotes = true;
			for ($i = 0; $i <= $strLen; $i++) {
				$char = substr($json, $i, 1);
				if ($char == '"' && $prevChar != '\\') $outOfQuotes = !$outOfQuotes;
				else if (($char == '}' || $char == ']') && $outOfQuotes) {
					$result .= $newLine;
					$pos--;
					for ($j = 0; $j < $pos; $j++) $result .= $indentStr;
				}
				$result .= $char;
				if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
					$result .= $newLine;
					if ($char == '{' || $char == '[') $pos++;
					for ($j = 0; $j < $pos; $j++) $result .= $indentStr;
				}
				$prevChar = $char;
			}
			return $result;
		}

		public static function getUrlizedString($string){
			$old = array('á','Á','é','É','í','Í','ó','Ó','ö','Ö','ő','Ő','ú','Ú','ü','Ü','ű','Ű', ' ');
			$new = array('a','A','e','E','i','I','o','O','o','O','o','O','u','U','u','U','u','U', '_');
			$string = str_replace($old, $new, $string);
			$string = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $string);
			$string = preg_replace( '/_+/', '_', $string);
			return $string;
		}

		public static function lipsum($wordCount, $format='plain', $mode=false){
			$generator = new LoremIpsum();
			return $generator->getContent($wordCount, $format, $mode);
		}

		public static function getPart($delimiter, $string, $index = 0, &$result = array()) {
			$result = explode($delimiter, $string);
			if ($index < 0) $index = count($result) + $index;
			return array_key_exists($index, $result)?$result[$index]:null;
		}

		public static function explodeAndTrim($delimeter, $string, $limit = null, $charlist = " \t\n\r\0\x0B"){
			$result = $limit !== null?explode($delimeter, $string, $limit):explode($delimeter, $string);
			for ($i=0; $i < count($result); $i++) $result[$i] = trim($result[$i], $charlist);
			return $result;
		}

		public static function trimAll(array $strings, $charlist = " \t\n\r\0\x0B") {
			if ($strings) foreach ($strings as $i => $str) $strings[$i] = trim($str, $charlist);
			return $strings;
		}

		public static function convertWin1250ToUtf8($string) {
			// map based on: http://konfiguracja.c0.pl/iso02vscp1250en.html; http://konfiguracja.c0.pl/webpl/index_en.html#examp; http://www.htmlentities.com/html/entities/
			$map = array(
				chr(0x8A) => chr(0xA9), chr(0x8C) => chr(0xA6), chr(0x8D) => chr(0xAB), chr(0x8E) => chr(0xAE),
				chr(0x8F) => chr(0xAC), chr(0x9C) => chr(0xB6), chr(0x9D) => chr(0xBB), chr(0xA1) => chr(0xB7),
				chr(0xA5) => chr(0xA1), chr(0xBC) => chr(0xA5), chr(0x9F) => chr(0xBC), chr(0xB9) => chr(0xB1),
				chr(0x9A) => chr(0xB9), chr(0xBE) => chr(0xB5), chr(0x9E) => chr(0xBE),
				chr(0x80) => '&euro;', chr(0x82) => '&sbquo;', chr(0x84) => '&bdquo;', chr(0x85) => '&hellip;',
				chr(0x86) => '&dagger;', chr(0x87) => '&Dagger;', chr(0x89) => '&permil;', chr(0x8B) => '&lsaquo;',
				chr(0x91) => '&lsquo;', chr(0x92) => '&rsquo;', chr(0x93) => '&ldquo;', chr(0x94) => '&rdquo;',
				chr(0x95) => '&bull;', chr(0x96) => '&ndash;', chr(0x97) => '&mdash;', chr(0x99) => '&trade;',
				chr(0x9B) => '&rsquo;', chr(0xA6) => '&brvbar;', chr(0xA9) => '&copy;', chr(0xAB) => '&laquo;',
				chr(0xAE) => '&reg;', chr(0xB1) => '&plusmn;', chr(0xB5) => '&micro;', chr(0xB6) => '&para;',
				chr(0xB7) => '&middot;', chr(0xBB) => '&raquo;'
			);
			return html_entity_decode(mb_convert_encoding(strtr($string, $map), 'UTF-8', 'ISO-8859-2'), ENT_QUOTES, 'UTF-8');
		}

		public static function convertUtf8ToWin1250($string) {
			return iconv('UTF-8', 'ISO-8859-2', $string);
			/*return str_replace(
				array("\xc3\xb6", "\xc3\xbc", "\xc3\xb3", "\xc5\x91", "\xc3\xba", "\xc3\xa9", "\xc3\xa1", "\xc5\xb1", "\xc3\xad", "\xc3\x96", "\xc3\x9c", "\xc3\x93", "\xc5\x90", "\xc3\x9a", "\xc3\x89", "\xc3\x81", "\xc5\xb0", "\xc3\x8d"),
				array("\xf6", "\xfc", "\xf3", "\xf5", "\xfa", "\xe9", "\xe1", "\xfb", "\xed", "\xd6", "\xdc", "\xd3", "\xd5", "\xda", "\xc9", "\xc1", "\xdb", "\xcd"),
				$string
			);*/
		}

		public static function convertEncoding($string, $toEncoding, $fromEncoding = null) {
			if ($fromEncoding === null) $fromEncoding = mb_internal_encoding();
			return ($toEncoding == 'UTF-8' && ($fromEncoding == 'Windows-1250' || $fromEncoding == 'ISO-8859-2')) ?
				static::convertWin1250ToUtf8($string) :
				(
					($toEncoding == 'Windows-1250' || $toEncoding == 'ISO-8859-2') && $fromEncoding == 'UTF-8' ?
					static::convertUtf8ToWin1250($string) :
					mb_convert_encoding($string, $toEncoding, $fromEncoding)
				)
			;
		}

		public static function convertAllEncoding(array $strings, $toEncoding, $fromEncoding = null) {
			if ($fromEncoding === null) $fromEncoding = mb_internal_encoding();
			if ($strings) foreach ($strings as $i => $str) $strings[$i] = static::convertEncoding($str, $toEncoding, $fromEncoding);
			return $strings;
		}

		public static function getExpressionsFromString($string, $startSign = '(', $endSign = ')') {
			$expressions = array();
			$start = 0;
			while(substr_count($string, $startSign, $start)) {
				$brace = 1;
				$i = strpos($string, $startSign, $start) + 1;
				$expression = $startSign;
				while($brace && $i < strlen($string)) {
					if ($string{$i} == $startSign) $brace++;
					else if ($string{$i} == $endSign) $brace--;
					if ($brace != 0) $expression .= $string{$i};
					$i++;
				}
				$expression .= $endSign;
				$start += strlen($expression);
				str_replace(preg_replace('/\s+/ims', ' ', $expression), $expression, preg_replace('/\s+/ims', ' ', $string), $c);
				if (!$c) throw new \Exception('Invalid expression `'.$expression.'`');
				$expressions[] = array('expression' => $expression, 'occurance' => $c);
			}
			return $expressions;
		}

		public static function parseBool($str) {
			if (is_bool($str)) return $str;
			if (is_numeric($str)) return (bool)$str;
			if (!is_string($str)) return (bool)$str;
			return in_array(mb_strtolower($str), array('true','yes','on','available','enabled','ok','1'));
		}

		public static function findTags($string, $tagName){
			$regexp = '(\<'.$tagName.'(.*?)(?<!-|=)\>)';
			$num_of_tags = preg_match_all($regexp, $string, $tags);
			$rtags = array();
			if($num_of_tags>0) for($i=0;$i<$num_of_tags;$i++){
				$rtags[$i]['string'] = $tags[0][$i];
				$num_of_attrs = preg_match_all('(\s*(\w+)(="(.*?)")?)',$tags[1][$i], $attrs);
				if($num_of_attrs) $rtags[$i]['attributes'] = array_combine ($attrs[1], $attrs[3]);
				else $rtags[$i]['attributes'] = array();
			}
			return $rtags;
		}

		public static function ucFirst($string) {
			return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
		}
		
		public static function lcFirst($string) {
			return mb_strtolower(mb_substr($string, 0, 1)).mb_substr($string, 1);
		}
		
		public static function like($needle, $haystack) {
			if (!mb_strlen($needle)) return true;
			return (bool)preg_match('/.*'.str_replace(' ', '.*', preg_quote(preg_replace('/\s+/', ' ', trim($needle)), '/')).'.*/smi', $haystack);
		}
		
		const MAX_LENGTH_AFTER = 0;
		const MAX_LENGTH_BEFORE = 1;
		const MAX_LENGTH_MIDDLE = 2;
		public static function getEllipsisedByMaxLength($string, $maxLength, $mode = PxString::MAX_LENGTH_AFTER, $elipsis = '...') {
			switch ($mode) {
				case PxString::MAX_LENGTH_AFTER:
					if (!preg_match_all('/(\s+)?(\S+)?/', $string, $matches)) return $string;
					$chunks = $matches[0];
					$result = implode('', $chunks);
					while($chunks && mb_strlen($result) > $maxLength) {
						array_pop($chunks);
						$result = implode('', $chunks).$elipsis;
					}
					return $result;
					break;
				case PxString::MAX_LENGTH_BEFORE:
					if (!preg_match_all('/(\S+)?(\s+)?/', $string, $matches)) return $string;
					$chunks = $matches[0];
					$result = implode('', $chunks);
					while($chunks && mb_strlen($result) > $maxLength) {
						array_shift($chunks);
						$result = $elipsis.implode('', $chunks);
					}
					return $result;
					break;
				case PxString::MAX_LENGTH_MIDDLE:
					if (!($cl = preg_match_all('/(\S+)?(\s+)?/', $string, $matchesLeft))) return $string;
					
					$chunksLeft = array_slice(array_filter($matchesLeft[0]), 0, floor($cl / 2));
					$chunksRight = array_slice(array_filter($matchesLeft[0]), floor($cl / 2));
					$result = implode('', $chunksLeft).implode('', $chunksRight);
					$fromLeft = true;
					while(($chunksLeft || $chunksRight) && mb_strlen($result) > $maxLength) {
						if ($fromLeft) array_pop($chunksLeft);
						else array_shift($chunksRight);
						$result = implode('', $chunksLeft).$elipsis.implode('', $chunksRight);
						$fromLeft = !$fromLeft;
					}
					return $result;
					break;
			}
			
			return $string;
		}
		
	}

