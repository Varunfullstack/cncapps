<?php

/**
 * Quotation template controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUQuotationTemplate.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
class CTQuoteTemplates extends CTCNC
{
    public $dsQuotationTemplate;
    /** @var BUQuotationTemplate */
    public $buQuotationTemplate;
    const CONST_TEMPLATES = "templates";
    function __construct(
        $requestMethod,
        $postVars,
        $getVars,
        $cookieVars,
        $cfg
    ) {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            SALES_PERMISSION
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(311);
        $this->buQuotationTemplate = new BUQuotationTemplate($this);
        $this->dsQuotationTemplate = new DSForm($this);
        $this->dsQuotationTemplate->copyColumnsFrom($this->buQuotationTemplate->dbeQuotationTemplate);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(MAINTENANCE_PERMISSION);
        switch ($this->getAction()) {
            case self::CONST_TEMPLATES:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getTemplates(), JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->updateTemplate(), JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deleteTemplate(), JSON_NUMERIC_CHECK);
                        break;
                }
                exit;
            case CTCNC_ACT_DISP_TEMPLATE_QUOTATION_POPUP:
                $this->displayPopup();
                break;            
            default:
                $this->displayForm();
                break;
        }
    }


    /**
     * Display the popup selector form
     * @access private
     * @throws Exception
     */
    function displayPopup()
    {

        $dsResults = new DataSet($this);
        $this->buQuotationTemplate->getByNameMatch($this->getParam('description'), $dsResults);

        $this->template->set_var(
            array(
                'parentIDField'               => $this->getParam('parentIDField'),
                'parentDescField'             => $this->getParam('parentDescField'),
                'parentLinkedSalesOrderField' => $this->getParam('parentLinkedSalesOrderField')
            )
        );
        if ($dsResults->rowCount() == 1) {
            $this->setTemplateFiles(
                'QuotationTemplateSelect',
                'QuotationTemplateSelectOne.inc'
            );
            // This template runs a javascript function NOT inside HTML and so must use stripslashes()
            $this->template->set_var(
                array(
                    'description'        => addslashes($dsResults->getValue(DBEQuotationTemplate::description)),
                    // for javascript
                    'id'                 => $dsResults->getValue(DBEQuotationTemplate::id),
                    'linkedSalesOrderID' => $dsResults->getValue(DBEQuotationTemplate::linkedSalesOrderId),
                )
            );
        } else {
            if ($dsResults->rowCount() == 0) {
                $this->template->set_var(
                    array(
                        'description' => $this->getParam(DBEQuotationTemplate::description),
                    )
                );
                $this->setTemplateFiles(
                    'QuotationTemplateSelect',
                    'QuotationTemplateSelectNone.inc'
                );
            }
            if ($dsResults->rowCount() > 1) {
                $this->setTemplateFiles(
                    'QuotationTemplateSelect',
                    'QuotationTemplateSelectPopup.inc'
                );
            }

            // Parameters
            $this->setPageTitle('Quotation Template Selection');
            if ($dsResults->rowCount() > 0) {
                $this->template->set_block(
                    'QuotationTemplateSelect',
                    'quotationTemplateBlock',
                    'quotationTemplates'
                );
                while ($dsResults->fetchNext()) {
                    $this->template->set_var(
                        array(
                            'description'        => addslashes($dsResults->getValue(DBEQuotationTemplate::description)),
                            'id'                 => $dsResults->getValue(DBEQuotationTemplate::id),
                            'linkedSalesOrderID' => $dsResults->getValue(DBEQuotationTemplate::linkedSalesOrderId),
                        )
                    );

                    $this->template->parse(
                        'quotationTemplates',
                        'quotationTemplateBlock',
                        true
                    );
                }
            }
        }
        $this->template->parse(
            'CONTENTS',
            'QuotationTemplateSelect',
            true
        );
        $this->parsePage();
    } // end function editFurther Action()
    /**
     * Export expenses that have not previously been exported
     * @access private
     * @throws Exception     
     */
    function displayForm()
    {
        $this->setPageTitle('Quote Templates');
        $this->setTemplateFiles(
            'QuotationTemplateList',
            'QuotationTemplateList.inc'
        );
        $this->template->parse(
            'CONTENTS',
            'QuotationTemplateList',
            true
        );
        $this->loadReactScript('QuoteTemplatesComponent.js');
        $this->loadReactCSS('QuoteTemplatesComponent.css');
        $this->parsePage();
    }
    public function getTemplates()
    {
        $templates = [];
        $this->setMethodName('displayList');
        $dbeQuotationTemplate = new DBEQuotationTemplate($this);
        $dbeQuotationTemplate->getRows(DBEQuotationTemplate::sortOrder);
        while ($dbeQuotationTemplate->fetchNext()) {

            $quotationTemplateID = $dbeQuotationTemplate->getValue(DBEQuotationTemplate::id);
            $templates[] =
                array(
                    'id'                  => $quotationTemplateID,
                    'description'         => Controller::htmlDisplayText(
                        $dbeQuotationTemplate->getValue(DBEQuotationTemplate::description)
                    ),
                    'linkedSalesOrderId'  => Controller::htmlDisplayText(
                        $dbeQuotationTemplate->getValue(DBEQuotationTemplate::linkedSalesOrderId)
                    ),
                    'linkedSalesOrderURL' => "SalesOrder.php?action=displaySalesOrder&ordheadID= " . $dbeQuotationTemplate->getValue(
                        DBEQuotationTemplate::linkedSalesOrderId
                    ),
                    "sortOrder" => $dbeQuotationTemplate->getValue(DBEQuotationTemplate::sortOrder)
                );
        }
        return $this->success($templates);
    }


    /**
     * Edit/Add Further Action
     * @access private
     * @throws Exception
     */
    function updateTemplate()
    {
        $body = $this->getBody(true);
        $this->setMethodName('updateTemplate');
        $dsQuotationTemplate = &$this->dsQuotationTemplate; // ref to class var
        $id = @$_REQUEST["id"];
        if ($id) {
            $this->buQuotationTemplate->getQuotationTemplateByID(
                $id,
                $dsQuotationTemplate
            );
        } else {     // creating new;
            $dsQuotationTemplate->initialise();
            $dsQuotationTemplate->setValue(
                DBEQuotationTemplate::id,
                null
            );
            $dbeQuotationTemplate = new DBEQuotationTemplate($this);
            $body[DBEQuotationTemplate::sortOrder] = $dbeQuotationTemplate->getNextSortOrder();
        }
        //$this->dsQuotationTemplate->debug=true;
        $this->formError = (!$this->dsQuotationTemplate->populateFromArray(["data" => $body]));
        if (!$this->dsQuotationTemplate->getValue(DBEQuotationTemplate::id)) {
            $this->setAction(ctQuotationTemplates_ACT_EDIT);
        } else {
            $this->setAction(ctQuotationTemplates_ACT_CREATE);
        }
        if ($this->formError) {
            return $this->fail(APIException::badRequest, $this->formError);
        }
        if (!$this->buQuotationTemplate->updateQuotationTemplate($this->dsQuotationTemplate)) {
            return $this->fail(APIException::badRequest, $this->dsQuotationTemplate->getMessage(DBEQuotationTemplate::linkedSalesOrderId));
        };
        return $this->success();
    }

    function deleteTemplate()
    {
        $this->setMethodName('delete');
        try {
            $this->buQuotationTemplate->deleteQuotationTemplate($this->getParam('id'));
            return $this->success();
        } catch (Exception $exception) {
            return $this->fail(APIException::badRequest, 'Cannot delete this row');
        }
    }
}
