<?php


namespace CNCLTD;


use DateTime;
use Exception;

class SolarwindsBackupAPI
{
    private $currentVisa = null;
    private $partnerName;
    private $userName;
    private $password;

    /**
     * SolarwindsBackupAPI constructor.
     * @param $partnerName
     * @param $userName
     * @param $password
     */
    public function __construct($partnerName, $userName, $password)
    {
        $this->partnerName = $partnerName;
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * @return SolarwindsAccountItem[]
     * @throws Exception
     */
    public function getAccountsInfo()
    {
        if (!$this->currentVisa) {
            $this->login();
        }
        $data = [
            "id"      => "jsonrpc",
            "visa"    => $this->currentVisa,
            "method"  => "EnumerateAccountStatistics",
            "jsonrpc" => "2.0",
            "params"  => [
                "query" => [
                    "PartnerId"         => 1719619,
                    "StartRecordNumber" => 0,
                    "RecordsCount"      => 1000000000,
                    "Columns"           => [
                        "I1",
                        "TL",
                        "AA1600",
                        "TM"
                    ]
                ]
            ]
        ];
        $data = $this->sendRequest($data);
        $data = $data['result']['result'];
        return array_map(
            function ($item) {
                if (!isset($item['Settings'])) {
                    return null;
                }

                $contractId = null;
                $lastSuccessfulBackupDate = null;
                $name = null;
                $protectedUsers = null;
                foreach ($item['Settings'] as $setting) {
                    $key = array_keys($setting)[0];
                    if ($key == 'AA1600') {
                        $contractId = $setting[$key];
                    } elseif ($key == 'TL') {
                        $lastSuccessfulBackupDate = new DateTime();
                        $lastSuccessfulBackupDate->setTimestamp($setting[$key]);
                    } elseif ($key == 'I1') {
                        $name = $setting[$key];
                    } elseif ($key == 'TM') {
                        $protectedUsers = $setting[$key];
                    }
                }
                return new SolarwindsAccountItem($name, $contractId, $lastSuccessfulBackupDate, $protectedUsers);
            },
            $data
        );
    }

    /**
     * @throws Exception
     */
    private function login()
    {
        $data = [
            "jsonrpc" => "2.0",
            "method"  => "Login",
            "params"  => [
                "partner"  => $this->partnerName,
                "username" => $this->userName,
                "password" => $this->password
            ],
            "id"      => 1
        ];
        $this->sendRequest($data);
    }

    /**
     * @param $data
     * @return mixed
     * @throws Exception
     */
    private function sendRequest($data)
    {
        $url = 'https://api.backup.management/jsonapi';
        $cURL = curl_init();
        $dataString = json_encode($data);
        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($cURL, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($cURL, CURLOPT_VERBOSE, true);
        curl_setopt(
            $cURL,
            CURLOPT_HTTPHEADER,
            array('Content-Type: application/json-rpc')
        );

        $result = curl_exec($cURL);
        $err = curl_error($cURL);
        curl_close($cURL);

        if ($err) {
            throw new Exception('Failed to connect to server');
        }
        $result = json_decode($result, true);
        if (isset($result['error'])) {
            throw new Exception('Solarwinds connection failed - Error: ' . $result['error']);
        }
        $this->currentVisa = $result['visa'];
        return $result;
    }
}