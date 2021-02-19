<?php

namespace CNCLTD\ServiceRequestInternalNote;

use PDO;

class ServiceRequestInternalNotePDORepository
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * ServiceRequestInternalNotePDORepository constructor.
     */
    public function __construct()
    {
        $this->connection = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD
        );
    }

    public function getServiceRequestInternalNotesForSR($serviceRequestId)
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
}