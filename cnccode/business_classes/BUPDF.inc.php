<?php /**
 * PDF business class
 * Uses FPDF class v1.51 by http://www.fpdf.org/
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once(PDF_DIR . '/fpdf.php');                // Free PDF from http://www.fpdf.org/

define('FPDF_FONTPATH', PDF_DIR . '/font/');    // Used by fpdf class
define('BUPDF_A4_WIDTH', 595);
define('BUPDF_A4_LENGTH', 842);
define('BUPDF_FONT_TIMES_NEW_ROMAN', 'Times New Roman');
define('BUPDF_FONT_ARIAL', 'Arial');

class BUPDF extends BaseObject
{
    public $pdf;                    // fpdf object
    var $currentXPos = '';    // Position accross page from LHS
    var $currentYPos = '';    // Position up page from bottom
    var $margin = '';                // Non-print area
    var $pageWidth = '';        // Position up page from bottom
    var $pageLength = '';        // Position up page from bottom
    var $pageNo = 0;
    var $fontFamily = '';
    var $fontStyle = '';
    var $fontSize = '';
    var $filename = '';

    function __construct(&$owner, $filename, $author, $title, $creator, $subject, $paperSize)
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
        if ($paperSize == '') {
            $this->raiseError('No paper size passed');
            return FALSE;
        }
        if ($paperSize != 'A4') {
            $this->raiseError('Only A4 paper size supported at present');
            return FALSE;
        }
//		$this->pdf = pdf_new();
        $this->pdf = new FPDF();

//        $this->pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf');
//        $this->pdf->SetFont('DejaVu', '', 14);
        $this->setFilename($filename);        // Disk file to be created
//        $this->open();
        $this->setInfo("Author", $author);
        $this->setInfo("Title", $title);
        $this->setInfo("Creator", $creator);
        $this->setInfo("Subject", $subject);
        $this->setFontFamily(BUPDF_FONT_ARIAL);
        $this->setFontSize(10);
        $this->setPaperSize($paperSize);
    }


    function setFilename($filename)
    {
        $this->filename = $filename;
    }

    function getFilename()
    {
        return $this->filename;
    }

    function setFontFamily($family)
    {
        $this->fontFamily = $family;
    }

    function getFontFamily()
    {
        return $this->fontFamily;
    }

    function setFontSize($size)
    {
        $this->fontSize = $size;
    }

    function getFontSize()
    {
        return $this->fontSize;
    }

    function setFontStyle($style)
    {
        $this->fontStyle = $style;
    }

    function getFontStyle()
    {
        return $this->fontStyle;
    }

    function setFont()
    {
        $this->setMethodName('setFont');
        $this->pdf->SetFont($this->getFontFamily(), $this->getFontStyle(), $this->getFontSize());
        return TRUE;
    }

    function open()
    {
        $this->pdf->open();
        return TRUE;
    }

    function setInfo($element, $value)
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

    function setPaperSize($paperSize)
    {
        if ($paperSize != 'A4') {
            $this->raiseError('Only A4 paper size supported at present');
            return FALSE;
        } else {
            $this->margin = 50;
            $this->pageWidth = BUPDF_A4_WIDTH;
            $this->pageLength = BUPDF_A4_LENGTH;
        }
    }

    function startPage()
    {
        $this->pageNo++;
        $this->pdf->AddPage();
        $this->setFont();
        return TRUE;
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

    function setBoldUnderlineOn()
    {
        $this->setFontStyle('BU');
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

    /*
        function moveDown($lineCount){
            $this->currentYPos -= ($lineCount * $this->getFontSize());
    //		pdf_moveto($this->pdf, $this->currentXPos, $this->currentYPos);
            $this->pdf->SetY($this->currentYPos);
            return TRUE;
        }
    */
    function moveXTo($position)
    {
        $this->currentXPos = $position;
        $this->pdf->SetX($position);
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
    function printString($string)
    {
        if ($this->detectUTF8($string)) {
            $string = utf8_decode($string);
        }
        $this->pdf->Write(($this->getFontSize() / 2), $string); // Fix bug where auto line break adds blank line
        return TRUE;
    }

    function detectUTF8($string)
    {
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
    }

    function printStringAt($position, $string)
    {
        $this->moveXTo($position);
        $this->printString($string);
        return TRUE;
    }

    // Right Justified
    function printStringRJAt($position, $string)
    {
        $textWidth = $this->pdf->GetStringWidth($string);
        $this->moveXTo($position - $textWidth);
        $this->printString($string);
        return TRUE;
    }

    function endPage()
    {
    }

    function close()
    {
        $this->pdf->Output('F', $this->getFilename(), true);
    }

    function placeImageAt($filename, $imageType, $position, $width = 0)
    {
        $this->setMethodName('placeImageAt');
        if ($filename == '') {
            $this->raiseError('No filename passed');
        }
        if ($imageType == '') {
            $this->raiseError('No imageType passed');
        }
        $this->pdf->Image($filename, $position, $this->pdf->GetY(), $width);
    }

    function box($x, $y, $width, $height)
    {
        $this->pdf->Rect($x, $y, $width, $height);
    }
}// End of class
?>