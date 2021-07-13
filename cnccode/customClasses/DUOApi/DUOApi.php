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
use CNCLTD\DUOApi\Users\UserTransformer;
use CNCLTD\StreamOneProcessing\Subscription\Subscription;
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
    const FAILED_TO_PULL_ACCOUNTS_LIST_ERROR_MESSAGE = 'Failed to pull accounts list';
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
            throw new Exception(self::FAILED_TO_PULL_ACCOUNTS_LIST_ERROR_MESSAGE);
        }
        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new AccountTransformer());
        $jsonDecoder->register(new AccountsResponseTransformer());
        /** @var AccountsResponse $duoAccountsResponse */
        $duoAccountsResponse = $jsonDecoder->decode($response['response'], AccountsResponse::class);
        return $duoAccountsResponse->response;
    }


    private function retrieveUsers($accountId, $offset = 0): RetrieveUsersResponse
    {
        $response = $this->duoAPIClient->apiCall(
            'GET',
            '/admin/v1/users',
            [
                "offset"     => $offset,
                "account_id" => $accountId
            ]
        );
        if (!$response['success']) {
            throw new Exception(self::FAILED_TO_PULL_ACCOUNTS_LIST_ERROR_MESSAGE);
        }
        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new UserTransformer());
        $jsonDecoder->register(new RetrieveUsersResponseTransformer());
        return $jsonDecoder->decode($response['response'], RetrieveUsersResponse::class);
    }

    /**
     * @param $accountId
     * @return array
     * @throws Exception
     */
    function getUsers($accountId): array
    {
        $users      = [];
        $nextOffset = 0;
        do {
            $retrieveUsersResponse = $this->retrieveUsers($accountId, $nextOffset);
            $users                 = array_merge($users, $retrieveUsersResponse->response);
            $nextOffset            = $retrieveUsersResponse->nextOffset;
        } while ($nextOffset);
        return $users;
    }

    /**
     * @param $accountId
     * @return AccountInfo
     */
    function getAccountInfo($accountId): AccountInfo
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
    public function getAuthenticationLogs($accountId, DateTime $minTime): array
    {
        $authLogs   = [];
        $nextOffset = null;
        do {
            $authLogsResponse = $this->retrieveAuthenticationLogs($accountId, $nextOffset, $minTime);
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
    private function retrieveAuthenticationLogs($accountId, ?array $nextOffset, DateTime $minTime)
    {
        $maxTime = new DateTime();
        $params  = [
            "account_id" => $accountId,
            "mintime"    => $minTime->getTimestamp() * 1000,
            "maxtime"    => $maxTime->getTimestamp() * 1000,
            "limit"      => 1000,
            "reasons"    => "user_marked_fraud,deny_unenrolled_user,error,locked_out,user_disabled,user_cancelled,invalid_passcode,no_response,no_keys_pressed,call_timed_out,location_restricted,factor_restricted,platform_restricted,version_restricted,rooted_device,no_screen_lock,touch_id_disabled,no_disk_encryption,anonymous_ip,out_of_date,denied_by_policy,software_restricted,no_duo_certificate_present,could_not_determine_if_endpoint_was_trusted,invalid_management_certificate_collection_state,no_referring_hostname_provided,invalid_referring_hostname_provided,no_web_referer_match,endpoint_failed_google_verification,endpoint_is_not_trusted,invalid_device,anomalous_push,endpoint_is_not_in_management_system,no_activated_duo_mobile_account,allow_unenrolled_user,bypass_user,remembered_device,trusted_location,user_approved,valid_passcode,allowed_by_policy,user_not_in_permitted_group"
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
            throw new Exception(self::FAILED_TO_PULL_ACCOUNTS_LIST_ERROR_MESSAGE);
        }
        if ($response['http_status_code'] != 200) {
            throw new Exception('Failed to pull data');
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