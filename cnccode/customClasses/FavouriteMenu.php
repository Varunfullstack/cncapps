<?php


namespace CNCLTD;


class FavouriteMenu
{
    const MAX_FAVOURITES = 10;
    private $db;
    private $userId;
    /** @var int[] */
    private $favourites;

    public function __construct($userId)
    {
        global $db;
        $this->db = $db;

        $this->userId = $userId;
        $this->fetchFavourites();
    }

    public function fetchFavourites()
    {
        $statement = $this->db->preparedQuery(
            'select menuId from favourites where userId = ? order by createdAt',
            [["type" => "i", "value" => $this->userId]]
        );
        $this->favourites = [];
        while ($fav = $statement->fetch_assoc()) {
            $this->favourites[] = $fav['menuId'];
        }
    }

    public function getFavourites()
    {
        return $this->favourites;
    }

    public function addFavourite($menuId)
    {
        // if someone tries to add a favourite for which is already added ..ignore
        if (in_array(+$menuId, $this->favourites)) {
            return;
        }

        if (count($this->favourites) == self::MAX_FAVOURITES) {
            $this->removeFavourite($this->favourites[0]);
        }
        $this->insertFavourite($menuId);
        $this->favourites[] = $menuId;

    }

    public function removeFavourite(int $menuId)
    {
        $this->db->preparedQuery(
            "delete from favourites where menuId = ? and userId = ?",
            [
                [
                    "type"  => "i",
                    "value" => $menuId
                ],
                [
                    "type"  => "i",
                    "value" => $this->userId
                ],
            ]
        );
        $this->favourites = array_filter(
            $this->favourites,
            function ($itemId) use ($menuId) {
                return $itemId != $menuId;
            }
        );
        $this->favourites = array_values($this->favourites);
    }

    private function insertFavourite($menuId)
    {
        $this->db->preparedQuery(
            "insert into favourites(menuId,userId) values (?,?)",
            [
                [
                    "type"  => "i",
                    "value" => $menuId,
                ],
                [
                    "type"  => "i",
                    "value" => $this->userId
                ],
            ]
        );
    }

    public function isFavourite($itemId)
    {
        return in_array($itemId, $this->favourites);
    }
}