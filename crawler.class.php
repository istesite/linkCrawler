<?php
class Crawler{
	static $url;
	static $urlContent;
	static $code;

	function crawl($url){
		self::$url = $url;
		if(self::getContent()){
			$links = self::getLinkList();
		
			$returning = array();
			foreach($links as $link){
				$fullLink = self::genFullLink($link);
				if(!in_array($fullLink, $returning)){
					$returning[] = $fullLink;
				}
			}

			return $returning;
		}
		else{
			return FALSE;
		}
	}

	function getUrl(){
		return self::$url;
	}

	function getUrlContent(){
		return self::$urlContent;
	}

	function getCode(){
		return self::$code;
	}

	function isLocalUrl($url){
		$exp = parse_url($url);
		$hostName = self::getHostName();
		if((!isset($exp['host'])) or (isset($exp['host']) and $exp['host'] == $hostName) or (strstr(strtolower($hostName."/"), strtolower($url)))){
			return true;
		}
		else{
			return false;
		}
	}

	function getHostName(){
		$host = self::getSiteRoot(false);
		str_replace('www.', '', $host);
		return $host;
	}

	function getSiteRoot($scheme = true){
		$exp = parse_url(self::$url);
		if(isset($exp['host'])){
			if($scheme){
				return $exp['scheme'] . '://' . $exp['host'];
			}
			else{
				return $exp['host'];
			}
		}
		else{
			return false;
		}
	}

	function getSiteCurrentDir($scheme = true){
		$exp = parse_url(self::$url);
		if(isset($exp['host'])){
			if($scheme){
				return $exp['scheme'] . '://' . $exp['host'].$exp['path'];
			}
			else{
				return $exp['host'].$exp['path'];
			}
		}
		else{
			return false;
		}
	}

	function getSiteDir($url){
		$exp = parse_url($url);
		if(isset($exp['host'])){
			if(isset($exp['path'])){
				$exppath = pathinfo($exp['path']);
				if(substr($exppath['dirname'], 0, 1) == '/'){
					return $exppath['dirname'];
				}
				else{
					return '/' . $exppath['dirname'];
				}
			}
			else{
				return '/';
			}
		}
		else{
			if(isset($exp['path'])){
				$exppath = pathinfo($exp['path']);
				if(substr($exppath['dirname'], 0, 1) == '/'){
					if(self::getSiteRoot()){
						return $exppath['dirname'];
					}
					else{
						return false;
					}
				}
				else{
					if(self::getSiteRoot()){
						return $exppath['dirname'];
					}
					else{
						return false;
					}
				}
			}
			else{
				if(self::getSiteRoot()){
					return '/';
				}
				else{
					return false;
				}
			}
		}
	}

	function genFullLink($url){
		if(strstr($url, 'javascript:') or strstr($url, 'mailto:') or strstr($url, 'tel:')){
			$url = '';
		}

		if(strstr($url, '#fragment-')){
			$url = str_replace(array('#fragment-1','#fragment-2','#fragment-3','#fragment-4','#fragment-5','#fragment-6','#fragment-7','#fragment-8','#fragment-9'), '', $url);
		}

		if(substr($url, -1, 1) == '#'){
			$url = substr($url, 0, -1);
		}

		$exp = parse_url($url);
		if(!self::isLocalUrl($url) or (isset($exp['host']) and trim($exp['host']) != self::getHostName())){
			return array('type'=>'remote', 'url'=>$url);
		}
		else{
			if(substr(strtolower($url), 0, 4) == 'http' or substr($url, 0, 3) == ''){
				return array('type'=>'local', 'url'=>$url);
			}
			else {
				return array('type'=>'local', 'url'=>self::url_to_absolute(self::$url, $url));
			}
		}
	}

	function getLinkList(){
		/*preg_match_all('%<a.*?href=["|\'](.*?)["|\'].*?>.*?</a>%', self::$urlContent, $result, PREG_PATTERN_ORDER);*/
		/*preg_match_all('%<[aA]{1}.*?[hrefHREF][\s\r\n\t]{0,}=[\s\r\n\t]{0,}["|\'](.*?)["|\'].*?>.*?</[aA]{1}>%', self::$urlContent, $result, PREG_PATTERN_ORDER);*/
		/*preg_match_all('/<[aA]{1}.*?[hrefHREF]=[\'"`]{1,}(.*?)[\'"`]{1,}.*?>/sx', self::$urlContent, $result, PREG_PATTERN_ORDER);*/
		/*preg_match_all('%<a.*?href=(?:"(.*?)"|\'(.*?)\').*?>.*?</a>%sx', self::$urlContent, $result, PREG_PATTERN_ORDER);*/
		//die(self::$urlContent);
		preg_match_all('/<a.*?href=(?:[\'|"](.*?)[\'|"]).*?>/x', self::$urlContent, $result, PREG_PATTERN_ORDER);
		$result = $result[1];
		if(count($result) > 0){
			return $result;
		}
		else{
			return FALSE;
		}
	}

