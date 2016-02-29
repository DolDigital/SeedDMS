<?php
/**
 * Implementation of a access control list.
 *
 * SeedDMS uses access control list for setting permission,
 * on various operations.
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2016 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Class to represent an access request object
 *
 * This class provides a model for access request objects.
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Acl {
	/**
	 * @var object $dms reference to dms object.
	 * @access public
	 */
	protected $dms;

	/**
	 * Create a new instance of an acl
	 *
	 * @param object $dms object of dms
	 * @return object instance of SeedDMS_Acl
	 */
	function __construct($dms) { /* {{{ */
		$this->dmѕ = $dms;
	} /* }}} */

	public function check($aro, $aco) { /* {{{ */
		$db = $dms->getDB();
		$queryStr = "SELECT * FROM tblArosAcos WHERE aro=".$aro->getID()." AND aco=".$aco->getID();
		$resArr = $db->getResultArray($queryStr);
		if (is_bool($resArr) && $resArr == false)
			return false;
		if (count($resArr) != 1)
			return false;
		$resArr = $resArr[0];
		return($resArr['read'] == 1 ? true : false);

	} /* }}} */
}

/**
 * Class to represent an access request/controll object
 *
 * This class provides a model for access request/controll objects.
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_AroAco { /* {{{ */
	/**
	 * @var object $dms reference to dms object.
	 * @access protected
	 */
	public $dms;

	/**
	 * @var integer id of access request object
	 */
	protected $_id;

	/**
	 * @var string alias of access request object
	 */
	protected $_alias;

	/**
	 * @var object object of access request object
	 */
	protected $_object;

	/**
	 * Create a new instance of an aro
	 *
	 * @param object $dms object of dms
	 * @return object instance of SeedDMS_Aco
	 */
	function __construct($dms, $id, $object, $alias) { /* {{{ */
		$this->dmѕ = $dms;
		$this->_id = $id;
		$this->_object = $object;
		$this->_alias = $alias;
	} /* }}} */

	public function setDMS($dms) { /* {{{ */
		$this->dms = $dms;
	} /* }}} */

	public function getDMS() { /* {{{ */
		return($this->dms);
	} /* }}} */

	public function getID() { /* {{{ */
		return $this->_id;
	} /* }}} */

	public function getAlias() { /* {{{ */
		return $this->_alias;
	} /* }}} */

	public function getObject() { /* {{{ */
		return $this->_object;
	} /* }}} */
} /* }}} */

/**
 * Class to represent an access request object
 *
 * This class provides a model for access request objects.
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Aro extends SeedDMS_AroAco { /* {{{ */

	/**
	 * Create a new instance of an aro
	 *
	 * @param object $dms object to access the underlying database
	 * @return object instance of SeedDMS_Aro
	 */
	function __construct($dms, $id, $object, $alias) { /* {{{ */
		parent::__construct($dms, $id, $object, $alias);
	} /* }}} */

	public static function getInstance($id, $dms) { /* {{{ */
		$db = $dms->getDB();
		if(is_int($id)) {
			$queryStr = "SELECT * FROM tblAros WHERE id = " . (int) $id;
			$resArr = $db->getResultArray($queryStr);
			if (is_bool($resArr) && $resArr == false)
				return null;
			if (count($resArr) != 1)
				return null;
			$resArr = $resArr[0];
		} elseif(is_object($id)) {
			if($dms->getClassname('role') == get_class($id)) {
				$model = 'Role';
				$queryStr = "SELECT * FROM tblAros WHERE model=".$db->qstr($model)." AND foreignid=".$id->getID();
				$resArr = $db->getResultArray($queryStr);
				if (is_bool($resArr) && $resArr == false)
					return null;
				if (count($resArr) != 1)
					return null;
				$parentid = $resArr[0]['parent'];
				$resArr = $resArr[0];
			} else {
				return null;
			}
		}

		if($resArr['model'] == 'Role') {
			$classname = $dms->getClassname('role');
			$object = $classname::getInstance($resArr['foreignid'], $dms);
		} else {
			$object = null;
		}

		$aro = new self($dms, $resArr["id"], $object, $resArr['alias']);
		return $aro;
	} /* }}} */

} /* }}} */

