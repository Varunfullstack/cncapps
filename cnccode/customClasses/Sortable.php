<?php


namespace CNCLTD;


interface Sortable
{
    function moveItemToTop($itemId);

    function moveItemToBottom($itemId);

    function moveItemUp($itemId);

    function moveItemDown($itemId);

}