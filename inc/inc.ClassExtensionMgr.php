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
	 * @var object $db reference to database object. This must be an instance
	 *      of {@link SeedDMS_Core_DatabaseAccess}.
	 * @access protected
	 */
	protected $db;

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


	function __construct($db, $extdir = '', $cachedir = '') {
		$this->db = $db;
		$this->cachedir = $cachedir;
		$this->extdir = $extdir;
	}

	function getExtensionsConfFile() { /* {{{ */
		return $this->cachedir."/extensions.php";
	} /* }}} */

	function createExtensionConf() { /* {{{ */
		$extensions = self::getExtensions();
		if($extensions) {
			$fp = fopen($this->cachedir."/extensions.php", "w");
			foreach($extensions as $_ext) {
				if(file_exists($this->extdir . "/" . $_ext . "/conf.php")) {
					$content = file_get_contents($this->extdir . "/" . $_ext . "/conf.php");
					fwrite($fp, $content);
				}
			}
			fclose($fp);
			include($this->cachedir."/extensions.php");
		}
	} /* }}} */

	function getExtensions() { /* {{{ */
		$extensions = array();
		$handle = opendir($this->extdir);
		while ($entry = readdir($handle) ) {
			if ($entry == ".." || $entry == ".")
				continue;
			else if (is_dir($this->extdir ."/". $entry))
				array_push($extensions, $entry);
		}
		closedir($handle);

		asort($extensions);
		return $extensions;
	} /* }}} */
}
