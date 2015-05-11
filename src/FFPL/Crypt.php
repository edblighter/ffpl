<?php
namespace FFPL {
	/**
	 * Abstract class to use in cryptographic functions
	 * you have to implement the $key encode and decode functions
	 * the __constructor needs two optional parameters to initialize
	 *
	 * @author FFPL (fued.felipe@hotmail.com)
	 *
	 * @version 0.1a
	 *
	 * @var string algoritm - default is MCRYPT_3DES
	 * @var string mode - default is ecb
	 * */
	abstract class Crypt {

		/** @var string Contains the algorithm used for generate the final string */
		private static $algo;
		/** @var string Contains the mode of crypt method */
		private static $mode;

		/**
		 * Set the algorithm used for generate the final string - default is
		 * 'MCRYPT_3DES'
		 *
		 * @var string
		 *
		 * @return boolean If the value is not in listAlgorithms() 'false' is returned
		 * */
		final private function setAlgorithm($value = MCRYPT_3DES) {
			if (in_array($value, $this -> listAlgorithms())) :
				self::$algo = $value;
			else :
				return false;
			endif;
		}

		/**
		 * Sets the mode to use in crypto - default is 'ecb'
		 *
		 * @var string
		 *
		 * @return boolean If the value is not in listModes() 'false' is returned
		 * */
		final private function setMode($value = "ecb") {
			if (in_array($value, $this -> listModes())) :
				self::$mode = $value;
			else :
				return false;
			endif;
		}

		/**
		 * Return the ciphers availble to use
		 *
		 *	@return array
		 */
		public static function listAlgorithms() {
			return array(MCRYPT_3DES, MCRYPT_BLOWFISH, MCRYPT_SERPENT);
		}

		/**
		 * Return the modes availble to use
		 *
		 *	@return array
		 */
		public static function listModes() {
			return array('ecb', 'cbc', 'cfb');
		}

		/**
		 * Constructs the object to crypt strings, you may change the cipher and the
		 * mode, just pass the parameters to constructor.
		 *
		 * The default algorithm is MCRYPT_3DES and the default mode is ECB.
		 * If you don't know nothing about cryptographic methods, don't set any
		 * parameter.
		 *
		 * @var string Algorithm to use - availble in listAlgorithms()
		 * @var string Mode to use - availble in listModes()
		 * */
		public function __construct($algo = null, $mode = null) {
			if (is_null($algo)) :
				$this -> setAlgorithm();
			else :
				$this -> setAlgorithm($algo);
			endif;
			if (is_null($mode)) :
				$this -> setMode();
			else :
				$this -> setMode($mode);
			endif;
		}

		/**
		 * Encrypt a string using the algorithm and mode setted in the constructor
		 *
		 * @var string Text to be crypted
		 * @var string key to criptograpth the text
		 *
		 * @return string base64
		 *
		 * FIXME
		 * - cbc and cfb need fix to encode e decode. see
		 * php.net/manual/en/function.mcrypt-encrypt.php
		 * - only ecb mode works properly
		 * */
		final public static function encrypt($str, $key) {
			$block = mcrypt_get_block_size(self::$algo, self::$mode);
			$pad = $block - (strlen($str) % $block);
			$str .= str_repeat(chr($pad), $pad);
			switch(self::$mode) :
				case 'ecb' :
					$str = mcrypt_encrypt(self::$algo, $key, $str, MCRYPT_MODE_ECB);
					break;
				case 'cbc' :
					$str = mcrypt_encrypt(self::$algo, $key, $str, MCRYPT_MODE_CBC);
					break;
				case 'cfb' :
					$str = mcrypt_encrypt(self::$algo, $key, $str, MCRYPT_MODE_CFB);
					break;
			endswitch;
			return base64_encode($str);
		}

		/**
		 * Decrypt a string using the algorithm and mode setted in the constructor
		 *
		 * @var string base64 text to be decrypted
		 * @var string base64 key to decrypt the text
		 *
		 * @return string plain Text
		 * */
		final public static function decrypt($str, $key) {
			switch(self::$mode) :
				case 'ecb' :
					$str = mcrypt_decrypt(self::$algo, base64_decode($key), base64_decode($str), MCRYPT_MODE_ECB);
					break;
				case 'cbc' :
					$str = base64_decode(mcrypt_decrypt(self::$algo, $key, $str, MCRYPT_MODE_CBC));
					break;
				case 'cfb' :
					$str = base64_decode(mcrypt_decrypt(self::$algo, $key, $str, MCRYPT_MODE_CFB));
					break;
			endswitch;
			$block = mcrypt_get_block_size(self::$algo, self::$mode);
			$pad = ord($str[($len = strlen($str)) - 1]);
			return substr($str, 0, strlen($str) - $pad);
		}

		/**
		 * @return string base64 secret encoded key to crypt or decrypt a string
		 * */
		abstract public function encodeKey($key);
		/**
		 * @return string base64 key to crypt or decrypt a string
		 * */
		abstract public function decodeKey($key);

	}

}
?>