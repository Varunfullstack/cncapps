<?php

use CNCLTD\ItemNotYetReceived;

/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 10/12/2018
 * Time: 8:57
 */
class BUItemsNotYetReceived extends Business
{
    /**
     * @param int $daysAgo
     * @return ItemNotYetReceived[]
     */
    public function getItemsNotYetReceived($daysAgo = 7)
    {
        global $db;
        $query = "SELECT porhead.deliveryConfirmedFlag,
       porhead.`poh_porno`                                             as purchaseOrderId,
       customer.`cus_name`                                             as customerName,
       item.`itm_desc`                                                 as itemDescription,
       supplier.`sup_name`                                             as supplierName,
       itm_itemno                                                      as itemId,
       pol_lineno                                                      as lineSequenceNumber,

       IF(
               poh_direct_del = 'N',
               'CNC',
               'Direct'
           )                                                           AS direct,
       poh_ord_date                                                    as purchaseOrderDate,
       
       (SELECT MIN(ca.caa_date)
        FROM callactivity ca
        WHERE ca.caa_problemno = minServiceRequest.`pro_problemno`
          and ca.caa_callacttypeno in (4, 7)
          AND ca.caa_date >= date(NOW())
       )                                                               AS futureDate,
       poh_required_by                                                 as purchaseOrderRequiredBy,
       project.description                                             as projectName,
       poh_supp_ref                                                    as supplierRef,
       IF(poh_contno <> 0 OR poh_contno IS NOT NULL, poh_contno, NULL) AS orderedBy,
       poh_type                                                        as purchaseOrderType,
       poh_ord_date is not null                                        as hasBeenOrdered,
       pol_qty_ord <> pol_qty_rec                                      as hasNotBeenReceivedYet,
       pol_qty_ord                                                     AS orderedQuantity,
       ordhead.odh_ordno                                               as salesOrderID,
       pol_exp_date                                                    as expectedOn,
       pol_cost                                                        as cost,
       project.projectID,
       
       minServiceRequest.`pro_problemno`                               as serviceRequestID,
       expectedTBC
FROM porline
         LEFT JOIN porhead
                   ON porline.pol_porno = porhead.`poh_porno`
         LEFT JOIN item
                   ON item.`itm_itemno` = porline.`pol_itemno`
         LEFT JOIN ordhead
                   ON porhead.`poh_ordno` = ordhead.`odh_ordno`
         LEFT JOIN customer
                   ON ordhead.`odh_custno` = customer.`cus_custno`
         LEFT JOIN supplier
                   ON supplier.`sup_suppno` = porhead.`poh_suppno`
         left join (select problem.pro_linked_ordno, min(problem.pro_problemno) as pro_problemno
                    from problem
                    group by problem.pro_linked_ordno) minServiceRequest
                   on minServiceRequest.pro_linked_ordno = porhead.poh_ordno
         left join project
                   on project.ordHeadID = ordhead.odh_ordno
WHERE  item.excludeFromPOCompletion = 'N'
  AND customer.cus_name <> 'CNC Operating Stock'
  and (porline.pol_cost > 0 or porline.pol_cost < 0)and odh_type <> 'C'
  and poh_required_by > (now() - INTERVAL ? day)
ORDER BY poh_required_by, ordhead.`odh_custno` DESC, pol_porno, `pol_lineno` 
";
        $statement = $db->preparedQuery(
            $query,
            [
                [
                    "type"  => "i",
                    "value" => $daysAgo
                ]
            ]
        );

        $data = [];

        /** @var ItemNotYetReceived $item */
        while ($item = $statement->fetch_object(ItemNotYetReceived::class)) {
            if (!isset(ItemNotYetReceived::$items[$item->getPurchaseOrderId()])) {
                ItemNotYetReceived::$items[$item->getPurchaseOrderId()] = true;
            }

            if (!$item->isDeliveryConfirmed() && ItemNotYetReceived::$items[$item->getPurchaseOrderId(
                )] && !$item->isGreenType()) {
                ItemNotYetReceived::$items[$item->getPurchaseOrderId()] = false;
            }

            $data[] = $item;
        }
        $toReturn = [];

        foreach ($data as $item) {
            if (ItemNotYetReceived::$items[$item->getPurchaseOrderId()]) {
                continue;
            }
            $toReturn[] = $item;
        }

        return $toReturn;
    }

}