<?php


namespace CNCLTD\WebrootAPI;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Karriere\JsonDecoder\JsonDecoder;

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
     * @var \DateTime
     */
    private $expiresAt;
    private $refreshToken;

    public function __construct($user, $password, $clientId, $clientSecret, $gsmKey)
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
        $this->user = $user;
        $this->password = $password;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->gsmKey = $gsmKey;
    }

    /**
     * @return GetSitesResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSites()
    {
        $response = $this->getAuthenticatedFromURL("api/console/gsm/{$this->gsmKey}/sites");
        $jsonDecoder = new JsonDecoder(true);
        $jsonDecoder->register(new SiteTransformer());
        $jsonDecoder->register(new GetSitesResponseTransformer());
        $data = $jsonDecoder->decode((string)$response->getBody(), GetSitesResponse::class);
        if (!$data) {
            throw new Exception('Failed to parse body');
        }
        return $data;
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    private function getAuthenticatedFromURL($url)
    {
        $accessToken = $this->getAccessToken();
        do {
            if (!$accessToken) {
                throw new Exception('Unable to retrieve an access token');
            }
            $request = new Request(
                'GET', $url, [
                         "Authorization" => "Bearer $accessToken"
                     ]
            );
            try {
                return $this->guzzleClient->send($request);
            } catch (ClientException $exception) {
                $response = $exception->getResponse();
                if ($response->getStatusCode() === 401) {
                    $this->clearToken();
                    $accessToken = $this->getAccessToken();
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
            $request = new Request(
                'POST', '/auth/token', [
                          "Content-Type" => "application/x-www-form-urlencoded"
                      ]
            );
            $response = $this->guzzleClient->send(
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

            $body = (string)$response->getBody();
            $parsedBody = json_decode($body);
            if (!$parsedBody) {
                throw new Exception('Failed to parse response body');
            }
            $this->accessToken = $parsedBody->access_token;
            $this->expiresAt = (new \DateTime())->add(new \DateInterval("PT{$parsedBody->expires_in}S"));
            $this->refreshToken = $parsedBody->refresh_token;
            return $this->accessToken;
        }
        // we assume that if we have an access token ..we do have a refresh token
        if ((new \DateTime()) >= $this->expiresAt) {
            // our token is expired..lets get a new one
            $this->accessToken = null;
            $this->expiresAt = null;
            // we have to ask for the access token
            $request = new Request(
                'POST', '/auth/token', [
                          "Content-Type" => "application/x-www-form-urlencoded"
                      ]
            );
            try {

                $response = $this->guzzleClient->send(
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
                $body = (string)$response->getBody();
                $parsedBody = json_decode($body);
                if (!$parsedBody) {
                    throw new Exception('Failed to parse response body');
                }
                $this->accessToken = $parsedBody->access_token;
                $this->expiresAt = (new \DateTime())->add(new \DateInterval("PT{$parsedBody->expires_in}S"));
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
        $this->expiresAt = null;
    }

}