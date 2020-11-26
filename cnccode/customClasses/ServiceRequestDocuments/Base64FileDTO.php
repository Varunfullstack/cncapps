<?php

namespace CNCLTD\ServiceRequestDocuments;
class Base64FileDTO
{
    public $file;
    public $name;

    /**
     * @param $filesArray
     * @return Base64FileDTO[]
     */
    public static function fromArray($filesArray)
    {
        return array_map(
            function ($item) {
                $file       = new self();
                $file->file = $item['file'];
                $file->name = $item['name'];
                return $file;
            },
            $filesArray
        );
    }
}