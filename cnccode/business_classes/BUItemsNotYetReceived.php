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
  poh_ord_date as purchaseOrderDate,
  (SELECT 
    MIN(ca.caa_date) 
  FROM
    callactivity ca 
    LEFT JOIN callacttype cat 
      ON cat.cat_callacttypeno = ca.caa_callacttypeno 
  WHERE ca.caa_problemno = problem.`pro_problemno` 
    AND ca.caa_date >= NOW()) AS futureDate,
    poh_required_by as purchaseOrderRequiredBy,
    project.description as projectName,
    (select max(deliverynote.dateTime) from deliverynote where ordheadID = ordhead.odh_ordno ) as dispatchedDate,
       poh_supp_ref as supplierRef,
       IF(poh_contno <> 0 OR poh_contno IS NOT NULL, poh_contno, NULL) AS orderedBy ,
       poh_type as purchaseOrderType
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
WHERE poh_required_by is not null and poh_required_by <> '0000-00-00' and poh_required_by >= NOW()
AND item.itm_desc NOT LIKE '%labour%'
AND customer.cus_name <> 'CNC Sales Stock'
AND item.itm_desc NOT LIKE '%Office 365%'
AND customer.cus_name <> 'CNC Operating Stock'
and (porline.pol_cost > 0 or porline.pol_cost < 0)
  order by poh_required_by asc 
";


        $db->query($query);
        $data = [];
        while ($item = $db->next_record_object(\CNCLTD\ItemNotYetReceived::class)) {
            $data[] = $item;
        };

        return $data;
    }
}