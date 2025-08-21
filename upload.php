<?php
// inspired by https://www.tiny.cloud/docs/tinymce/latest/php-upload-handler/
/*
	remember to edit php.ini to accept
	larger files than the standard 2 MB
	AND edit MAX_FILE_SIZE hidden field
	for files bigger than 2 MB
*/

include('config.php');

function addLog($message) {
	$message = preg_replace('/\e\[[\d;]*[A-Za-z]/', '', $message);	// Strip ANSI escape codes
	$message = preg_replace('/[^\P{C}\t\r\n]/u', '', $message);	// Remove non-printable ASCII except line breaks and tabs
	$message = mb_convert_encoding($message, 'UTF-8', 'UTF-8'); // Ensure UTF-8 safety (optional, drops invalid sequences)
	$message  = "[" . date("Y-m-d H:i:s") . "] " . $_SESSION['username'] . " - " . $message . PHP_EOL;
	file_put_contents(UPLOADLOG, $message, FILE_APPEND | LOCK_EX);
}

function error($error_text, $code=500) {
	http_response_code($code);
	echo json_encode(['error' => $error_text]);
	addLog($error_text);
	exit(1);
}

if (!(session_start() && $_SESSION['username'])) error('No access', 401);

if (isset($_SERVER['HTTP_ORIGIN'])) {
	// same-origin requests won't set an origin. If the origin is set, it must be valid.
	if (in_array($_SERVER['HTTP_ORIGIN'], ORIGINS)) {
		header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	} else {
		error('', 403);
		addLog('Origin denied '.$_SERVER['HTTP_ORIGIN']);
		return;
	}
}

// Don't attempt to process the upload on an OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	header("Access-Control-Allow-Methods: POST, OPTIONS");
	addLog('Options request '.$_SERVER['REQUEST_METHOD']);
	return;
}

if (@$_POST['delete']) {
  $filename = $_POST['delete'];
	addLog('Trying to delete '.$filename);
  if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $filename)) {
		error('Invalid filename '.$filename, 400);
  }
  if (!preg_match(ACCEPTS, @$absolute_filename))
    error('Verwijderen van '.$filename.' is mislukt - ik zei nog zo, ALLEEN PLAATJES', 400);
  unlink(__DIR__.MEDIADIR.$delete);
}

reset($_FILES);
$file = current($_FILES);
if ($file['name']) {
	$filename=$file['name'];	// create filename with unique id
	// Sanitize input
	if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $filename)) {
		error('Invalid filename '.$filename, 400);
	}

	$absolute_filename=__DIR__.MEDIADIR.$filename;

	if (!preg_match(ACCEPTS, @$absolute_filename))
		error('uploaden van '.$filename.' is mislukt - ik zei nog zo, ALLEEN PLAATJES', 400);

	if (!@move_uploaded_file ($file['tmp_name'], $absolute_filename))	// move file to upload dir from temp (@=silent error)
		error('uploaden van '.$filename.' ('.$file['size'].' bytes) is mislukt - neem contact op met uw homeboy');

	$relative_filename=".".MEDIADIR.$filename;
	addLog('Uploaded '.$absolute_filename);
	echo json_encode(['location' => $relative_filename]);
	exit;
}
error('Did not receive file');
?>
