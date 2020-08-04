<?php


namespace CNCLTD\DUOApi;

use Exception;
use Karriere\JsonDecoder\JsonDecoder;

date_default_timezone_set('UTC');

class DUOApi
{
    private $secretKet;
    private $integrationKey;
    private $host;
    private $duoAPIClient;

    /**
     * DUOApi constructor.
     * @param $secretKet
     * @param $integrationKey
     * @param $host
     */
    public function __construct($secretKet, $integrationKey, $host)
    {
        $this->secretKet = $secretKet;
        $this->integrationKey = $integrationKey;
        $this->host = $host;
        $this->duoAPIClient = new \DuoAPI\Auth($integrationKey, $secretKet, $host, null, false);
    }

    /**
     * @return DuoAccount[]
     * @throws Exception
     */
    function getAccountsList()
    {
        $response = $this->duoAPIClient->apiCall('POST', '/accounts/v1/account/list', []);
        if (!$response['success']) {
            throw new Exception('Failed to pull accounts list');
        }

        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new DuoAccountTransformer());
        $jsonDecoder->register(new DuoAccountsResponseTransformer());
        /** @var DuoAccountsResponse $duoAccountsResponse */
        $duoAccountsResponse = $jsonDecoder->decode($response['response'], DuoAccountsResponse::class);
        return $duoAccountsResponse->response;
    }


}