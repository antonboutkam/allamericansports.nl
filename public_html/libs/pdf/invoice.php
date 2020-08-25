<?php
require_once('fpdf.php');
require_once('barcode128.php');
define('EURO', chr(128).' ' );
define('EURO_VAL', 6.55957 );

// Xavier Nicolay 2004
// Version 1.02

//////////////////////////////////////
// Public functions                 //
//////////////////////////////////////
//  function sizeOfText( $texte, $larg )
//  function addSociete( $nom, $adresse )
//  function fact_dev( $libelle, $num )
//  function addDevis( $numdev )
//  function addFacture( $numfact )
//  function addDate( $date )
//  function addClient( $ref )
//  function addPageNumber( $page )
//  function addClientAdresse( $adresse )
//  function addReglement( $mode )
//  function addEcheance( $date )
//  function addNumTVA($tva)
//  function addReference($ref)
//  function addCols( $tab )
//  function addLineFormat( $tab )
//  function lineVert( $tab )
//  function addLine( $ligne, $tab )
//  function addRemarque($remarque)
//  function addCadreTVAs()
//  function addCadreEurosFrancs()
//  function addTVAs( $params, $tab_tva, $invoice )
//  function temporaire( $texte )

class Invoice extends PDF_Code128
{
// private variables
var $colonnes;
var $format;
var $angle=0;
static $translation;

// private functions
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{
    $k = $this->k;
    $hp = $this->h;
    if($style=='F')
        $op='f';
    elseif($style=='FD' || $style=='DF')
        $op='B';
    else
        $op='S';
    $MyArc = 4/3 * (sqrt(2) - 1);
    $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
    $xc = $x+$w-$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

    $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
    $xc = $x+$w-$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
    $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
    $xc = $x+$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
    $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
    $xc = $x+$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
    $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
    $this->_out($op);
}

function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
    $h = $this->h;
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
                        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
}

function Rotate($angle, $x=-1, $y=-1)
{
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}

function _endpage()
{
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}

// public functions
function sizeOfText( $texte, $largeur )
{
    $index    = 0;
    $nb_lines = 0;
    $loop     = TRUE;
    while ( $loop )
    {
        $pos = strpos($texte, "\n");
        if (!$pos)
        {
            $loop  = FALSE;
            $ligne = $texte;
        }
        else
        {
            $ligne  = substr( $texte, $index, $pos);
            $texte = substr( $texte, $pos+1 );
        }
        $length = floor( $this->GetStringWidth( $ligne ) );
        $res = 1 + floor( $length / $largeur) ;
        $nb_lines += $res;
    }
    return $nb_lines;
}

// Company
function addSociete( $nom, $adresse,$x1=120,$y1=34)
{

    //Positionnement en bas
    
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','B',12);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( $x1, $y1 + 4 );
    $this->SetFont('Arial','',0);
    $length = $this->GetStringWidth( $adresse );
    //Coordonn�es de la soci�t�
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 4, $adresse);
}

// Client address
function addClientAdresse( $adresse,$xOverride=null,$yOverride=null )
{
    $r1     = $this->w - 410;
    if($xOverride)
        $r1 = $xOverride;
    $r2     = $r1 + 168;
    $y1     = 8;
    if($yOverride)
        $y1 = $yOverride;
    $this->SetFont( "Arial", "", 12);
    $this->SetXY( $r1, $y1);
    $this->MultiCell( 60, 4, $adresse);
}


// Company
function addSummate($text,$x1=10,$y1=62)
{
    
    //Positionnement en bas
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','BU',12);

    $length = $this->GetStringWidth( $text );
    $this->Cell( $length, 2, $text);
                
}


// Label and number of invoice/estimate
function fact_dev( $libelle, $num )
{
    $r1  = $this->w - 90;
    $r2  = $r1 + 78;
    $y1  = 6;
    $y2  = $y1 + 2;
    $mid = ($r1 + $r2 ) / 2;
    
    $texte  = $libelle . " " . $num;    
    $szfont = 12;
    $loop   = 0;
    
    while ( $loop == 0 )
    {
       $this->SetFont( "Arial", "B", $szfont );
       $sz = $this->GetStringWidth( $texte );
       if ( ($r1+$sz) > $r2 )
          $szfont --;
       else
          $loop ++;
    }

    $this->SetLineWidth(0.1);
    $this->SetFillColor(192);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
    $this->SetXY( $r1+1, $y1+2);
    $this->Cell($r2-$r1 -1,5, $texte, 0, 0, "C" );
}

