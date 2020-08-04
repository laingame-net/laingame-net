<?php
namespace Lain;

use Lain\Model;
use Lain\View;

class SiteController
{

    public function __construct()
    {
        $this->model = new Model();
        $this->view = new View("../templates");
    }

    public function exportActionGet($id = "", $lang = "", $event = "")
    {
        $this->model->getBlockPrepare();
        $ids = explode("-", $id);
        $rid = range($ids[0], $ids[1]);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addTextBreak(1);
        #$section->addText('Ids from '.$ids[0].' to '.$ids[1].' lang '.$lang, $header);

        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = array('borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER, 'cellSpacing' => 50);
        $fancyTableFirstRowStyle = array('borderBottomSize' => 18, 'borderBottomColor' => '0000FF', 'bgColor' => '66BBFF');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('bold' => true);
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);

        foreach ($rid as $id) {
            $block = $this->model->getBlock($id, $lang);
            if (!$block['subtitles']) {
                continue;
            }

            $table = $section->addTable($fancyTableStyleName);

            $table->addRow(900);
            $table->addCell(1500, $fancyTableCellStyle)->addText($block['name'] . " (" . $block['id'] . ")", $fancyTableFontStyle);
            $infoCell = $table->addCell(7500, $fancyTableCellStyle);
            $infoCell->addText("Location: " . $block['level'] . " site " . $block['site'] . " , level " . $block['level'] . ", #" . $block['page_pos'], $fancyTableFontStyle);
            $infoCell->addText("SW: " . $block['sw'] . ", q: " . $block['q'] . ", state: " . $block['state'] . ", type: " . $block['type'], $fancyTableFontStyle);
            $infoCell->addText("Info: " . $block['info1'] . ", " . $block['info2'] . ", " . $block['info3'] . ", " . $block['info4'], $fancyTableFontStyle);
            $infoCell->addText("Tags: " . $block['tag1_value'] . ", " . $block['tag2_value'] . ", " . $block['tag3_value'], $fancyTableFontStyle);

            if (!empty($block['need_name'])) {
                $infoCell->addText("Depends on: " . $block['need_name'], $fancyTableFontStyle);
            }

            if (count($block['reveals']) > 0) {
                $infoCell->addText("Reveals: " . implode(", ", $block['reveals']), $fancyTableFontStyle);
            }

            if ($block['type'] == 2) {
                $imgTable = $table->addRow()->addCell()->addTable();
                //$imgTable = $section->addTable();
                //$textrun = $section->addTextRun();
                //$wrapping_style = array('wrappingStyle' => 'square', 'positioning' => 'absolute', 'posHorizontalRel' => 'margin', 'posVerticalRel' => 'line', 'width' => 80, 'height' => 30);
                $wrapping_style = array('wrappingStyle' => 'inline', 'height' => 100);
                $imgTable->addRow();
                for ($i = 1; $i <= 3; $i++) {
                    if (isset($block['img' . $i])) {
                        $imgTable->addCell(3000)->addImage(sprintf("media/img_%d_%03d.png", $block['site'], $block['img' . $i]), $wrapping_style);
                    } else {
                        $imgTable->addCell(3000)->addText("");
                    }
                }
            } elseif ($block['type'] == 3) {
                $wrapping_style = array('wrappingStyle' => 'inline', 'height' => 180);
                $table->addRow();
                $table->addCell()->addImage("media/" . $block['name'] . ".png", $wrapping_style);
            }

            foreach ($block['subtitles'] ?? [] as $line) {
                $table->addRow();
                $table->addCell(1200)->addText($line['actor']);
                $table->addCell(3900)->addText($line['text']);
                $table->addCell(3900)->addText("");
            }
            $section->addPageBreak();
        }

        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = "1.docx";
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . $fileName);
        header("Content-Transfer-Encoding: binary");
        $objWriter->save("php://output");
    }
}
