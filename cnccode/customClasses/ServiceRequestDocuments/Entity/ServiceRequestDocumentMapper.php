<?php

namespace CNCLTD\ServiceRequestDocuments\Entity;

use CNCLTD\ServiceRequestDocuments\ServiceRequestDocumentDTO;
use CNCLTD\ServiceRequestDocuments\ServiceRequestDocumentMySQLDTO;

class ServiceRequestDocumentMapper
{
    public static function fromMYSQLtoDomain(ServiceRequestDocumentMySQLDTO $fromPersistence): ServiceRequestDocument
    {
        return ServiceRequestDocument::create(
            $fromPersistence->id,
            $fromPersistence->serviceRequestId,
            $fromPersistence->originalFileName,
            $fromPersistence->storedFileName,
            $fromPersistence->mimeType,
            \DateTime::createFromFormat(DATE_MYSQL_DATETIME, $fromPersistence->createdAt)
        );
    }

    public static function fromDomainArrayToJSONDTO($arrayOfDomainObjects)
    {
        return array_map(
            function (ServiceRequestDocument $element) {
                return static::fromDomainToJSONDTO($element);
            },
            $arrayOfDomainObjects
        );
    }

    private static function fromDomainToJSONDTO(ServiceRequestDocument $element): ServiceRequestDocumentDTO
    {
        $dto                   = new ServiceRequestDocumentDTO();
        $dto->id               = $element->id();
        $dto->originalFileName = $element->originalFileName();
        $dto->storedFileName   = $element->storedFileName();
        $dto->mimeType         = $element->mimeType();
        $dto->serviceRequestId = $element->serviceRequestId();
        $dto->createdAt        = $element->createdAt()->format(DATE_MYSQL_DATETIME);
        return $dto;
    }
}