// Estimate
function addDevis( $numdev )
{
    $string = sprintf("DEV%04d",$numdev);
    $this->fact_dev( "Devis", $string );
}

// Invoice
function addFacture( $numfact )
{
    $string = sprintf("FA%04d",$numfact);
    $this->fact_dev( "Facture", $string );
}

function addDate( $date )
{
    $r1  = $this->w - 71;
    $r2  = $r1 + 25;
    $y1  = 17;
    $y2  = $y1 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, self::$translation['lbl_date'], 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
}
function addDateTime( $datetime )
{
    $r1  = $this->w - 71;
    $r2  = $r1 + 30;
    $y1  = 17;
    $y2  = $y1 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, self::$translation['lbl_date'], 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$datetime, 0,0, "C");
}
function addClient( $ref )
{
    $r1  = $this->w - 46;
    $r2  = $r1 + 34;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, self::$translation['lbl_customernumber'], 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$ref, 0,0, "C");
}
function addType( $type )
{
    $r1  = $this->w - 41;
    $r2  = $r1 + 30;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, self::$translation['lbl_type'], 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$type, 0,0, "C");
}
function addPageNumber( $page )
{
    $r1  = $this->w - 90;
    $r2  = $r1 + 19;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, self::$translation['lbl_page'], 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$page, 0,0, "C");
}



// Mode of payment
function addReglement( $mode )
{
    $r1  = 10;
    $r2  = $r1 + 60;
    $y1  = 90;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,4, self::$translation['lbl_paymethod'], 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1 + 5 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$mode, 0,0, "C");
}

// Expiry date
function addEcheance( $date ,$xOverride=null)
{
    $r1  = 75;
    if($xOverride)
        $r1  = $xOverride;
    $r2  = $r1 + 50;
    $y1  = 90;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,4,self::$translation['lbl_paybefore'], 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
}

// VAT number
function addNumTVA($tva)
{
    $this->SetFont( "Arial", "B", 10);
    $r1  = $this->w - 80;
    $r2  = $r1 + 70;
    $y1  = 90;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + 16 , $y1+1 );
    $this->Cell(40, 4,self::$translation['lbl_vatnumber'], '', '', "C");
    $this->SetFont( "Arial", "", 10);
    $this->SetXY( $r1 + 16 , $y1+5 );
    $this->Cell(40, 5, $tva, '', '', "C");
}

function addReference($ref)
{
    $this->SetFont( "Arial", "", 10);
    $length = $this->GetStringWidth( self::$translation['lbl_helpedby']." : " . $ref );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = 102;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4, self::$translation['lbl_helpedby']." : " . $ref);
}

function addCols( $tab,$top=100,$end=null)
{
    if($end==null){
        if($top>=100){
            $end = -50;
        }else{
            $end = -15;
        }        
    }
    global $colonnes;
    
    $r1  = 10;
    $r2  = $this->w - ($r1 * 2) ;
    $y1  = $top;
    $y2  = $this->h + $end - $y1;
    $this->SetXY( $r1, $y1 );
    $this->Rect( $r1, $y1, $r2, $y2, "D");
    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
    $colX = $r1;
    $colonnes = $tab;
    $c = count($tab);
    
    foreach($tab as $lib => $pos)
    {
        $c=$c-1;            
        $this->SetXY( $colX, $y1+2 );
        $this->Cell( $pos, 1, $lib, 0, 0, "C");
        $colX += $pos;
        if($c>0)
            $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
}

function addLineFormat( $tab )
{
    global $format, $colonnes;
    
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        if ( isset( $tab["$lib"] ) )
            $format[ $lib ] = $tab["$lib"];
    }
}

function lineVert( $tab )
{
    global $colonnes;

    reset( $colonnes );
    $maxSize=0;
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        $texte = $tab[ $lib ];
        $longCell  = $pos -2;
        $size = $this->sizeOfText( $texte, $longCell );
        if ($size > $maxSize)
            $maxSize = $size;
    }
    return $maxSize;
}

