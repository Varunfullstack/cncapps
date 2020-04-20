<?php


namespace CNCLTD;


class MenuItem
{
    private $id;
    private $label;
    private $href;
    private $attributes;

    /**
     * MenuItem constructor.
     * @param $id
     * @param $label
     * @param $href
     * @param $attributes
     */
    public function __construct($id, $label, $href, $attributes = null)
    {
        $this->id = $id;
        $this->label = $label;
        $this->href = $href;
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}