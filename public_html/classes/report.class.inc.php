<?php
class Report{
    public static function build($type,$view,$periodZoom,$year,$month,$day){
        if($type=='turnover'){
            if($periodZoom=='day'){
                $sql = sprintf('SELECT
                                    DATE_FORMAT(hh.hour,"%%k") period,
                                    ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                    ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                FROM 
                                    helper_hours hh
                                    LEFT JOIN orders o ON HOUR(hh.hour) = HOUR(o.paid) AND o.paid IS NOT NULL
                                    LEFT JOIN order_item oi ON oi.order_id = o.id
                                WHERE 
                                    (YEAR(o.paid)=%1$d OR o.paid IS NULL)
                                AND (MONTH(o.paid)=%2$d OR o.paid IS NULL) 
                                AND (DAY(o.paid)=%3$d OR o.paid IS NULL)                                                                               
                                GROUP BY HOUR(hh.hour)',$year,$month,$day);            
            }if($periodZoom=='month'){
                $sql = sprintf('SELECT
                                    DATE_FORMAT(hd.day,"%%e") period,
                                    ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                    ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                FROM 
                                    helper_days hd
                                    LEFT JOIN orders o ON DAY(hd.day) = DAY(o.paid) AND o.paid IS NOT NULL
                                    LEFT JOIN order_item oi ON oi.order_id = o.id
                                WHERE 
                                    (YEAR(o.paid)=%1$d OR o.paid IS NULL)
                                AND (MONTH(o.paid)=%2$d OR o.paid IS NULL)                                                                                
                                GROUP BY DAY(hd.day)',$year,$month);            
            }else if($periodZoom=='year'){
                $sql = sprintf('SELECT
                                    DATE_FORMAT(hm.month,"%%b") period,
                                    ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                    ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                FROM 
                                    helper_months hm
                                    LEFT JOIN orders o ON MONTH(hm.month) = MONTH(o.paid) AND o.paid IS NOT NULL
                                    LEFT JOIN order_item oi ON oi.order_id = o.id
                                WHERE 
                                    (YEAR(o.paid)=%1$d OR o.paid IS NULL)                                                                                
                                GROUP BY MONTH(hm.month)',$year);
            }
        }else if($type=='staff'){
                if($periodZoom=='day'){
                    $sql = sprintf('SELECT
                                        u.full_name period,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                    FROM 
                                        order_item oi
                                        LEFT JOIN orders o ON oi.order_id = o.id
                                        LEFT JOIN users u ON u.id = o.user_id                                                                                                                                              
                                    WHERE 
                                        (YEAR(o.paid)=%1$d)
                                    AND (MONTH(o.paid)=%2$d OR o.paid IS NULL)                                     
                                    AND (DAY(o.paid)=%3$d OR o.paid IS NULL)                                                                                          
                                    GROUP BY u.id',$year,$month,$day);           
                          
                }if($periodZoom=='month'){
                    $sql = sprintf('SELECT
                                        u.full_name period,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                    FROM 
                                        order_item oi
                                        LEFT JOIN orders o ON oi.order_id = o.id
                                        LEFT JOIN users u ON u.id = o.user_id                                                                                                                                              
                                    WHERE 
                                        (YEAR(o.paid)=%1$d)       
                                    AND (MONTH(o.paid)=%2$d OR o.paid IS NULL)                                                                                                              
                                    GROUP BY u.id',$year,$month);
                    
                }else if($periodZoom=='year'){                
                    $sql = sprintf('SELECT
                                        u.full_name period,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                    FROM 
                                        order_item oi
                                        LEFT JOIN orders o ON oi.order_id = o.id
                                        LEFT JOIN users u ON u.id = o.user_id                                                                                                                                              
                                    WHERE 
                                        (YEAR(o.paid)=%1$d)                                                                                
                                    GROUP BY u.id',$year);
                }
            }else if($type=='location'){     
                if($periodZoom=='day'){
                    $sql = sprintf('SELECT
                                        wl.name period,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                    FROM 
                                        order_item oi
                                        LEFT JOIN orders o ON oi.order_id = o.id
                                        LEFT JOIN warehouse_locations wl ON wl.id = o.location_id 	                                                                                                                                              
                                    WHERE 
                                        (YEAR(o.paid)=%1$d)
                                    AND (MONTH(o.paid)=%2$d OR o.paid IS NULL)                                     
                                    AND (DAY(o.paid)=%3$d OR o.paid IS NULL)                                                                                          
                                    GROUP BY wl.id',$year,$month,$day);           
                          
                }if($periodZoom=='month'){
                    $sql = sprintf('SELECT
                                        wl.name period,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                    FROM 
                                        order_item oi
                                        LEFT JOIN orders o ON oi.order_id = o.id
                                        LEFT JOIN warehouse_locations wl ON wl.id = o.location_id 	                                                                                                                                              
                                    WHERE 
                                        (YEAR(o.paid)=%1$d)       
                                    AND (MONTH(o.paid)=%2$d OR o.paid IS NULL)                                                                                                              
                                    GROUP BY wl.id',$year,$month);
                    
                }else if($periodZoom=='year'){                
                    $sql = sprintf('SELECT
                                        wl.name period,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed),0)  turnover,
                                        ROUND(((SUM(oi.sale_price * oi.quantity)/100) * (100 + o.discount_perc) + o.discount_fixed) - SUM(oi.purchase_price),0)  bruto_profit 
                                    FROM 
                                        order_item oi
                                        LEFT JOIN orders o ON oi.order_id = o.id
                                        LEFT JOIN warehouse_locations wl ON wl.id = o.location_id 	                                                                                                                                             
                                    WHERE 
                                        (YEAR(o.paid)=%1$d)                                                                                
                                    GROUP BY wl.id',$year);                
                }                        
            }
        
        return fetchArray($sql,__METHOD__);            
    }    
}