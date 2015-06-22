<?php
namespace FFPL {
	/**
	 * Util Functions to use in general projects.
	 *
	 * The contructor may be initalized with parameters to criptographic functions
	 *
	 * @author FFPL (fued.felipe@hotmail.com)
	 * @version 1.1a
	 *
	 * @var string optinal Algorithm to use in crypto function - You may use the
	 * function Utils::listAlgorithms() to get a list of availble algorithms -
	 * default is MCRYPT_3DES.
	 * @var string optional Mode to use in crypto function - You may use the function
	 * Utils::listModes() to get a list of availble modes - default mode is ECB.
	 * */
	use FFPL\Crypt;
	class Utils extends Crypt {
		static $known_mime_types = array('txt' => 'text/plain', 'html' => 'text/html', 'htm' => 'text/html', 'php' => 'text/plain', 'css' => 'text/css', 'js' => 'application/x-javascript', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png', 'bmp' => 'image/bmp', 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'doc' => 'application/msword', 'docx' => 'application/msword', 'xls' => 'application/excel', 'xlsx' => 'application/excel', 'ppt' => 'application/powerpoint', 'pptx' => 'application/powerpoint', 'pdf' => 'application/pdf', 'wmv' => 'application/octet-stream', 'mpg' => 'video/mpeg', 'mov' => 'video/quicktime', 'mp4' => 'video/quicktime', 'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed', 'dmg' => 'application/x-apple-diskimage', 'exe' => 'application/octet-stream');
		static $image_mime_types = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png', 'bmp' => 'image/bmp', 'tif' => 'image/tiff', 'tiff' => 'image/tiff');
		/**
		 * Starts the contructor. If you will use the crypto function you have to set the
		 * parameters which you will need.
		 *
		 * @var string Algorithm to use in crypto function - You may use the function
		 * Utils::listAlgorithms() to get a list of availble algorithms - default is
		 * MCRYPT_3DES.
		 * @var string Mode to use in crypto function - You may use the function
		 * Utils::listModes() to get a list of availble modes - default mode is ECB.
		 * */
		public function __contruct($algo = null, $mode = null) {
			if (!is_null($algo) && !is_null($mode)) :
				parent::__construct($algo, $mode);
			else :
				parent::__construct();
			endif;
		}

		/** @var mixed Accepts multi vars to debug
		 * 	@return String Returns the var_dump of the variables
		 **/
		public static function d() {
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

		public static function is_image_test($fullPath) {
			$extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
			return in_array($extension, self::$image_mime_types);
		}

		public static function is_image($fullPath) {
			$k = strtolower(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullPath));
			return in_array($k, self::$image_mime_types);
		}

		public static function eraseFile($path, $fileName) {
			$fullPath = $path . $name;
			if (is_dir($path)) :
				if (is_file($fullPath)) :
					if (@unlink($fullPath))
						return true;
				endif;
			endif;
			return false;
		}

		public static function eraseFiles($path, array $fileNames) {
			foreach ($fileNames as $fileName) :
				$this -> eraseFile($path, $fileName);
			endforeach;
			return false;
		}

		public static function filesToVector($toVector) {
			$vector = array();
			$i = 0;
			foreach ($toVector as $key => $value) :
				foreach ($value as $val) :
					$vector[$i][$key] = $val;
					$i++;
				endforeach;
				$i = 0;
			endforeach;
			return $vector;
		}

		public static function geraThumb($fullPathImage, $fullPathOutput, $new_width) {
			$source = imagecreatefromstring(file_get_contents($fullPathImage));
			list($width, $height) = getimagesize($fullPathImage);
			if ($width > $new_width) :
				$new_height = ($new_width / $width) * $height;
				$thumb = imagecreatetruecolor($new_width, $new_height);
				imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				if (imagejpeg($thumb, $fullPathOutput, 100)) :
					return true;
				endif;
			else :
				if (copy($photo, $output))
					return true;
			endif;
			return false;
		}

		private function image_create_gd($path = '') {
			$image_type = pathinfo($path, PATHINFO_EXTENSION);
			switch ($image_type) {
				case 'gif' :
					if (!function_exists('imagecreatefromgif'))
						return false;
					return imagecreatefromgif($path);
					break;
				case 'jpg' :
					if (!function_exists('imagecreatefromjpeg'))
						return false;
					return imagecreatefromjpeg($path);
					break;
				case 'png' :
					if (!function_exists('imagecreatefrompng'))
						return false;
					return imagecreatefrompng($path);
					break;
			}
			return false;
		}

		private function overlay_watermark($fullPathImagem, $fullPathOverlay, $options = array('opacity'=>20,'output_quality','overwrite'=>true,'output_path'=>'')) {
			if (!function_exists('imagecolortransparent')) :
				return false;
			endif;

			//  Fetch watermark image properties
			list($wm_width, $wm_height) = getimagesize($fullPathOverlay);
			//  Create two image resources
			$wm_img = $this -> image_create_gd($fullPathOverlay);
			$src_img = $this -> image_create_gd($fullPathImagem);

			//ajustar os tamanhos da marca para os tamanhos da img.
			list($img_width, $img_height) = getimagesize($fullPathImagem);
			$wm_width_adj = $img_width / 5;
			$wm_height_adj = $img_height / 7;

			$image_p = imagecreatetruecolor($wm_width_adj, $wm_height_adj);

			imagecopyresampled($image_p, $wm_img, 0, 0, 0, 0, $wm_width_adj, $wm_height_adj, $wm_width, $wm_height);

			@imagealphablending($src_img, true);
			$x_axis = 0;
			$y_axis = 0;

			//for a matricial 5x7 overlayer
			for ($i = 0; $i < 5; $i++)
				for ($j = 0; $j < 7; $j++)
					imagecopymerge($src_img, $image_p, $x_axis + ($i * $wm_width_adj), $y_axis + ($j * $wm_height_adj), 0, 0, $wm_width_adj, $wm_height_adj, $options['opacity']);

			if ($options['overwrite'])
				@imagejpeg($src_img, $fullPathImagem, $options['output_quality']);
			else if (!empty($options['output_path']))
				@imagejpeg($src_img, $options['output_path'], $options['output_quality']);
			else
				return false;

			imagedestroy($src_img);
			imagedestroy($wm_img);
			return true;
		}

		/**
		 * Convert bytes into human readable file size
		 * @var string value in bytes
		 * @return string human readable file size
		 * */
		public static function fileSizeConvert($bytes) {
			$bytes = floatval($bytes);
			$arBytes = array(0 => array("UNIT" => "TB", "VALUE" => pow(1024, 4)), 1 => array("UNIT" => "GB", "VALUE" => pow(1024, 3)), 2 => array("UNIT" => "MB", "VALUE" => pow(1024, 2)), 3 => array("UNIT" => "KB", "VALUE" => 1024), 4 => array("UNIT" => "B", "VALUE" => 1), );

			foreach ($arBytes as $arItem) {
				if ($bytes >= $arItem["VALUE"]) {
					$result = $bytes / $arItem["VALUE"];
					$result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
					break;
				}
			}
			return $result;
		}

		public
		/**
		 * This functions generate a random salt using sha512 hash


		 *
		 * @ return string A sha512 random hash


		 * */
		public static function generateSalt() {
			return hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
		}

		/**
		 * Hashes a passoword using a salt in SHA-512
		 *
		 * @var string Password
		 * @var string Salt to hash the string password
		 * @return string in SHA-512 hash
		 * */
		public static function hashPassword($password, $random_salt) {
			return hash('sha512', $password . $random_salt);
		}

		/**
		 * This function will encode de key password from a database string encode to be
		 * saved in file or database
		 *
		 * @var string plain text of key
		 * @return string base64 of the key coded
		 **/
		final public function encodeKey($key) {
			$t = date("H=i=s");
			// dynamic key generator requires the minimum size password length 8 characters
			$string = $key;
			$key = $t;
			for ($i = 0; $i < strlen($string); $i++)
				for ($j = 0; $j < strlen($key); $j++)
					$string[$i] = $string[$i] ^ $key[$j];

			$k = base64_encode($string) . "@@" . base64_encode($t);
			//the separetor is @@

			return base64_encode($k);
		}

		/**
		 * @var string base64 generated by encodeKey($key)
		 * @return string base64 of the key
		 **/
		final public function decodeKey($key) {
			$t = explode("@@", base64_decode($key));
			if (sizeof($t) < 2)
				return false;
			$key = base64_decode($t[1]);
			$string = base64_decode($t[0]);
			for ($i = 0; $i < strlen($string); $i++)
				for ($j = 0; $j < strlen($key); $j++)
					$string[$i] = $key[$j] ^ $string[$i];

			return base64_encode($string);
		}

	}

}
?>