<?php

namespace CNCLTD\ServiceRequestDocuments;

use CNCLTD\ServiceRequestDocuments\Entity\ServiceRequestDocument;
use CNCLTD\ServiceRequestDocuments\Entity\ServiceRequestDocumentMapper;

class ServiceRequestDocumentRepository
{

    /**
     * @param $serviceRequestId
     * @return ServiceRequestDocument[]
     * @throws \Exception
     */
    public function getServiceRequestsDocuments($serviceRequestId)
    {
        global $db;
        $result   = $db->preparedQuery(
            "select * from serviceRequestDocument where serviceRequestId = ?",
            [["type" => "i", "value" => $serviceRequestId]]
        );
        $toReturn = [];
        /** @var ServiceRequestDocumentMySQLDTO $row */
        while ($row = $result->fetch_object(ServiceRequestDocumentMySQLDTO::class)) {
            $toReturn[] = ServiceRequestDocumentMapper::fromMYSQLtoDomain($row);
        }
        return $toReturn;
    }

    public function save(ServiceRequestDocument $serviceRequestDocument)
    {
        global $db;
        $result = $db->preparedQuery(
            "insert into serviceRequestDocument(id, serviceRequestId, originalFileName,storedFileName, mimeType, createdAt) values (?,?,?,?,?,?)",
            [
                [
                    "type"  => "i",
                    "value" => $serviceRequestDocument->id()
                ],
                [
                    "type"  => "i",
                    "value" => $serviceRequestDocument->serviceRequestId()
                ],
                [
                    "type"  => "s",
                    "value" => $serviceRequestDocument->originalFileName()
                ],
                [
                    "type"  => "s",
                    "value" => $serviceRequestDocument->storedFileName()
                ],
                [
                    "type"  => "s",
                    "value" => $serviceRequestDocument->mimeType()
                ],
                [
                    "type"  => "s",
                    "value" => $serviceRequestDocument->createdAt()->format(DATE_MYSQL_DATETIME)
                ]
            ]
        );
    }

    public function getNextId()
    {
        global $db;
        return $db->nextId('serviceRequestDocument');
    }

    /**
     * @param $documentId
     * @return ServiceRequestDocument
     * @throws \Exception
     */
    public function getById($documentId): ServiceRequestDocument
    {
        global $db;
        $result = $db->preparedQuery(
            "select * from serviceRequestDocument where id = ?",
            [["type" => "i", "value" => $documentId]]
        );
        /** @var ServiceRequestDocumentMySQLDTO $row */
        $row = $result->fetch_object(ServiceRequestDocumentMySQLDTO::class);
        return ServiceRequestDocumentMapper::fromMYSQLtoDomain($row);
    }

    public function deleteDocument(ServiceRequestDocument $serviceRequestDocument)
    {
        global $db;
        $db->preparedQuery(
            "delete from serviceRequestDocument where id = ?",
            [["type" => "i", "value" => $serviceRequestDocument->id()]]
        );
        $serviceRequestDocument->deleteFile();
    }


}