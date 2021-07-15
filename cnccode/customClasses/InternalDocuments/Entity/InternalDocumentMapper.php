<?php

namespace CNCLTD\InternalDocuments\Entity;

use CNCLTD\InternalDocuments\InternalDocumentDTO;
use CNCLTD\InternalDocuments\InternalDocumentMySQLDTO;
use DateTime;

class InternalDocumentMapper
{
    public static function fromMYSQLtoDomain(InternalDocumentMySQLDTO $fromPersistence): InternalDocument
    {
        return InternalDocument::create(
            $fromPersistence->id,
            $fromPersistence->serviceRequestId,
            $fromPersistence->originalFileName,
            $fromPersistence->storedFileName,
            $fromPersistence->mimeType,
            DateTime::createFromFormat(DATE_MYSQL_DATETIME, $fromPersistence->createdAt)
        );
    }

    public static function fromDomainArrayToJSONDTO($arrayOfDomainObjects)
    {
        return array_map(
            function (InternalDocument $element) {
                return static::fromDomainToJSONDTO($element);
            },
            $arrayOfDomainObjects
        );
    }

    private static function fromDomainToJSONDTO(InternalDocument $element): InternalDocumentDTO
    {
        $dto                   = new InternalDocumentDTO();
        $dto->id               = $element->id();
        $dto->originalFileName = $element->originalFileName();
        $dto->storedFileName   = $element->storedFileName();
        $dto->mimeType         = $element->mimeType();
        $dto->serviceRequestId = $element->serviceRequestId();
        $dto->createdAt        = $element->createdAt()->format(DATE_MYSQL_DATETIME);
        return $dto;
    }
}