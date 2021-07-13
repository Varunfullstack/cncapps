<?php

namespace CNCLTD;
use Exception;
use UnexpectedValueException;

class SideMenu
{
    /** @var MenuSection[] */
    private $sections = [];
    /** @var FavouriteMenu */
    private $favouriteMenu;
    /** @var MenuItem[] */
    private $favouriteItems = [];

    public function __construct(FavouriteMenu $favouriteMenu)
    {
        $this->favouriteMenu = $favouriteMenu;
    }

    /**
     * @param $key
     * @param $icon
     * @param $items
     * @param null $name
     * @throws Exception
     */
    public function addSection($key, $icon, $items, $name = null)
    {
        if ($this->getSection($key)) {
            throw new Exception("This key already exists");
        }
        $section              = new MenuSection($key, $icon, $name);
        $this->sections[$key] = $section;
        $section->addItemsFromArray($items);
        foreach ($section->getItems() as $item) {
            if ($this->favouriteMenu->isFavourite($item->getId())) {
                $item->makeFavourite();
                $this->favouriteItems[] = $item;
            }
        }
    }

    public function getSection($key)
    {
        if (!isset($this->sections[$key])) {
            return null;
        }
        return $this->sections[$key];
    }

    public function addItemToSection($sectionKey, MenuItem $menuItem)
    {
        if (!$section = $this->getSection($sectionKey)) {
            throw new UnexpectedValueException("Section does not exist");
        }
        if ($this->favouriteMenu->isFavourite($menuItem->getId())) {
            $menuItem->makeFavourite();
            $this->favouriteItems[] = $menuItem;
        }
        $section->addItem($menuItem);
    }

    public function getFavouriteItems()
    {
        $favouriteIds = $this->favouriteMenu->getFavourites();
        usort(
            $this->favouriteItems,
            function ($a, $b) use ($favouriteIds) {
                $aPos = array_search($a->getId(), $favouriteIds);
                $bPos = array_search($b->getId(), $favouriteIds);
                return $aPos - $bPos;
            }
        );
        return $this->favouriteItems;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function sort()
    {
        foreach ($this->sections as $section) {
            $section->sortItems();
        }
    }
}