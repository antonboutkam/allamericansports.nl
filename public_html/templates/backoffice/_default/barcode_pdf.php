<?php
require_once('pdf/barcode128.php');

/**
 * Bill_pdf
 * 
 * @package bleuturban
 * @author Oriana Martinelli
 * @copyright 2010
 * @version $Id$
 * @access public
 */
class Barcode_pdf {
    function run($params){
        $paper = BarcodeDao::getLabelSettings($params['barcodepaper']);

        $pdf=new PDF_Code128();
        $pdf->AddPage();
        $pdf->SetFont('Arial','',4);
        $pdf->SetMargins(0,0,0);
        $product = ProductDao::getById($params['id']);
        $label   = $product['article_number'].'::'.$product['id'].'::'.substr($product['article_name'],0,20);
        //A set
        $code   =  'ARTN'.$params['id'];
        $row = 1;
        
        $rowDist = 31;
        for($x=0;($x<297&&$row<=$paper['rows']);$x=$x+$paper['row_dist']){
            
            if($params['barcode'][$row][1]){
                $pdf->Code128($paper['margin_left'],$x+$paper['margin_top'],$code,$paper['barcode_width'],$paper['barcode_height']);
                $pdf->SetXY($paper['margin_left'],$paper['barcode_height']+$x+$paper['margin_top']);
                $pdf->Write(3,$label);
            }            
            for($colCount=1;$colCount<$paper['cols'];$colCount++)
                if($params['barcode'][$row][$colCount+1]){
                    $pdf->Code128($paper['margin_left']+($colCount*$paper['col_width']),$x+$paper['margin_top'],$code,$paper['barcode_width'],$paper['barcode_height']);
                    $pdf->SetXY($paper['margin_left']+($colCount*$paper['col_width']),$paper['barcode_height']+$x+$paper['margin_top']);
                    $pdf->Write(3,$label);
                }

            $row = $row+1;
        }

        $pdf->Output();
        exit();                        
    }  
}