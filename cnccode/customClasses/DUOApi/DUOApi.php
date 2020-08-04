<?php


namespace CNCLTD\DUOApi;
date_default_timezone_set('UTC');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class DUOApi
{
    private $secretKet;
    private $integrationKey;
    private $host;


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
    }

    function getAccountsList()
    {
        $client = new Client(['base_uri' => "https://{$this->host}"]);
        $path = "/accounts/v1/account/list";
        $headers = $this->generateSignature(
            'POST',
            $this->host,
            $path,
            [],
            $this->secretKet,
            $this->integrationKey
        );
        var_dump($headers);
        try {
            $response = $client->post(
                $path,
                [
                    "headers" => $headers
                ]
            );
            var_dump($response);
        } catch (ClientException $exception) {
            var_dump($exception->getMessage());
        }

    }

    function generateSignature($method, $host, $path, $params, $skey, $ikey)
    {
        $now = "Tue, 04 Aug 2020 11:54:46 +0000";
        //$now = (new \DateTime())->format(DATE_RFC2822);
        $canon = [$now, strtoupper($method), strtolower($host), $path];
        $args = [];
        ksort($params);
        foreach ($params as $key => $value) {
            $args[] = "{$key}={$value}";
        }
        if (count($args)) {
            $canon[] = urlencode(join('&', $args));
        }
        $canon = join('\n', $canon);
        var_dump($canon);


        $sig = hash_hmac("sha1", utf8_encode($canon), utf8_encode($skey), false);
        $auth = "{$ikey}:{$sig}";
        var_dump($auth);
        $encodedAuth = base64_encode($auth);
        return [
            "Date"          => $now,
            "Authorization" => "Basic {$encodedAuth}"
        ];
    }

}