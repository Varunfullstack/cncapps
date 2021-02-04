<?php

namespace CNCLTD\ChildItem;
class ChildItemDTO implements \JsonSerializable
{
    private $parentItemId;
    private $childItemId;
    private $description;
    private $quantity;
    private $curUnitCost;
    private $curUnitSale;

    public static function fromPersistence($array): ChildItemDTO
    {
        $instance               = new self();
        $instance->parentItemId = $array['parentItemId'];
        $instance->childItemId  = $array['childItemId'];
        $instance->description  = $array['itm_desc'];
        $instance->quantity     = $array['quantity'];
        $instance->curUnitCost  = $array['curUnitCost'];
        $instance->curUnitSale  = $array['curUnitSale'];
        return $instance;
    }

    /**
     * @return mixed
     */
    public function getParentItemId()
    {
        return $this->parentItemId;
    }

    /**
     * @return mixed
     */
    public function getChildItemId()
    {
        return $this->childItemId;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return mixed
     */
    public function getCurUnitCost()
    {
        return $this->curUnitCost;
    }

    /**
     * @return mixed
     */
    public function getCurUnitSale()
    {
        return $this->curUnitSale;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}