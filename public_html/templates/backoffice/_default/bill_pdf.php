<?php
/**
 * Bill_pdf
 * 
 * @package bleuturban
 * @author Oriana Martinelli
 * @copyright 2010
 * @version $Id$
 * @access public
 */
class Bill_pdf {
    function run($params){
        Billgen::run($params);      
        exit();      
    }  
}