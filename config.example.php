<?php
# if you get errors, check your error logs, usually in /var/log/apache2/error.log

define('USERS', '../example.passwd');
define('SELECTOR', '.edit');
define ("MEDIADIR", "/userfiles/");
define ("UPLOADLOG", "/var/log/uploads.log");
define ("ORIGINS", array("http://localhost", "https://example.com", "https://www.example.com"));
define ("ACCEPTS", '/\.(png|jpg|webp|gif|mp3|ogg|mp4)$/i');

?>
