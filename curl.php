<?php
function curl($url){
	$thisPageCharset = 'UTF-8';
	$ch = curl_init($url);
	//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
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
	if($encoding != $thisPageCharset){
		$html = iconv($encoding, $thisPageCharset, $html);	
	}
	
	$code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
	curl_close($ch);
	return $html;
	$result = array('content' => $html,
					'code' => $code);

	return $result;
}

echo curl("http://www.turkticaret.net/epazaryeri/");
//echo file_get_contents("http://www.turkticaret.net/epazaryeri/");