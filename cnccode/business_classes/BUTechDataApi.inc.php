<?php
/**
 *
 *
 * @access public
 * @authors Mustafa Taha
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");

use CNCLTD\LoggerCLI;

class BUTechDataApi extends Business
{
    private $accessToken;
    private $SOIN;
    private $signature;

    private $client_id;
    private $client_secret;
    private $grant_type = 'client_credentials';
    private $baseUrl; //production
    private $authUrl; // production
    private $timeStamp;
    private $mode       = 'production';
    private $logger;
    //private $mode='test';

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        if (!isset($_SESSION["AuthTimeStamp"])) {
            $_SESSION["AuthTimeStamp"] = 0;
        }
        if (!isset($_SESSION["AuthSignature"])) $_SESSION["AuthSignature"] = "";
        if (!isset($_SESSION["AuthAccessToken"])) $_SESSION["AuthAccessToken"] = "";
        if (!isset($_SESSION["AuthMod"])) $_SESSION["AuthMod"] = "";
        $this->client_id = 's1_white_label_client';
        if ($this->mode == 'production') {
            $this->client_secret = 'sH5Bq05rr3KEtTVFda7KxRmzgo8VOFCVAHOPYtweXlS0z5rWdPTYMhSaAhA33laj';
            $this->SOIN          = "8D3BE069277C5CC450DA5173B989AA390FDECA03";
            $this->baseUrl       = 'https://partnerapi.tdstreamone.eu/'; //production
            $this->authUrl       = 'https://eauth.techdata.eu/as/token.oauth2'; // production
        } else {
            $this->client_secret = 'IthNk5spz54XgnKREuSAVHNIk1oXNkNjR4YeOhVhckThmlz3hcrzL6lPVIJCUrZ2';
            $this->SOIN          = "B3DD8A8BB01D7D18DC8D6D47340D847BFFE730D7";
            $this->baseUrl       = 'https://eu-uat-papi.tdmarketplace.net/'; //testing
            $this->authUrl       = 'https://eauth-quality.techdata.eu/as/token.oauth2'; // testing
        }
        $this->authenticate();
        $this->logger = new LoggerCLI("StreamOne");

    }

    function authenticate()
    {
        if ((time() - $_SESSION["AuthTimeStamp"]) > 7200 || $_SESSION["AuthMod"] != $this->mode) {
            $data    = array(
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type'    => $this->grant_type
            );
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context = stream_context_create($options);
            $result  = file_get_contents($this->authUrl, false, $context);
            if ($result === FALSE) {
                return false;
            }
            $obj                         = json_decode($result);
            $this->accessToken           = $obj->access_token;
            $this->timeStamp             = time();
            $this->signature             = base64_encode("$obj->access_token:$this->SOIN:$this->timeStamp");
            $_SESSION["AuthSignature"]   = $this->signature;
            $_SESSION["AuthTimeStamp"]   = $this->timeStamp;
            $_SESSION["AuthAccessToken"] = $this->accessToken;
            $_SESSION["AuthMod"]         = $this->mode;
        }
        return true;
    }

    function getProductList($page = 1)
    {
        return $this->callApi("catalog/products/$page");
    }

    function callApi($url, $body = null, $method = 'GET')
    {
        $this->logger->info($url);
        //$this->logger->info($body );
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL            => $this->baseUrl . $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_HTTPHEADER     => $this->getHeader(),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    function getHeader()
    {
        $this->signature   = $_SESSION["AuthSignature"];
        $this->timeStamp   = $_SESSION["AuthTimeStamp"];
        $this->accessToken = $_SESSION["AuthAccessToken"];
        file_put_contents(
            'streamOneAuth.txt',
            json_encode(
                array(
                    "Content-type: application/json",
                    "Authorization: Bearer $this->accessToken",
                    "SOIN: $this->SOIN",
                    "TimeStamp: $this->timeStamp",
                    "Signature: $this->signature",
                    "Accept: application/json"
                )
            )
        );
        return array(
            "Content-type: application/json",
            "Authorization: Bearer $this->accessToken",
            "SOIN: $this->SOIN",
            "TimeStamp: $this->timeStamp",
            "Signature: $this->signature",
            "Accept: application/json"
        );
    }

    function updateSubscription()
    {
        $body = file_get_contents('php://input');
        return $this->callApi("order/", $body, 'PATCH');
    }

    // orders
    function updateSubscriptionAddOns()
    {
        $body = file_get_contents('php://input');
        return $this->callApi("order/addOns", $body, 'PATCH');
    }

    function purchaseSubscriptionAddOns()
    {
        $body = file_get_contents('php://input');
        return $this->callApi("order/addOns", $body, 'POST');
    }

    function getAllSubscriptions()
    {

    }

    function getAllSubscriptionsForPage($page = 1)
    {
        return $this->callApi("order/subscriptions/$page");
    }

    function getAllSubscriptionsSync($pages, $pageSize = 25)
    {
        $urls = array();
        foreach ($pages as $page) {
            array_push($urls, "order/subscriptions/$page");
        }
        $totalPages = count($urls) / $pageSize;
        $result     = array();
        for ($i = 0; $i < $totalPages; $i++) {
            $subUrls  = array_slice($urls, $i * $pageSize, $pageSize);
            $response = $this->callMultipleApi($subUrls);
            $result   = array_merge($result, $response);

        }
        return $result;
    }

    function callMultipleApi($urls, $body = null, $method = 'GET')
    {
        //$this->logger->info(json_encode($urls) );
        $result    = array();
        $multiCurl = array();
        $mh        = curl_multi_init();
        foreach ($urls as $i => $url) {
            $multiCurl[$i] = curl_init();
            curl_setopt_array(
                $multiCurl[$i],
                array(
                    CURLOPT_URL            => $this->baseUrl . $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => "",
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => $method,
                    CURLOPT_POSTFIELDS     => $body,
                    CURLOPT_HTTPHEADER     => $this->getHeader(),
                )
            );
            curl_multi_add_handle($mh, $multiCurl[$i]);
        }
        //$index=null;
        do {
            curl_multi_exec($mh, $running);
            // Wait a short time for more activity
            curl_multi_select($mh);

        } while ($running > 0);
        // get content and remove handles
        foreach ($multiCurl as $k => $ch) {
            $result[$k] = json_decode(curl_multi_getcontent($ch), true);
            curl_multi_remove_handle($mh, $ch);
        }
        // close
        curl_multi_close($mh);
        return $result;
    }

    function getSubscriptionsByEmail($page = 1)
    {
        $email = null;
        if (isset($_GET['email'])) $email = $_GET['email'];
        if ($email != null) {
            return $this->callApi("order/subscriptions/endCustomerEmails/$email/$page");
        } else return false;
    }

    function getSubscriptionsByDateRange($page = 1)
    {
        $from = null;
        if (isset($_GET['from'])) $from = $_GET['from'];
        $to = null;
        if (isset($_GET['to'])) $to = $_GET['to'];
        if ($from != null) {
            return $this->callApi("order/subscriptions/dateRange/$from/$to/$page");
        } else return false;
    }

    //customers
    function searchCustomers($body = null)
    {
        if ($body == null) $body = file_get_contents('php://input');
        return $this->callApi("endCustomer/search", $body, 'POST');
    }

    function createCustomer()
    {
        $body = file_get_contents('php://input');
        return $this->callApi("endCustomer/", $body, 'POST');
    }

    function getCustomerById($endCustomerId)
    {
        return $this->callApi("endCustomer/$endCustomerId");
    }

    function getStreamOneCustomerByEmail()
    {
        $body = file_get_contents('php://input');
        return $this->callApi("endCustomer/search", $body, 'POST');
    }

    function updateCustomer($id, $body)
    {
        $b     = json_decode($body);
        $b->id = $id;
        return $this->callApi("endCustomer/" . $id, json_encode($b), 'PATCH');
    }

    function getSubscriptionsByEndCustomerId($page)
    {
        $endCustomerId = null;
        if (isset($_GET['endCustomerId'])) $endCustomerId = $_GET['endCustomerId'];
        if ($endCustomerId != null) return $this->callApi(
            "order/subscriptions/endCustomerIds/$endCustomerId/$page"
        ); else return json_encode(['Result' => 'Failed']);

    }

    function getVendors($page)
    {
        return $this->callApi("catalog/vendors/$page");
    }

    function getProductsByVendor($page)
    {
        //micrsosft =397
        $vendorId = null;
        if (isset($_GET['vendorId'])) $vendorId = $_GET['vendorId'];
        if ($vendorId != null) {
            $body = [
                "vendorIds" => [$vendorId],
                "page"      => $page
            ];
            return $this->callApi("catalog/search", json_encode($body), 'POST');
        } else return $this->failed();
    }

    function failed()
    {
        return json_encode(['Result' => 'Failed']);
    }

    // orders
    function getOrderDetials()
    {
        $orderId = null;
        if (isset($_GET['orderId'])) $orderId = $_GET['orderId'];
        if ($orderId != null) return $this->callApi("order/details/$orderId"); else return $this->failed();
    }

    function addOrder()
    {
        $body = file_get_contents('php://input');
        return $this->callApi("order/", $body, 'POST');
    }

    // product
    function getProductBySKU()
    {
        $body = file_get_contents('php://input');
        return $this->callApi("catalog/productDetails", $body, 'POST');
    }

    function getProductsPrices($body)
    {
        return $this->callApi("catalog/price", $body, 'POST');
    }

    /**
     * @var $orderNumbers array
     */
    function getProductsDetails($orderNumbers, $pageSize = 10)
    {
        $urls = array();
        foreach ($orderNumbers as $orderNumber) {
            array_push($urls, "order/details/$orderNumber");
        }
        $totalPages = count($orderNumbers) / $pageSize;
        $result     = array();
        for ($i = 0; $i < $totalPages; $i++) {
            $subUrls = array_slice($urls, $i * $pageSize, $pageSize);
            //echo json_encode($subUrls);
            $response = $this->callMultipleApi($subUrls);
            $result   = array_merge($result, $response);
        }
        // return $this->callMultipleApi($urls);
        return $result;
    }

}// End of class
