<?php


namespace CNCLTD;

trait SortableWithQueryDBE
{
    public function moveItemToBottom()
    {
        if ($this->isLast()) {
            return;
        }
        $this->swapPlaces($this->getSortOrder(), $this->getMaxSortOrder());
    }

    public function isLast()
    {
        return $this->getSortOrder() >= $this->getMaxSortOrder();
    }

    public function getMaxSortOrder()
    {
        /** @var $db \dbSweetcode */
        global $db;
        $query = "select max({$this->getSortOrderColumnName()}) as maxSortOrder from {$this->getTableName()} where 
                                                   {$this->getDiscriminatorCondition()}
                                                   
                                                   ";

        $result = $db->query($query);
        if (!$result) {
            throw new \Exception("Failed to execute query: $query");
        }
        if (!$db->next_record(MYSQLI_ASSOC)) {
            return 0;
        }
        return $db->Record['maxSortOrder'];
    }

    abstract protected function getSortOrderColumnName();

    abstract protected function getTableName();

    abstract protected function getDiscriminatorCondition();

    public function swapPlaces($oldOrderId, $newOrderId)
    {
        /** @var $db \dbSweetcode */
        global $db;
        $query = "UPDATE
  {$this->getTableName()}
SET
  {$this->getSortOrderColumnName()} =
  CASE
    WHEN {$this->getSortOrderColumnName()} = $oldOrderId
    THEN $newOrderId
    WHEN $newOrderId < $oldOrderId
    AND {$this->getSortOrderColumnName()} < $oldOrderId
    THEN {$this->getSortOrderColumnName()} + 1
    WHEN $newOrderId > $oldOrderId
    AND {$this->getSortOrderColumnName()} > $oldOrderId
    THEN {$this->getSortOrderColumnName()} - 1
    ELSE {$this->getSortOrderColumnName()}
  END
WHERE {$this->getSortOrderColumnName()} BETWEEN LEAST($newOrderId, $oldOrderId)
    AND GREATEST($newOrderId, $oldOrderId) and {$this->getDiscriminatorCondition()} ";

        $db->query($query);
        $db->next_record();
    }

    public function moveItemUp()
    {
        if ($this->isFirst()) {
            return;
        }
        $this->swapPlaces($this->getSortOrder(), $this->getSortOrder() - 1);
    }

    public function isFirst()
    {
        return $this->getSortOrder() <= 1;
    }

    public function moveItemDown()
    {
        if ($this->isLast()) {
            return;
        }
        $this->swapPlaces($this->getSortOrder(), $this->getSortOrder() + 1);
    }

    public function getNextSortOrder()
    {
        return $this->getMaxSortOrder() + 1;
    }

    public function moveItemToTop()
    {
        if ($this->isFirst()) {
            return;
        }

        $this->swapPlaces($this->getSortOrder(), 1);
    }
}