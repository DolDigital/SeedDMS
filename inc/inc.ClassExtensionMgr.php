<?php
/**
 * Implementation of an extension management.
 *
 * SeedDMS can be extended by extensions. Extension usually implement
 * hook.
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2011 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Class to represent an extension manager
 *
 * This class provides some very basic methods to manage extensions.
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2011 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Extension_Mgr {
	/**
	 * @var string $extdir directory where extensions are located
	 * @access protected
	 */
	protected $extdir;

	/**
	 * @var string $cachedir directory where cached extension configuration
	 *      is stored
	 * @access protected
	 */
	protected $cachedir;


	function __construct($extdir = '', $cachedir = '') {
		$this->cachedir = $cachedir;
		$this->extdir = $extdir;
	}

	function getExtensionsConfFile() { /* {{{ */
		return $this->cachedir."/extensions.php";
	} /* }}} */

	/**
	 * Create the cached file containing extension information
	 *
	 * This function will always create a file, even if no extensions
	 * are installed.
	 */
	function createExtensionConf() { /* {{{ */
		$extensions = self::getExtensions();
		$fp = fopen(self::getExtensionsConfFile(), "w");
		if($fp) {
			if($extensions) {
				foreach($extensions as $_ext) {
					if(file_exists($this->extdir . "/" . $_ext . "/conf.php")) {
						$content = file_get_contents($this->extdir . "/" . $_ext . "/conf.php");
						fwrite($fp, $content);
					}
				}
			}
			fclose($fp);
			return true;
		} else {
			return false;
		}
	} /* }}} */

	function getExtensions() { /* {{{ */
		$extensions = array();
		if(file_exists($this->extdir)) {
			$handle = opendir($this->extdir);
			while ($entry = readdir($handle) ) {
				if ($entry == ".." || $entry == ".")
					continue;
				else if (is_dir($this->extdir ."/". $entry))
					array_push($extensions, $entry);
			}
			closedir($handle);

			asort($extensions);
		}
		return $extensions;
	} /* }}} */

	public function createArchive($extname, $version) { /* {{{ */
		if(!is_dir($this->extdir ."/". $extname))
			return false;

		$tmpfile = $this->cachedir."/".$extname."-".$version.".zip";

		$cmd = "cd ".$this->extdir."/".$extname."; zip -r ".$tmpfile." .";
		exec($cmd);

		return $tmpfile;
	} /* }}} */

	static protected function rrmdir($dir) { /* {{{ */
		if (is_dir($dir)) { 
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") self::rrmdir($dir."/".$object); else unlink($dir."/".$object); 
				} 
			} 
			reset($objects); 
			rmdir($dir); 
		} 
	} /* }}} */

	public function updateExtension($file) { /* {{{ */
		$newdir = $this->cachedir ."/ext.new";
		if(!mkdir($newdir, 0755)) {
			return false;
		}
		$cmd = "cd ".$newdir."; unzip ".$file;
		exec($cmd);

		if(!file_exists($newdir."/conf.php")) {
			self::rrmdir($newdir);
			return false;
		}

		include($newdir."/conf.php");
		if(!isset($EXT_CONF)) {
			self::rrmdir($newdir);
			return false;
		}
		$extname = key($EXT_CONF);
		if(!$extname || !preg_match('/[a-zA-Z_]*/', $extname)) {
			self::rrmdir($newdir);
			return false;
		}

		if(!is_dir($this->extdir)) {
			if(!mkdir($this->extdir, 0755)) {
				self::rrmdir($newdir);
				return false;
			}
		} elseif(is_dir($this->extdir ."/". $extname)) {
			$this->rrmdir($this->extdir ."/". $extname);
		}
		rename($newdir, $this->extdir ."/". $extname);

		return true;
	} /* }}} */

}
