<?php


namespace CNCLTD;
global $cfg;
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
require_once($cfg["path_dbe"] . "/DBEHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBEOffice365License.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');

class Office365LicensesExportPowerShellCommand extends PowerShellCommandRunner
{
    private $user;
    private $password;
    private $dbeCustomer;

    public function __construct($dbeCustomer, $logger)
    {
        $this->dbeCustomer = $dbeCustomer;
        $customerID = $dbeCustomer->getValue(\DBECustomer::customerID);
        $customerName = $dbeCustomer->getValue(\DBECustomer::name);
        $buCustomer = new \BUCustomer($this);

        $logger->info('Getting A Office 365 Data for Customer: ' . $customerID . ' - ' . $customerName);
        // we have to pull from passwords.. the service 10
        $dbePassword = $buCustomer->getOffice365PasswordItem($customerID);

        if (!$dbePassword->rowCount) {
            $message = 'This customer does not have a Office 365 Admin Portal service password';
            $logger->warning($message);
            throw new \UnexpectedValueException($message);
        }
        $buPassword = new \BUPassword($this);
        $userName = $buPassword->decrypt($dbePassword->getValue(\DBEPassword::username));
        $password = $buPassword->decrypt($dbePassword->getValue(\DBEPassword::password));
        $this->outputFilePath = __DIR__ . '\office365Output.json';
        $this->user = $userName;
        $this->password = $password;
        $this->logger = $logger;
        $this->commandName = "365OfficeLicensesExport";
    }

    public function run()
    {
        try {
            $data = parent::run();
        } catch (PowerShellScriptFailedToProcessException $exception) {
            $this->logger->error('Failed to parse for customer: ' . $exception->getOutput());
            createFailedSR($this->dbeCustomer, "Could not parse Powershell response: {$exception->getOutput()}");
            throw new \Exception('Failed');
        }
        if (isset($data['error'])) {
            $this->logger->error(
                'Failed to pull data for customer: ' . $data['errorMessage'] . ' ' . $data['stackTrace']
            );
            createFailedSR($this->dbeCustomer, $data['errorMessage'], $data['stackTrace'], $data['position']);
            throw new \Exception('Errors detected');
        }

        if (count($data['errors'])) {
            foreach ($data['errors'] as $error) {
                $this->logger->warning(
                    "Error received from powershell output, but the execution was not stopped:  " . $error
                );
            }
        }
        return $data;
    }

    protected function getParams(): PowerShellParamCollection
    {
        $collection = new PowerShellParamCollection();
        $collection[] = new PowerShellParam("User", $this->user);
        $collection[] = new PowerShellParam("Password", $this->password);
        return $collection;
    }
}