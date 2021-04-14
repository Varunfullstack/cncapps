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
        $this->secretKet      = $secretKet;
        $this->integrationKey = $integrationKey;
        $this->host           = $host;
        $this->duoAPIClient   = new \DuoAPI\Auth($integrationKey, $secretKet, $host, null, false);
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


    private function retrieveUsers($offset = 0): RetrieveUsersResponse
    {
        $response = $this->duoAPIClient->apiCall(
            'GET',
            '/admin/v1/users',
            [
                "offset" => $offset
            ]
        );
        if (!$response['success']) {
            throw new Exception('Failed to pull accounts list');
        }
        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new UserTransformer());
        $jsonDecoder->register(new RetrieveUsersResponseTransformer());
        return $jsonDecoder->decode($response['response'], RetrieveUsersResponse::class);
    }

    /**
     * @return User[]
     * @throws Exception
     */
    function getUsers(): array
    {
        $users      = [];
        $nextOffset = 0;
        do {
            $retrieveUsersResponse = $this->retrieveUsers($nextOffset);
            $users                 = array_merge($users, $retrieveUsersResponse->response);
            $nextOffset            = $retrieveUsersResponse->nextOffset;
        } while ($nextOffset);
        return $users;
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

    public function getAuthenticationLogs(\DateTime $minTime)
    {
        $users      = [];
        $nextOffset = 0;
        do {
            $retrieveUsersResponse = $this->retrieveAuthenticationLogs($nextOffset, $minTime);
            $users                 = array_merge($users, $retrieveUsersResponse->response);
            $nextOffset            = $retrieveUsersResponse->nextOffset;
        } while ($nextOffset);
        return $users;
    }

    private function retrieveAuthenticationLogs(?string $nextOffset, \DateTime $minTime)
    {
        $maxTime = new \DateTime();
        $params = [];
        if($nextOffset){
            $params['next_offset'] = $nextOffset;
        }
        $response = $this->duoAPIClient->apiCall(
            'GET',
            "/admin/v2/logs/authentication?mintime={$minTime->getTimestamp()}&maxtime={$maxTime->getTimestamp()}",
            $params
        );
        if (!$response['success']) {
            throw new Exception('Failed to pull accounts list');
        }
        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new UserTransformer());
        $jsonDecoder->register(new RetrieveUsersResponseTransformer());
        return $jsonDecoder->decode($response['response'], RetrieveUsersResponse::class);
    }

}