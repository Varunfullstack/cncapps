<?php

namespace CNCLTD\ServiceRequestInternalNote\UseCases;

use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNote;
use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNoteRepository;

class AddServiceRequestInternalNote
{
    /**
     * @var ServiceRequestInternalNoteRepository
     */
    private $internalNotePDORepository;

    public function __construct(ServiceRequestInternalNoteRepository $internalNotePDORepository)
    {

        $this->internalNotePDORepository = $internalNotePDORepository;
    }

    public function __invoke(\DBEProblem $serviceRequest, \DBEUser $currentUser, $content)
    {
        $dateTime  = new \DateTimeImmutable();
        $userId    = $currentUser->getValue(\DBEUser::userID);
        $newNoteId = $this->internalNotePDORepository->newIdentity();
        $newNote   = ServiceRequestInternalNote::create(
            $newNoteId,
            $serviceRequest->getValue(\DBEProblem::problemID),
            $userId,
            $dateTime,
            $userId,
            $dateTime,
            $content
        );
        $this->internalNotePDORepository->addServiceRequestInternalNote($newNote);
    }
}