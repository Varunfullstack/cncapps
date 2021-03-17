<?php

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\ChargeableWorkCustomerRequest\infra\ChargeableWorkCustomerRequestMySQLRepository;
use CNCLTD\ChargeableWorkCustomerRequest\usecases\ApprovePendingChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\usecases\GetPendingToProcessChargeableRequestInfo;
use CNCLTD\CustomerFeedback;
use CNCLTD\CustomerFeedbackRepository;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestAlreadyProcessedException;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use CNCLTD\Exceptions\ContactNotFoundException;
use CNCLTD\FeedbackTokenGenerator;
use CNCLTD\JsonBodyParserMiddleware;
use CNCLTD\SignableProcess;
use DI\Container;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Signable\ApiClient;
use Signable\DocumentWithoutTemplate;
use Signable\Envelopes;
use Signable\Party;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../config.inc.php';
global $cfg;
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBESignableEnvelope.inc.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
$container = new Container();
AppFactory::setContainer($container);
$app   = AppFactory::create();
$thing = null;
$app->add(new JsonBodyParserMiddleware());
$app->addErrorMiddleware(true, true, true);
$container->set(
    'twig',
    function () {
        $loader = new FilesystemLoader('', __DIR__ . '/../../twig');
        $loader->addPath('api', 'api');
        return new Environment($loader, ["cache" => __DIR__ . '/../../cache']);
    }
);
$container->set(
    'logger',
    function () {
        $logger      = new Logger('api-log');
        $logFileName = 'api.log';
        $logPath     = APPLICATION_LOGS . '/' . $logFileName;
        $logger->pushHandler(new RotatingFileHandler($logPath, 14, Logger::INFO));
        return $logger;
    }
);
$app->group(
    '/internal-api',
    function (RouteCollectorProxy $group) {
        $group->get(
            '/',
            function (Request $request, Response $response) {
                $response->getBody()->write('CNC Internal API');
                return $response;
            }
        );
        $group->get(
            '/customerStats/{customerId}',
            function (Request $request, Response $response, $args) {
                $queryParams = $request->getQueryParams();
                $endDate     = new DateTime();
                $startDate   = (clone $endDate)->sub(new DateInterval('P365D'));
                if (isset($queryParams['startDate'])) {
                    $startDateString = $queryParams['startDate'];
                    $startDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $startDateString);
                    if (!$startDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The start date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                if (isset($queryParams['endDate'])) {
                    $endDateString = $queryParams['endDate'];
                    $endDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $endDateString);
                    if (!$endDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The end date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                $params      = [
                    ["type" => "i", "value" => $args['customerId']],
                    ["type" => "s", "value" => $startDate->format(DATE_MYSQL_DATE)],
                    ["type" => "s", "value" => $endDate->format(DATE_MYSQL_DATE)],
                ];
                $isBreakDown = isset($queryParams['breakDown']);
                $query       = "select SUM(1) AS raised,
    SUM(pro_status IN ('F' , 'C')) AS `fixed`,
    AVG(problem.`pro_responded_hours`) AS responseTime,
       null as sla,
    AVG(IF(pro_status IN ('F' , 'C'),
        problem.`pro_responded_hours` < CASE problem.`pro_priority`
            WHEN 1 THEN customer.`cus_sla_p1`
            WHEN 2 THEN customer.`cus_sla_p2`
            WHEN 3 THEN customer.`cus_sla_p3`
            WHEN 4 THEN customer.`cus_sla_p4`
            ELSE 0
        END,
        NULL)) AS slaMet,
       sum(IF(pro_status IN ('F' , 'C'),
        problem.`pro_responded_hours` < CASE problem.`pro_priority`
            WHEN 1 THEN customer.`cus_sla_p1`
            WHEN 2 THEN customer.`cus_sla_p2`
            WHEN 3 THEN customer.`cus_sla_p3`
            WHEN 4 THEN customer.`cus_sla_p4`
            ELSE 0
        END,
        NULL)) AS slaMetRaw,
       AVG(IF(pro_status IN ('F' , 'C'),
        problem.`pro_working_hours` > CASE problem.`pro_priority`
            WHEN 1 THEN customer.`slaFixHoursP1`
            WHEN 2 THEN customer.`slaFixHoursP2`
            WHEN 3 THEN customer.`slaFixHoursP3`
            WHEN 4 THEN customer.`slaFixHoursP4`
        END,
        NULL)) AS fixSLAFailedPct,
       sum(IF(pro_status IN ('F' , 'C'),
        problem.`pro_working_hours` > CASE problem.`pro_priority`
            WHEN 1 THEN customer.slaFixHoursP1
            WHEN 2 THEN customer.slaFixHoursP2
            WHEN 3 THEN customer.slaFixHoursP3
            WHEN 4 THEN customer.slaFixHoursP4
            ELSE 0
        END,
        NULL)) AS fixSLAFailedCount,
       sum(
               if(
                       pro_status in ('F', 'C'),
                       timestampdiff(HOUR, concat(initial.caa_date, ' ', initial.caa_endtime, ':00'),
                                     concat(fixed.caa_date, ' ', fixed.caa_starttime, ':00')) >
                       CASE problem.`pro_priority`
                           WHEN 1 THEN customer.slaFixHoursP1
                           WHEN 2 THEN customer.slaFixHoursP2
                           WHEN 3 THEN customer.slaFixHoursP3
                           WHEN 4 THEN customer.slaFixHoursP4
                           END,
                       null
                   )
           )                              as overFixSLAWorkingHours,
    AVG(IF(pro_status IN ('F' , 'C'),
        openHours < 8,
        NULL)) AS closedWithin8Hours,
    AVG(IF(pro_status = 'C',
        problem.`pro_reopened_date` IS NOT NULL,
        NULL)) AS reopened,
       sum(IF(pro_status = 'C',
        problem.`pro_reopened_date` IS NOT NULL,
        NULL)) AS reopenedCount,
    AVG(IF(pro_status IN ('F' , 'C'),
        problem.`pro_chargeable_activity_duration_hours`,
        NULL)) AS avgChargeableTime,
    AVG(IF(pro_status IN ('F' , 'C'),
        problem.pro_working_hours,
        NULL)) AS avgTimeAwaitingCNC,
    AVG(IF(pro_status IN ('F' , 'C'),
        openHours,
        NULL)) AS avgTimeFromRaiseToFixHours
FROM
    problem
        LEFT JOIN
    callactivity initial ON initial.`caa_problemno` = problem.`pro_problemno`
        AND initial.`caa_callacttypeno` = 51
        left join callactivity fixed on fixed.caa_problemno = problem.pro_problemno and fixed.caa_callacttypeno = 57
        JOIN
    customer ON problem.`pro_custno` = customer.`cus_custno`
WHERE

        problem.pro_custno = ?
  and initial.caa_date between ? and ?
        AND pro_priority < 5";
                if ($isBreakDown) {
                    $query = "SELECT 
    pro_priority as priority,
    SUM(1) AS raised,
    SUM(pro_status IN ('F' , 'C')) AS `fixed`,
    AVG(problem.`pro_responded_hours`) AS responseTime,
       CASE problem.`pro_priority`
            WHEN 1 THEN customer.`cus_sla_p1`
            WHEN 2 THEN customer.`cus_sla_p2`
            WHEN 3 THEN customer.`cus_sla_p3`
            WHEN 4 THEN customer.`cus_sla_p4`
            ELSE 0 end as sla,
       CASE problem.`pro_priority`
            WHEN 1 THEN customer.slaFixHoursP1
            WHEN 2 THEN customer.slaFixHoursP2
            WHEN 3 THEN customer.slaFixHoursP3
            WHEN 4 THEN customer.slaFixHoursP4
                else 0 end as fixSLA,
       AVG(IF(pro_status IN ('F' , 'C'),
        problem.`pro_responded_hours` < CASE problem.`pro_priority`
            WHEN 1 THEN customer.`cus_sla_p1`
            WHEN 2 THEN customer.`cus_sla_p2`
            WHEN 3 THEN customer.`cus_sla_p3`
            WHEN 4 THEN customer.`cus_sla_p4`
            ELSE 0
        END,
        NULL)) AS slaMet,
       sum(IF(pro_status IN ('F' , 'C'),
        problem.`pro_responded_hours` < CASE problem.`pro_priority`
            WHEN 1 THEN customer.`cus_sla_p1`
            WHEN 2 THEN customer.`cus_sla_p2`
            WHEN 3 THEN customer.`cus_sla_p3`
            WHEN 4 THEN customer.`cus_sla_p4`
            ELSE 0
        END,
        NULL)) AS slaMetRaw,
       AVG(IF(pro_status IN ('F' , 'C'),
        problem.`pro_working_hours` > CASE problem.`pro_priority`
            WHEN 1 THEN customer.`slaFixHoursP1`
            WHEN 2 THEN customer.`slaFixHoursP2`
            WHEN 3 THEN customer.`slaFixHoursP3`
            WHEN 4 THEN customer.`slaFixHoursP4`
        END,
        NULL)) AS fixSLAFailedPct,
       sum(IF(pro_status IN ('F' , 'C'),
        problem.`pro_working_hours` > CASE problem.`pro_priority`
            WHEN 1 THEN customer.slaFixHoursP1
            WHEN 2 THEN customer.slaFixHoursP2
            WHEN 3 THEN customer.slaFixHoursP3
            WHEN 4 THEN customer.slaFixHoursP4
            ELSE 0
        END,
        NULL)) AS fixSLAFailedCount,
       sum(
               if(
                       pro_status in ('F', 'C'),
                       timestampdiff(HOUR, concat(initial.caa_date, ' ', initial.caa_endtime, ':00'),
                                     concat(fixed.caa_date, ' ', fixed.caa_starttime, ':00')) >
                       CASE problem.`pro_priority`
                           WHEN 1 THEN customer.slaFixHoursP1
                           WHEN 2 THEN customer.slaFixHoursP2
                           WHEN 3 THEN customer.slaFixHoursP3
                           WHEN 4 THEN customer.slaFixHoursP4
                           END,
                       null
                   )
           )                              as overFixSLAWorkingHours,
    AVG(IF(pro_status IN ('F' , 'C'),
        openHours < 8,
        NULL)) AS closedWithin8Hours,
    AVG(IF(pro_status = 'C',
        problem.`pro_reopened_date` IS NOT NULL,
        NULL)) AS reopened,
       sum(IF(pro_status = 'C',
        problem.`pro_reopened_date` IS NOT NULL,
        NULL)) AS reopenedCount,
    AVG(IF(pro_status IN ('F' , 'C'),
        problem.`pro_chargeable_activity_duration_hours`, 
        NULL)) AS avgChargeableTime,
    AVG(IF(pro_status IN ('F' , 'C'),
        problem.pro_working_hours,
        NULL)) AS avgTimeAwaitingCNC,
    AVG(IF(pro_status IN ('F' , 'C'),
        openHours,
        NULL)) AS avgTimeFromRaiseToFixHours
FROM
    problem
        LEFT JOIN
    callactivity initial ON initial.`caa_problemno` = problem.`pro_problemno`
        AND initial.`caa_callacttypeno` = 51
        left join callactivity fixed on fixed.caa_problemno = problem.pro_problemno and fixed.caa_callacttypeno = 57
        JOIN
    customer ON problem.`pro_custno` = customer.`cus_custno`
WHERE

        problem.pro_custno = ?
  and initial.caa_date between ? and ?
        AND pro_priority < 5
        group by pro_priority
        order by pro_priority ";
                }
                /** @var $db dbSweetcode */ global $db;
                $statement = $db->preparedQuery($query, $params);
                if ($isBreakDown) {

                    $data = $statement->fetch_all(MYSQLI_ASSOC);
                } else {
                    $data = $statement->fetch_assoc();
                }
                $response->getBody()->write(json_encode($data, JSON_NUMERIC_CHECK));
                return $response;
            }
        );
        $group->get(
            '/SRCount/{customerId}',
            function (Request $request, Response $response, $args) {
                $queryParams = $request->getQueryParams();
                $endDate     = new DateTime();
                $startDate   = (clone $endDate)->sub(new DateInterval('P365D'));
                if (isset($queryParams['startDate'])) {
                    $startDateString = $queryParams['startDate'];
                    $startDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $startDateString);
                    if (!$startDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The start date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                if (isset($queryParams['endDate'])) {
                    $endDateString = $queryParams['endDate'];
                    $endDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $endDateString);
                    if (!$endDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The end date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                $params = [
                    ["type" => "i", "value" => $args['customerId']],
                    ["type" => "s", "value" => $startDate->format(DATE_MYSQL_DATE)],
                    ["type" => "s", "value" => $endDate->format(DATE_MYSQL_DATE)],
                ];
                $query  = "SELECT
 
  SUM(
    problem.pro_hide_from_customer_flag <> 'Y'
  ) AS raisedManually,
  SUM(
    problem.pro_hide_from_customer_flag = 'Y'
  ) AS proactiveWork
FROM
  problem
WHERE pro_custno = ?
  AND DATE(pro_date_raised) BETWEEN ?
  AND  ?
  AND pro_status = 'C'
ORDER BY raisedManually DESC";
                /** @var $db dbSweetcode */ global $db;
                $statement = $db->preparedQuery($query, $params);
                $data      = $statement->fetch_all(MYSQLI_ASSOC);
                $response->getBody()->write(json_encode($data, JSON_NUMERIC_CHECK));
                return $response;
            }
        );
        $group->get(
            '/SRCountByPerson/{customerId}',
            function (Request $request, Response $response, $args) {
                $queryParams = $request->getQueryParams();
                $endDate     = new DateTime();
                $startDate   = (clone $endDate)->sub(new DateInterval('P365D'));
                if (isset($queryParams['startDate'])) {
                    $startDateString = $queryParams['startDate'];
                    $startDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $startDateString);
                    if (!$startDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The start date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                if (isset($queryParams['endDate'])) {
                    $endDateString = $queryParams['endDate'];
                    $endDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $endDateString);
                    if (!$endDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The end date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                $params = [
                    ["type" => "i", "value" => $args['customerId']],
                    ["type" => "s", "value" => $startDate->format(DATE_MYSQL_DATE)],
                    ["type" => "s", "value" => $endDate->format(DATE_MYSQL_DATE)],
                ];
                $query  = "SELECT
          CONCAT(con_first_name, ' ' , con_last_name) AS name,
             SUM(
    problem.pro_hide_from_customer_flag <> 'Y'
  ) AS raisedManually,
  SUM(
    problem.pro_hide_from_customer_flag = 'Y'
  ) AS proactiveWork
        FROM
          problem
          JOIN contact ON con_contno = pro_contno  WHERE pro_custno = ? and DATE(pro_date_raised) BETWEEN ? AND ? and con_contno <> 0 AND pro_status =  'C'  GROUP BY
          pro_contno
        ORDER BY
          raisedManually DESC";
                /** @var $db dbSweetcode */ global $db;
                $statement = $db->preparedQuery($query, $params);
                $data      = $statement->fetch_all(MYSQLI_ASSOC);
                $response->getBody()->write(json_encode($data, JSON_NUMERIC_CHECK));
                return $response;
            }
        );
        $group->get(
            '/SRCountByRootCause/{customerId}',
            function (Request $request, Response $response, $args) {
                $queryParams = $request->getQueryParams();
                $endDate     = new DateTime();
                $startDate   = (clone $endDate)->sub(new DateInterval('P365D'));
                if (isset($queryParams['startDate'])) {
                    $startDateString = $queryParams['startDate'];
                    $startDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $startDateString);
                    if (!$startDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The start date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                if (isset($queryParams['endDate'])) {
                    $endDateString = $queryParams['endDate'];
                    $endDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $endDateString);
                    if (!$endDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The end date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                $params = [
                    ["type" => "i", "value" => $args['customerId']],
                    ["type" => "s", "value" => $startDate->format(DATE_MYSQL_DATE)],
                    ["type" => "s", "value" => $endDate->format(DATE_MYSQL_DATE)],
                ];
                $query  = "SELECT
          rtc_desc AS rootCauseDescription,
          COUNT(*) AS count
        FROM
          problem
          JOIN rootcause ON rootcause.rtc_rootcauseno = problem.pro_rootcauseno  WHERE pro_custno = ?  and pro_hide_from_customer_flag <> 'Y' and
          DATE(pro_date_raised) BETWEEN ? AND ?   AND pro_status =  'C' GROUP BY
          problem.pro_rootcauseno
        ORDER BY
          count DESC";
                /** @var $db dbSweetcode */ global $db;
                $statement = $db->preparedQuery($query, $params);
                $data      = $statement->fetch_all(MYSQLI_ASSOC);
                $response->getBody()->write(json_encode($data, JSON_NUMERIC_CHECK));
                return $response;
            }
        );
        $group->get(
            '/SRCountByLocation/{customerId}',
            function (Request $request, Response $response, $args) {
                $queryParams = $request->getQueryParams();
                $endDate     = new DateTime();
                $startDate   = (clone $endDate)->sub(new DateInterval('P365D'));
                if (isset($queryParams['startDate'])) {
                    $startDateString = $queryParams['startDate'];
                    $startDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $startDateString);
                    if (!$startDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The start date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                if (isset($queryParams['endDate'])) {
                    $endDateString = $queryParams['endDate'];
                    $endDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $endDateString);
                    if (!$endDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The end date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                $params = [
                    ["type" => "i", "value" => $args['customerId']],
                    ["type" => "s", "value" => $startDate->format(DATE_MYSQL_DATE)],
                    ["type" => "s", "value" => $endDate->format(DATE_MYSQL_DATE)],
                ];
                $query  = "SELECT
  address.`add_postcode`,
  address.`add_town`,
  COUNT(*) AS COUNT
FROM
  problem
  JOIN callactivity
    ON `callactivity`.`caa_problemno` = problem.`pro_problemno`
    AND callactivity.`caa_callacttypeno` = 51
  JOIN contact
    ON callactivity.`caa_contno` = contact.`con_contno`
  JOIN address
    ON address.`add_custno` = pro_custno
    AND contact.`con_siteno` = address.`add_siteno`
WHERE pro_custno = ?
  AND pro_hide_from_customer_flag <> 'Y'
  AND DATE(pro_date_raised) BETWEEN ?
  AND ?
  AND address.`add_active_flag` <> 'N'
  AND pro_status = 'C'
GROUP BY address.`add_siteno`
ORDER BY COUNT DESC";
                /** @var $db dbSweetcode */ global $db;
                $statement = $db->preparedQuery($query, $params);
                $data      = $statement->fetch_all(MYSQLI_ASSOC);
                $response->getBody()->write(json_encode($data, JSON_NUMERIC_CHECK));
                return $response;
            }
        );
        $group->get(
            '/stats',
            function (Request $request, Response $response) {
                global $db;
                $queryParams = $request->getQueryParams();
                $endDate     = new DateTime();
                $startDate   = (clone $endDate)->sub(new DateInterval('P30D'));
                if (isset($queryParams['startDate'])) {
                    $startDateString = $queryParams['startDate'];
                    $startDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $startDateString);
                    if (!$startDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The start date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                if (isset($queryParams['endDate'])) {
                    $endDateString = $queryParams['endDate'];
                    $endDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $endDateString);
                    if (!$endDate) {
                        $response->getBody()->write(
                            json_encode(["error" => "The end date parameter format is not valid: YYYY-MM-DD"])
                        );
                        return $response->withStatus(400);
                    }
                }
                $params      = [
                    ["type" => "s", "value" => $startDate->format(DATE_MYSQL_DATE)],
                    ["type" => "s", "value" => $endDate->format(DATE_MYSQL_DATE)],
                ];
                $isBreakDown = isset($queryParams['breakDown']);
                $query       = 'SELECT
  SUM(1) AS raised,
    SUM(pro_status IN("F","C")) AS `fixed`,
  AVG(if(problem.pro_priority = 1,problem.`pro_responded_hours`, null)) AS responseTime,
  AVG(
   IF(pro_status IN ("F","C"),   
   problem.`pro_responded_hours` < 
    CASE
      problem.`pro_priority`
      WHEN 1
      THEN customer.`cus_sla_p1`
      WHEN 2
      THEN customer.`cus_sla_p2`
      WHEN 3
      THEN customer.`cus_sla_p3`
      WHEN 4
      THEN customer.`cus_sla_p4`
      ELSE 0
    END,
    NULL) 
  ) AS slaMet,
    AVG(IF(pro_status IN ("F","C"), openHours < 8, NULL)) AS closedWithin8Hours,
    AVG(IF(pro_status ="C" and pro_hide_from_customer_flag <> "Y",problem.`pro_reopened_date` IS NOT NULL, NULL)) AS reopened,
    AVG(IF(pro_status IN ("F","C"), problem.`pro_chargeable_activity_duration_hours`,NULL)) AS avgChargeableTime,
    AVG(IF(pro_status IN ("F","C"), problem.pro_working_hours,NULL)) AS avgTimeAwaitingCNC,
    AVG(IF(pro_status IN ("F","C"), openHours,NULL)) AS avgTimeFromRaiseToFixHours
FROM
  problem
  LEFT JOIN callactivity initial
    ON initial.`caa_problemno` = problem.`pro_problemno`
    AND initial.`caa_callacttypeno` = 51
  JOIN customer
    ON problem.`pro_custno` = customer.`cus_custno`
WHERE  caa_date between ? and ?
  AND pro_priority < 5';
                if ($isBreakDown) {
                    $query = 'SELECT
       pro_priority as priority,
  SUM(1) AS raised,
    SUM(pro_status IN("F","C")) AS `fixed`,
  AVG(if(problem.pro_priority = 1,problem.`pro_responded_hours`, null)) AS responseTime,
  AVG(
   IF(pro_status IN ("F","C"),   
   problem.`pro_responded_hours` < 
    CASE
      problem.`pro_priority`
      WHEN 1
      THEN customer.`cus_sla_p1`
      WHEN 2
      THEN customer.`cus_sla_p2`
      WHEN 3
      THEN customer.`cus_sla_p3`
      WHEN 4
      THEN customer.`cus_sla_p4`
      ELSE 0
    END,
    NULL) 
  ) AS slaMet,
    AVG(IF(pro_status IN ("F","C"), openHours < 8, NULL)) AS closedWithin8Hours,
    AVG(IF(pro_status ="C" and pro_hide_from_customer_flag <> "Y",problem.`pro_reopened_date` IS NOT NULL, NULL)) AS reopened,
    AVG(IF(pro_status IN ("F","C"), problem.`pro_chargeable_activity_duration_hours`,NULL)) AS avgChargeableTime,
    AVG(IF(pro_status IN ("F","C"), problem.pro_working_hours,NULL)) AS avgTimeAwaitingCNC,
    AVG(IF(pro_status IN ("F","C"), openHours,NULL)) AS avgTimeFromRaiseToFixHours
FROM
  problem
  LEFT JOIN callactivity initial
    ON initial.`caa_problemno` = problem.`pro_problemno`
    AND initial.`caa_callacttypeno` = 51
  JOIN customer
    ON problem.`pro_custno` = customer.`cus_custno`
WHERE 
 caa_date between ? and ?
  AND pro_priority < 5 
  group by pro_priority
        order by pro_priority';
                }
                try {
                    $statement = $db->preparedQuery($query, $params);
                    if (!$isBreakDown) {
                        $data = $statement->fetch_assoc();
                    } else {
                        $data = $statement->fetch_all(MYSQLI_ASSOC);
                    }
                    $response->getBody()->write(json_encode($data, JSON_NUMERIC_CHECK));
                    return $response;
                } catch (Exception $exception) {
                    throw new Exception('Failed operation');
                }
            }
        );
        $group->post(
            '/termsAndConditionsRequest',
            function (Request $request, Response $response) {
                $requestBody = $request->getParsedBody();
                if (!isset($requestBody['contactId'])) {
                    $response->getBody()->write(
                        json_encode(["error" => "ContactId missing"])
                    );
                    return $response->withStatus(400);
                }
                $buRenewal = new BURenewal($thing);
                try {
                    $buRenewal->sendTermsAndConditionsEmailToContact($requestBody['contactId']);
                    $response->getBody()->write(json_encode(["status" => "ok"]));
                    return $response;
                } catch (ContactNotFoundException $exception) {
                    $response->getBody()->write(
                        json_encode(["status" => "error", "error" => "Contact not found!"])
                    );
                    return $response->withStatus(400);
                }
            }
        );
        $group->post(
            '/renewalsRequest',
            function (Request $request, Response $response) {
                $requestBody = $request->getParsedBody();
                if (!isset($requestBody['contactId'])) {
                    $response->getBody()->write(
                        json_encode(["error" => "ContactId missing"])
                    );
                    return $response->withStatus(400);
                }
                $buRenewal = new BURenewal($thing);
                try {
                    $buRenewal->sendRenewalEmailToContact($requestBody['contactId']);
                    $response->getBody()->write(json_encode(["status" => "ok"]));
                    return $response;
                } catch (ContactNotFoundException $exception) {
                    $response->getBody()->write(
                        json_encode(["status" => "error", "error" => "Contact not found!"])
                    );
                    return $response->withStatus(400);
                }
            }
        );
        $group->get(
            '/tokenData',
            function (Request $request, Response $response) {
                $queryParams = $request->getQueryParams();
                if (empty($queryParams['token'])) {

                    $response->getBody()->write(json_encode(["error" => "Token not provided"]));
                    return $response->withStatus(400);
                }
                global $db;
                $feedbackTokenGenerator = new FeedbackTokenGenerator($db);
                $data                   = $feedbackTokenGenerator->getTokenData($queryParams['token']);
                if (!$data) {
                    $response->getBody()->write(json_encode(["error" => "Token not found!"]));
                    return $response->withStatus(400);
                }
                $response->getBody()->write(json_encode(["status" => "ok", "data" => $data]));
                return $response;
            }
        );
        $group->post(
            '/feedback',
            function (Request $request, Response $response) {
                $data = $request->getParsedBody();
                if (!$data) {
                    $response->getBody()->write(json_encode(["error" => "Data is missing"]));
                    return $response->withStatus(400);
                }
                if (empty($data['token'])) {
                    $response->getBody()->write(json_encode(["error" => "Token not provided"]));
                    return $response->withStatus(400);
                }
                global $db;
                $feedbackTokenGenerator = new FeedbackTokenGenerator($db);
                $tokenData              = $feedbackTokenGenerator->getTokenData($data['token']);
                if (!$tokenData) {
                    $response->getBody()->write(json_encode(["status" => "error", "message" => "Token not found!"]));
                    return $response->withStatus(400);
                }
                if (empty($data['value'])) {
                    $response->getBody()->write(
                        json_encode(["status" => "error", "message" => "Feedback Value not provided"])
                    );
                    return $response->withStatus(400);
                }
                $dbeProblem = new DBEProblem($this);
                $dbeProblem->getRow($tokenData->serviceRequestId);
                if (!$dbeProblem->rowCount()) {
                    $response->getBody()->write(
                        json_encode(["status" => "error", "message" => "Service Request not found"])
                    );
                    return $response->withStatus(400);
                }
                $contactId                          = $dbeProblem->getValue(DBEProblem::contactID);
                $customerFeedbackRepo               = new CustomerFeedbackRepository($db);
                $customerFeedback                   = new CustomerFeedback();
                $customerFeedback->serviceRequestId = $tokenData->serviceRequestId;
                $customerFeedback->contactId        = $contactId;
                $customerFeedback->value            = $data['value'];
                $customerFeedback->comments         = @$data['comments'];
                $customerFeedbackRepo->persistCustomerFeedback($customerFeedback);
                $feedbackTokenGenerator->invalidateToken($data['token']);
                $response->getBody()->write(json_encode(["status" => "ok"]));
                return $response;
            }
        );
        $group->get(
            '/pendingChargeableWorkCustomerRequest/{tokenId}',
            function (Request $request, Response $response, $args) {
                $tokenId = $args['tokenId'];
                if (!$tokenId) {
                    $response->getBody()->write(
                        json_encode(["status" => "error", "message" => "Token id required!", "code" => 1264])
                    );
                    return $response->withStatus(400);
                }
                $chargeableRequestRepo = new ChargeableWorkCustomerRequestMySQLRepository();
                $usecase               = new GetPendingToProcessChargeableRequestInfo($chargeableRequestRepo);
                try {
                    $info = $usecase(new ChargeableWorkCustomerRequestTokenId($tokenId));
                    $response->getBody()->write(
                        json_encode(["status" => "ok", "data" => $info])
                    );
                    return $response->withStatus(400);

                } catch (ChargeableWorkCustomerRequestNotFoundException $exception) {

                    $response->getBody()->write(
                        json_encode(["status" => "error", "message" => "Request not found!", "code" => 1265])
                    );
                    return $response->withStatus(404);

                } catch (ChargeableWorkCustomerRequestAlreadyProcessedException $exception) {
                    $response->getBody()->write(
                        json_encode(["status" => "error", "message" => "Request already processed!", "code" => 1266])
                    );
                    return $response->withStatus(404);
                }
            }
        );
        $group->post(
            '/pendingChargeableWorkCustomerRequest/{tokenId}/approve',
            function (Request $request, Response $response, $args) {
                $tokenId = $args['tokenId'];
                if (!$tokenId) {
                    $response->getBody()->write(
                        json_encode(["status" => "error", "message" => "Token id required!", "code" => 1264])
                    );
                    return $response->withStatus(400);
                }
                $chargeableRequestRepo = new ChargeableWorkCustomerRequestMySQLRepository();
                $usecase               = new ApprovePendingChargeableWorkCustomerRequest($chargeableRequestRepo);
                $requestData           = $request->getParsedBody();
                $comments              = $requestData['comments'];
                try {
                    $info = $usecase(new ChargeableWorkCustomerRequestTokenId($tokenId),$comments);
                    $response->getBody()->write(
                        json_encode(["status" => "ok", "data" => $info])
                    );
                    return $response->withStatus(400);

                } catch (ChargeableWorkCustomerRequestNotFoundException $exception) {

                    $response->getBody()->write(
                        json_encode(["status" => "error", "message" => "Request not found!", "code" => 1265])
                    );
                    return $response->withStatus(404);

                } catch (ChargeableWorkCustomerRequestAlreadyProcessedException $exception) {
                    $response->getBody()->write(
                        json_encode(["status" => "error", "message" => "Request already processed!", "code" => 1266])
                    );
                    return $response->withStatus(404);
                }
            }
        );
    }
);
$app->group(
    '/api',
    function (RouteCollectorProxy $group) {
        $group->get(
            '/',
            function (Request $request, Response $response) {
                $response->getBody()->write('<h1>CNC API v1</h1>');
                return $response;
            }
        );
        $group->get(
            '/signedConfirmation',
            function (Request $request, Response $response) {
                /** @var Environment $twig */
                $twig = $this->get('twig');
                $response->getBody()->write(
                    $twig->render('@api/signedConfirmation.html.twig', ["message" => "Code not provided"])
                );
                return $response;
            }
        );
        $group->get(
            '/acceptQuotation',
            function (Request $request, Response $response) {
                /** @var Environment $twig */
                $twig        = $this->get('twig');
                $queryParams = $request->getQueryParams();
                $code        = @$queryParams['code'];
                if (!$code) {
                    $response->getBody()->write(
                        $twig->render('@api/acceptQuotation.html.twig', ["message" => "Code not provided"])
                    );
                    return $response->withStatus(400);
                }
                // we have to find a quotation with the given code
                $dbeQuotation = new  DBEQuotation($thing);
                $dbeQuotation->setValue(DBEQuotation::confirmCode, $code);
                if (!$dbeQuotation->getRowByColumn(DBEQuotation::confirmCode)) {
                    $response->getBody()->write(
                        $twig->render(
                            '@api/acceptQuotation.html.twig',
                            ["message" => "The Quote to be signed was not found"]
                        )
                    );
                    return $response->withStatus(400);
                }
                if ($dbeQuotation->getValue(DBEQuotation::signableEnvelopeID)) {
                    $response->getBody()->write(
                        $twig->render(
                            '@api/acceptQuotation.html.twig',
                            ["message" => "The Quote is already being processed in Signable"]
                        )
                    );
                    return $response->withStatus(400);
                }
                // we have to generate the PDF file, send it to signable and register it in the DB
                $BUPdfSalesQuote = new BUSalesOrder($thing);
                try {
                    $pdfData = $BUPdfSalesQuote->createSignableOrderForm($dbeQuotation);
                    ApiClient::setApiKey("fc2d9ba05f3f3d9f2e9de4d831e8fed9");
                    $envDocs           = [];
                    $dsDeliveryContact = new DBEContact($this);
                    $dsDeliveryContact->getRow($dbeQuotation->getValue(DBEQuotation::deliveryContactID));
                    $firstName = $dsDeliveryContact->getValue(DBEContact::firstName);
                    $lastName  = $dsDeliveryContact->getValue(DBEContact::lastName);
                    $email     = $dsDeliveryContact->getValue(DBEContact::email);
                    global $server_type;
                    if ($server_type !== MAIN_CONFIG_SERVER_TYPE_LIVE) {
                        $email = "sales@" . CONFIG_PUBLIC_DOMAIN;
                    }
                    $ordHeadID         = $dbeQuotation->getValue(DBEQuotation::ordheadID);
                    $versionNo         = $dbeQuotation->getValue(DBEQuotation::versionNo);
                    $orderFile         = $ordHeadID . '_' . $versionNo . '.pdf';
                    $envelopeDocument  = new DocumentWithoutTemplate(
                        'Customer Form', null, base64_encode($pdfData), $orderFile
                    );
                    $envDocs[]         = $envelopeDocument;
                    $envelopeParties   = [];
                    $envelopeParty     = new Party(
                        $firstName . ' ' . $lastName, $email, 'signer1', 'Please sign here', 'no', false
                    );
                    $envelopeParties[] = $envelopeParty;
                    $expiration        = 7 * 24;
                    $signableResponse  = Envelopes::createNewWithoutTemplate(
                        "Document #" . $ordHeadID . "_" . $versionNo . "_" . uniqid(),
                        $envDocs,
                        $envelopeParties,
                        null,
                        false,
                        API_URL . '/signedConfirmation',
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
                    $dbeSignableEnvelope->setValue(
                        DBESignableEnvelope::processingClass,
                        '\CNCLTD\SignableSignedQuoteDownload'
                    );
                    $dbeSignableEnvelope->insertRow();
                    $dbeQuotation->setValue(DBEQuotation::signableEnvelopeID, $signableResponse->envelope_fingerprint);
                    $dbeQuotation->updateRow();

                } catch (Exception $exception) {
                    error_log($exception->getMessage());
                    $response->getBody()->write(
                        $twig->render(
                            '@api/acceptQuotation.html.twig',
                            ["message" => "Failed to generate PDF file to be sent to Signable, please contact us."]
                        )
                    );
                    return $response->withStatus(500);
                }
                $response->getBody()->write(
                    $twig->render(
                        '@api/acceptQuotation.html.twig',
                        ["message" => "You will now receive an email from Signable with details on how to confirm your order. This quotation is subject to our terms and conditions which are available <a href='https://www.cnc-ltd.co.uk/terms-and-conditions'>here</a>"]
                    )
                );
                return $response;
            }
        );
        $group->group(
            '/signable-hooks',
            function (RouteCollectorProxy $signableHooksGroup) {
                $signableHooksGroup->post(
                    '/',
                    function (Request $request, Response $response) {
                        /** @var LoggerInterface $logger */
                        $logger      = $this->get('logger');
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
                            ["signableEnvelope" => $signableRequest]
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
                        $r                    = new ReflectionClass(
                            $dbeSignableEnvelope->getValue(DBESignableEnvelope::processingClass)
                        );
                        $jsonArguments        = $dbeSignableEnvelope->getValue(
                            DBESignableEnvelope::processingArguments
                        );
                        $associativeArguments = json_decode($jsonArguments);
                        $arguments            = [];
                        if ($associativeArguments) {
                            $arguments = array_values($associativeArguments);
                        }
                        /** @var SignableProcess $objectInstance */
                        $objectInstance = $r->newInstanceArgs($arguments);
                        try {
                            $objectInstance->process($signableRequest, $logger);
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
                    function (Request $request, Response $response) {
                        // we are receiving information about a signed document
                        $response->getBody()->write(json_encode(["message" => "this is the signed hook"]));
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                );
            }
        );
    }
);
$app->any(
    '{route:.*}',
    function (Request $request, Response $response) {
        return $response->withStatus(404, 'page not found');
    }
);
try {
    $app->run();
} catch (HttpNotFoundException $exception) {
    http_response_code(404);
    echo json_encode(["Error" => "Resource not found"]);
}

