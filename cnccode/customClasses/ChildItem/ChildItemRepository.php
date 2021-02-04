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

    public function getChildItemsForItem($parentItemId): array
    {
        $this->db->preparedQuery(
            "SELECT * FROM item JOIN childItem ON childItem.`childItemId` = item.`itm_itemno` WHERE childItem.`parentItemId` = ?",
            [
                "type"  => "i",
                "value" => $parentItemId
            ]
        );
        $rows = $this->db->fetchAll(MYSQLI_ASSOC);
        $data = [];
        foreach ($rows as $row) {
            $data[] = ChildItemDTO::fromPersistence($row);
        }
        return $data;
    }
}