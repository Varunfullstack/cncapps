<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/11/2018
 * Time: 9:19
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEAnswerType.inc.php');

class CTAnswerTypeConfig extends CTCNC
{
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
        $roles = [
            "maintenance",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }

    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case 'saveConfig':
                $this->saveConfig();
                break;
            case 'configure':
                $this->showConfig($_REQUEST['answerTypeID']);
                break;
            case CTACTIVITYTYPE_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    private function displayList()
    {
        $this->setPageTitle('AnswerType Config List');
        $this->setTemplateFiles(
            array('AnswerTypeConfigList' => 'AnswerTypeConfigList')
        );

        $answerType = new DBEAnswerType($this);

        $answerType->getConfigurableAnswerTypes();

        if ($answerType->rowCount() > 0) {
            $this->template->set_block(
                'AnswerTypeConfigList',
                'configurableAnswerTypesBlock',
                'configurableAnswerTypes'
            );
            while ($answerType->fetchNext()) {

                $URL = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'       => 'configure',
                        'answerTypeID' => $answerType->getValue(DBEAnswerType::answerTypeID)
                    )
                );


                $this->template->set_var(
                    array(
                        'answerTypeName'      => $answerType->getValue(DBEAnswerType::description),
                        'answerTypeConfigURL' => $URL
                    )
                );
                $this->template->parse(
                    'configurableAnswerTypes',
                    'configurableAnswerTypesBlock',
                    true
                );
            }//while $dsCallActType->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'AnswerTypeConfigList',
            true
        );
        $this->parsePage();
    }

    private function showConfig($answerTypeID)
    {


        $answerType = new DBEAnswerType($this);

        $answerType->getRow($answerTypeID);

        $this->setPageTitle('AnswerType Config: ' . $answerType->getValue(DBEAnswerType::description));

        switch ($answerTypeID) {
            case 5:
                $this->setTemplateFiles(
                    array('Config' => 'AnswerTypeConfig5')
                );

                $configOptions = $answerType->getValue(DBEAnswerType::answerOptions);

                $values = [null, null, null, null, null, null, null, null];
                if ($configOptions) {
                    $values = json_decode($configOptions);
                }

                $this->template->set_block(
                    'Config',
                    'ratingValueBlock',
                    'ratingValue'
                );

                for ($i = 0; $i < 8; $i++) {
                    $this->template->set_var(
                        array(
                            'ratingValueID'    => $i,
                            'ratingValueValue' => isset($values[$i]) ? $values[$i] : ""
                        )
                    );

                    $this->template->parse(
                        'ratingValue',
                        'ratingValueBlock',
                        true
                    );
                }

                $this->template->setVar(
                    [
                        "saveURL" => Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'       => 'saveConfig',
                                'answerTypeID' => $answerTypeID
                            )
                        )
                    ]
                );

                break;
        }


        $this->template->parse(
            'CONTENTS',
            'Config',
            true
        );
        $this->parsePage();
    }

    private function saveConfig()
    {
        $answerTypeID = $_REQUEST['answerTypeID'];

        $answerType = new DBEAnswerType($this);

        $answerType->getRow($answerTypeID);

        switch ($answerTypeID) {
            case 5:
                $config = $_REQUEST['config'];

                $answerType->setValue(
                    DBEAnswerType::answerOptions,
                    json_encode($config)
                );

                $answerType->updateRow();
        }
        return $this->displayList();
    }
}