	function getContent(){
		$content = self::curl(self::$url);
		if(strlen($content['content']) > 0){
			self::$urlContent = $content['content'];
			self::$code = $content['code'];
			return self::$urlContent;
		}
		else{
			return FALSE;
		}
	}

	function curl($url){
		$thisPageCharset = 'UTF-8';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.69 Safari/537.36");
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_URL, $url);
		$html = curl_exec($ch);


		$encoding = mb_detect_encoding($html);
		//$encoding = 'ISO-8859-9';
		if($encoding != $thisPageCharset){
			$html = iconv($encoding, $thisPageCharset, $html);
		}

		$code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
		curl_close($ch);

		$result = array('content' => $html,
		                'code' => $code);

		return $result;
	}


	function split_url( $url, $decode=TRUE )
	{
		// Character sets from RFC3986.
		$xunressub     = 'a-zA-Z\d\-._~\!$&\'()*+,;=';
		$xpchar        = $xunressub . ':@%';

		// Scheme from RFC3986.
		$xscheme        = '([a-zA-Z][a-zA-Z\d+-.]*)';

		// User info (user + password) from RFC3986.
		$xuserinfo     = '((['  . $xunressub . '%]*)' .
			'(:([' . $xunressub . ':%]*))?)';

		// IPv4 from RFC3986 (without digit constraints).
		$xipv4         = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';

		// IPv6 from RFC2732 (without digit and grouping constraints).
		$xipv6         = '(\[([a-fA-F\d.:]+)\])';

		// Host name from RFC1035.  Technically, must start with a letter.
		// Relax that restriction to better parse URL structure, then
		// leave host name validation to application.
		$xhost_name    = '([a-zA-Z\d-.%]+)';

		// Authority from RFC3986.  Skip IP future.
		$xhost         = '(' . $xhost_name . '|' . $xipv4 . '|' . $xipv6 . ')';
		$xport         = '(\d*)';
		$xauthority    = '((' . $xuserinfo . '@)?' . $xhost .
			'?(:' . $xport . ')?)';

		// Path from RFC3986.  Blend absolute & relative for efficiency.
		$xslash_seg    = '(/[' . $xpchar . ']*)';
		$xpath_authabs = '((//' . $xauthority . ')((/[' . $xpchar . ']*)*))';
		$xpath_rel     = '([' . $xpchar . ']+' . $xslash_seg . '*)';
		$xpath_abs     = '(/(' . $xpath_rel . ')?)';
		$xapath        = '(' . $xpath_authabs . '|' . $xpath_abs .
			'|' . $xpath_rel . ')';

		// Query and fragment from RFC3986.
		$xqueryfrag    = '([' . $xpchar . '/?' . ']*)';

		// URL.
		$xurl          = '^(' . $xscheme . ':)?' .  $xapath . '?' .
			'(\?' . $xqueryfrag . ')?(#' . $xqueryfrag . ')?$';


		// Split the URL into components.
		if ( !preg_match( '!' . $xurl . '!', $url, $m ) )
			return FALSE;

		if ( !empty($m[2]) )		$parts['scheme']  = strtolower($m[2]);

		if ( !empty($m[7]) ) {
			if ( isset( $m[9] ) )	$parts['user']    = $m[9];
			else			$parts['user']    = '';
		}
		if ( !empty($m[10]) )		$parts['pass']    = $m[11];

		if ( !empty($m[13]) )		$h=$parts['host'] = $m[13];
		else if ( !empty($m[14]) )	$parts['host']    = $m[14];
		else if ( !empty($m[16]) )	$parts['host']    = $m[16];
		else if ( !empty( $m[5] ) )	$parts['host']    = '';
		if ( !empty($m[17]) )		$parts['port']    = $m[18];

		if ( !empty($m[19]) )		$parts['path']    = $m[19];
		else if ( !empty($m[21]) )	$parts['path']    = $m[21];
		else if ( !empty($m[25]) )	$parts['path']    = $m[25];

		if ( !empty($m[27]) )		$parts['query']   = $m[28];
		if ( !empty($m[29]) )		$parts['fragment']= $m[30];

		if ( !$decode )
			return $parts;
		if ( !empty($parts['user']) )
			$parts['user']     = rawurldecode( $parts['user'] );
		if ( !empty($parts['pass']) )
			$parts['pass']     = rawurldecode( $parts['pass'] );
		if ( !empty($parts['path']) )
			$parts['path']     = rawurldecode( $parts['path'] );
		if ( isset($h) )
			$parts['host']     = rawurldecode( $parts['host'] );
		if ( !empty($parts['query']) )
			$parts['query']    = rawurldecode( $parts['query'] );
		if ( !empty($parts['fragment']) )
			$parts['fragment'] = rawurldecode( $parts['fragment'] );
		return $parts;
	}

	function join_url( $parts, $encode=TRUE )
	{
		if ( $encode )
		{
			if ( isset( $parts['user'] ) )
				$parts['user']     = rawurlencode( $parts['user'] );
			if ( isset( $parts['pass'] ) )
				$parts['pass']     = rawurlencode( $parts['pass'] );
			if ( isset( $parts['host'] ) &&
				!preg_match( '!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'] ) )
				$parts['host']     = rawurlencode( $parts['host'] );
			if ( !empty( $parts['path'] ) )
				$parts['path']     = preg_replace( '!%2F!ui', '/',
					rawurlencode( $parts['path'] ) );
			if ( isset( $parts['query'] ) )
				$parts['query']    = rawurlencode( $parts['query'] );
			if ( isset( $parts['fragment'] ) )
				$parts['fragment'] = rawurlencode( $parts['fragment'] );
		}

		$url = '';
		if ( !empty( $parts['scheme'] ) )
			$url .= $parts['scheme'] . ':';
		if ( isset( $parts['host'] ) )
		{
			$url .= '//';
			if ( isset( $parts['user'] ) )
			{
				$url .= $parts['user'];
				if ( isset( $parts['pass'] ) )
					$url .= ':' . $parts['pass'];
				$url .= '@';
			}
			if ( preg_match( '!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'] ) )
				$url .= '[' . $parts['host'] . ']';	// IPv6
			else
				$url .= $parts['host'];			// IPv4 or name
			if ( isset( $parts['port'] ) )
				$url .= ':' . $parts['port'];
			if ( !empty( $parts['path'] ) && $parts['path'][0] != '/' )
				$url .= '/';
		}
		if ( !empty( $parts['path'] ) )
			$url .= $parts['path'];
		if ( isset( $parts['query'] ) )
			$url .= '?' . $parts['query'];
		if ( isset( $parts['fragment'] ) )
			$url .= '#' . $parts['fragment'];
		return $url;
	}

	function url_to_absolute( $baseUrl, $relativeUrl ) {
		// If relative URL has a scheme, clean path and return.
		$r = self::split_url( $relativeUrl );
		if ( $r === FALSE )
			return FALSE;
		if ( !empty( $r['scheme'] ) )
		{
			if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
				$r['path'] = self::url_remove_dot_segments( $r['path'] );
			return self::join_url( $r );
		}

		// Make sure the base URL is absolute.
		$b = self::split_url( $baseUrl );
		if ( $b === FALSE || empty( $b['scheme'] ) || empty( $b['host'] ) )
			return FALSE;
		$r['scheme'] = $b['scheme'];

		// If relative URL has an authority, clean path and return.
		if ( isset( $r['host'] ) )
		{
			if ( !empty( $r['path'] ) )
				$r['path'] = self::url_remove_dot_segments( $r['path'] );
			return self::join_url( $r );
		}
		unset( $r['port'] );
		unset( $r['user'] );
		unset( $r['pass'] );

		// Copy base authority.
		$r['host'] = $b['host'];
		if ( isset( $b['port'] ) ) $r['port'] = $b['port'];
		if ( isset( $b['user'] ) ) $r['user'] = $b['user'];
		if ( isset( $b['pass'] ) ) $r['pass'] = $b['pass'];

		// If relative URL has no path, use base path
		if ( empty( $r['path'] ) )
		{
			if ( !empty( $b['path'] ) )
				$r['path'] = $b['path'];
			if ( !isset( $r['query'] ) && isset( $b['query'] ) )
				$r['query'] = $b['query'];
			return self::join_url( $r );
		}

		// If relative URL path doesn't start with /, merge with base path
		if ( $r['path'][0] != '/' )
		{
			$base = mb_strrchr( $b['path'], '/', TRUE, 'UTF-8' );
			if ( $base === FALSE ) $base = '';
			$r['path'] = $base . '/' . $r['path'];
		}
		$r['path'] = self::url_remove_dot_segments( $r['path'] );
		return self::join_url( $r );
	}

	function url_remove_dot_segments( $path )
	{
		// multi-byte character explode
		$inSegs  = preg_split( '!/!u', $path );
		$outSegs = array( );
		foreach ( $inSegs as $seg )
		{
			if ( $seg == '' || $seg == '.')
				continue;
			if ( $seg == '..' )
				array_pop( $outSegs );
			else
				array_push( $outSegs, $seg );
		}
		$outPath = implode( '/', $outSegs );
		if ( $path[0] == '/' )
			$outPath = '/' . $outPath;
		// compare last multi-byte character against '/'
		if ( $outPath != '/' &&
			(mb_strlen($path)-1) == mb_strrpos( $path, '/', 'UTF-8' ) )
			$outPath .= '/';
		return $outPath;
	}
}