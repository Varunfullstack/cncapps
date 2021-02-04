<?php

namespace CNCLTD\ChildItem;
class ChildItemDTO implements \JsonSerializable
{
    private $parentItemId;
    private $childItemId;
    private $description;
    private $quantity;

    public static function fromPersistence($array)
    {
        $instance               = new self();
        $instance->parentItemId = $array['parentItemId'];
        $instance->childItemId  = $array['childItemId'];
        $instance->description  = $array['itm_desc'];
        $instance->quantity     = $array['quantity'];
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

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}