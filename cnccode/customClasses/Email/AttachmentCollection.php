<?php


namespace CNCLTD\Email;


use Iterator;

class AttachmentCollection implements Iterator
{
    /**
     * @var AttachmentItem[]
     */
    private $attachments = [];
    private $position = 0;

    public function add($content, $contentType, $name, $isFile)
    {
        $attachment = new AttachmentItem($content, $contentType, $name, $isFile);
        $this->attachments[] = $attachment;
    }

    /**
     * @return AttachmentItem
     */
    public function current()
    {
        return $this->attachments[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->attachments[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }
}