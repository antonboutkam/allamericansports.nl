var sort;
function updateTable(resetPage){
    if(resetPage!=undefined)
        $('#current_page').val(resetPage);
        send = {    'query':$('#fld_search').val(),
                    'ajaxresult':1,
                    'defaultquery':$('#fld_search').attr('title'),
                    'current_page':current_page,              
                    'sort':sort}
    $.fancybox.showActivity();                                                                        
    $.post(request_uri,
            send,function(data){
                $('#order_relation_tbl').html(data);
                $.bindFancybox();
                $.fancybox.hideActivity();
                fixTotals();
            });         
}
$.barcodeEventProduct = function(barcode){
    if($( ".selector" ).accordion( "option", "active" )!=1){
        $.fancybox.showActivity();
        $('.accordion').accordion("activate",1);
        $.fancybox.hideActivity();
    } 
    addProductByBarcode(barcode);       
}


function addCustomer(clientId){
    $.fancybox.close();      
    $.fancybox.showActivity();
    $.post(request_uri,{
        'ajaxresult':'1',
        'clientid':clientId,
        'orderid':$('#orderid').val(),
        '_do':'add_client'                
    },function(data){
        $('#orderid').val(data.orderid);
        fixTotals();
        if(data.relation_id!='0'){
            $('#customer_info').html(data.customer_info);
            $('#customer_info').css('display','block');
            $('#add_customer_yn').css('display','none');
            $('#add_customer_n').css('display','none');                        
        }
        $.fancybox.hideActivity();
    },'json');
}
function closeOrder(method,paymentTookPlace){
    if(paymentTookPlace==undefined){
        paymentTookPlace = false;
    }
    $.fancybox.close();      
    $.fancybox.showActivity();
    properties = {
        'ajaxresult'        :   '1',
        'orderid'           :   $('#orderid').val(),
        'orderdetail'       :   $('.orderdetail').serialize(),
        'paymenttookplace'  :   paymentTookPlace,
        '_do'               :   method                        
    }
    $.post(request_uri,properties,function(data){
        if(data._do=='finalize'){
            window.open(root+'/bill_pdf.php?orderid='+data.done_orderid+'&rand='+data.rand,'bill');
            $('#show_customer_info').trigger('click');
        }
        if(data._do=='offer'){
            window.open(root+'/bill_pdf.php?type=offer&orderid='+data.orderid+'&rand='+data.rand);
        }else{
            $('#orderid').val(data.orderid);
            $('#customer_info').html('');
            $('#order_items_tbl').html(data.order_items_tbl);
            $('#show_customer_info').trigger('click');
            /* $('.accordion').accordion( "activate" , 0); */
            $('#pricetbl').html('');
            if(data._do=='park')
                window.location = root+'/orders/new.html';
        }
        $.fancybox.hideActivity();
    },'json');            
}
function addProductByBarcode(barcode){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   'barcode'           :   barcode,           
        'ajax'              :   1,
        '_do'               :   'add_product_by_barcode',
        'orderid'           :   $('#orderid').val()
    },function(data){
        $.fancybox.hideActivity();        
        $('#order_items_tbl').html(data.order_items_tbl);
        $('.fld_id').val(data.id);
        $.fancybox.close();    
        fixTotals();
        $('#fld_barcode').focus();
        $('#pricetbl').html(data.pricetbl);
    },'json');     
}
// Called from child window
function addProduct(productId){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   'productId'         :   productId,           
        'ajax'              :   1,
        '_do'               :   'add_product',
        'orderid'           :   $('#orderid').val()
    },function(data){
        $.fancybox.hideActivity();        
        $('#order_items_tbl').html(data.order_items_tbl);
        $('.fld_id').val(data.id);
        $.fancybox.close();
        $('#pricetbl').html(data.pricetbl);
        fixTotals();
    },'json');        
}
function getArticleProperty(articleProperty,resultAction){
    if(articleProperty!=''){
        $.post(request_uri,
        {   'articleProperty'   :    articleProperty,
            'ajax'              :   1,
            'showwostock'       :   false,              
        },function(data){
            resultAction(data);
        },'json');
    }
}
    
