<?php

namespace CNCLTD\DUOApi;

use CNCLTD\DUOApi\AccountInfo\AccountInfo;
use CNCLTD\DUOApi\AccountInfo\AccountInfoResponse;
use CNCLTD\DUOApi\AccountInfo\AccountInfoResponseTransformer;
use CNCLTD\DUOApi\AccountInfo\AccountInfoTransformer;
use CNCLTD\DUOApi\Accounts\Account;
use CNCLTD\DUOApi\Accounts\AccountsResponse;
use CNCLTD\DUOApi\Accounts\AccountsResponseTransformer;
use CNCLTD\DUOApi\Accounts\AccountTransformer;
use CNCLTD\DUOApi\AuthLog\AccessDeviceLocationTransformer;
use CNCLTD\DUOApi\AuthLog\AccessDeviceTransformer;
use CNCLTD\DUOApi\AuthLog\ApplicationTransformer;
use CNCLTD\DUOApi\AuthLog\AuthLog;
use CNCLTD\DUOApi\AuthLog\AuthLogResponseItemTransformer;
use CNCLTD\DUOApi\AuthLog\AuthLogsMetadataTransformer;
use CNCLTD\DUOApi\AuthLog\AuthLogsResponse;
use CNCLTD\DUOApi\AuthLog\AuthLogsResponseTransformer;
use CNCLTD\DUOApi\AuthLog\AuthLogTransformer;
use CNCLTD\DUOApi\Users\RetrieveUsersResponse;
use CNCLTD\DUOApi\Users\RetrieveUsersResponseTransformer;
use CNCLTD\DUOApi\Users\User;
use CNCLTD\DUOApi\Users\UserTransformer;
use DateTime;
use DuoAPI\Admin;
use DuoAPI\Auth;
use Exception;
use Karriere\JsonDecoder\Exceptions\InvalidBindingException;
use Karriere\JsonDecoder\Exceptions\InvalidJsonException;
use Karriere\JsonDecoder\Exceptions\JsonValueException;
use Karriere\JsonDecoder\Exceptions\NotExistingRootException;
use Karriere\JsonDecoder\JsonDecoder;

date_default_timezone_set('UTC');

class DUOApi
{
    private $secretKet;
    private $integrationKey;
    private $host;
    private $duoAPIClient;
    /**
     * @var Admin
     */
    private $adminAPIClient;

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
        $this->duoAPIClient   = new Auth($integrationKey, $secretKet, $host, null, false);
        $this->adminAPIClient = new Admin($integrationKey, $secretKet, $host);
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

    /**
     * @param DateTime $minTime
     * @return AuthLog[]
     * @throws InvalidBindingException
     * @throws InvalidJsonException
     * @throws JsonValueException
     * @throws NotExistingRootException
     */
    public function getAuthenticationLogs(DateTime $minTime): array
    {
        $authLogs   = [];
        $nextOffset = null;
        do {
            $authLogsResponse = $this->retrieveAuthenticationLogs($nextOffset, $minTime);
            $authLogs         = array_merge($authLogs, $authLogsResponse->response()->authLogs());
            $nextOffset       = $authLogsResponse->response()->metadata()->nextOffset();
        } while ($nextOffset);
        return $authLogs;
    }

    /**
     * @param array|null $nextOffset
     * @param DateTime $minTime
     * @return AuthLogsResponse
     * @throws InvalidBindingException
     * @throws InvalidJsonException
     * @throws JsonValueException
     * @throws NotExistingRootException
     */
    private function retrieveAuthenticationLogs(?array $nextOffset, DateTime $minTime)
    {
        $maxTime = new DateTime();
        $params  = [
            "mintime" => $minTime->getTimestamp() * 1000,
            "maxtime" => $maxTime->getTimestamp() * 1000,
            "limit"   => 1000
        ];
        if ($nextOffset) {
            $params['next_offset'] = implode(",", $nextOffset);
        }
        $response = $this->adminAPIClient->apiCall(
            'GET',
            "/admin/v2/logs/authentication",
            $params
        );
        if (!$response['success']) {
            throw new Exception('Failed to pull accounts list');
        }
        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new AccessDeviceLocationTransformer());
        $jsonDecoder->register(new AccessDeviceTransformer());
        $jsonDecoder->register(new ApplicationTransformer());
        $jsonDecoder->register(new UserTransformer());
        $jsonDecoder->register(new AuthLogTransformer());
        $jsonDecoder->register(new AuthLogsMetadataTransformer());
        $jsonDecoder->register(new AuthLogResponseItemTransformer());
        $jsonDecoder->register(new AuthLogsResponseTransformer());
        return $jsonDecoder->decode($response['response'], AuthLogsResponse::class);
    }

}