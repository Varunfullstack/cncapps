<?php

use Twig\Environment;

require_once __DIR__ . '/../config.inc.php';
$container = new \DI\Container();
\Slim\Factory\AppFactory::setContainer($container);
$app = \Slim\Factory\AppFactory::create();
$thing = null;
$app->add(new \CNCLTD\JsonBodyParserMiddleware());
$app->addErrorMiddleware(true, true, true);
$container->set(
    'twig',
    function () {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../twig');
        return new Environment($loader, ["cache" => __DIR__ . '/../../cache']);
    }
);

$app->group(
    '/api',
    function (\Slim\Routing\RouteCollectorProxy $group) {
        $group->get(
            '/',
            function (\Slim\Psr7\Request $request, \Slim\Psr7\Response $response) {
                $response->getBody()->write('<h1>test</h1>');
                return $response;
            }
        );

        $group->get(
            '/acceptQuotation',
            function (\Slim\Psr7\Request $request, \Slim\Psr7\Response $response) {
                /** @var Environment $twig */
                $twig = $this->get('twig');
                $queryParams = $request->getQueryParams();
                $code = @$queryParams['code'];

                if (!$code) {
                    $response->getBody()->write(
                        $twig->render('acceptQuotation.html.twig', ["message" => "Code not provided"])
                    );
                    return $response->withStatus(400);
                }

                // we have to find a quotation with the given code
                $dbeQuotation = new  DBEQuotation($thing);
                $dbeQuotation->setValue(DBEQuotation::confirmCode, $code);
                if (!$dbeQuotation->getRowByColumn(DBEQuotation::confirmCode)) {
                    $data = ["error" => true, "description" => "Quotation not found", "code" => 2];
                    $payload = json_encode($data);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }

                // we have to generate the PDF file, send it to signable and register it in the DB

                $BUPdfSalesQuote = new BUPDFSalesQuote($thing);
//        $BUPdfSalesQuote->generate()
                return $response->getBody()->write('<h1>test</h1>');
            }
        );


        $group->group(
            '/signable-hooks',
            function (\Slim\Routing\RouteCollectorProxy $signableHooksGroup) {
                $signableHooksGroup->post(
                    '/signed',
                    function (\Slim\Psr7\Request $request, \Slim\Psr7\Response $response) {
                        $requestType = [
                            "envelope_fingerprint" => "the envelope ID basically",
                            "envelope_documents"   => "an object with the document in this envelope",
                            "envelope_fields"      => "the fields filled up by the signers",
                            "envelope_download"    => "the URL to download the envelope",
                            "action"               => "signed-envelope-complete",
                            "action_date"          => "the date the action happened",
                            "envelope_meta"        => "the metadata information sent with the envelope"
                        ];
                        // we are receiving information about a signed document


                        $response->getBody()->write(json_encode(["message" => "this is the signed hook"]));
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                );

                $signableHooksGroup->get(
                    '/signed',
                    function (\Slim\Psr7\Request $request, \Slim\Psr7\Response $response) {
                        // we are receiving information about a signed document
                        $response->getBody()->write(json_encode(["message" => "this is the signed hook"]));
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                );
            }
        );
    }
);


$app->run();

