<?php /**
 * Supplier controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Exceptions\JsonHttpException;
use CNCLTD\paymentMethods\PaymentMethodsMySQLRepository;
use CNCLTD\Supplier\CreateSupplierContactRequest;
use CNCLTD\Supplier\CreateSupplierRequest;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;
use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\infra\SupplierMySQLMapper;
use CNCLTD\Supplier\SupplierId;
use CNCLTD\Supplier\UpdateSupplierContactRequest;
use CNCLTD\Supplier\UpdateSupplierRequest;
use CNCLTD\Supplier\usecases\ArchiveSupplier;
use CNCLTD\Supplier\usecases\ArchiveSupplierContact;
use CNCLTD\Supplier\usecases\CreateSupplier;
use CNCLTD\Supplier\usecases\CreateSupplierContact;
use CNCLTD\Supplier\usecases\ReactivateSupplier;
use CNCLTD\Supplier\usecases\ReactivateSupplierContact;
use CNCLTD\Supplier\usecases\UpdateSupplier;
use CNCLTD\Supplier\usecases\UpdateSupplierContact;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
// Messages
define(
    'CTSUPPLIER_MSG_SUPPLIERID_NOT_PASSED',
    'SupplierID not passed'
);
define(
    'CTSUPPLIER_MSG_SUPPLIER_ARRAY_NOT_PASSED',
    'Supplier array not passed'
);
define(
    'CTSUPPLIER_MSG_NONE_FND',
    'No suppliers found'
);
define(
    'CTSUPPLIER_MSG_SUPPLIER_NOT_FND',
    'Supplier not found'
);
// Actions
define(
    'CTSUPPLIER_ACT_SUPPLIER_INSERT',
    'insertSupplier'
);
define(
    'CTSUPPLIER_ACT_SUPPLIER_SEARCH_FORM',
    'searchForm'
);
// Page text
define(
    'CTSUPPLIER_TXT_NEW_SUPPLIER',
    'Create Supplier'
);
define(
    'CTSUPPLIER_TXT_UPDATE_SUPPLIER',
    'Update Supplier'
);

class CTSupplier extends CTCNC
{
    const GET_SUPPLIERS               = "getSuppliers";
    const GET_SUPPLIER_DATA           = "getSupplierData";
    const GET_PAYMENT_METHODS         = "getPaymentMethods";
    const UPDATE_SUPPLIER             = "updateSupplier";
    const ARCHIVE_SUPPLIER            = "archiveSupplier";
    const REACTIVATE_SUPPLIER         = "reactivateSupplier";
    const CREATE_SUPPLIER             = "createSupplier";
    const REACTIVATE_SUPPLIER_CONTACT = "reactivateSupplierContact";
    const ARCHIVE_SUPPLIER_CONTACT    = "archiveSupplierContact";
    const UPDATE_SUPPLIER_CONTACT     = "updateSupplierContact";
    const CREATE_SUPPLIER_CONTACT     = "createSupplierContact";

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $this->setMenuId(810);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::UPDATE_SUPPLIER:
                $this->supplierUpdateController();
                exit;
            case self::CREATE_SUPPLIER:
                $this->supplierCreateController();
                exit;
            case self::ARCHIVE_SUPPLIER:
                $this->supplierArchiveController();
                exit;
            case self::REACTIVATE_SUPPLIER:
                $this->supplierReactivateController();
                exit;
            case self::REACTIVATE_SUPPLIER_CONTACT:
                $this->supplierContactReactivateController();
                exit;
            case self::ARCHIVE_SUPPLIER_CONTACT:
                $this->supplierContactArchiveController();
                exit;
            case self::GET_SUPPLIERS:
                $this->getSuppliersController();
                exit;
            case self::GET_SUPPLIER_DATA:
                $this->getSupplierDataController();
                exit;
            case self::GET_PAYMENT_METHODS:
                $this->getPaymentMethodsController();
                exit;
            case self::UPDATE_SUPPLIER_CONTACT:
                $this->updateSupplierContactController();
                exit;
            case self::CREATE_SUPPLIER_CONTACT:
                $this->createSupplierContactController();
                exit;
            default:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->reactController();
                break;
        }
    }

    /**
     * Display the search form
     * @access private
     * @throws Exception
     */
    function reactController()
    {
        $this->setContainerTemplate();
        $this->setPageTitle("Supplier");
        $this->loadReactScript('SupplierComponent.js');
        $this->loadReactCSS('SupplierComponent.css');
        $this->template->setVar('CONTENTS', '<div id="reactMainActivity"></div>');
        $this->parsePage();
    }

    private function getSuppliersController()
    {
        $repo = new MySQLSupplierRepository();
        echo json_encode(["status" => "ok", "data" => $repo->getAllSuppliers()]);
    }

    private function getPaymentMethodsController()
    {
        $repo = new PaymentMethodsMySQLRepository();
        echo json_encode(["status" => "ok", "data" => $repo->getAll()]);
    }

    /**
     * @throws JsonHttpException
     */
    private function getSupplierDataController()
    {
        $supplierIdValue = @$_REQUEST['supplierId'];
        try {
            $supplierId = new SupplierId((int)$supplierIdValue);
        } catch (Exception $exception) {
            throw new JsonHttpException(400, 'Invalid supplier Id');
        }
        $repo = new MySQLSupplierRepository();
        try {
            $supplier = $repo->getById($supplierId);
        } catch (Exception $exception) {
            throw new JsonHttpException(400, "Supplier not found or failed!:" . $exception->getMessage());
        }
        echo json_encode(
            ["status" => "ok", "data" => SupplierMySQLMapper::toJSONArray($supplier)]
        );
    }

    /**
     * @throws JsonHttpException
     */
    private function supplierUpdateController()
    {
        if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
            throw new JsonHttpException(403, "You cannot make changes to a supplier!");
        }
        $data = $this->getJSONData();
        try {
            $request = UpdateSupplierRequest::fromJSONArray($data);
            $usecase = new UpdateSupplier(new MySQLSupplierRepository());
            $usecase($request);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to parse request: {$exception->getMessage()}");
        }
        echo json_encode(
            ["status" => "ok"]
        );
    }

    private function supplierArchiveController()
    {
        if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
            throw new JsonHttpException(403, "You cannot make changes to a supplier!");
        }
        $data = $this->getJSONData();
        try {
            $supplierId = new SupplierId(@$data['id']);
            $usecase    = new ArchiveSupplier(new MySQLSupplierRepository());
            $usecase($supplierId);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to parse request: {$exception->getMessage()}");
        }
        echo json_encode(
            ["status" => "ok"]
        );
    }

    private function supplierReactivateController()
    {
        if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
            throw new JsonHttpException(403, "You cannot make changes to a supplier!");
        }
        $data = $this->getJSONData();
        try {
            $supplierId = new SupplierId(@$data['id']);
            $usecase    = new ReactivateSupplier(new MySQLSupplierRepository());
            $usecase($supplierId);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to parse request: {$exception->getMessage()}");
        }
        echo json_encode(
            ["status" => "ok"]
        );
    }

    private function supplierContactReactivateController()
    {
        if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
            throw new JsonHttpException(403, "You cannot make changes to a supplier!");
        }
        $data = $this->getJSONData();
        try {
            $supplierId = new SupplierId(@$data['supplierId']);
            $contactId  = new SupplierContactId(@$data['supplierContactId']);
            $usecase    = new ReactivateSupplierContact(new MySQLSupplierRepository());
            $usecase($supplierId, $contactId);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to parse request: {$exception->getMessage()}");
        }
        echo json_encode(
            ["status" => "ok"]
        );
    }

    private function supplierContactArchiveController()
    {
        if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
            throw new JsonHttpException(403, "You cannot make changes to a supplier!");
        }
        $data = $this->getJSONData();
        try {
            $supplierId = new SupplierId(@$data['supplierId']);
            $contactId  = new SupplierContactId(@$data['supplierContactId']);
            $usecase    = new ArchiveSupplierContact(new MySQLSupplierRepository());
            $usecase($supplierId, $contactId);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to parse request: {$exception->getMessage()}");
        }
        echo json_encode(
            ["status" => "ok"]
        );
    }

    private function supplierCreateController()
    {
        if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
            throw new JsonHttpException(403, "You cannot create a supplier!");
        }
        $data = $this->getJSONData();
        try {
            $request = CreateSupplierRequest::fromJSONArray($data);
            $usecase = new CreateSupplier(new MySQLSupplierRepository());
            $usecase($request);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to parse request: {$exception->getMessage()}");
        }
        echo json_encode(
            ["status" => "ok"]
        );
    }

    private function updateSupplierContactController()
    {
        if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
            throw new JsonHttpException(403, "You cannot create a supplier!");
        }
        $data = $this->getJSONData();
        try {
            $request = UpdateSupplierContactRequest::fromJSONArray($data);
            $usecase = new UpdateSupplierContact(new MySQLSupplierRepository());
            $usecase($request);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to parse request: {$exception->getMessage()}");
        }
        echo json_encode(
            ["status" => "ok"]
        );
    }

    private function createSupplierContactController()
    {
        if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
            throw new JsonHttpException(403, "You cannot create a supplier!");
        }
        $data = $this->getJSONData();
        try {
            $request = CreateSupplierContactRequest::fromJSONArray($data);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to parse request: {$exception->getMessage()}");
        }
        try {
            $usecase = new CreateSupplierContact(new MySQLSupplierRepository());
            $usecase($request);
        } catch (Exception $exception) {
            throw new JsonHttpException(401, "Failed to create supplier: {$exception->getMessage()}");
        }
        echo json_encode(
            ["status" => "ok"]
        );

    }
}