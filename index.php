<META CHARSET='UTF-8'>
<?php
	require_once('./class.php');

	$API = new youtubeAPI();
	if($_GET['callback'] === 'auth') {
		$API->youtubeToken(); // Youtube API get token
		// Upload info data send form
	?>
		<form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>?callback=upload">
			<input type='text' name='title' placeholder='제목'><br><br>
			<textarea name='content' placeholder='글 내용'></textarea><br><br>
			<input type='text' name='keywords' placeholder='keywords, tags'><br><br>
			<button>전송</button>
		</form>
	<?php
	}
	else if($_GET['callback'] === 'upload') {
		$apiData = $API->youtubeUpload();
		// Upload video form
	?>
		<form method="POST" action="<?= $apiData->url ?>?nexturl=https://hepstar.kr/API/?callback=token" enctype="multipart/form-data">
			<input name="token" type="hidden" value="<?= $apiData->token ?>"/>   
			<input type='file' id='file' name='file' accept="video/*" />
			<button>전송</button>
		</form>
	<?php
	}
	else if($_GET['callback'] === 'token')
		// Youtube upload url location
		header('Location: https://www.youtube.com/watch?v='. $_GET['id']);
	else
		// Youtube user auth <- first
		header('Location: '. $API->youtubeAuth());
?>