<?php
require_once __DIR__ . '/../config.inc.php';


$app = \Slim\Factory\AppFactory::create();
$thing = null;
$app->post(
    '/acceptQuotation/{code}',
    function (\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) {
        $code = @$args['code'];

        if (!$code) {
            $data = ["error" => true, "description" => "Code not provided", "code" => 1];
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // we have to find a quotation with the given code
        $dbeQuotation = new DBEQuotation($thing);
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

    }
);

