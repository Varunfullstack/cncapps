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
     * @return Account[]
     * @throws Exception
     */
    function getAccountsList()
    {
        $response = $this->duoAPIClient->apiCall('POST', '/accounts/v1/account/list', []);
        if (!$response['success']) {
            throw new Exception('Failed to pull accounts list');
        }

        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new AccountTransformer());
        $jsonDecoder->register(new AccountsResponseTransformer());
        /** @var AccountsResponse $duoAccountsResponse */
        $duoAccountsResponse = $jsonDecoder->decode($response['response'], AccountsResponse::class);
        return $duoAccountsResponse->response;
    }

    /**
     * @param $accountId
     * @return AccountInfo
     */
    function getAccountInfo($accountId)
    {
        $response = $this->duoAPIClient->apiCall(
            'GET',
            "/admin/v1/info/summary",
            [
                "account_id" => $accountId
            ]
        );
        if (!$response['success']) {
            throw new Exception('Failed to pull account info: ' . json_encode($response));
        }
        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new AccountInfoTransformer());
        $jsonDecoder->register(new AccountInfoResponseTransformer());
        /** @var AccountInfoResponse $accountInfoResponse */
        $accountInfoResponse = $jsonDecoder->decode($response['response'], AccountInfoResponse::class);
        return $accountInfoResponse->response;
    }

}