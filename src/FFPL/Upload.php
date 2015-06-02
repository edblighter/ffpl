<?php
use FFPL\Utils;
namespace FFPL {
	Class Upload {
		public function doUpload($file, $output) {
			$fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);
			$newFileName = md5(uniqid($file['name'])) . "." . $fileExtension;
			$fileOutput = $output . $newFileName;
			@$perms = fileperms($output);
			//need owner or group write permissions to output folder (same owner or group
			// from server)
			if (is_dir($output) && ((($perms & 0x0080) && (fileowner($output) == posix_geteuid())) || (($perms & 0x0010) && (filegroup($output) == posix_getegid())))) :
				if (move_uploaded_file($file["tmp_name"], $fileOutput)) :
					chmod($fileOutput, 0666);
					return array('oldFileName' => $file['name'], 'outputFileName' => $newFileName, 'extension' => $fileExtension, 'size' => filesize($fileOutput));
				endif;
			endif;
			return false;
		}

	}

}
?>