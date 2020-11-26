<?php

namespace CNCLTD\ServiceRequestDocuments\UseCases;

use CNCLTD\ServiceRequestDocuments\Base64FileDTO;
use CNCLTD\ServiceRequestDocuments\Entity\ServiceRequestDocument;
use CNCLTD\ServiceRequestDocuments\ServiceRequestDocumentRepository;

class AddDocumentsToServiceRequest
{
    /**
     * @var ServiceRequestDocumentRepository
     */
    private $serviceRequestDocumentRepository;
    /**
     * @var \DBEProblem
     */
    private $problem;

    public function __construct(ServiceRequestDocumentRepository $serviceRequestDocumentRepository,
                                \DBEProblem $problem
    )
    {

        $this->serviceRequestDocumentRepository = $serviceRequestDocumentRepository;
        $this->problem                          = $problem;
    }

    /**
     * @param $serviceRequestId
     * @param Base64FileDTO[] $files
     * @throws \Exception
     */
    public function __invoke($serviceRequestId, $files)
    {
        if (!$serviceRequestId) {
            throw new \Exception('Service request id required');
        }
        if (!$this->problem->getRow($serviceRequestId)) {
            throw new \Exception('Service request does not exist');
        }
        foreach ($files as $file) {
            $id                     = $this->serviceRequestDocumentRepository->getNextId();

            $serviceRequestDocument = ServiceRequestDocument::createFromBase64URIFileObject(
                $file,
                $serviceRequestId,
                $id
            );
            $this->serviceRequestDocumentRepository->save($serviceRequestDocument);
        }

    }
}