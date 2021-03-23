<?php

namespace CNCLTD\ServiceRequestInternalNote\infra;

use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNote;
use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNotePDODTO;
use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNotePDOMapper;
use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNoteRepository;
use mysqli;
use PDO;

class ServiceRequestInternalNotePDORepository implements ServiceRequestInternalNoteRepository
{
    /**
     * @var PDO
     */
    private $connection;
    /** @var mysqli */
    private $mysqliConn;

    /**
     * ServiceRequestInternalNotePDORepository constructor.
     */
    public function __construct()
    {
        $this->connection = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD
        );
        $this->mysqliConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $this->mysqliConn->set_charset('utf8mb4');
    }

    /**
     * @param $serviceRequestId
     * @return ServiceRequestInternalNote[]
     */
    public function getServiceRequestInternalNotesForSR($serviceRequestId): array
    {
        $statement = $this->connection->prepare('Select * from ServiceRequestInternalNote where serviceRequestId = ?');
        $statement->execute([$serviceRequestId]);
        $dbDTOs = $statement->fetchAll(PDO::FETCH_CLASS, ServiceRequestInternalNotePDODTO::class);
        return array_map(
            function (ServiceRequestInternalNotePDODTO $item) {
                return ServiceRequestInternalNotePDOMapper::toDomain($item);
            },
            $dbDTOs
        );
    }

    public function addServiceRequestInternalNote(ServiceRequestInternalNote $serviceRequestInternalNote)
    {
        $statement = $this->connection->prepare(
            "insert into ServiceRequestInternalNote(id,serviceRequestId, createdBy, createdAt, updatedBy, updatedAt,content) values (?,?,?,?,?,?,?) "
        );
        if (!$statement->execute(
            [
                $serviceRequestInternalNote->getId(),
                $serviceRequestInternalNote->getServiceRequestId(),
                $serviceRequestInternalNote->getCreatedBy(),
                $serviceRequestInternalNote->getCreatedAt()->format(DATE_MYSQL_DATETIME),
                $serviceRequestInternalNote->getUpdatedBy(),
                $serviceRequestInternalNote->getUpdatedAt()->format(DATE_MYSQL_DATETIME),
                $serviceRequestInternalNote->getContent()
            ]
        )) {
            throw new \Exception('Failed to save serviceRequestInternalNote:' . $statement->errorInfo()[2]);
        }
    }

    public function newIdentity(): int
    {
        /** @var \dbSweetcode */ global $db;
        return $db->nextid('serviceRequestInternalNote');
    }

    public function save(ServiceRequestInternalNote $serviceRequestInternalNote)
    {
        $statement = $this->connection->prepare(
            "update ServiceRequestInternalNote set content = ?, updatedBy = ?, updatedAt =? where id = ?; "
        );
        if (!$statement->execute(
            [
                $serviceRequestInternalNote->getContent(),
                $serviceRequestInternalNote->getUpdatedBy(),
                $serviceRequestInternalNote->getUpdatedAt()->format(DATE_MYSQL_DATETIME),
                $serviceRequestInternalNote->getId()
            ]
        )) {
            throw new \Exception('Failed to save serviceRequestInternalNote:' . $statement->errorInfo()[2]);
        }
    }
}