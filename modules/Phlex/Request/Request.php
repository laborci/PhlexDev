<?php namespace Phlex\Request {

	/**
	 * Class Request
	 *
	 * @package Phlex\Request
	 * @property-read string $method
	 * @property-read string $referer
	 * @property-read string $remoteAddress
	 * @property-read string $host
	 * @property-read array $headers
	 * @property-read string $raw_userAgent
	 * @property-read UserAgent $userAgent
	 * @property-read string $raw_uri
	 * @property-read string $uri
	 * @property-read string $path
	 * @property-read array $pathSegments
	 * @property-read PathData $pathData
	 * @property-read string $queryString
	 * @property-read string $protocol
	 * @property-read string $protocolVersion
	 * @property-read array $raw_get
	 * @property-read Data $get
	 * @property-read array $raw_post
	 * @property-read Data $post
	 * @property-read Data $json
	 * @property-read array $files
	 * @property-read string $raw_content
	 */

	class Request {

		private $method;
		private $referer;
		private $remoteAddress;
		private $host;
		private $headers;
		private $raw_userAgent, $userAgent;
		private $raw_uri, $uri;
		private $path, $pathSegments, $pathData;
		private $queryString;
		private $protocol, $protocolVersion;
		private $raw_get, $get;
		private $raw_post, $post;
		private $json;
		private $files;
		private $raw_content;

		const METHOD_GET = 'GET';
		const METHOD_POST = 'POST';
		const METHOD_DELETE = 'DELETE';
		const METHOD_PUT = 'PUT';

		function __get($name) {
			switch ($name) {
				case 'get':
					if($this->get === null) $this->get = new Data($this->raw_get);
					return $this->get;
					break;
				case 'post':
					if($this->post === null) $this->post = new Data($this->raw_post);
					return $this->post;
					break;
				case 'json':
					if($this->json === null) $this->json = new Data(json_decode($this->raw_content, true));
					return $this->json;
					break;
				case 'pathData':
					if($this->pathData === null) $this->pathData = new PathData($this->pathSegments);
					return $this->pathData;
					break;
				case 'userAgent':
					if($this->userAgent === null) $this->userAgent = new UserAgent($this->raw_userAgent);
					return $this->userAgent;
					break;
				default:
					return $this->$name;
					break;
			}
		}

		/**
		 * @var \Phlex\Request\Request
		 */
		private static $currentRequest;

		/**
		 * @return Request
		 */
		public static function getCurrent(){
			if(static::$currentRequest === null){
				static::$currentRequest = new static($_SERVER, $_POST, $_GET, $_FILES, apache_request_headers(), true);
			}
			return static::$currentRequest;
		}

		/**
		 * @param Request $request
		 */
		public static function setCurrent(Request $request) { static::$currentRequest = $request; }

		/**
		 * @param array $server
		 * @param array $post
		 * @param array $get
		 * @param array $files
		 * @param array $requestHeaders
		 * @param string $content (true: read from php://input, false: no content, string: content itself)
		 */
		function __construct(array &$server, array &$post, array &$get, array &$files, array $requestHeaders, $content = null) {
			$this->method =strtoupper($server['REQUEST_METHOD']);
			$this->raw_userAgent = $server['HTTP_USER_AGENT'];
			if(isset($server['HTTP_REFERER'])) $this->referer = $server['HTTP_REFERER'];
			$this->remoteAddress = $server['REMOTE_ADDR'];
			$this->raw_uri = $server['REQUEST_URI'];
			$this->host = $server['HTTP_HOST'];
			$this->headers = $requestHeaders;

			$this->uri = urldecode($this->raw_uri);
			$uriParts = explode('?', $this->uri, 2);
			$this->path = preg_replace('~/+~', '/',$uriParts[0].'/');
			$this->pathSegments = explode('/', trim($this->path, '/'));
			$this->queryString = $server['QUERY_STRING'];

			$protocolParts = explode('/', $server['SERVER_PROTOCOL']);
			$this->protocol = $protocolParts[0];
			$this->protocolVersion = $protocolParts[1];


			$this->raw_get = $get;
			$this->raw_post = $post;
			$this->files = $files;

			if($content === false) return;
			else if($content === true) $this->raw_content = file_get_contents('php://input');
			else $this->raw_content = $content;
		}
	}
}
