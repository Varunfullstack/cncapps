<?php

namespace CNCLTD\ServiceRequestInternalNote\UseCases;

use CNCLTD\ServiceRequestInternalNote\ServiceRequestInternalNoteRepository;
use Exception;

class ChangeServiceRequestInternalNote
{
    /**
     * @var ServiceRequestInternalNoteRepository
     */
    private $serviceRequestInternalNoteRepository;

    /**
     * changeServiceRequestInternalNote constructor.
     * @param ServiceRequestInternalNoteRepository $serviceRequestInternalNoteRepository
     */
    public function __construct(ServiceRequestInternalNoteRepository $serviceRequestInternalNoteRepository)
    {
        $this->serviceRequestInternalNoteRepository = $serviceRequestInternalNoteRepository;
    }

    public function __invoke($serviceRequestId, $content, \DBEUser $currentUser)
    {
        $notes = $this->serviceRequestInternalNoteRepository->getServiceRequestInternalNotesForSR($serviceRequestId);
        if (count($notes) > 1) {
            throw new Exception("You cannot edit notes when there is more than 1");
        }
        $note = $notes[0];
        $note->updateContent($content, $currentUser);
        $this->serviceRequestInternalNoteRepository->save($note);
    }
}