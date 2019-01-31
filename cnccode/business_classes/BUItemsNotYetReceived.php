<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 10/12/2018
 * Time: 8:57
 */

class BUItemsNotYetReceived extends Business
{
    /**
     * @return \CNCLTD\ItemNotYetReceived[]
     */
    public function getItemsNotYetReceived()
    {
        global $db;
        $query = "SELECT 
  porhead.`poh_porno` as purchaseOrderId,
  customer.`cus_name` as customerName,
  item.`itm_desc` as itemDescription,
  supplier.`sup_name` as supplierName,
  IF(
    poh_direct_del = 'N',
    'CNC',
    'Direct'
  ) AS direct,
  if(poh_ord_date = '0000-00-00', null, poh_ord_date) as purchaseOrderDate,
  (SELECT 
    MIN(ca.caa_date) 
  FROM
    callactivity ca 
  WHERE ca.caa_problemno = problem.`pro_problemno`
    and ca.caa_callacttypeno in (4,7)
    AND ca.caa_date >= date(NOW())
    ) AS futureDate,
    poh_required_by as purchaseOrderRequiredBy,
    project.description as projectName,
    poh_supp_ref as supplierRef,
    IF(poh_contno <> 0 OR poh_contno IS NOT NULL, poh_contno, NULL) AS orderedBy ,
    poh_type as purchaseOrderType,
    poh_ord_date is not null and poh_ord_date <> '0000-00-00' as hasBeenOrdered ,
    pol_qty_ord <> pol_qty_rec as hasNotBeenReceivedYet,
    pol_qty_ord AS orderedQuantity,
      ordhead.odh_ordno as salesOrderID,
       project.projectID
FROM
  porline 
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
  LEFT JOIN problem 
    ON problem.`pro_linked_ordno` = porhead.`poh_ordno`
  left join project 
    on project.ordHeadID = ordhead.odh_ordno
WHERE poh_required_by is not null and poh_required_by <> '0000-00-00'
  and poh_required_by > (now() - INTERVAL 1 week )
AND item.itm_desc NOT LIKE '%labour%'
AND item.itm_desc NOT LIKE '%Office 365%'
  AND item.itm_desc NOT LIKE '%carriage%'
AND customer.cus_name <> 'CNC Operating Stock'
and (porline.pol_cost > 0 or porline.pol_cost < 0)
  ORDER BY poh_required_by ASC, ordhead.`odh_custno` DESC, pol_porno ASC, `pol_lineno` ASC
";

        $db->query($query);
        $data = [];

        /** @var \CNCLTD\ItemNotYetReceived $item */
        while ($item = $db->next_record_object(\CNCLTD\ItemNotYetReceived::class)) {

            if (!isset(\CNCLTD\ItemNotYetReceived::$items[$item->getPurchaseOrderId()])) {
                \CNCLTD\ItemNotYetReceived::$items[$item->getPurchaseOrderId()] = true;
            }

            if (\CNCLTD\ItemNotYetReceived::$items[$item->getPurchaseOrderId()] && !$item->isGreenType()) {
                \CNCLTD\ItemNotYetReceived::$items[$item->getPurchaseOrderId()] = false;
            }

            $data[] = $item;
        };

        return $data;
    }

}