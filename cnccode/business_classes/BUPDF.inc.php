<?php /**
 * PDF business class
 * Uses FPDF class v1.51 by http://www.fpdf.org/
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once(PDF_DIR . '/fpdf_protection.php');

define(
    'FPDF_FONTPATH',
    PDF_DIR . '/font/'
);
define(
    'BUPDF_A4_WIDTH',
    595
);
define(
    'BUPDF_A4_LENGTH',
    842
);
define(
    'BUPDF_FONT_TIMES_NEW_ROMAN',
    'Times New Roman'
);
define(
    'BUPDF_FONT_ARIAL',
    'Arial'
);

class BUPDF extends BaseObject
{
    public $pdf;                    // fpdf object
    var $currentXPos = '';    // Position across page from LHS
    var $currentYPos = '';    // Position up page from bottom
    var $margin = '';                // Non-print area
    var $pageNo = 0;
    var $fontFamily = '';
    var $fontStyle = '';
    var $fontSize = '';
    var $filename = '';

    function __construct(&$owner,
                         $filename,
                         $author,
                         $title,
                         $creator,
                         $subject,
                         $encrypted = true
    )
    {
        BaseObject::__construct($owner);


        if ($filename == '') {
            $this->raiseError('No filename passed');
            return FALSE;
        }
        if ($author == '') {
            $this->raiseError('No author passed');
            return FALSE;
        }
        if ($title == '') {
            $this->raiseError('No title passed');
            return FALSE;
        }
        if ($creator == '') {
            $this->raiseError('No creator passed');
            return FALSE;
        }
        if ($subject == '') {
            $this->raiseError('No subject passed');
            return FALSE;
        }
        $this->pdf = new FPDF_Protection();
        if ($encrypted) {
            $this->pdf->SetProtection(
                ['print', 'annot-forms'],
                '',
                '[V.^DW_uA^2~vER$'
            );
        }
        $this->setFilename($filename);        // Disk file to be created

        $this->setInfo(
            "Author",
            $author
        );
        $this->setInfo(
            "Title",
            $title
        );
        $this->setInfo(
            "Creator",
            $creator
        );
        $this->setInfo(
            "Subject",
            $subject
        );
        $this->setFontFamily(BUPDF_FONT_ARIAL);
        $this->setFontSize(10);
    }

    function setInfo($element,
                     $value
    )
    {
        $this->setMethodName('setInfo');
        switch ($element) {
            case 'Subject':
                $this->pdf->SetSubject($value);
                break;
            case 'Title':
                $this->pdf->SetTitle($value);
                break;
            case 'Author':
                $this->pdf->SetAuthor($value);
                break;
            case 'Creator':
                $this->pdf->SetCreator($value);
                break;
            default:
                $this->raiseError('Info element ' . $element . ' not supported.');
                break;
        }
        return TRUE;
    }

    /**
     * @return bool
     */
    function startPage()
    {
        $this->pageNo++;
        $this->pdf->AddPage();
        $this->pdf->Image(
            $GLOBALS['cfg']['cncwatermark_path'],
            0,
            0
        );
        $this->setFont();
        return TRUE;
    }

    function setFont()
    {
        $this->setMethodName('setFont');
        $this->pdf->SetFont(
            $this->getFontFamily(),
            $this->getFontStyle(),
            $this->getFontSize()
        );
        return TRUE;
    }

    function getFontFamily()
    {
        return $this->fontFamily;
    }

    function setFontFamily($family)
    {
        $this->fontFamily = $family;
    }

    function getFontStyle()
    {
        return $this->fontStyle;
    }

    function setFontStyle($style)
    {
        $this->fontStyle = $style;
    }

    function getFontSize()
    {
        return $this->fontSize;
    }

    function setFontSize($size)
    {
        $this->fontSize = $size;
    }

    /**
     * NOTE: This is flakey at present cause you cant combine font styles e.g. Bold and Italic
     * needs changing to add B to a concatonated string instead
     */
    function setBoldOn()
    {
        $this->setFontStyle('B');
        return TRUE;
    }

    function setBoldItalicOn()
    {
        $this->setFontStyle('BI');
        return TRUE;
    }

    /**
     * NOTE: This is flakey at present cause you cant combine font styles e.g. Bold and Italic
     * needs changing to remove B from a concatonated string instead
     */
    function setBoldOff()
    {
        $this->setFontStyle('');
        return TRUE;
    }

    function CR()
    {
        $this->pdf->ln($this->getFontSize() / 2);
        return TRUE;
    }

    function moveYTo($position)
    {
        $this->currentYPos = $position;
        $this->pdf->SetY($position);
        return TRUE;
    }

    function getYPos()
    {
        return $this->pdf->GetY();
    }

    /***
     * @param $position
     * @param $string
     * @return bool
     */
    function printStringAt($position,
                           $string
    )
    {
        $this->moveXTo($position);
        $this->printString($string);
        return TRUE;
    }

    function moveXTo($position)
    {
        $this->currentXPos = $position;
        $this->pdf->SetX($position);
        return TRUE;
    }

    /**
     * @param $string
     * @param null $link
     * @return bool
     */
    function printString($string, $link = null)
    {
        if ($this->detectUTF8($string)) {
            $string = utf8_decode($string);
        }
        $previousFontStyle = $this->getFontStyle();
        if ($link) {
            $this->pdf->SetTextColor(0, 0, 255);
            $this->setFontStyle('U');
            $this->setFont();
        }

        $this->pdf->Write(
            ($this->getFontSize() / 2),
            $string,
            $link
        );

        if ($link) {
            $this->pdf->SetTextColor(0);
            $this->setFontStyle($previousFontStyle);
            $this->setFont();
        }

        return TRUE;
    }

    /*
        function divLine(){
            $this->CR();
    //		pdf_moveto($this->pdf, $this->currentXPos, $this->currentYPos);
    //		$this->pdf->SetXY($this->currentXPos, $this->currentYPos)
    //		pdf_lineto($this->pdf, ($this->pageWidth - $this->margin), $this->currentYPos);
    //		pdf_stroke($this->pdf);
            $this->pfd->Line($this->currentXPos, $this->currentYPos, ($this->pageWidth - $this->margin), $this->currentYPos);
            $this->CR();
            return TRUE;
        }
    */

    function detectUTF8($string)
    {
        return preg_match(
            '%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs',
            $string
        );
    }

    /**
     * @param $position
     * @param $string
     * @return bool
     */
    function printStringRJAt($position,
                             $string
    )
    {
        $textWidth = $this->pdf->GetStringWidth($string);
        $this->moveXTo($position - $textWidth);
        $this->printString($string);
        return TRUE;
    }

    function endPage()
    {
    }

    // Right Justified

    /**
     * @return string
     */
    function getData()
    {
        return $this->pdf->Output(
            'S',
            $this->getFilename(),
            true
        );
    }

    function getFilename()
    {
        return $this->filename;
    }

    function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     */
    function close()
    {
        $this->pdf->Output(
            'F',
            $this->getFilename(),
            true
        );
    }

    function placeImageAt($filename,
                          $imageType,
                          $position,
                          $width = 0
    )
    {
        $this->setMethodName('placeImageAt');
        if ($filename == '') {
            $this->raiseError('No filename passed');
        }
        if ($imageType == '') {
            $this->raiseError('No imageType passed');
        }
        $this->pdf->Image(
            $filename,
            $position,
            $this->pdf->GetY(),
            $width
        );
    }

    function box($x,
                 $y,
                 $width,
                 $height
    )
    {
        $this->pdf->Rect(
            $x,
            $y,
            $width,
            $height
        );
    }

    public function footerCallback(Closure $param)
    {
        $this->pdf->setFooterCallback($param);
    }

}
