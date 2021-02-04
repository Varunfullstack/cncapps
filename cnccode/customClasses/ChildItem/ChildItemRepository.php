<?php

namespace CNCLTD\ChildItem;
class ChildItemRepository
{
    /**
     * @var \dbSweetcode
     */
    private $db;

    /**
     * ChildItemRepository constructor.
     * @param \dbSweetcode $dbSweetcode
     */
    public function __construct(\dbSweetcode $dbSweetcode)
    {
        $this->db = $dbSweetcode;
    }

    /**
     * @param $parentItemId
     * @return ChildItemDTO[]
     * @throws \Exception
     */
    public function getChildItemsForItem($parentItemId): array
    {
        $result = $this->db->preparedQuery(
            "SELECT * FROM item JOIN childItem ON childItem.`childItemId` = item.`itm_itemno` WHERE childItem.`parentItemId` = ?",
            [
                [
                    "type"  => "i",
                    "value" => $parentItemId
                ]
            ]
        );
        $rows   = $result->fetch_all(MYSQLI_ASSOC);
        $data   = [];
        foreach ($rows as $row) {
            $data[] = ChildItemDTO::fromPersistence($row);
        }
        return $data;
    }

    public function updateChildItemQuantity($parentItemId, $childItemId, $quantity)
    {
        $result = $this->db->preparedQuery(
            "update childItem set quantity = ? where parentItemId = ? and childItemId = ?",
            [
                [
                    "type"  => "i",
                    "value" => $quantity
                ],
                [
                    "type"  => "i",
                    "value" => $parentItemId
                ],
                [
                    "type"  => "i",
                    "value" => $childItemId
                ],
            ]
        );
    }
}