/**
 * Class to represent an access control object
 *
 * This class provides a model for access control objects.
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_Aco extends SeedDMS_AroAco{ /* {{{ */

	/**
	 * Create a new instance of an aco
	 *
	 * @param object $dms object to access the underlying database
	 * @return object instance of SeedDMS_Aco
	 */
//	function __construct($dms, $id, $object, $alias) { /* {{{ */
//		parent::__construct($dms, $id, $object, $alias);
//	} /* }}} */

	public static function getInstance($id, $dms) { /* {{{ */
		$db = $dms->getDB();
		if(is_int($id)) {
			$queryStr = "SELECT * FROM tblAcos WHERE id = " . (int) $id;
			$resArr = $db->getResultArray($queryStr);
			if (is_bool($resArr) && $resArr == false)
				return null;
			if (count($resArr) != 1)
				return null;
			$resArr = $resArr[0];
		} elseif(is_string($id)) {
			$tmp = explode('/', $id);
			$parentid = 0;
			foreach($tmp as $part) {
				$queryStr = "SELECT * FROM tblAcos WHERE alias = " . $db->qstr($part);
				if($parentid)
					$queryStr .= " AND parent=".$parentid;
				$resArr = $db->getResultArray($queryStr);
				if (is_bool($resArr) && $resArr == false)
					return null;
				if (count($resArr) != 1)
					return null;
				$parentid = $resArr[0]['parent'];
			}
			$resArr = $resArr[0];
		}

		if($resArr['model'] == 'Document') {
			$classname = $dms->getClassname('document');
			$object = $classname::getInstance($resArr['foreignid'], $dms);
		} elseif($resArr['model'] == 'Folder') {
			$classname = $dms->getClassname('focument');
			$object = $classname::getInstance($resArr['foreignid'], $dms);
		} else {
			$object = null;
		}

		$aco = new SeedDMS_Aco($dms, $resArr["id"], $object, $resArr['alias']);
		$aco->setDMS($dms);
		return $aco;
	} /* }}} */

	public function getChildren() { /* {{{ */
		$dms = $this->getDMS();
		$db = $dms->getDB();
		$queryStr = "SELECT * FROM tblAcos WHERE parent = " . $this->_id;
		$resArr = $db->getResultArray($queryStr);
		if (is_bool($resArr) && $resArr == false)
			return null;
		if (count($resArr) != 1)
			return null;

		$acos = array();
		foreach($resArr as $row) {
			$aco = new SeedDMS_Aco($this->dms, $row["id"], null, $row['alias']);
			$aco->setDMS($dms);
			$acos[] = $aco;
		}
		return $acos;
	} /* }}} */

	public function getPermission($aro) { /* {{{ */
		$dms = $this->getDMS();
		$db = $dms->getDB();
		$queryStr = "SELECT * FROM tblArosAcos WHERE aro=".$aro->getID()." AND aco=".$this->_id;
		$resArr = $db->getResultArray($queryStr);
		if (is_bool($resArr) && $resArr == false)
			return false;
		if (count($resArr) != 1)
			return 0;
		return $resArr[0]['read'];
	} /* }}} */

	public static function getRoot($dms) { /* {{{ */
		$db = $dms->getDB();
		$queryStr = "SELECT * FROM tblAcos WHERE parent IS NULL";
		$resArr = $db->getResultArray($queryStr);
		if (is_bool($resArr) && $resArr == false)
			return null;
		if (count($resArr) != 1)
			return null;

		$acos = array();
		foreach($resArr as $row) {
			$aco = new SeedDMS_Aco($dms, $row["id"], null, $row['alias']);
			$aco->setDMS($dms);
			$acos[] = $aco;
		}
		return $acos;
	} /* }}} */
} /* }}} */
