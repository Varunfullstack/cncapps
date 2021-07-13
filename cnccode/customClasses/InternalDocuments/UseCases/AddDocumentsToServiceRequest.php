<?php

namespace CNCLTD\InternalDocuments\UseCases;

use CNCLTD\InternalDocuments\Base64FileDTO;
use CNCLTD\InternalDocuments\Entity\InternalDocument;
use CNCLTD\InternalDocuments\InternalDocumentRepository;
use DBEProblem;
use Exception;

class AddDocumentsToServiceRequest
{
    /**
     * @var InternalDocumentRepository
     */
    private $internalDocumentRepository;
    /**
     * @var DBEProblem
     */
    private $problem;

    public function __construct(InternalDocumentRepository $internalDocumentRepository,
                                DBEProblem $problem
    )
    {

        $this->internalDocumentRepository = $internalDocumentRepository;
        $this->problem                          = $problem;
    }

    /**
     * @param $serviceRequestId
     * @param Base64FileDTO[] $files
     * @throws Exception
     */
    public function __invoke($serviceRequestId, $files)
    {
        if (!$serviceRequestId) {
            throw new Exception('Service request id required');
        }
        if (!$this->problem->getRow($serviceRequestId)) {
            throw new Exception('Service request does not exist');
        }
        foreach ($files as $file) {
            $id                     = $this->internalDocumentRepository->getNextId();

            $internalDocument = InternalDocument::createFromBase64URIFileObject(
                $file,
                $serviceRequestId,
                $id
            );
            $this->internalDocumentRepository->save($internalDocument);
        }

    }
}