// add a line to the invoice/estimate
/*    $ligne = array( "REFERENCE"    => $prod["ref"],
                      "DESIGNATION"  => $libelle,
                      "QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
                      "P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
                      "MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
                      "TVA"          => $prod["tva"] );
*/
function addLine( $ligne, $tab )
{
    global $colonnes, $format;

    $ordonnee     = 10;
    $maxSize      = $ligne;

    reset( $colonnes );
    foreach($colonnes as $lib => $pos)
    {
        $longCell  = $pos -2;
        $texte     = $tab[ $lib ];
        $length    = $this->GetStringWidth( $texte );

        $formText  = $format[ $lib ];
        $this->SetXY( $ordonnee, $ligne-1);
        $this->MultiCell( $longCell, 4 , $texte, 0, $formText);
        if ( $maxSize < ($this->GetY()  ) )
            $maxSize = $this->GetY() ;
        $ordonnee += $pos;
    }
    return ( $maxSize - $ligne );
}

function addRemarque($remarque)
{
    
    $this->SetFont( "Arial", "", 10);
    $length = $this->GetStringWidth( self::$translation['lbl_remarks']." : " . $remarque );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = $this->h - 45.5;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4,  self::$translation['lbl_remarks']." : " . $remarque);
}

function addKaderTotals($left = 122)
{
    $left = 160;    
    $this->SetFont( "Arial", "B", 8);
    $r1  = $left;
    $r2  = $r1 + 30;
    $y1  = $this->h - 40;
    $y2  = $y1+10;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $y1+4, $r2, $y1+4);
    #$this->Line( $r1+5,  $y1+4, $r1+5, $y2); // avant BEDRAG
    #$this->Line( $r1+27, $y1, $r1+27, $y2);  // avant BTW
    #$this->Line( $r1+43, $y1, $r1+43, $y2);  // avant TOTAAL
    #$this->Line( $r1+63, $y1, $r1+63, $y2);  // avant % TVA
    //$this->Line( $r1+75, $y1, $r1+75, $y2);  // avant PORT
    // $this->Line( $r1+91, $y1, $r1+91, $y2);  // avant TOTAUX
    $this->SetXY( $r1+8, $y1);
    #$this->Cell(10,4, self::$translation['lbl_amount']);
    #$this->SetX( $r1+29 );
        
    #$this->Cell(10,4, self::$translation['lbl_vat']);
    #$this->SetX( $r1+48 );
    $this->Cell(10,4, self::$translation['lbl_totals']);
    #$this->SetX( $r1+65 );
    #$this->Cell(12,4, "% ".self::$translation['lbl_vat']);
    #$this->SetX( $r1+78 );
}
//
//function addCadreEurosFrancs()
//{
//    $r1  = $this->w - 70;
//    $r2  = $r1 + 60;
//    $y1  = $this->h - 40;
//    $y2  = $y1+20;
//    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
//    $this->Line( $r1+20,  $y1, $r1+20, $y2); // avant EUROS
//    $this->Line( $r1+20, $y1+4, $r2, $y1+4); // Sous Euros & Francs
//    $this->Line( $r1+38,  $y1, $r1+38, $y2); // Entre Euros & Francs
//    $this->SetFont( "Arial", "B", 8);
//    $this->SetXY( $r1+22, $y1 );
//    $this->Cell(15,4, "EUROS", 0, 0, "C");
//    $this->SetFont( "Arial", "", 8);
//    $this->SetXY( $r1+42, $y1 );
//    $this->Cell(15,4, "FRANCS", 0, 0, "C");
//    $this->SetFont( "Arial", "B", 6);
//    $this->SetXY( $r1, $y1+5 );
//    $this->Cell(20,4, "TOTAL TTC", 0, 0, "C");
//    $this->SetXY( $r1, $y1+10 );
//    $this->Cell(20,4, "ACOMPTE", 0, 0, "C");
//    $this->SetXY( $r1, $y1+15 );
//    $this->Cell(20,4, "NET A PAYER", 0, 0, "C");
//}

