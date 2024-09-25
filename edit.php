<?php
include('config.php');
include('Translator.php');
use Gt\CssXPath\Translator;

function replaceChildren($element, $html_string) {
	while($child = $element->firstChild) $child->remove();	// remove existing children
	$doc = new DOMDocument();
	$doc->loadHTML("<html><body>".$html_string."</body></html>");	// make new document to clone from
	$child = $doc->documentElement->firstChild->firstChild;
	while($child) {
		$new_child = $element->ownerDocument->importNode($child, true);	// copy child from new document to current document so it can be added to the element
		$element->append($new_child);
		$child = $child->nextSibling;
	}
}

function saveDocument($target, $new_element_content) {
	while (substr($target, 0, 1) == '/') $target = substr($target, 1);
	if ($target == '') $target = 'index';
	$content = file_get_contents($target.'.html');	// force .html extension at the end so we can never overwrite other files like .php or .htaccess
	$file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '_', $target);
	$file = mb_ereg_replace("([\.]{2,})", '', $file);
	$backup_file = $file.'-'.date("Ymd-His").'-'.$_SESSION['username'].'.html';
	$backup_file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '_', $backup_file);	// sanitize: better safe than sorry
	$doc = new DOMDocument();
	@$doc->loadHTML($content);
	$xpath = new DomXPath($doc);
	$elements = $xpath->query(new Translator(SELECTOR));
	foreach ($elements as $element) {
		replaceChildren($element, $new_element_content );
	}
	$new_content = $doc->saveHTML();
	if (strcmp($content, $new_content) !== 0) {
		file_put_contents($backup_file, $content);
		file_put_contents($file.'.html', $new_content);
	}
	exit;
}

function verifyPassword($username, $password) {
	$result = 0;
	if ($username && $password) {
		$users = file_get_contents( USERS );
		preg_match_all("/^([^\s#]+?)[ \t]+(\S+)([ \t]*)(.*?)$/m", $users, $matches); 
		$user_map = array_combine($matches[1], $matches[2]);
		$result = $username && $password && $user_map[$username] && password_verify($password, $user_map[$username]);
	}
	return $result;
}

$error = '';
if (session_start()) {
	if (
		isset($_POST['login'])
		&& verifyPassword($_POST['username'], $_POST['password'])
	) {
		setcookie('edit', '1', 0);	// so the page knows when to check for the editor
		$_SESSION['username'] = $_POST['username'];
	} elseif (isset($_POST['login'])) {
		$error = "Invalid username or password";
	} elseif (isset($_POST['logout'])) {
		setcookie('edit', '', time()-3600);	// so the page knows when to check for the editor
		unset($_SESSION['username']);
	} elseif (isset($_POST['edit']) && isset($_SESSION['username']) && $_SESSION['username']) {
		saveDocument($_POST['edit'], $_POST['content']);
	}
}	else {
	$error = 'Could not start session. Contact us';
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CMS Login</title>
	<style type="text/css">
.error {
	display: block;
	color: #cc0000;
}
form {
	display: inline-block;
	text-align: right;
}

	</style>
</head>
<body>
  <form method="POST">
		<?php if ($error) { ?><div class="error"><?=$error?></div><?php } ?>
		<?php if(isset($_SESSION['username'])) { ?>
			<label for="username">Username</label> <?=isset($_SESSION['username'])?htmlentities($_SESSION['username']):''?><br>
			<input type="button" name="website" value="website" onclick="window.location='index.html'">
			<input type="submit" name="logout" value="logout">
		<?php } else { ?>
			<label for="username">Username</label> <input id="username" type="text" name="username" value="<?=isset($_POST['username'])?htmlentities($_POST['username']):''?>"><br>
			<label for="password">Password</label> <input id="password" type="password" name="password" value="<?=isset($_POST['password'])?htmlentities($_POST['password']):''?>"><br>
			<input type="submit" name="login" value="login">
		<?php } ?>
	</form>
</body>
</html>