function fixTotals(){
   var subtotal = 0;
   $('.pricerow').each(function(){
       discount = parseFloat($('.discount',this).html().replace('%','')).toFixed(2);
       price = parseFloat($('.price',this).html());
       if(discount>0)
           price = (parseFloat(price)/100) * (100-parseFloat(discount));

        subprice = price * parseFloat($('.update_quantity',this).val());
        $('.subprice',this).html(parseFloat(subprice).toFixed(2));

        subtotal = subtotal + parseFloat(subprice);
   });
   $('#subtotal').html(subtotal.toFixed(2));
   $('.subtotal').html(subtotal.toFixed(2));
   btw = parseFloat(0+'.'+$('#fldbtw').val())

   $('.vat').html((subtotal*btw).toFixed(2));
   $('.incvat').html(((subtotal*btw)+subtotal).toFixed(2));
} 
function validOrder(){
    if(!$('#direct').is(':checked') && !$('#picking').is(':checked')){
        $('#show_pay').trigger('click');
        return alert($('#err_no_stockselection').html());
    }
    productCount = 0;
    $('.update_quantity').each(function(){
        productCount =+1;    
    });
    if(productCount==0){
        $('#show_products').trigger('click');
        return alert($('#err_no_products').html());
    }
    paymethodSelected = false;    
    $('.paymethod').each(function(){
        if($(this).is(':checked'))
           paymethodSelected = true;    
    });
    if(!paymethodSelected){
        $('#show_pay').trigger('click');
        return alert($('#err_no_paymethod').html());
    }
    return true;
}
$(document).ready(function() {
    $('.discount_fixed').focus(function(e){
        $('.odfixed').attr('checked',true);
    });

    $('.discount_perc').focus(function(e){
        $('.odperc').attr('checked',true);
    });
    $('.steps').live('click',function(e){
        e.preventDefault();
        $('.steps').removeClass('active');

        $('#show_pay_calc').css('display',($(this).attr('id')=='show_pay')?'block':'none');
        $(this).addClass('active');

        $('.block_step').css('display','none');
        $('#'+$(this).attr('id').replace('show','block')).css('display','block');
    });

    $('#add_product').live('click',function(e){
        e.preventDefault();
        addProduct($(this).attr('rel'));
    })
    fixTotals();
    if($('#orderid').val()!='blank' && $('#relation_id').val()!='0')
        $('#customer_info').css('display','block');

    $('.del_item').live('click',function(e){
        if(confirm($('#confirm_sure').html())){
            $.fancybox.showActivity();
            e.preventDefault();
            $.post(request_uri,{
                'ajaxresult'    :'1',
                'orderitemid'   :$(this).attr('rel'),
                'orderid'       :$('#orderid').val(),
                '_do'           :'del_orderitem'
            },function(data){
                fixTotals();
                $('#order_items_tbl').html(data.order_items_tbl);
                $('#pricetbl').html(data.pricetbl);
                $.fancybox.hideActivity();
            },'json');
        }
    });
    $('#finalize').live('click',function(e){
        e.preventDefault();
        if(validOrder()){
            paymentTookPlace = false;
            invalidInput = '';

            while(paymentTookPlace!=$('#inf_yes').html() && paymentTookPlace!=$('#inf_no').html() && paymentTookPlace != null){
                paymentTookPlace = prompt(invalidInput+$('#prompt_direct_payment').html());
                invalidInput = $('#err_invalid_input').html()+"\n";
            }
            if(paymentTookPlace==null)
                return;
            closeOrder('finalize',(paymentTookPlace==$('#inf_yes').html()));
        }
    });

    $('#offer').live('click',function(e){
        e.preventDefault();
        closeOrder('offer');        
    });

    $('#park').live('click',function(e){
        e.preventDefault();
        closeOrder('park');
    });

    $('#cancel').live('click',function(e){
        e.preventDefault();
        closeOrder('cancel');
    });
    $('.no_client').live('click',function(e){
        $('#customer_info').html('');
        $('#customer_info').css('display','none');
        $('#add_customer_yn').css('display','none');
        $('#add_customer_n').css('display','block');
        $('#show_products').trigger('click');
        addCustomer(0);
    });
    $('.reload').live('click',function(e){
        e.preventDefault(); 
        updateTable();          
    });
    $('.paginate').live('click',function(e){
        e.preventDefault();
        current_page = $(this).attr('rel');
        updateTable();
    });
    $('.sort').live('click',function(e){
        e.preventDefault();
        sort = $(this).attr('rel');
        updateTable();         
    });
    $('.view-product').click(function(e){
        e.preventDefault();        
        window.location = $(this).attr('href')+'&query='+$('#fld_search').val();           
    });     
    $('#fld_search').keyup(
        function(e){
            updateTable(1);
        }).blur(function(e){
            updateTable(1);
        });    
    var detailBaseUrl = $('#detail-search').attr('href');
    var searchTimeout = null;
    $('#fld_article_property').keyup(function(e){
	if(searchTimeout){
	   clearTimeout(searchTimeout);	
	}	
        if($(this).val().trim()!=''){
	    var currentSearchElement = $(this);
	    searchTimeout = setTimeout(function(){
		    $.fancybox.showActivity();
		    getArticleProperty(currentSearchElement.val(),function(data){
			$.fancybox.hideActivity();                
			if(data.rowcount==1){                    
			    productViewUrl = $('#show_product').attr('href').replace(/id=[0-9]+/,'id='+data.products[0].id);
			    $('#show_product').attr('href',productViewUrl);
			    $('#show_product').css('display','inline');
			    $('#add_product').attr('rel',data.products[0].id);
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
		},300);    
        }
    });
    $('.update_quantity').live('focus',function(){
        this.select();
    });

    $('.update_quantity').live('keyup',function(){
       $.fancybox.showActivity();
       
       id = $(this).attr('id').replace('order_item_','');
       newprice = $(this).val()*$('#sale_price_'+id).html();       
       $('#price_'+id).html(newprice.toFixed(2));
       fixTotals();              
       $.post(request_uri,    {        
            'orderItemId'       :   id,           
            'ajax'              :   1,
            'new_quantity'      :   $(this).val(),
            '_do'               :   'change_quantity'
        },function(data){
            if(data.out_of_stock)
                alert($('#err_out_of_stock').html().replace('@@productcount@@',data.left_over));
            $('#pricetbl').html(data.pricetbl);
            $.fancybox.hideActivity();            
       },'json'); 
    });
});
