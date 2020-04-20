<?php

namespace CNCLTD;

class MenuSection
{
    private $key;
    private $name;
    /** @var MenuItem[] */
    private $items = [];
    private $icon;

    /**
     * MenuSection constructor.
     * @param $key
     * @param $icon
     * @param $name
     */
    public function __construct($key, $icon, $name = null)
    {
        $this->key = $key;
        $this->name = $name;
        if (!$name) {
            $this->name = $key;
        };
        $this->icon = $icon;
    }

    /**
     * @param array $items
     */
    public function addItemsFromArray(array $items): void
    {
        foreach ($items as $item) {
            $this->items[] = new MenuItem($item['id'], $item['label'], $item['href'], @$item['attributes']);
        }
        $this->items = $items;
    }

    public function addItem($menuItem)
    {
        $this->items[] = $menuItem;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return MenuItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }
}