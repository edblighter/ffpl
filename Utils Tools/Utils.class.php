<?php

class Utils {

	/** @var mixed Accepts multi vars to debug
	 * 	@return String Returns the var_dump of the variables*/
	public static function debugMulti() {
		echo "<pre>";
		$num = 0;
		foreach (func_get_args() as $k) :
			$num += 1;
			echo "Variable #{$num}<br>";
			highlight_string(var_dump($k));
			echo "<br><hr>";
		endforeach;
		echo "</pre>";
	}

	final public static function encrypt($str, $key) {
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);

		return mcrypt_encrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB);
	}

	final public static function decrypt($str, $key) {
		$str = mcrypt_decrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB);

		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		return substr($str, 0, strlen($str) - $pad);
	}

}
?>