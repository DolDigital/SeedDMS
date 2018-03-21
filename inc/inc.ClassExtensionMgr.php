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
	 * @var string $reposurl url for fetching list of extensions in repository
	 * @access protected
	 */
	protected $reposurl;

	/**
	 * @var array[] $extconf configuration of all extensions
	 * @access protected
	 */
	protected $extconf;

	/**
	 * @var string $cachedir directory where cached extension configuration
	 *      is stored
	 * @access protected
	 */
	protected $cachedir;

	/**
	 * @var string[] $errmsg list of error message from last operation
	 * @access protected
	 */
	protected $errmsgs;

	/**
	 * Compare two version
	 *
	 * This functions compares two version in the format x.x.x with x being
	 * an integer
	 *
	 * @param string $ver1
	 * @param string $ver2
	 * @return int -1 if $ver1 < $ver2, 0 if $ver1 == $ver2, 1 if $ver1 > $ver2
	 */
	static public function cmpVersion($ver1, $ver2) { /* {{{ */
		$tmp1 = explode('.', $ver1);
		$tmp2 = explode('.', $ver2);
		if(intval($tmp1[0]) < intval($tmp2[0])) {
			return -1;
		} elseif(intval($tmp1[0]) > intval($tmp2[0])) {
			return 1;
		} else {
			if(intval($tmp1[1]) < intval($tmp2[1])) {
				return -1;
			} elseif(intval($tmp1[1]) > intval($tmp2[1])) {
				return 1;
			} else {
				if(intval($tmp1[2]) < intval($tmp2[2])) {
					return -1;
				} elseif(intval($tmp1[2]) > intval($tmp2[2])) {
					return 1;
				} else {
					return 0;
				}
			}
		}
	} /* }}} */

	/**
	 * Constructor of extension manager
	 *
	 * Reads the configuration of all extensions and creates the
	 * configuration file if it does not exist and the extension dir
	 * is given
	 */
	public function __construct($extdir = '', $cachedir = '', $reposurl = '') { /* {{{ */
		$this->cachedir = $cachedir;
		$this->extdir = $extdir;
		$this->reposurl = $reposurl;
		$this->extconf = array();
		if($extdir) {
			if(!file_exists($this->getExtensionsConfFile())) {
				$this->createExtensionConf();
			}
			include($this->getExtensionsConfFile());
			if($EXT_CONF) {
				$this->extconf = $EXT_CONF;
			}
		}
	} /* }}} */

	protected function getExtensionsConfFile() { /* {{{ */
		return $this->cachedir."/extensions.php";
	} /* }}} */

	/**
	 * Get the configuration of extensions
	 *
	 * @return array[]
	 */
	public function getExtensionConfiguration() { /* {{{ */
		return $this->extconf;
	} /* }}} */

	/**
	 * Check if extension directory is writable
	 *
	 * @return boolean
	 */
	public function isWritableExitDir() { /* {{{ */
		return is_writable($this->extdir);
	} /* }}} */

	/**
	 * Create the cached file containing extension information
	 *
	 * This function will always create a file, even if no extensions
	 * are installed.
	 */
	public function createExtensionConf() { /* {{{ */
		$extensions = self::getExtensions();
		$fp = @fopen(self::getExtensionsConfFile(), "w");
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

	protected function getExtensions() { /* {{{ */
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

	/**
	 * Check content of extension directory or configuration of extension
	 *
	 * @param string|array $dir full path to extension directory or extension name
	 * or an array containing the configuration.
	 * @param boolean $noconstraints set to true if constraints to local seeddms
	 * installation shall not be checked.
	 */
	public function checkExtension($dir, $options=array()) { /* {{{ */
		$this->errmsgs = array();

		if(is_string($dir)) {
			if(!file_exists($dir)) {
				if(!file_exists($this->extdir.'/'.$dir))
					return false;
				else
					$dir = $this->extdir.'/'.$dir;
			}
			if(!file_exists($dir."/conf.php")) {
				$this->errmsgs[] = "Missing extension configuration";
				return false;
			}
			include($dir."/conf.php");
			if(!isset($EXT_CONF)) {
				$this->errmsgs[] = "Missing \$EXT_CONF in configuration";
				return false;
			}
			$extname = key($EXT_CONF);
			if(!$extname || !preg_match('/[a-zA-Z_]*/', $extname)) {
				return false;
			}

			$extconf = $EXT_CONF[$extname];
		} elseif(is_array($dir)) {
			$extconf = $dir;
			/* If just the configuration is passed, then there is no way to check
			 * for existence of files.
			 */
			$options['nofiles'] = true;
		}

		if(!isset($extconf['constraints']['depends']['seeddms'])) {
			$this->errmsgs[] = "Missing dependency on SeedDMS";
		}
		if(!isset($extconf['constraints']['depends']['php'])) {
			$this->errmsgs[] = "Missing dependency on PHP";
		}
		if(!isset($extconf['version'])) {
			$this->errmsgs[] = "Missing version information";
		}
		if(!isset($extconf['title'])) {
			$this->errmsgs[] = "Missing title";
		}
		if(!isset($extconf['author'])) {
			$this->errmsgs[] = "Missing author";
		}
		if(!isset($options['nofiles']) || $options['nofiles'] == false) {
			if(!empty($extconf['language']['file']) && !file_exists($dir."/".$extconf['language']['file'])) {
				$this->errmsgs[] = "Missing language file";
			}
			if(!empty($extconf['class']['file']) && !file_exists($dir."/".$extconf['class']['file'])) {
				$this->errmsgs[] = "Missing class file";
			}
		}

		if(!isset($options['noconstraints']) || $options['noconstraints'] == false) {
			if(isset($extconf['constraints']['depends'])) {
				foreach($extconf['constraints']['depends'] as $dkey=>$dval) {
					switch($dkey) {
					case 'seeddms':
						$version = new SeedDMS_Version;
						$tmp = explode('-', $dval, 2);
						if(self::cmpVersion($tmp[0], $version->version()) > 0 || ($tmp[1] && self::cmpVersion($tmp[1], $version->version()) < 0))
							$this->errmsgs[] = sprintf("Incorrect SeedDMS version (needs version %s)", $extconf['constraints']['depends']['seeddms']);
						break;
					case 'php':
						$tmp = explode('-', $dval, 2);
						if(self::cmpVersion($tmp[0], phpversion()) > 0 || ($tmp[1] && self::cmpVersion($tmp[1], phpversion()) < 0))
							$this->errmsgs[] = sprintf("Incorrect PHP version (needs version %s)", $extconf['constraints']['depends']['php']);
						break;
					case 'phpext':
						if(is_array($dval) && $dval) {
							$extlist = get_loaded_extensions();
							foreach($dval as $d) {
								if(!in_array($d, $extlist))
									$this->errmsgs[] = sprintf("Missing php extension '%s'", $d);
							}
						}
						break;
					default:
						$tmp = explode('-', $dval, 2);
						if(isset($GLOBALS['EXT_CONF'][$dkey]['version'])) {
							if(self::cmpVersion($tmp[0], $GLOBALS['EXT_CONF'][$dkey]['version']) > 0 || ($tmp[1] && self::cmpVersion($tmp[1], $GLOBALS['EXT_CONF'][$dkey]['version']) < 0))
								$this->errmsgs[] = sprintf("Incorrect version of extension '%s' (needs version '%s' but provides '%s')", $dkey, $dval, $GLOBALS['EXT_CONF'][$dkey]['version']);
						} else {
							$this->errmsgs[] = sprintf("Missing extension or version for '%s'", $dkey);
						}
						break;
					}
				}
			}
		}

		if($this->errmsgs)
			return false;

		return true;
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

	/**
	 * Update an extension
	 *
	 * This function will replace an existing extension or add a new extension
	 * The passed file has to be zipped content of the extension directory not
	 * including the directory itself.
	 *
	 * @param string $file name of extension archive
	 * @return boolean true on success, othewise false
	 */
	public function updateExtension($file) { /* {{{ */
		$newdir = $this->cachedir ."/ext.new";
		if(!mkdir($newdir, 0755)) {
			$this->errmsgs[] = "Cannot create temp. extension directory";
			return false;
		}
		$cmd = "cd ".$newdir."; unzip ".$file;
		exec($cmd);

		if(!self::checkExtension($newdir)) {
			self::rrmdir($newdir);
			return false;
		}

		include($newdir."/conf.php");
		$extname = key($EXT_CONF);

		/* Create the target directory */
		if(!is_dir($this->extdir)) {
			if(!mkdir($this->extdir, 0755)) {
				$this->errmsgs[] = "Cannot create extension directory";
				self::rrmdir($newdir);
				return false;
			}
		} elseif(is_dir($this->extdir ."/". $extname)) {
			$this->rrmdir($this->extdir ."/". $extname);
		}
		/* Move the temp. created ext directory to the final location */
		rename($newdir, $this->extdir ."/". $extname);

		return true;
	} /* }}} */

	/**
	 * Import list of extension from repository
	 *
	 * @param boolean $force force download even if file already exists
	 */
	public function getExtensionList() { /* {{{ */
		if(file_exists($this->cachedir."/repository.json")) {
			return file($this->cachedir."/repository.json");
		} else {
			return array();
		}
	} /* }}} */

	/**
	 * Import list of extension from repository
	 *
	 * @param boolean $force force download even if file already exists
	 */
	public function updateExtensionList($version='', $force=false) { /* {{{ */
		if($this->reposurl) {
			if(!file_exists($this->cachedir."/repository.json") || $force) {
				$file = @file_get_contents($this->reposurl.($version ? '?seeddms_version='.$version : ''));
				if(is_array($http_response_header)) {
					$parts=explode(' ',$http_response_header[0]);
					if(count($parts)>1) //HTTP/1.0 <code> <text>
						if(intval($parts[1]) != 200) {
							$this->errmsgs[] = 'Getting extension list returned http code ('.$parts[1].')';
							return false;
						}
				}
				file_put_contents($this->cachedir."/repository.json", $file);
			}
			return true;
		} else {
			return false;
		}
	} /* }}} */

	/**
	 * Return last error message
	 *
	 * @return string
	 */
	public function getErrorMsg() { /* {{{ */
		if($this->errmsgs)
			return $this->errmsgs[0];
		else
			return '';
	} /* }}} */

	/**
	 * Return all error messages
	 *
	 * @return string[]
	 */
	public function getErrorMsgs() { /* {{{ */
		return $this->errmsgs;
	} /* }}} */
}
