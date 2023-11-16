<?php

class ErrorHandling{
	static function audioError2() {
		// error, user did not upload file
		global $dbcon;
		logs("error-at-2", $_SESSION['username'], $dbcon);
		header("Location: history_audio.php?error=2");
		exit();
	}
	static function audioError3() {
		// error, for some reason, there is no output
		global $dbcon;
		logs("audio-to-text-fail", $_SESSION['username'], $dbcon);
		header("Location: history_audio.php?error=5");
		exit();
	}
	
	static function checkLanguageChosen() {
		global $dbcon;
	
		// Error Handling if user did not select language 
		//	and if user select same language on src and target
	
		if ($_POST["src"] == "" || $_POST['target'] == "") {
			// error, user did not choose language
			logs("error-at-1", $_SESSION['username'], $dbcon);
			header("Location: history_audio.php?error=1");
			exit();
		} 
	
		if ($_POST["src"] == $_POST['target']) {
			// error, user choose two same language
			logs("error-at-4", $_SESSION['username'], $dbcon);
			header("Location: history_audio.php?error=4");
			exit();
		} 
	
	}
	
	static function validateFormat($filePath) {
		// error, user no upload file
		if (!$filePath) {
			self::audioError2();
		}
		
		// error, user uploaded invalid file format
		// only accepts these formats provided
		$validExtensions = array('m4a', 'mp3', 'webm', 'mp4', 'mpga', 'wav', 'mpeg');
	
		// get the file extension, then check if extension is in array, return error if none
		$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
		global $dbcon;
		
		if (!in_array($ext, $validExtensions)) {
			logs("error-at-3", $_SESSION['username'], $dbcon);
			header("Location: history_audio.php?error=3");
			exit();
		}
	}

	static function checkFolder() {
		// make sure audio_files folder is created
		if(!is_dir("audio_files")){
			mkdir("audio_files", 0777, true);
		}
	}
}

?>