// remplit les cadres TVA / Totaux et la remarque
// params  = array( "RemiseGlobale" => [0|1],
//                      "remise_tva"     => [1|2...],  // {la remise s'applique sur ce code TVA}
//                      "remise"         => value,     // {montant de la remise}
//                      "remise_percent" => percent,   // {pourcentage de remise sur ce montant de TVA}
//                  "FraisPort"     => [0|1],
//                      "portTTC"        => value,     // montant des frais de ports TTC
//                                                     // par defaut la TVA = 19.6 %
//                      "portHT"         => value,     // montant des frais de ports HT
//                      "portTVA"        => tva_value, // valeur de la TVA a appliquer sur le montant HT
//                  "AccompteExige" => [0|1],
//                      "accompte"         => value    // montant de l'acompte (TTC)
//                      "accompte_percent" => percent  // pourcentage d'acompte (TTC)
//                  "Remarque" => "texte"              // texte
// tab_tva = array( "1"       => 19.6,
//                  "2"       => 5.5, ... );
// invoice = array( "px_unit" => value,
//                  "qte"     => qte,
//                  "tva"     => code_tva );
function addTotals($bedrag,$btwperc,$x=25,$y=261){
        $this->SetFont('Arial','',8);
        $btw = ($bedrag/100)*$btwperc;
        $totaal = $bedrag+$btw;
        
        // BEDRAG
        $this->SetXY($x, $y);
        $this->Cell( 5,4, EURO.number_format(sprintf("%0.2F",$bedrag),2,",","."));
        
        /*        
        // BTW
        $this->SetXY($x+8, $y);
        $this->Cell( 19,4, sprintf("%0.2F",$btw),'', '' ,'R');        
        
        // TOTAAL
        $this->SetXY($x+28, $y);
        $this->Cell( 19,4, sprintf("%0.2F",$totaal),'', '' ,'R');

        // % BTW
        $this->SetXY($x+55, $y);
        $this->Cell( 19,4, $btwperc.'%');
        */        
//    reset ($px);
//    natsort( $px );
//    while ( list($code_tva, $articleHT) = each( $px ) )
//    {
//        $tva = $tab_tva[$code_tva];
//        $this->SetXY(17, $y);
//        $this->Cell( 19,4, sprintf("%0.2F", $articleHT),'', '','R' );
//        if ( $params["RemiseGlobale"]==1 )
//        {
//            if ( $params["remise_tva"] == $code_tva )
//            {
//                $this->SetXY( 37.5, $y );
//                if ($params["remise"] > 0 )
//                {
//                    if ( is_int( $params["remise"] ) )
//                        $l_remise = $param["remise"];
//                    else
//                        $l_remise = sprintf ("%0.2F", $params["remise"]);
//                    $this->Cell( 14.5,4, $l_remise, '', '', 'R' );
//                    $articleHT -= $params["remise"];
//                }
//                else if ( $params["remise_percent"] > 0 )
//                {
//                    $rp = $params["remise_percent"];
//                    if ( $rp > 1 )
//                        $rp /= 100;
//                    $rabais = $articleHT * $rp;
//                    $articleHT -= $rabais;
//                    if ( is_int($rabais) )
//                        $l_remise = $rabais;
//                    else
//                        $l_remise = sprintf ("%0.2F", $rabais);
//                    $this->Cell( 14.5,4, $l_remise, '', '', 'R' );
//                }
//                else
//                    $this->Cell( 14.5,4, "ErrorRem", '', '', 'R' );
//            }
//        }
//        $totalHT += $articleHT;
//        $totalTTC += $articleHT * ( 1 + $tva/100 );
//        $tmp_tva = $articleHT * $tva/100;
//        $a_tva[ $code_tva ] = $tmp_tva;
//        $totalTVA += $tmp_tva;

//        $y+=4;
//    }
//
//    if ( $params["FraisPort"] == 1 )
//    {
//        if ( $params["portTTC"] > 0 )
//        {
//            $pTTC = sprintf("%0.2F", $params["portTTC"]);
//            $pHT  = sprintf("%0.2F", $pTTC / 1.196);
//            $pTVA = sprintf("%0.2F", $pHT * 0.196);
//            $this->SetFont('Arial','',6);
//            $this->SetXY(85, 261 );
//            $this->Cell( 6 ,4, "HT : ", '', '', '');
//            $this->SetXY(92, 261 );
//            $this->Cell( 9 ,4, $pHT, '', '', 'R');
//            $this->SetXY(85, 265 );
//            $this->Cell( 6 ,4, "TVA : ", '', '', '');
//            $this->SetXY(92, 265 );
//            $this->Cell( 9 ,4, $pTVA, '', '', 'R');
//            $this->SetXY(85, 269 );
//            $this->Cell( 6 ,4, "TTC : ", '', '', '');
//            $this->SetXY(92, 269 );
//            $this->Cell( 9 ,4, $pTTC, '', '', 'R');
//            $this->SetFont('Arial','',8);
//            $totalHT += $pHT;
//            $totalTVA += $pTVA;
//            $totalTTC += $pTTC;
//        }
//        else if ( $params["portHT"] > 0 )
//        {
//            $pHT  = sprintf("%0.2F", $params["portHT"]);
//            $pTVA = sprintf("%0.2F", $params["portTVA"] * $pHT / 100 );
//            $pTTC = sprintf("%0.2F", $pHT + $pTVA);
//            $this->SetFont('Arial','',6);
//            $this->SetXY(85, 261 );
//            $this->Cell( 6 ,4, "HT : ", '', '', '');
//            $this->SetXY(92, 261 );
//            $this->Cell( 9 ,4, $pHT, '', '', 'R');
//            $this->SetXY(85, 265 );
//            $this->Cell( 6 ,4, "TVA : ", '', '', '');
//            $this->SetXY(92, 265 );
//            $this->Cell( 9 ,4, $pTVA, '', '', 'R');
//            $this->SetXY(85, 269 );
//            $this->Cell( 6 ,4, "TTC : ", '', '', '');
//            $this->SetXY(92, 269 );
//            $this->Cell( 9 ,4, $pTTC, '', '', 'R');
//            $this->SetFont('Arial','',8);
//            $totalHT += $pHT;
//            $totalTVA += $pTVA;
//            $totalTTC += $pTTC;
//        }
//    }
//
//    $this->SetXY(114,266.4);
//    $this->Cell(15,4, sprintf("%0.2F", $totalHT), '', '', 'R' );
//    $this->SetXY(114,271.4);
//    $this->Cell(15,4, sprintf("%0.2F", $totalTVA), '', '', 'R' );
//
//    $params["totalHT"] = $totalHT;
//    $params["TVA"] = $totalTVA;
//    $accompteTTC=0;
//    if ( $params["AccompteExige"] == 1 )
//    {
//        if ( $params["accompte"] > 0 )
//        {
//            $accompteTTC=sprintf ("%.2F", $params["accompte"]);
//            if ( strlen ($params["Opmerkingen"]) == 0 )
//                $this->addRemarque( "Accompte de $accompteTTC Euros exig� � la commande.");
//            else
//                $this->addRemarque( $params["Remarque"] );
//        }
//        else if ( $params["accompte_percent"] > 0 )
//        {
//            $percent = $params["accompte_percent"];
//            if ( $percent > 1 )
//                $percent /= 100;
//            $accompteTTC=sprintf("%.2F", $totalTTC * $percent);
//            $percent100 = $percent * Opmerkingen;
//            if ( strlen ($params["Opmerkingen"]) == 0 )
//                $this->addRemarque( "Accompte de $percent100 % (soit $accompteTTC Euros) exig� � la commande." );
//            else
//                $this->addRemarque( $params["Opmerkingen"] );
//        }
//        else
//            $this->addRemarque( "Dr�le d'acompte !!! " . $params["Opmerkingen"]);
//    }else{
//        if ( strlen ($params["Opmerkingen"]) > 0 )
//            $this->addRemarque( $params["Opmerkingen"] );
//    }
//    $re  = $this->w - 50;
//    $rf  = $this->w - 29;
//    $y1  = $this->h - 40;
//    $this->SetFont( "Arial", "", 8);
//    $this->SetXY( $re, $y1+5 );
//    $this->Cell( 17,4, sprintf("%0.2F", $totalTTC), '', '', 'R');
//    $this->SetXY( $re, $y1+10 );
//    $this->Cell( 17,4, sprintf("%0.2F", $accompteTTC), '', '', 'R');
//    $this->SetXY( $re, $y1+14.8 );
////    $this->Cell( 17,4, sprintf("%0.2F", $totalTTC - $accompteTTC), '', '', 'R');
//    $this->SetXY( $rf, $y1+5 );
//    $this->Cell( 17,4, sprintf("%0.2F", $totalTTC * EURO_VAL), '', '', 'R');
//    $this->SetXY( $rf, $y1+10 );
//    $this->Cell( 17,4, sprintf("%0.2F", $accompteTTC * EURO_VAL), '', '', 'R');
//    $this->SetXY( $rf, $y1+14.8 );
//    $this->Cell( 17,4, sprintf("%0.2F", ($totalTTC - $accompteTTC) * EURO_VAL), '', '', 'R');
}

// add a watermark (temporary estimate, DUPLICATA...)
// call this method first
function temporaire( $texte )
{
    $this->SetFont('Arial','B',50);
    $this->SetTextColor(203,203,203);
    $this->Rotate(45,55,190);
    $this->Text(55,190,$texte);
    $this->Rotate(0);
    $this->SetTextColor(0,0,0);
}

}
