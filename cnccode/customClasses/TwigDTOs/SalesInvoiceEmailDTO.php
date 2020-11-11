<?php


namespace CNCLTD\TwigDTOs;


use CNCLTD\Email\AttachmentCollection;

class SalesInvoiceEmailDTO
{
    private $emails = [];
    private $invoices = [];
    private $totalAmount = 0;
    private $invoiceAttachments;

    /**
     * SalesInvoiceEmailDTO constructor.
     */
    public function __construct()
    {
        $this->invoiceAttachments = new AttachmentCollection();
    }

    public function addPDFInvoiceAttachment($filePath, $fileName)
    {
        $this->invoiceAttachments->add($filePath, 'Application/pdf', $fileName, true);
    }

    public function addEmail($email)
    {
        $this->emails[] = $email;
    }

    public function addInvoice($id, $amount)
    {
        $invoice = new InvoiceItemDTO($id, $amount);
        $this->totalAmount += $amount;
        $this->invoices[] = $invoice;
    }

    /**
     * @return InvoiceItemDTO[]
     */
    public function getInvoices()
    {
        usort(
            $this->invoices,
            function (InvoiceItemDTO $a, InvoiceItemDTO $b) {
                return $b->getId() - $a->getId();
            }
        );
        return $this->invoices;
    }

    /**
     * @return int
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @return string
     */
    public function getEmails(): string
    {
        return implode(',', $this->emails);
    }

    /**
     * @return AttachmentCollection
     */
    public function getAttachments()
    {
        return $this->invoiceAttachments;
    }


}