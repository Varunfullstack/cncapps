<?php

namespace CNCLTD\InternalDocuments;

use CNCLTD\InternalDocuments\Entity\InternalDocument;
use CNCLTD\InternalDocuments\Entity\InternalDocumentMapper;
use Exception;

class InternalDocumentRepository
{

    /**
     * @param $serviceRequestId
     * @return InternalDocument[]
     * @throws Exception
     */
    public function getServiceRequestsDocuments($serviceRequestId)
    {
        global $db;
        $result   = $db->preparedQuery(
            "select * from InternalDocument where serviceRequestId = ?",
            [["type" => "i", "value" => $serviceRequestId]]
        );
        $toReturn = [];
        /** @var InternalDocumentMySQLDTO $row */
        while ($row = $result->fetch_object(InternalDocumentMySQLDTO::class)) {
            $toReturn[] = InternalDocumentMapper::fromMYSQLtoDomain($row);
        }
        return $toReturn;
    }

    public function save(InternalDocument $internalDocument)
    {
        global $db;
        $result = $db->preparedQuery(
            "insert into InternalDocument(id, serviceRequestId, originalFileName,storedFileName, mimeType, createdAt) values (?,?,?,?,?,?)",
            [
                [
                    "type"  => "i",
                    "value" => $internalDocument->id()
                ],
                [
                    "type"  => "i",
                    "value" => $internalDocument->serviceRequestId()
                ],
                [
                    "type"  => "s",
                    "value" => $internalDocument->originalFileName()
                ],
                [
                    "type"  => "s",
                    "value" => $internalDocument->storedFileName()
                ],
                [
                    "type"  => "s",
                    "value" => $internalDocument->mimeType()
                ],
                [
                    "type"  => "s",
                    "value" => $internalDocument->createdAt()->format(DATE_MYSQL_DATETIME)
                ]
            ]
        );
    }

    public function getNextId()
    {
        global $db;
        return $db->nextId('InternalDocument');
    }

    /**
     * @param $documentId
     * @return InternalDocument
     * @throws Exception
     */
    public function getById($documentId): InternalDocument
    {
        global $db;
        $result = $db->preparedQuery(
            "select * from InternalDocument where id = ?",
            [["type" => "i", "value" => $documentId]]
        );
        /** @var InternalDocumentMySQLDTO $row */
        $row = $result->fetch_object(InternalDocumentMySQLDTO::class);
        return InternalDocumentMapper::fromMYSQLtoDomain($row);
    }

    public function deleteDocument(InternalDocument $internalDocument)
    {
        global $db;
        $db->preparedQuery(
            "delete from InternalDocument where id = ?",
            [["type" => "i", "value" => $internalDocument->id()]]
        );
        $internalDocument->deleteFile();
    }


}