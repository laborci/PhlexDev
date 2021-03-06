<?php namespace Phlex\Request;

class UserAgent {
	
	public $platform;
	public $browser;
	public $version;
			 
	function __toString() { return $this->platform.' '.$this->browser.'/'.$this->version; }
	
	function __construct($userAgentRaw) {
		$data = static::parse($userAgentRaw);
		$this->platform = $data['platform'];
		$this->browser = $data['browser'];
		$this->version = $data['version'];
	}
	
	static function parse($userAgent) {
		$data = array('platform' => null, 'browser'  => null, 'version'  => null);
		if (!$userAgent) return $data;
		
		if(preg_match('/\((.*?)\)/im', $userAgent, $parent_matches)) {
			preg_match_all(
				'/(?P<platform>Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone\ OS)?|Silk|linux-gnu|BlackBerry|PlayBook|Nintendo\ (WiiU?|3DS)|Xbox)
				(?:\ [^;]*)?
				(?:;|$)/imx',
				$parent_matches[1],
				$result,
				PREG_PATTERN_ORDER
			);

			$priority = array('Android', 'Xbox');
			$result['platform'] = array_unique($result['platform']);
			
			if (count($result['platform']) > 1) {
				if ($keys = array_intersect($priority, $result['platform'])) $data['platform'] = reset($keys);
				else $data['platform'] = $result['platform'][0];
			} else if (isset($result['platform'][0])) $data['platform'] = $result['platform'][0];
		}

		if ($data['platform'] == 'linux-gnu') $data['platform'] = 'Linux';
		if ($data['platform'] == 'CrOS') $data['platform'] = 'Chrome OS';

		preg_match_all(
			'%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera|OPR|Silk|Lynx|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
			(?:;?)
			(?:(?:[/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
			$userAgent,
			$result,
			PREG_PATTERN_ORDER
		);

		$key = 0;
		$data['browser'] = $result['browser'][0];
		$data['version'] = $result['version'][0];

		if ($key = array_search('Playstation Vita', $result['browser']) !== false) {
			$data['platform'] = 'PlayStation Vita';
			$data['browser'] = 'Browser';
		} else if (($key = array_search('Kindle Fire Build', $result['browser'])) !== false || ($key = array_search('Silk', $result['browser'])) !== false) {
			$data['browser'] = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
			$data['platform'] = 'Kindle Fire';
			if(!($data['version'] = $result['version'][$key]) || !is_numeric($data['version'][0])) {
				$data['version'] = $result['version'][array_search('Version', $result['browser'])];
			}
		}elseif( ($key = array_search( 'NintendoBrowser', $result['browser'] )) !== false || $data['platform'] == 'Nintendo 3DS' ) {
			$data['browser']  = 'NintendoBrowser';
			$data['version']  = $result['version'][$key];
		}elseif( ($key = array_search( 'Kindle', $result['browser'] )) !== false ) {
			$data['browser']  = $result['browser'][$key];
			$data['platform'] = 'Kindle';
			$data['version']  = $result['version'][$key];
		}elseif( ($key = array_search( 'OPR', $result['browser'] )) !== false || ($key = array_search( 'Opera', $result['browser'] )) !== false ) {
			$data['browser'] = 'Opera';
			$data['version'] = $result['version'][$key];
			if( ($key = array_search( 'Version', $result['browser'] )) !== false ) { $data['version'] = $result['version'][$key]; }
		}elseif( $result['browser'][0] == 'AppleWebKit' ) {
			if( ( $data['platform'] == 'Android' && !($key = 0) ) || $key = array_search( 'Chrome', $result['browser'] ) ) {
				$data['browser'] = 'Chrome';
				if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
			}elseif( $data['platform'] == 'BlackBerry' || $data['platform'] == 'PlayBook' ) {
				$data['browser'] = 'BlackBerry Browser';
				if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
			}elseif( $key = array_search( 'Safari', $result['browser'] ) ) {
				$data['browser'] = 'Safari';
				if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
			}

			$data['version'] = $result['version'][$key];
		}elseif( $result['browser'][0] == 'MSIE' ){
			if( $key = array_search( 'IEMobile', $result['browser'] ) ) {
				$data['browser'] = 'IEMobile';
			}else{
				$data['browser'] = 'MSIE';
				$key = 0;
			}
			$data['version'] = $result['version'][$key];
		}elseif( $key = array_search( 'PLAYSTATION 3', $result['browser'] ) !== false ) {
			$data['platform'] = 'PlayStation 3';
			$data['browser']  = 'NetFront';
		}

		return $data;
	}
	
}
