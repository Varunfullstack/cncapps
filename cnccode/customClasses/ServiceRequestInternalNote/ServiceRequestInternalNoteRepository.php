<?php

namespace CNCLTD\ServiceRequestInternalNote;
interface ServiceRequestInternalNoteRepository
{
    /**
     * @param $serviceRequestId
     * @return ServiceRequestInternalNote[]
     */
    public function getServiceRequestInternalNotesForSR($serviceRequestId): array;

    public function addServiceRequestInternalNote(ServiceRequestInternalNote $serviceRequestInternalNote);

    public function newIdentity();

    public function save(ServiceRequestInternalNote $serviceRequestInternalNote);
}