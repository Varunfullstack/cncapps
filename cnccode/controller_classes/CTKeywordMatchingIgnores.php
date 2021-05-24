<?php
global $cfg;

use CNCLTD\Data\DBConnect;
use CNCLTD\Exceptions\APIException;
use CNCLTD\Exceptions\JsonHttpException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTKeywordMatchingIgnores extends CTCNC
{
    const CONST_KEYWORDS_IGNORE = "keywordsIgnore";

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg,
            false
        );
        $action = @$_REQUEST['action'];
        if (!self::isSdManager() && !self::isSRQueueManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(219);
    }


    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case self::CONST_KEYWORDS_IGNORE:
                switch ($this->requestMethod) {
                    case "GET":
                        echo json_encode($this->getAllWords(), JSON_NUMERIC_CHECK);
                        break;
                    case "POST":
                        echo json_encode($this->addWord(), JSON_NUMERIC_CHECK);
                        break;
                    case "PUT":
                        echo json_encode($this->updateWord(), JSON_NUMERIC_CHECK);
                        break;
                    case "DELETE":
                        echo json_encode($this->deleteWord(), JSON_NUMERIC_CHECK);
                        break;

                }
                exit;
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {
        $this->setPageTitle('Keyword Matching Ignores');
        $this->setTemplateFiles(
            array('KeywordMatchingIgnores' => 'KeywordMatchingIgnores.rct')
        );
        $this->loadReactScript('KeywordMatchingIgnoresComponent.js');
        $this->loadReactCSS('KeywordMatchingIgnoresComponent.css');
        $this->template->parse(
            'CONTENTS',
            'KeywordMatchingIgnores',
            true
        );
        $this->parsePage();
    }

    function humanize($string)
    {
        return str_replace(
            '_',
            ' ',
            $string
        );
    }

    /**
     * @return array
     */
    function getAllWords()
    {
        return DBConnect::fetchAll("select id,word from keywordMatchingIgnores order by word");
    }

    /**
     * @return boolean
     * @throws JsonHttpException
     */
    function addWord()
    {
        $body = $this->getBody();
        if ($body->word) {
            if ($this->isDublicate($body->word)) return $this->fail(APIException::conflict, "Word exist.");
            return $this->success(
                DBConnect::execute("insert into keywordMatchingIgnores(word) values(:word)", ["word" => $body->word])
            );
        } else
            return $this->fail(APIException::badRequest, "Missed data");
    }

    /**
     * @return boolean
     */
    function updateWord()
    {
        $body = $this->getBody();
        if ($body->word && $body->id) {
            if ($this->isDublicate($body->word, $body->id)) return $this->fail(APIException::conflict, "Word exist.");
            return $this->success(
                DBConnect::execute(
                    "update keywordMatchingIgnores set word=:word where id=:id",
                    ["word" => $body->word, "id" => $body->id]
                )
            );
        } else
            return false;
    }

    /**
     * @return boolean
     */
    function deleteWord()
    {
        $id = @$_REQUEST["id"];
        if ($id) return DBConnect::execute("delete from  keywordMatchingIgnores where id=:id", ["id" => $id]); else
            return false;
    }

    /**
     * @return boolean
     */
    function isDublicate($word, $id = null)
    {
        if ($id == null) {
            return DBConnect::fetchOne(
                    "select count(*) total from keywordMatchingIgnores where word=:word",
                    ["word" => $word]
                )["total"] > 0;
        } else
            return DBConnect::fetchOne(
                    "select count(*) total from keywordMatchingIgnores where word=:word and id<>:id",
                    ["word" => $word, "id" => $id]
                )["total"] > 0;
    }
}
