// Called from child window
function addLocationToProduct(stockId,locationId){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   '_do'               :   'update_location',
        'ajax'              :   1,
        'stockId'           :   stockId,
        'locationId'        :   locationId
    },function(data){
        $('#stock_fill_tbl').html(data.stock_fill_tbl);
        $.fancybox.hideActivity();
        $.fancybox.close();    
    },'json');
}

function addProduct(productId){    
    $.fancybox.showActivity();
    $.post(request_uri,
    {   'productId'         :   productId,           
        'ajax'              :   1    
    },function(data){
        $.fancybox.hideActivity();
        $('#stock_fill_tbl').html(data.stock_fill_tbl);
        $('.fld_id').val(data.id);
        $.fancybox.close();    
        $.bindFancybox();
    },'json');        
}

$.barcodeEventProduct = function(barcode){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   'barcode'           :   barcode,           
        'ajax'              :   1   
    },function(data){
        $.fancybox.hideActivity();
        $('#stock_fill_tbl').html(data.stock_fill_tbl);
        $('.fld_id').val(data.id);
        $.fancybox.close();    
    },'json');               
}

function getArticleProperty(articleProperty,resultAction){
    if(articleProperty!=''){
        $.post(root+'/ajax.html',
        {   'articleProperty'   :    articleProperty,
            'ajax'              :   1        
        },function(data){
            resultAction(data);
        },'json');
    }
}
function deleteDelivery(stockId){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   '_do'               :   'delete',
        'ajax'              :   1,
        'stockId'           :   stockId
    },function(data){
        $('#stock_fill_tbl').html(data.stock_fill_tbl);
        $.fancybox.hideActivity();
    },'json');        
} 
function clearDelivery(){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   '_do'               :   'clear',
        'ajax'              :   1,
    },function(data){
        $('#stock_fill_tbl').html('');
        $.fancybox.hideActivity();
    },'json');        
}
function updateQuantity(name,val){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   '_do'               :   'update_quantity',
        'stockId'           :   name.replace('quantity_',''),
        'val'               :   val,        
        'ajax'              :   1,
    },function(data){
        $.fancybox.hideActivity();
    },'json');      
} 

function complete(name,val){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   '_do'               :   'complete', 
        'ajax'              :   1,
    },function(data){
        $.fancybox.hideActivity();
        if(data.incomplete){
            alert($('#error-msg').html());
        }if(!data.incomplete){            
            var html  = $('#stored-ok').html();
            var seconds = 5;
            $('#stored-ok').html(html.replace("@@seconds@@",seconds));
            $('#stored-ok').css('display','inline');
            setInterval(function(){
                        $('#stored-ok').html(html.replace("@@seconds@@",seconds));
                        seconds -=1;
            },1000);
            setTimeout(function(){
                $('#stored-ok').css('display','none');
                window.location = root+'/stock/current.html';
                
            },5000);            
        }            
    },'json');    
}
var articleProperty;
$(document).ready(function() {    
    var detailBaseUrl = $('#detail-search').attr('href');
    $('.quantity').live('keyup',function(){
        updateQuantity($(this).attr('name'),$(this).val());
    });
    $('#fld_article_property').focus();
    $('.delete-product').live('click',function(e){        
       e.preventDefault();
       deleteDelivery($(this).attr('rel'));       
    });
    $('#fld_delete_all').live('click',function(e){
        e.preventDefault();
        clearDelivery();
    });    
    $('#fld_complete').live('click',function(e){
        e.preventDefault();
        complete();
    });
    
    $('#fld_article_property').keyup(function(e){
        articleProperty = $(this).val();
        timeout = setTimeout(
            function(){
                if(articleProperty!=''){
                    $.fancybox.showActivity();
                    getArticleProperty(articleProperty,function(data){
                        $.fancybox.hideActivity();
                        if(data.productId){
                            productViewUrl = $('#show_product').attr('href').replace(/id=[0-9]+/,'id='+data.productId);
                            $('#show_product').attr('href',productViewUrl);
                            $('#show_product').css('display','inline');
                            $('#add_product').attr('rel',data.productId);
                            $('#add_product').css('display','inline');
                        }else{
                            $('#add_product').css('display','none');
                            $('#show_product').css('display','none');
                        }
                        if(data.rowcount > 1){
                            $('#row-count-cnt').css('display','inline');
                            $('#row-count').html(data.rowcount);
                            $('#detail-search').attr('href',detailBaseUrl+'&query='+data.articleProperty);
                        }else{
                            if(data.rowcount==1)
                                $('#fld_article_number').val(data.articleNumber);

                            $('#row-count-cnt').css('display','none');
                        }
                    });
                }
            }
        ,500);
    });
    $('#add_product').live('click',function(e){
        e.preventDefault();
        addProduct($(this).attr('rel')); 
    });
});