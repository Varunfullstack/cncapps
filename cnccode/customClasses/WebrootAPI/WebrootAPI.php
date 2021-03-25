<?php

namespace CNCLTD\WebrootAPI;

use CNCLTD\LoggerCLI;
use DateInterval;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Karriere\JsonDecoder\JsonDecoder;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class WebrootAPI
{
    /** @var Client */
    private $guzzleClient;
    private $user;
    private $password;
    private $clientId;
    private $clientSecret;
    private $gsmKey;
    private $accessToken;
    /**
     * @var DateTime
     */
    private $expiresAt;
    private $refreshToken;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($user, $password, $clientId, $clientSecret, $gsmKey, LoggerCLI $logger)
    {
        $this->guzzleClient = new Client(
            [
                'base_uri' => "https://unityapi.webrootcloudav.com/service/",
                'headers'  => [
                    "User-Agent" => "CNCApps/1.0.0",
                    "Accept"     => "application/json"
                ]
            ]
        );
        $this->user         = $user;
        $this->password     = $password;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->gsmKey       = $gsmKey;
        $this->logger       = $logger;
    }

    public function getEndpointsRaw($siteKeyCode)
    {
        return $this->getAuthenticatedFromURL(
            "api/console/gsm/{$this->gsmKey}/sites/{$siteKeyCode}/endpoints"
        );
    }

    public function getDevicesRaw($siteKeyCode)
    {
        return $this->getAuthenticatedFromURL(
            "api/status/site/{$siteKeyCode}?returnedInfo=SystemAnalyzer&batchSize=1000"
        );
    }

    /**
     * @param $siteKeyCode
     * @return Endpoint[]
     * @throws GuzzleException
     */
    public function getEndpoints($siteKeyCode)
    {

        $this->logger->debug("Fetching first page of end points of site: $siteKeyCode");
        $page           = 1;
        $pageSize       = 300;
        $firstBatch     = $this->getEndpointsBatch($siteKeyCode, 1, $pageSize);
        $endpoints      = $firstBatch->endpoints;
        $totalAvailable = $firstBatch->totalAvailable;
        $retrievedCount = $pageSize * $page;
        while ($retrievedCount < $totalAvailable) {
            $this->logger->debug(
                "Fetching next page we have retrieved {$retrievedCount}/{$totalAvailable} so far of end points of site: $siteKeyCode"
            );
            $page++;
            $nextBatch      = $this->getEndpointsBatch(
                $siteKeyCode,
                $page
            );
            $endpoints      = array_merge(
                $endpoints,
                $nextBatch->endpoints
            );
            $retrievedCount = $pageSize * $page;
        }
        return $endpoints;
    }

    private function getEndpointsBatch($siteKeyCode, $pageNumber = 1, $pageSize = 2000): GetEndpointsResponse
    {
        $response    = $this->getAuthenticatedFromURL(
            "api/console/gsm/{$this->gsmKey}/sites/{$siteKeyCode}/endpoints?pageNr=$pageNumber&pageSize=$pageSize"
        );
        $jsonDecoder = new JsonDecoder(true);
        $jsonDecoder->register(new EndpointTransformer());
        $jsonDecoder->register(new GetEndpointsResponseTransformer());
        /** @var GetEndpointsResponse $data */
        $data = $jsonDecoder->decode(
            (string)$response->getBody(),
            GetEndpointsResponse::class
        );
        if (!$data) {
            throw new Exception('Failed to parse body');
        }
        return $data;
    }

    /**
     * @param $url
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws Exception
     */
    private function getAuthenticatedFromURL($url): ResponseInterface
    {
        $request = new Request(
            'GET', $url
        );
        return $this->sendAuthenticatedRequest($request);
    }

    private function sendAuthenticatedRequest($request, $timeOut = 0): ResponseInterface
    {
        $this->getAccessToken();
        do {
            if (!$this->accessToken) {
                throw new Exception('Unable to retrieve an access token');
            }
            if (!$request->hasHeader('Authorization')) {
                $request = $request->withAddedHeader(
                    'Authorization',
                    "Bearer {$this->accessToken}"
                );
            }
            try {
                return $this->guzzleClient->send($request, ["timeout" => $timeOut]);
            } catch (ClientException $exception) {
                $response = $exception->getResponse();
                if ($response->getStatusCode() === 401) {
                    $this->clearToken();
                    $this->getAccessToken();
                } else {
                    throw $exception;
                }

            }
        } while (true);
    }

    private function getAccessToken()
    {
        if (!$this->accessToken) {
            // we have to ask for the access token
            $request    = new Request(
                'POST', '/auth/token', [
                          "Content-Type" => "application/x-www-form-urlencoded"
                      ]
            );
            $response   = $this->guzzleClient->send(
                $request,
                [
                    "auth"        => [$this->clientId, $this->clientSecret],
                    "form_params" => [
                        "username"   => $this->user,
                        "password"   => $this->password,
                        "grant_type" => 'password',
                        "scope"      => '*'
                    ]
                ]
            );
            $body       = (string)$response->getBody();
            $parsedBody = json_decode($body);
            if (!$parsedBody) {
                throw new Exception('Failed to parse response body');
            }
            $this->accessToken  = $parsedBody->access_token;
            $this->expiresAt    = (new DateTime())->add(new DateInterval("PT{$parsedBody->expires_in}S"));
            $this->refreshToken = $parsedBody->refresh_token;
            return $this->accessToken;
        }
        // we assume that if we have an access token ..we do have a refresh token
        if ((new DateTime()) >= $this->expiresAt) {
            // our token is expired..lets get a new one
            $this->accessToken = null;
            $this->expiresAt   = null;
            // we have to ask for the access token
            $request = new Request(
                'POST', '/auth/token', [
                          "Content-Type" => "application/x-www-form-urlencoded"
                      ]
            );
            try {

                $response   = $this->guzzleClient->send(
                    $request,
                    [
                        "auth"        => [$this->clientId, $this->clientSecret],
                        "form_params" => [
                            "refresh_token" => $this->refreshToken,
                            "grant_type"    => 'refresh_token',
                            "scope"         => '*'
                        ]
                    ]
                );
                $body       = (string)$response->getBody();
                $parsedBody = json_decode($body);
                if (!$parsedBody) {
                    throw new Exception('Failed to parse response body');
                }
                $this->accessToken  = $parsedBody->access_token;
                $this->expiresAt    = (new DateTime())->add(new DateInterval("PT{$parsedBody->expires_in}S"));
                $this->refreshToken = $parsedBody->refresh_token;
                return $this->accessToken;
            } catch (ClientException $exception) {
                if ($exception->getCode() == 401) {
                    // we have to start over, as the refresh token is expired ..this shouldn't happen ...
                    return $this->getAccessToken();
                }
            }

        }
        return $this->accessToken;

    }

    private function clearToken()
    {
        $this->accessToken = null;
        $this->expiresAt   = null;
    }

    /**
     * @return GetSitesResponse
     * @throws GuzzleException
     */
    public function getSites()
    {
        $response    = $this->getAuthenticatedFromURL("api/console/gsm/{$this->gsmKey}/sites");
        $jsonDecoder = new JsonDecoder(true);
        $jsonDecoder->register(new SiteTransformer());
        $jsonDecoder->register(new GetSitesResponseTransformer());
        $data = $jsonDecoder->decode(
            (string)$response->getBody(),
            GetSitesResponse::class
        );
        if (!$data) {
            throw new Exception('Failed to parse body');
        }
        return $data;
    }

    public function deactivateEndpoint($siteId, $endpointId)
    {
        $request = new Request(
            'POST',
            "api/console/gsm/{$this->gsmKey}/sites/{$siteId}/endpoints/deactivate",
            ['Content-Type' => "application/json"],
            json_encode(
                [
                    "EndpointsList" => $endpointId
                ]
            )
        );
        return $this->sendAuthenticatedRequest($request, 20);
    }

}