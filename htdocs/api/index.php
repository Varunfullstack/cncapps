<?php

use Monolog\Logger;
use Signable\ApiClient;
use Signable\DocumentWithoutTemplate;
use Signable\Envelopes;
use Signable\Party;
use Twig\Environment;

require_once __DIR__ . '/../config.inc.php';

require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBESignableEnvelope.inc.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");

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

$container->set(
    'logger',
    function () {
        $logger = new Logger('api-log');
        $logFileName = 'api.log';
        $logPath = APPLICATION_LOGS . '/' . $logFileName;
        $logger->pushHandler(new \Monolog\Handler\RotatingFileHandler($logPath, 14, Logger::INFO));
        return $logger;
    }
);

$app->group(
    '/api',
    function (\Slim\Routing\RouteCollectorProxy $group) {
        $group->get(
            '/',
            function (\Slim\Psr7\Request $request, \Slim\Psr7\Response $response) {
                $response->getBody()->write('<h1>CNC API v1</h1>');
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
                    $response->getBody()->write(
                        $twig->render(
                            'acceptQuotation.html.twig',
                            ["message" => "The Quotation to be signed was not found"]
                        )
                    );
                    return $response->withStatus(400);
                }


                if ($dbeQuotation->getValue(DBEQuotation::signableEnvelopeID)) {
                    $response->getBody()->write(
                        $twig->render(
                            'acceptQuotation.html.twig',
                            ["message" => "The Quotation is already being processed in Signable"]
                        )
                    );
                    return $response->withStatus(400);
                }

                // we have to generate the PDF file, send it to signable and register it in the DB
                $BUPdfSalesQuote = new BUSalesOrder($thing);
                try {
                    $pdfData = $BUPdfSalesQuote->createSignableOrderForm($dbeQuotation);

                    ApiClient::setApiKey("fc2d9ba05f3f3d9f2e9de4d831e8fed9");

                    $envDocs = [];

                    $dsDeliveryContact = new DBEContact($this);
                    $dsDeliveryContact->getRow($dbeQuotation->getValue(DBEQuotation::deliveryContactID));

                    $firstName = $dsDeliveryContact->getValue(DBEContact::firstName);
                    $lastName = $dsDeliveryContact->getValue(DBEContact::lastName);
                    $email = $dsDeliveryContact->getValue(DBEContact::email);
                    global $server_type;
                    if ($server_type !== MAIN_CONFIG_SERVER_TYPE_LIVE) {
                        $email = "sales@cnc-ltd.co.uk";
                    }
                    $ordHeadID = $dbeQuotation->getValue(DBEQuotation::ordheadID);
                    $versionNo = $dbeQuotation->getValue(DBEQuotation::versionNo);
                    $orderFile = $ordHeadID . '_' . $versionNo . '.pdf';
                    $envelopeDocument = new DocumentWithoutTemplate(
                        'Customer Form',
                        null,
                        base64_encode($pdfData),
                        $orderFile
                    );

                    $envDocs[] = $envelopeDocument;

                    $envelopeParties = [];

                    $envelopeParty = new Party(
                        $firstName . ' ' . $lastName,
                        $email,
                        'signer1',
                        'Please sign here',
                        'no',
                        false
                    );
                    $envelopeParties[] = $envelopeParty;
                    $expiration = 7 * 24;

                    $signableResponse = Envelopes::createNewWithoutTemplate(
                        "Document #" . $ordHeadID . "_" . $versionNo . "_" . uniqid(),
                        $envDocs,
                        $envelopeParties,
                        null,
                        false,
                        null,
                        0,
                        $expiration
                    );


                    if (!$signableResponse || $signableResponse->http != 202) {
                        throw new Exception('Failed to send to signable');
                    }

                    $dbeSignableEnvelope = new DBESignableEnvelope($this);
                    $dbeSignableEnvelope->setValue(DBESignableEnvelope::id, $signableResponse->envelope_fingerprint);
                    $dbeSignableEnvelope->setValue(DBESignableEnvelope::status, 'envelope-processing');
                    $dbeSignableEnvelope->setValue(
                        DBESignableEnvelope::createdAt,
                        (new DateTimeImmutable())->format(DATE_MYSQL_DATETIME)
                    );
                    $dbeSignableEnvelope->setValue(
                        DBESignableEnvelope::updatedAt,
                        (new DateTimeImmutable())->format(DATE_MYSQL_DATETIME)
                    );
                    $dbeSignableEnvelope->insertRow();
                    $dbeQuotation->setValue(DBEQuotation::signableEnvelopeID, $signableResponse->envelope_fingerprint);
                    $dbeQuotation->updateRow();

                } catch (Exception $exception) {
                    echo 'catch';
                    $response->getBody()->write(
                        $twig->render(
                            'acceptQuotation.html.twig',
                            ["message" => "Failed to generate PDF file to be sent to Signable, please contact us."]
                        )
                    );
                    return $response->withStatus(500);
                }
                $response->getBody()->write(
                    $twig->render(
                        'acceptQuotation.html.twig',
                        ["message" => "An email from Signable will arrive shortly"]
                    )
                );
                return $response;
            }
        );


        $group->group(
            '/signable-hooks',
            function (\Slim\Routing\RouteCollectorProxy $signableHooksGroup) {
                $signableHooksGroup->post(
                    '/',
                    function (\Slim\Psr7\Request $request, \Slim\Psr7\Response $response) {
                        /** @var \Psr\Log\LoggerInterface $logger */
                        $logger = $this->get('logger');
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
                        $signableRequest = $request->getParsedBody();
                        $logger->info(
                            'Signable webHook has been called',
                            ["signableEnvelope" => $request->getParsedBody()]
                        );

                        // for now we are going to ignore add user/add client/contact and add template actions
                        $ignoredActions = ["add-contact", "add-template", "add-user"];

                        if (in_array($signableRequest['action'], $ignoredActions)) {
                            $logger->notice('The action will be ignored, stop process');
                            return $response;
                        }

                        // we have to find the envelope in our DB
                        $dbeSignableEnvelope = new DBESignableEnvelope($this);
                        $dbeSignableEnvelope->getRow($signableRequest['envelope_fingerprint']);

                        if (!$dbeSignableEnvelope->rowCount()) {
                            $logger->notice('The envelope was not found, we are creating the envelope');
                            $dbeSignableEnvelope = new DBESignableEnvelope($this);
                            $dbeSignableEnvelope->setValue(
                                DBESignableEnvelope::id,
                                $signableRequest['envelope_fingerprint']
                            );
                            $dbeSignableEnvelope->setValue(DBESignableEnvelope::status, $signableRequest['action']);
                            $dbeSignableEnvelope->setValue(
                                DBESignableEnvelope::createdAt,
                                (new DateTime())->format(DATE_MYSQL_DATETIME)
                            );
                            $dbeSignableEnvelope->setValue(
                                DBESignableEnvelope::updatedAt,
                                (new DateTime())->format(DATE_MYSQL_DATETIME)
                            );
                            $dbeSignableEnvelope->insertRow();
                        }
                        $dbeSignableEnvelope->setValue(DBESignableEnvelope::status, $signableRequest['action']);
                        if (isset($signableRequest['envelope_download'])) {
                            $dbeSignableEnvelope->setValue(
                                DBESignableEnvelope::downloadLink,
                                $signableRequest['envelope_download']
                            );
                        }
                        $dbeSignableEnvelope->updateRow();
                        if (!$dbeSignableEnvelope->getValue(DBESignableEnvelope::processingClass)) {
                            $logger->notice(
                                'We have found the Envelope but, there is no processing class, so we finish here.'
                            );
                            return $response;
                        }
                        $logger->info(
                            "The envelope has a processing class, continue processing",
                            [
                                "processingCLass" => $dbeSignableEnvelope->getValue(
                                    DBESignableEnvelope::processingClass
                                )
                            ]
                        );
                        $r = new ReflectionClass($dbeSignableEnvelope->getValue(DBESignableEnvelope::processingClass));
                        $jsonArguments = $dbeSignableEnvelope->getValue(DBESignableEnvelope::processingArguments);
                        $associativeArguments = json_decode($jsonArguments);
                        /** @var \CNCLTD\SignableProcess $objectInstance */
                        $objectInstance = $r->newInstanceArgs(array_values($associativeArguments));

                        try {
                            $objectInstance->process($signableRequest);
                        } catch (Exception $exception) {
                            $logger->error('Failed to process envelope', ["exception" => $exception]);
                        }
                        $logger->info('Finished processing envelope');
                        $response->getBody()->write(json_encode(["message" => "this is the signed hook"]));
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                );

                $signableHooksGroup->get(
                    '/',
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

