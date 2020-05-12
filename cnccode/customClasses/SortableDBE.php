<?php


namespace CNCLTD;


trait SortableDBE
{
    public function moveItemToBottom($id)
    {
        if ($this->isLast($id)) {
            return;
        }
        $this->getRow($id);
        $this->swapPlaces($this->getSortOrderForItem($id), $this->getMaxSortOrder());
    }

    private function isLast($id)
    {
        $this->getRow($id);
        return $this->getSortOrderForItem($id) >= $this->getMaxSortOrder();
    }

    abstract protected function getRow($id);

    abstract protected function getSortOrderForItem($id);

    public function getMaxSortOrder()
    {
        $query = "select max({$this->getSortOrderColumnName()}) as maxSortOrder from {$this->getTableName()}";
        $this->getDB()->query($query);

        $this->getDB()->next_record(MYSQLI_ASSOC);
        return $this->getDB()->Record['maxSortOrder'];
    }

    abstract protected function getSortOrderColumnName();

    abstract protected function getTableName();

    abstract protected function getDB();

    private function swapPlaces($oldOrderId, $newOrderId)
    {
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
    AND GREATEST($newOrderId, $oldOrderId)";

        $this->setQueryString($query);
        $this->runQuery();

    }

    abstract protected function setQueryString($query);

    abstract protected function runQuery();

    public function moveItemUp($id)
    {
        if ($this->isFirst($id)) {
            return;
        }
        $this->getRow($id);
        $this->swapPlaces($this->getSortOrderForItem($id), $this->getSortOrderForItem($id) - 1);
    }

    private function isFirst($id)
    {
        return $this->getSortOrderForItem($id) <= 1;
    }

    public function moveItemDown($id)
    {
        if ($this->isLast($id)) {
            return;
        }
        $this->getRow($id);
        $this->swapPlaces($this->getSortOrderForItem($id), $this->getSortOrderForItem($id) + 1);
    }

    public function getNextSortOrder()
    {
        return $this->getMaxSortOrder() + 1;
    }

    public function moveItemToTop($id)
    {
        if ($this->isFirst($id)) {
            return;
        }

        $this->swapPlaces($this->getSortOrderForItem($id), 1);
    }
}