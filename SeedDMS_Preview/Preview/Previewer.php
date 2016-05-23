<?php
/**
 * Implementation of preview documents
 *
 * @category   DMS
 * @package    SeedDMS_Preview
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010, Uwe Steinmann
 * @version    Release: @package_version@
 */


/**
 * Class for managing creation of preview images for documents.
 *
 * @category   DMS
 * @package    SeedDMS_Preview
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2011, Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Preview_Previewer {
	/**
	 * @var string $cacheDir location in the file system where all the
	 *      cached data like thumbnails are located. This should be an
	 *      absolute path.
	 * @access public
	 */
	public $previewDir;

	/**
	 * @var integer $width maximum width/height of resized image
	 * @access protected
	 */
	protected $width;

	/**
	 * @var array $converters list of mimetypes and commands for converting
	 * file into preview image
	 * @access protected
	 */
	protected $converters;

	/**
	 * @var integer $timeout maximum time for execution of external commands
	 * @access protected
	 */
	protected $timeout;

	function __construct($previewDir, $width=40, $timeout=5) {
		if(!is_dir($previewDir)) {
			if (!SeedDMS_Core_File::makeDir($previewDir)) {
				$this->previewDir = '';
			} else {
				$this->previewDir = $previewDir;
			}
		} else {
			$this->previewDir = $previewDir;
		}
		$this->width = intval($width);
		$this->converters = array(
			'image/png' => "convert -resize %wx '%f' '%o'",
			'image/gif' => "convert -resize %wx '%f' '%o'",
			'image/jpg' => "convert -resize %wx '%f' '%o'",
			'image/jpeg' => "convert -resize %wx '%f' '%o'",
			'image/svg+xml' => "convert -resize %wx '%f' '%o'",
			'text/plain' => "convert -resize %wx '%f' '%o'",
			'application/pdf' => "convert -density 100 -resize %wx '%f[0]' '%o'",
			'application/postscript' => "convert -density 100 -resize %wx '%f[0]' '%o'",
			'application/x-compressed-tar' => "tar tzvf '%f' | convert -density 100 -resize %wx text:-[0] '%o",
		);
		$this->timeout = intval($timeout);
	}

	static function execWithTimeout($cmd, $timeout=5) { /* {{{ */
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);
		$pipes = array();
	 
		$timeout += time();
		$process = proc_open($cmd, $descriptorspec, $pipes);
		if (!is_resource($process)) {
			throw new Exception("proc_open failed on: " . $cmd);
		}
			 
		$output = '';
		do {
			$timeleft = $timeout - time();
			$read = array($pipes[1]);
			$write = NULL;
			$exeptions = NULL;
			stream_select($read, $write, $exeptions, $timeleft, 200000);
					 
			if (!empty($read)) {
				$output .= fread($pipes[1], 8192);
													}
		} while (!feof($pipes[1]) && $timeleft > 0);
 
		if ($timeleft <= 0) {
			proc_terminate($process);
			throw new Exception("command timeout on: " . $cmd);
		} else {
			return $output;
		}
	} /* }}} */

	/**
	 * Set a list of converters
	 *
	 * @param array list of converters. The key of the array contains the mimetype
	 * and the value is the command to be called for creating the preview
	 */
	function setConverters($arr) { /* {{{ */
		$this->converters = array_merge($arr, $this->converters);
	} /* }}} */

	/**
	 * Return the physical filename of the preview image on disk
	 *
	 * @param object $object document content or document file
	 * @param integer $width width of preview image
	 * @return string file name of preview image
	 */
	protected function getFileName($object, $width) { /* {{{ */
		if(!$object)
			return false;

		$document = $object->getDocument();
		$dir = $this->previewDir.'/'.$document->getDir();
		switch(get_class($object)) {
			case "SeedDMS_Core_DocumentContent":
				$target = $dir.'p'.$object->getVersion().'-'.$width;
				break;
			case "SeedDMS_Core_DocumentFile":
				$target = $dir.'f'.$object->getID().'-'.$width;
				break;
			default:
				return false;
		}
		return $target;
	} /* }}} */

	/**
	 * Create a preview image for a given file
	 *
	 * This method creates a preview image in png format for a regular file
	 * in the file system and stores the result in the directory $dir relative
	 * to the configured preview directory. The filename of the resulting preview
	 * image is either $target.png (if set) or md5($infile)-$width.png.
	 * The $mimetype is used to select the propper conversion programm.
	 * An already existing preview image is replaced.
	 *
	 * @param string $infile name of input file including full path
	 * @param string $dir directory relative to $this->previewDir
	 * @param string $mimetype MimeType of input file
	 * @param integer $width width of generated preview image
	 * @param string $target optional name of preview image (without extension)
	 * @return boolean true on success, false on failure
	 */
	public function createRawPreview($infile, $dir, $mimetype, $width=0, $target='') { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;
		if(!is_dir($this->previewDir.'/'.$dir)) {
			if (!SeedDMS_Core_File::makeDir($this->previewDir.'/'.$dir)) {
				return false;
			}
		}
		if(!file_exists($infile))
			return false;
		if(!$target)
			$target = $this->previewDir.$dir.md5($infile).'-'.$width;
		if($target != '' && (!file_exists($target.'.png') || filectime($target.'.png') < filectime($infile))) {
			$cmd = '';
			if(isset($this->converters[$mimetype])) {
				$cmd = str_replace(array('%w', '%f', '%o'), array($width, $infile, $target.'.png'), $this->converters[$mimetype]);
			}
			/*
			switch($mimetype) {
				case "image/png":
				case "image/gif":
				case "image/jpeg":
				case "image/jpg":
				case "image/svg+xml":
					$cmd = 'convert -resize '.$width.'x '.$infile.' '.$target.'.png';
					break;
				case "application/pdf":
				case "application/postscript":
					$cmd = 'convert -density 100 -resize '.$width.'x '.$infile.'[0] '.$target.'.png';
					break;
				case "text/plain":
					$cmd = 'convert -resize '.$width.'x '.$infile.'[0] '.$target.'.png';
					break;
				case "application/x-compressed-tar":
					$cmd = 'tar tzvf '.$infile.' | convert -density 100 -resize '.$width.'x text:-[0] '.$target.'.png';
					break;
			}
			 */
			if($cmd) {
				//exec($cmd);
				try {
					self::execWithTimeout($cmd, $this->timeout);
				} catch(Exception $e) {
				}
			}
			return true;
		}
		return true;
			
	} /* }}} */

	/**
	 * Create preview image
	 *
	 * This function creates a preview image for the given document
	 * content or document file. It internally uses
	 * {@link SeedDMS_Preview::createRawPreview()}. The filename of the
	 * preview image is created by {@link SeedDMS_Preview_Previewer::getFileName()}
	 *
	 * @param object $object instance of SeedDMS_Core_DocumentContent
	 * or SeedDMS_Core_DocumentFile
	 * @param integer $width desired width of preview image
	 * @return boolean true on success, false on failure
	 */
	public function createPreview($object, $width=0) { /* {{{ */
		if(!$object)
			return false;

		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		$document = $object->getDocument();
		$file = $document->_dms->contentDir.$object->getPath();
		$target = $this->getFileName($object, $width);
		return $this->createRawPreview($file, $document->getDir(), $object->getMimeType(), $width, $target);

		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;
		$document = $object->getDocument();
		$dir = $this->previewDir.'/'.$document->getDir();
		if(!is_dir($dir)) {
			if (!SeedDMS_Core_File::makeDir($dir)) {
				return false;
			}
		}
		$file = $document->_dms->contentDir.$object->getPath();
		if(!file_exists($file))
			return false;
		$target = $this->getFileName($object, $width);
		if($target !== false && (!file_exists($target.'.png') || filectime($target.'.png') < $object->getDate())) {
			$cmd = '';
			if(isset($this->converters[$object->getMimeType()])) {
				$cmd = str_replace(array('%w', '%f', '%o'), array($width, $file, $target), $this->converters[$object->getMimeType()]);
			}
			/*
			switch($object->getMimeType()) {
				case "image/png":
				case "image/gif":
				case "image/jpeg":
				case "image/jpg":
				case "image/svg+xml":
					$cmd = 'convert -resize '.$width.'x '.$file.' '.$target.'.png';
					break;
				case "application/pdf":
				case "application/postscript":
					$cmd = 'convert -density 100 -resize '.$width.'x '.$file.'[0] '.$target.'.png';
					break;
				case "text/plain":
					$cmd = 'convert -resize '.$width.'x '.$file.'[0] '.$target.'.png';
					break;
				case "application/x-compressed-tar":
					$cmd = 'tar tzvf '.$file.' | convert -density 100 -resize '.$width.'x text:-[0] '.$target.'.png';
					break;
			}
			 */
			if($cmd) {
				//exec($cmd);
				try {
					self::execWithTimeout($cmd, $this->timeout);
				} catch(Exception $e) {
				}
			}
			return true;
		}
		return true;
	} /* }}} */

	/**
	 * Check if a preview image already exists.
	 *
	 * This function is a companion to {@link SeedDMS_Preview_Previewer::createRawPreview()}.
	 *
	 * @param string $infile name of input file including full path
	 * @param string $dir directory relative to $this->previewDir
	 * @param integer $width desired width of preview image
	 * @return boolean true if preview exists, otherwise false
	 */
	public function hasRawPreview($infile, $dir, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;
		$target = $this->previewDir.$dir.md5($infile).'-'.$width;
		if($target !== false && file_exists($target.'.png') && filectime($target.'.png') >= filectime($infile)) {
			return true;
		}
		return false;
	} /* }}} */

	/**
	 * Check if a preview image already exists.
	 *
	 * This function is a companion to {@link SeedDMS_Preview_Previewer::createPreview()}.
	 *
	 * @param object $object instance of SeedDMS_Core_DocumentContent
	 * or SeedDMS_Core_DocumentFile
	 * @param integer $width desired width of preview image
	 * @return boolean true if preview exists, otherwise false
	 */
	public function hasPreview($object, $width=0) { /* {{{ */
		if(!$object)
			return false;

		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;
		$target = $this->getFileName($object, $width);
		if($target !== false && file_exists($target.'.png') && filectime($target.'.png') >= $object->getDate()) {
			return true;
		}
		return false;
	} /* }}} */

	/**
	 * Return a preview image.
	 *
	 * This function returns the content of a preview image if it exists..
	 *
	 * @param string $infile name of input file including full path
	 * @param string $dir directory relative to $this->previewDir
	 * @param integer $width desired width of preview image
	 * @return boolean/string image content if preview exists, otherwise false
	 */
	public function getRawPreview($infile, $dir, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;

		$target = $this->previewDir.$dir.md5($infile).'-'.$width;
		if($target && file_exists($target.'.png')) {
			readfile($target.'.png');
		}
	} /* }}} */

	/**
	 * Return a preview image.
	 *
	 * This function returns the content of a preview image if it exists..
	 *
	 * @param object $object instance of SeedDMS_Core_DocumentContent
	 * or SeedDMS_Core_DocumentFile
	 * @param integer $width desired width of preview image
	 * @return boolean/string image content if preview exists, otherwise false
	 */
	public function getPreview($object, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;

		$target = $this->getFileName($object, $width);
		if($target && file_exists($target.'.png')) {
			readfile($target.'.png');
		}
	} /* }}} */

	/**
	 * Return file size preview image.
	 *
	 * @param object $object instance of SeedDMS_Core_DocumentContent
	 * or SeedDMS_Core_DocumentFile
	 * @param integer $width desired width of preview image
	 * @return boolean/integer size of preview image or false if image
	 * does not exist
	 */
	public function getFilesize($object, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		$target = $this->getFileName($object, $width);
		if($target && file_exists($target.'.png')) {
			return(filesize($target.'.png'));
		} else {
			return false;
		}

	} /* }}} */

	/**
	 * Delete preview image.
	 *
	 * @param object $object instance of SeedDMS_Core_DocumentContent
	 * or SeedDMS_Core_DocumentFile
	 * @param integer $width desired width of preview image
	 * @return boolean true if deletion succeded or false if file does not exist
	 */
	public function deletePreview($object, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;

		$target = $this->getFileName($object, $width);
		if($target && file_exists($target.'.png')) {
			return(unlink($target.'.png'));
		} else {
			return false;
		}
	} /* }}} */

	static function recurseRmdir($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? SeedDMS_Preview_Previewer::recurseRmdir("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	/**
	 * Delete all preview images belonging to a document
	 *
	 * This function removes the preview images of all versions and
	 * files of a document including the directory. It actually just
	 * removes the directory for the document in the cache.
	 *
	 * @param object $document instance of SeedDMS_Core_Document
	 * @return boolean true if deletion succeded or false if file does not exist
	 */
	public function deleteDocumentPreviews($document) { /* {{{ */
		if(!$this->previewDir)
			return false;

		$dir = $this->previewDir.'/'.$document->getDir();
		return SeedDMS_Preview_Previewer::recurseRmdir($dir);

	} /* }}} */
}
?>
