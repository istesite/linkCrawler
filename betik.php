<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
include_once "crawler.class.php";
include_once "mysql.class.php";

$url = 'http://www.turkticaret.net/';

$db = new DatabaseClass('localhost', 'root', '', 'crawler');
$crawler = new Crawler();

if($links = $crawler->crawl($url)){
	echo "HTTP_CODE : " . $crawler->getCode() . "\n";
	//-> Database ekleme veya güncelleme baþlar
	$existUrl = $db->fetchArray("SELECT id, url FROM url_list WHERE url='".$url."' LIMIT 1");
	if(isset($existUrl[0]['id']) and $existUrl[0]['id'] > 0){
		$db->query("UPDATE url_list SET http_code='".$crawler->getCode()."' WHERE id='".$existUrl[0]['id']."'");
	}
	else{
		$db->query("INSERT INTO url_list (url, referer_url, status, type, http_code, indate) VALUES ('$url', 'START', '1', 'local', ".$crawler->getCode().", '".date('Y-m-d H:i:s')."')");
	}
	//-> Database ekleme veya güncelleme biter.

	foreach($links as $link){
		$query = $db->fetchArray("SELECT count(id) as sayi FROM url_list WHERE url='".$link['url']."'");
		if($query[0]['sayi'] == 0){
			$db->query("INSERT INTO url_list (url, referer_url, status, type, indate) VALUES ('".$link['url']."', '$url', '0', '".$link['type']."', '".date("Y-m-d H:i:s")."')");
			echo $link['url']." OK\n";
		}
		else{
			//echo $link['url']." EXIST\n";
		}
	}

	$scanning = true;
	while($scanning){
		$newQuery = $db->fetchArray("SELECT id, url FROM url_list WHERE status='0' and type='local' ORDER BY id ASC LIMIT 1");
		echo "\nREFERER : ".$newQuery[0]['url']."\n";

		if(count($newQuery[0]) > 0){
			$linksx = $crawler->crawl($newQuery[0]['url']);
			echo "HTTP_CODE : " . $crawler->getCode() . "\n";

			//-> Database ekleme veya güncelleme baþlar
			$existUrl2 = $db->fetchArray("SELECT id, url FROM url_list WHERE url='".$newQuery[0]['url']."' LIMIT 1");
			if(isset($existUrl2[0]['id']) and $existUrl2[0]['id'] > 0){
				$db->query("UPDATE  url_list SET http_code='".$crawler->getCode()."' WHERE id='".$existUrl2[0]['id']."'");
			}
			//-> Database ekleme veya güncelleme biter.

			if(count($linksx)>0){
				foreach($linksx as $linkx){
					$query2 = $db->fetchArray("SELECT count(id) as sayi FROM url_list WHERE url='".$linkx['url']."'");
					if($query2[0]['sayi'] == 0){
						$db->query("INSERT INTO url_list (url, referer_url, status, type, indate) VALUES ('".$linkx['url']."', '".$newQuery[0]['url']."', '0', '".$linkx['type']."', '".date("Y-m-d H:i:s")."')");
						echo $linkx['url']." OK\n";
					}
					else{
						//echo $link['url']." EXIST\n";
					}
				}
			}
			$db->query("UPDATE url_list SET status='1' WHERE id='".$newQuery[0]['id']."'");
		}
		else{
			$scanning = false;
		}
	}
}
else{
	echo "false";
}
echo "\n- SON -\n";