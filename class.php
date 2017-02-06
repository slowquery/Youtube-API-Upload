<?php
	@session_start();
	class youtubeAPI {
		function __construct() {
			$this->author = "4ntiweb";
			$this->apiKey = "*****";
			$this->secretParse();
		}
		public function makeHeader($url) {
			// curl init, curl header define
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_FAILONERROR, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			return $curl;
		}
		public function postHeader($curl, $param) { // after makeHeader function call
			// param POST method send
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
			return curl_exec($curl);
		}
		public function postCheck($checkArr) {
			// POST recv data check
			$count = count($checkArr);
			for($i = 0; $i < count($checkArr); $i++) {
				if(!empty($_POST[$checkArr[$i]]))
					$count -= 1;
			}
			return $count;
		}
		public function secretParse() {
			// api_secret.json JSON file parsing
			$secretFile = fopen("./api_secret.json", "r");
			$this->secretRead = json_decode(fread($secretFile, filesize("./api_secret.json")), true);
		}
		public function youtubeAuth() {
			// youtube API URL return
			return $this->secretRead['web']['auth_uri']
				.'?client_id='.$this->secretRead['web']['client_id']
				.'&redirect_uri='.$this->secretRead['web']['redirect_uris'][0]
				.'&scope=https://gdata.youtube.com'
				.'&response_type=code&access_type=offline';
		}
		public function youtubeToken() {
			// youtube API GET Access Token
			$curl = $this->makeHeader($this->secretRead['web']['token_uri']);
			$data = json_decode($this->postHeader($curl, http_build_query([
					'code' => $_GET['code'],
					'client_id' => $this->secretRead['web']['client_id'],
					'client_secret' => $this->secretRead['web']['client_secret'],
					'redirect_uri' => $this->secretRead['web']['redirect_uris'][0],
					'grant_type' => 'authorization_code'
				])
			), true);
			$_SESSION['access_token'] = $data['access_token'];
		}
		public function youtubeUpload() {
			// Youtube API video upload
			if($this->postCheck(array("title", "content", "keywords")) !== 0) // title, content, keywords is not empty
				header('Location: /API/');
			$data = implode('', array(
		     '<?xml version="1.0"?>',
		     '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xmlns:yt="http://gdata.youtube.com/schemas/2007">',  
		       '<media:group>',  
		         '<media:title type="plain">' . $_POST['title'] . '</media:title>',  
		         '<media:description type="plain">' . $_POST['content'] . '</media:description>',  
		         '<media:category scheme="http://gdata.youtube.com/schemas/2007/categories.cat">Animals</media:category>',  
		         '<media:keywords>' . $_POST['keywords'] . '</media:keywords>',
		         '<yt:accessControl action="list" permission="denied"/>', 
		       '</media:group>',  
		     '</entry>'));
			$curl = $this->makeHeader('http://gdata.youtube.com/action/GetUploadToken');
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            	'Authorization: Bearer '.$_SESSION['access_token'],
            	'GData-Version: 2',
            	'X-GData-Key: key='.$this->apiKey,
            	'Content-Type: application/atom+xml; charset=UTF-8'
            ));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

			$response = simplexml_load_string(curl_exec($curl));
			return $response;
		}
	}
?>
