<?php

namespace CNCLTD\InternalDocuments\Entity;

use CNCLTD\InternalDocuments\Base64FileDTO;
use DataURI\Parser;
use DateTime;
use DateTimeInterface;
use Exception;

class InternalDocument
{
    private $id;
    private $serviceRequestId;
    private $originalFileName;
    private $storedFileName;
    private $mimeType;
    private $createdAt;


    /**
     * InternalDocument constructor.
     * @param $id
     * @param $serviceRequestId
     * @param $originalFileName
     * @param $storedFileName
     * @param $mimeType
     * @param $createdAt
     */
    private function __construct($id,
                                 $serviceRequestId,
                                 $originalFileName,
                                 $storedFileName,
                                 $mimeType,
                                 DateTimeInterface $createdAt
    )
    {
        $this->id               = $id;
        $this->serviceRequestId = $serviceRequestId;
        $this->originalFileName = $originalFileName;
        $this->storedFileName   = $storedFileName;
        $this->mimeType         = $mimeType;
        $this->createdAt        = $createdAt;
    }

    public static function createFromBase64URIFileObject(Base64FileDTO $base64FileDTO, $serviceRequestId, $id)
    {
        $dataObject       = Parser::parse($base64FileDTO->file);
        $originalFileName = $base64FileDTO->name;
        $extension        = pathinfo($base64FileDTO->name, PATHINFO_EXTENSION);
        $storedFileName   = uniqid() . ".{$extension}";
        $path             = INTERNAL_DOCUMENTS_FOLDER . "/{$serviceRequestId}";
        if (!is_dir($path)) {
            mkdir($path, null, true);
            if (!is_dir($path)) {
                throw new Exception("Failed to create folder!! {$path} ");
            }
        }
        file_put_contents("{$path}/$storedFileName", $dataObject->getData());
        return self::create(
            $id,
            $serviceRequestId,
            $originalFileName,
            $storedFileName,
            $dataObject->getMimeType(),
            new DateTime()
        );
    }

    public static function create($id,
                                  $serviceRequestId,
                                  $originalFileName,
                                  $storedFileName,
                                  $mimeType,
                                  DateTimeInterface $createdAt
    )
    {
        return new self($id, $serviceRequestId, $originalFileName, $storedFileName, $mimeType, $createdAt);
    }

    public function getFilePath()
    {
        return INTERNAL_DOCUMENTS_FOLDER . "/{$this->serviceRequestId}/{$this->storedFileName}";
    }

    public function getFileContents()
    {
        return file_get_contents($this->getFilePath());
    }

    public function deleteFile()
    {
        return unlink($this->getFilePath());
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function serviceRequestId()
    {
        return $this->serviceRequestId;
    }

    /**
     * @return mixed
     */
    public function originalFileName()
    {
        return $this->originalFileName;
    }

    /**
     * @return mixed
     */
    public function storedFileName()
    {
        return $this->storedFileName;
    }

    /**
     * @return mixed
     */
    public function mimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return DateTimeInterface
     */
    public function createdAt(): DateTimeInterface
    {
        return $this->createdAt;
    }


}