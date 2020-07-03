<?php /**
 * Base business class
 * Must be extended to be useful
 * @access virtual
 * @author Karim Ahmed
 */
global $cfg;
require_once($cfg["path_gc"] . "/BaseObject.inc.php");
require_once($cfg["path_gc"] . "/DataSet.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

define("BUSINESS_FK_ERR", "_fk_error");    // ext for fk ref integrity error columns in data sets
define("BUSINESS_NT_PSD", "not passed");    // ext for fk ref integrity error columns in data sets

class Business extends BaseObject
{
    /**
     * @var mysqli $db
     */
    protected $db;

    function __construct(&$owner)
    {
        BaseObject::__construct($owner);
        $this->setMethodName("unsetMethodName");
        $this->db = CNCMysqli::instance()->getDB();
    }

    /**
     * Insert/update one or more rows from a source DataSet into a destination DBObject
     * @param DataSet $dsSource
     * @param DataAccess $dbDestination
     * @return bool
     * @access private
     */
    function updateDataAccessObject(DataSet &$dsSource, DataAccess &$dbDestination)
    {
        $this->setMethodName("updateDataaccessObject");
        if (!is_object($dsSource)) {
            $this->raiseError("dsSource is not initialised");
        }
        if (
            (!is_subclass_of($dsSource, DA_CLASSNAME_DATASET)) &
            ($dsSource->getClassname() != DA_CLASSNAME_DATASET)
        ) {
            $this->raiseError("dsSource must be subclass or class of " . DA_CLASSNAME_DATASET);
        }
        if (!is_object($dbDestination)) {
            $this->raiseError("dbDestination is not initialised");
        }
        if (!is_subclass_of($dbDestination, DA_CLASSNAME_DBENTITY)) {
            $this->raiseError(
                "dbDestination must be subclass of " .
                DA_CLASSNAME_DBENTITY
            );
        }
        if ($dsSource->columnExists($dbDestination->getPKName()) == DA_PK_NOT_SET) {
            $this->raiseError("No Primary key column in dsSource");
        }
        return ($dbDestination->replicate($dsSource));
    }

    /**
     * Check referential integrity of a column on the dataset we are about to post to a data access
     * If it fails then an error message is posted to the dataset and affected row is not posted
     * @param $columnName
     * @param DataAccess $dsSource
     * @param DataAccess $dbTarget
     * @param DBEntity $dbParent
     * @return bool
     * @access private
     */
    function validateFK($columnName, &$dsSource, &$dbTarget, &$dbParent)
    {
        $this->setMethodName("validateFK");
        if (
            ($dsSource->getClassname() != DA_CLASSNAME_DATASET) &
            (!is_subclass_of($dsSource, DA_CLASSNAME_DATASET))
        ) {
            $this->raiseError("dsSource must be subclass or class of " . DA_CLASSNAME_DATASET);
        }
        if (!is_subclass_of($dbTarget, DA_CLASSNAME_DBENTITY)) {
            $this->raiseError("dbTarget must be subclass of " . DA_CLASSNAME_DBENTITY);
        }
        if (!is_subclass_of($dbParent, DA_CLASSNAME_DBENTITY)) {
            $this->raiseError("dbParent must be subclass of " . DA_CLASSNAME_DBENTITY);
        }
        if ( // FK may be null
            ($dbTarget->getNull($columnName) == DA_ALLOW_NULL) &
            ($dsSource->getValue($columnName) == 0)
        ) {
            $ret = TRUE;
        } else {
            $dbParent->setPKValue($dsSource->getValue($columnName));
            /*
            If the FK does not exist on the parent table then add an error column and cancel post()
            operation
            */
            if (!$dbParent->getRow()) {
                $dsSource->addColumn($columnName . BUSINESS_FK_ERR, DA_STRING, DA_ALLOW_NULL);
                $dsSource->setUpdateModeUpdate();
                $dsSource->setValue(
                    $columnName . BUSINESS_FK_ERR,
                    "Not found on " . $dbParent->getTableName() . " table"
                );
                $dsSource->post();
                $dbTarget->setPostRowOff();
                $ret = FALSE;
            } else {
                $ret = TRUE;
            }
        }
        return $ret;
    }

    /**
     * Check referential integrity of a column on the dataset we are about to post to a data access
     * If it fails then an error message is posted to the dataset and affected row is not posted
     * @param $pK
     * @param Dataset|DBEntity $dbSource
     * @param DataSet $dsResult Target DataAccess(the one we are updating)
     * @return bool
     * @access private
     */
    function getDatasetByPK($pK, &$dbSource, &$dsResult)
    {

        $this->setMethodName("getDatasetByPK");
        if ($pK == '') {
            $this->raiseError($dbSource->getPKName() . ' ' . BUSINESS_NT_PSD);
        }
        $dbSource->setPKValue($pK);
        if (!$dbSource->getRow()) {
            $ret = FALSE;
        } else {
            $ret = ($this->getData($dbSource, $dsResult));
        }
        return $ret;
    }

    /**
     * Get all rows from a data access object into a dataset
     * @param DataAccess $dbSource
     * @param DataAccess $dsDestination
     * @param bool $withPK
     * @return bool
     * @access private
     */
    function getData(&$dbSource, &$dsDestination)
    {
        if (!is_object($dsDestination)) {
            $dsDestination = new Dataset($this);
        } else {
            if (
                ($dsDestination->getClassname() != DA_CLASSNAME_DATASET) &
                (!is_subclass_of($dsDestination, DA_CLASSNAME_DATASET))
            ) {
                $this->raiseError(
                    "dsDestination must be subclass or class of " .
                    DA_CLASSNAME_DATASET
                );
            }
        }
        if (gettype($dbSource) != "object")
            $this->raiseError("dbSource is not initialised");
        if (!is_subclass_of($dbSource, DA_CLASSNAME_DBENTITY))
            $this->raiseError("dbSource must be subclass of " . DA_CLASSNAME_DBENTITY);
        return ($dsDestination->replicate($dbSource));
    }
}
