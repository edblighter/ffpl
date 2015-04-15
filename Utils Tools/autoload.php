<?php
define("DATABASE_DIR", ".." . DIRECTORY_SEPARATOR . "Database Tools" . DIRECTORY_SEPARATOR);
define("TEMPLATE_DIR", ".." . DIRECTORY_SEPARATOR . "Template tools" . DIRECTORY_SEPARATOR);
define("CRYPTO_DIR", ".." . DIRECTORY_SEPARATOR . "Cryptographic Tools" . DIRECTORY_SEPARATOR);
define("UTILS_DIR", ".." . DIRECTORY_SEPARATOR . "Utils Tools" . DIRECTORY_SEPARATOR);

function __autoload($Class) {

	$cDir = [DATABASE_DIR, TEMPLATE_DIR, CRYPTO_DIR, UTILS_DIR];
	$iDir = null;

	foreach ($cDir as $dirName) :
		if (!$iDir && file_exists(__DIR__ . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . $Class . '.class.php') && !is_dir(__DIR__ . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . $Class . '.class.php')) :
			include_once (__DIR__ . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . $Class . '.class.php');
			$iDir = true;
		endif;
	endforeach;

	if (!$iDir) :
		trigger_error("Não foi possível incluir {$Class}.class.php", E_USER_ERROR);
		die ;
	endif;
}
?>