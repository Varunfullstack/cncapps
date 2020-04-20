<?php

namespace CNCLTD;


class SideMenu
{
    /** @var MenuSection[] */
    private $sections = [];

    /**
     * @param $key
     * @param $icon
     * @param null $name
     * @return MenuSection
     * @throws \Exception
     */
    public function addSection($key, $icon, $name = null)
    {
        if ($this->getSection($key)) {
            throw new \Exception("This key already exists");
        }
        $section = new MenuSection($key, $icon, $name);
        $this->sections[$key] = $section;
        return $section;
    }

    public function getSection($key)
    {
        if (!isset($this->sections[$key])) {
            return null;
        }
        return $this->sections[$key];
    }

    public function getSections()
    {
        return $this->sections;
    }
}