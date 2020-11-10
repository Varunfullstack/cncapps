<?php


namespace CNCLTD\Email;


class AttachmentItem
{
    private $content;
    private $contentType;
    private $name;
    private $isFile;

    /**
     * AttachmentItem constructor.
     * @param $content
     * @param $contentType
     * @param $name
     * @param $isFile
     */
    public function __construct($content, $contentType, $name, $isFile)
    {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->name = $name;
        $this->isFile = $isFile;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getIsFile()
    {
        return $this->isFile;
    }

}