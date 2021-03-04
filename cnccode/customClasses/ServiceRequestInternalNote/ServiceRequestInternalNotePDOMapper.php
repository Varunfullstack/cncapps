<?php

namespace CNCLTD\ServiceRequestInternalNote;
class ServiceRequestInternalNotePDOMapper
{
    public static function toDomain(ServiceRequestInternalNotePDODTO $fromDB): ServiceRequestInternalNote
    {
        return ServiceRequestInternalNote::create(
            $fromDB->getId(),
            $fromDB->getServiceRequestId(),
            $fromDB->getCreatedBy(),
            \DateTimeImmutable::createFromFormat(DATE_MYSQL_DATETIME, $fromDB->getCreatedAt()),
            $fromDB->getUpdatedBy(),
            \DateTimeImmutable::createFromFormat(DATE_MYSQL_DATETIME, $fromDB->getUpdatedAt()),
            $fromDB->getContent()
        );
    }

    public static function toJSONArray(ServiceRequestInternalNote $serviceRequestInternalNote): array
    {
        return [
            "id"               => $serviceRequestInternalNote->getId(),
            "serviceRequestId" => $serviceRequestInternalNote->getServiceRequestId(),
            "createdBy"        => $serviceRequestInternalNote->getCreatedBy(),
            "createdAt"        => $serviceRequestInternalNote->getCreatedAt(),
            "updatedBy"        => $serviceRequestInternalNote->getUpdatedBy(),
            "updatedAt"        => $serviceRequestInternalNote->getUpdatedAt(),
            "content"          => $serviceRequestInternalNote->getContent(),
        ];
    }
}