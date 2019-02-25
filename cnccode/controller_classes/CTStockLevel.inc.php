<?php /**
 * Stock Levels controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUItem.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

// Actions
define('CTSTOCKLEVEL_ACT_SEARCH', 'search');
define('CTSTOCKLEVEL_ACT_UPDATE', 'update');

class CTStockLevel extends CTCNC
{
    var $dsItem = '';
    public $BUItem;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->BUItem = new BUItem($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->setMethodName('defaultAction');
        switch ($_REQUEST['action']) {

            case CTSTOCKLEVEL_ACT_UPDATE:
                $this->update();
                break;

            case CTSTOCKLEVEL_ACT_SEARCH:
            default:
                $this->search();
                break;

        }
    }

    function search()
    {
        $this->setTemplateFiles('StockLevel', 'StockLevel.inc');

        $this->setPageTitle('Stock Levels');

        // save search text in a session var
        if ($_POST['itemText']) {

            $_SESSION['itemText'] = $_POST['itemText'];

        }

        if ($_SESSION['itemText']) {

            $this->BUItem->getItemsByNameMatch($_SESSION['itemText'], $this->dsItem);

        }

        $urlSearch = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTSTOCKLEVEL_ACT_SEARCH)
        );

        $urlUpdate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTSTOCKLEVEL_ACT_UPDATE)
        );

        $this->template->set_var(
            array(
                'urlSearch' => $urlSearch,
                'urlUpdate' => $urlUpdate,
                'itemText' => $_SESSION['itemText']
            )
        );

        if (is_object($this->dsItem) && $this->dsItem->rowCount() > 0) {

            $this->template->set_block('StockLevel', 'itemBlock', 'items');

            while ($this->dsItem->fetchNext()) {

                $this->template->set_var(
                    array(
                        'itemDescription' => Controller::htmlDisplayText($this->dsItem->getValue('description')),
                        'salesStockQty' => Controller::htmlInputText($this->dsItem->getValue('salesStockQty')),
                        'maintStockQty' => Controller::htmlInputText($this->dsItem->getValue('maintStockQty')),
                        'itemID' => $this->dsItem->getValue('itemID')
                    )
                );

                $this->template->parse('items', 'itemBlock', true);

            } //end while

        }

        $this->template->parse('CONTENTS', 'StockLevel', true);
        $this->parsePage();

    }

    function update()
    {

        $salesStockQty = $_POST['salesStockQty'];
        $maintStockQty = $_POST['maintStockQty'];

        $dbeItem = new DBEItem($this);

        foreach ($salesStockQty AS $key => $value) {

            $dbeItem->getRow($key);

            $dbeItem->setValue('salesStockQty', $value);
            $dbeItem->setValue('maintStockQty', $maintStockQty[$key]);

            $dbeItem->updateRow();

        }

        $this->search();

    }
}

?>