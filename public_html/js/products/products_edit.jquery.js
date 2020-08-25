/**
 * jQuery.ScrollTo - Easy element scrolling using jQuery.
 * Copyright (c) 2007-2008 Ariel Flesler - aflesler(at)gmail(dot)com | http://flesler.blogspot.com
 * Dual licensed under MIT and GPL.
 * Date: 9/11/2008
 * @author Ariel Flesler
 * @version 1.4
 *
 * http://flesler.blogspot.com/2007/10/jqueryscrollto.html
 */
;(function(h){var m=h.scrollTo=function(b,c,g){h(window).scrollTo(b,c,g)};m.defaults={axis:'y',duration:1};m.window=function(b){return h(window).scrollable()};h.fn.scrollable=function(){return this.map(function(){var b=this.parentWindow||this.defaultView,c=this.nodeName=='#document'?b.frameElement||b:this,g=c.contentDocument||(c.contentWindow||c).document,i=c.setInterval;return c.nodeName=='IFRAME'||i&&h.browser.safari?g.body:i?g.documentElement:this})};h.fn.scrollTo=function(r,j,a){if(typeof j=='object'){a=j;j=0}if(typeof a=='function')a={onAfter:a};a=h.extend({},m.defaults,a);j=j||a.speed||a.duration;a.queue=a.queue&&a.axis.length>1;if(a.queue)j/=2;a.offset=n(a.offset);a.over=n(a.over);return this.scrollable().each(function(){var k=this,o=h(k),d=r,l,e={},p=o.is('html,body');switch(typeof d){case'number':case'string':if(/^([+-]=)?\d+(px)?$/.test(d)){d=n(d);break}d=h(d,this);case'object':if(d.is||d.style)l=(d=h(d)).offset()}h.each(a.axis.split(''),function(b,c){var g=c=='x'?'Left':'Top',i=g.toLowerCase(),f='scroll'+g,s=k[f],t=c=='x'?'Width':'Height',v=t.toLowerCase();if(l){e[f]=l[i]+(p?0:s-o.offset()[i]);if(a.margin){e[f]-=parseInt(d.css('margin'+g))||0;e[f]-=parseInt(d.css('border'+g+'Width'))||0}e[f]+=a.offset[i]||0;if(a.over[i])e[f]+=d[v]()*a.over[i]}else e[f]=d[i];if(/^\d+$/.test(e[f]))e[f]=e[f]<=0?0:Math.min(e[f],u(t));if(!b&&a.queue){if(s!=e[f])q(a.onAfterFirst);delete e[f]}});q(a.onAfter);function q(b){o.animate(e,j,a.easing,b&&function(){b.call(this,r,a)})};function u(b){var c='scroll'+b,g=k.ownerDocument;return p?Math.max(g.documentElement[c],g.body[c]):k[c]}}).end()};function n(b){return typeof b=='object'?b:{top:b,left:b}}})(jQuery);


 
$.updateDropdown = function(update_lookup,value,label){
    // alert(update_lookup);
    // alert(value);
    // alert(label);
    // alert(typeof(label));
    if(typeof(label)=='undefined' || $.trim(label)=='')
        label = value;    
    lookup = $('#fld_'+update_lookup);
    select = "<option selected=\"selected\" value=\""+value+"\">"+label+"</option>";
    lookup.html(lookup.html()+select);
}
$.removeFromDropdown = function(field,id){
    $("#fld_"+field+" option[value='"+id+"']").remove();
}

$.barcodeEventProduct = function(barcode){
   if($.trim($('#fld_barcode_1').val())=='')
       $('#fld_barcode_1').val(barcode);
   else if($.trim($('#fld_barcode_2').val())=='')
       $('#fld_barcode_2').val(barcode);
   else
       $('#fld_barcode_1').val(barcode);

    //get the top offset of the target anchor
    var target_offset = $("#barcodes").offset();

    var target_top = target_offset.top;
    $('html, body').animate({scrollTop:target_top}, 500);
};
// Called child frame/fancybox
function addProduct(addProduct,type){  
    if(type=='relate'){
        dolike = 'relate';
    }else if(type=='product')
        dolike = 'is_part_of';
    else if(type=='part')
        dolike = 'has_part';
    else
        dolike = 'add_option';
    $.fancybox.showActivity();
    send = {addProduct:addProduct,_do:dolike,id:$('#fld_id').val(),ajax:1};
    $.post(request_uri,send,function(data){        
        if(data._do=='relate'){
            $.fancybox.hideActivity();
            $.fancybox.close();
            $('#module_relate_products').replaceWith(data.module_relate_products);
        }
        $('.partsoftable').html(data.partsoftable);
        $('.partsoftable').html(data.partsoftable);
        $('.haspartstable').html(data.haspartstable);
        $('.optionoftable').html(data.optionoftable);
    },'json');    
}
$.removeMinRecursively = function(element){
    element.removeClass('min').addClass('plus');
    if(typeof(window[element.parent().parent().parent()[0]]) != "undefined")    
        if(element.parent().parent().parent()[0].nodeName.toString().toLowerCase()!='ul')
            $.removeMinRecursively($('a.min:first',element.parent().parent().parent()));    
}
$(document).ready(function() {

        $('.sync_suppliers').live('click',function(e){
            $.fancybox.showActivity();
            /*
            e.preventDefault();
            addEmail = false;                                               
            if(confirm('Wilt u een e-mailbericht onvangen op '+$(this).attr('data-syncemail')+' als het synchroniseren is afgerond?')){
                addEmail = true;
            }
            $.fancybox.showActivity();
            send            = {};
            send.ajax       = 1;
            send._do        = 'plan_sync';
            send.add_email  = addEmail;
            
            $.post(window.location,send,function(data){
                $.fancybox.hideActivity();
                $('#sync_planned').fadeIn(200);                                
            },'json');
            */
        })

    
        $('#fld_ean').live('keyup',function(){
            if($.trim($(this).val())==''){                          
                return;
            }
            
            $.fancybox.showActivity();
            send        = {};
            send._do    = 'check_ean';
            send.ajax   = 1;
            send.id     = $('#fld_id').val();
            send.ean    = $('#fld_ean').val();
            
            $.post(request_uri,send,function(data){        
                $.fancybox.hideActivity();
                if(data.duplicate_ean){
                    $('#eanlink').attr('href','/products/view.html?id='+data.duplicate_ean+'&iframe=1&view=');
                    $('#eanexists').css('display','inline');
                    $('#eannotexists').css('display','none');                     
                }else{
                    $('#eanexists').css('display','none');
                    $('#eannotexists').css('display','inline');                                       
                }
                
                
            },'json');
            
        });    
    
        var translationblocks = $('.translationblocks');
        $('#translation_webshop_id').live('change',function(e){
            e.preventDefault();
            translationblocks.css('display','none');
            $('#translation_webshop_'+$(this).val()).css('display','block');                                    
        });    
    
        $('.addcolor').live('click',function(e){            
            e.preventDefault();            
            if($.trim($('#colorfld').val())==''){
                alert('Kies eerst een kleur uit het dropdown menu');
            }else{
                $.fancybox.showActivity();                            
                send = {_do:'add_color',id:$('#fld_id').val(),color_id:$('#colorfld').val(),ajax:1};                
                $.post(request_uri,send,function(data){
                    $('#product_edit_colors').html(data.product_edit_colors);                    
                    $.fancybox.hideActivity();    
                },'json');
            }                                                                 
        });
        $('.addusage').live('click',function(e){            
            e.preventDefault();            
            if($.trim($('#usagefld').val())==''){
                alert('Kies eerst een gebruik uit het dropdown menu');
            }else{
                $.fancybox.showActivity();                            
                send = {_do:'add_usage',id:$('#fld_id').val(),usage_id:$('#usagefld').val(),ajax:1};                
                $.post(request_uri,send,function(data){
                    $('#product_edit_usage').html(data.product_edit_usage);                    
                    $.fancybox.hideActivity();    
                },'json');
            }                                                                 
        });        
        
                                
        $('.removeproductusage').live('click',function(e){            
            e.preventDefault();            
            $.fancybox.showActivity();                            
            send = {_do:'remove_usage',id:$('#fld_id').val(),usage_id:$(this).attr('data-usageid'),ajax:1};                
            $.post(request_uri,send,function(data){
                $('#product_edit_usage').html(data.product_edit_usage);                    
                $.fancybox.hideActivity();    
            },'json');                                                                 
        });                
        $('.removeproductcolor').live('click',function(e){            
            e.preventDefault();            
            $.fancybox.showActivity();                            
            send = {_do:'remove_color',id:$('#fld_id').val(),color_id:$(this).attr('data-colorid'),ajax:1};                
            $.post(request_uri,send,function(data){
                $('#product_edit_colors').html(data.product_edit_colors);                    
                $.fancybox.hideActivity();    
            },'json');                                                                 
        });        
        
        
        
        $('.togglemenu').live('click',function(e){
            e.preventDefault();
            $.fancybox.showActivity();
            
            if($('#menu_item_'+$(this).attr('rel')).hasClass('open')){
                $('#menu_item_'+$(this).attr('rel')).removeClass('open')
                $('ul',$(this).parent()).remove('ul');
                $.fancybox.hideActivity();   
            }else{
                send = {_do:'get_submenu',by:'id',id:$(this).attr('rel'),'product_id':$('#fld_id').val()};
                var out = '';
                $.post(root+'/ajax.php',send,function(data){
                    $.each(data.data,function(i,e){                        
                        out = out+'<li>';                        
                        if(e.has_children=='1'){
                            children = (e.children_in_webshop=='1'?'children':'');
                            out = out+'<a rel="'+e.menu_id+'" class="togglemenu folder '+children+'" href="#"></a>';
                        }                                     
                        out = out+'<a rel="'+e.menu_id+'" class="'+((e.in_webshop=='0')?'min':'plus')+' minplus" rel= href="#"></a>';                                                                        
                        out = out+'<a rel="'+e.menu_id+'" id="menu_item_'+e.menu_id+'" class="togglemenu" href="">'+e.menu_item+'</a>';
                        out = out+'</li>';                        
                    });
                    if(out){
                        $('#menu_item_'+data.id).after('<ul>'+out+'</ul>')                    
                        $('#menu_item_'+data.id).addClass('open');                    
                    }
                    $.fancybox.hideActivity();    
                },'json');
            }
        });
        $('.plus').live('click',function(e){
            e.preventDefault();
            $(this).addClass('min').removeClass('plus');                                   
            $('a.minplus',$(this).parent().children()).removeClass('plus').addClass('min');                                    
            $.fancybox.showActivity(); 
            send = {_do:'remove_menu_item_recursive_down',menu_item:$(this).attr('rel'),product_id:$('#fld_id').val()};
            $.post(root+'/ajax.php',send,function(e){
                $.fancybox.hideActivity(); 
            },'json');
        });
        $('.min').live('click',function(e){                        
            $('li a.min',$(this).parent()).removeClass('min').addClass('plus');            
            currentId = 'menu_item_'+$('a.min:first',$(this).parent().parent().parent()).attr('rel')                           
            $.removeMinRecursively($(this));
            e.preventDefault();           
            $.fancybox.showActivity();                 
            send = {_do:'add_menu_item_recursive',menu_item:$(this).attr('rel'),product_id:$('#fld_id').val()};
            $.post(root+'/ajax.php',send,function(e){
                $.fancybox.hideActivity(); 
            },'json');            
        });


    $('.incvat').each(function(i,e){
        if($(e).val()=='.' || $(e).val()=='00.00'){
            $(e).val('0.00')
        } 
    });
    $('.exvat').each(function(i,e){
        if($(e).val()=='.' || $(e).val()=='00.00'){
            $(e).val('0.00')
        } 
    });    
    $('.incvat').live('keyup',function(e){        
        workval = $(this).val().replace(',','.').replace(/[^0-9.]+/,'');                                                 
        exvat   = $('.exvat',$(this).parent().parent());
        btw     = parseFloat($('#btw').val())
        workval = (workval / btw)*100;
        // round on max 2 decimals
        //workval = Math.round(workval*100)/100;
        // force 2 decimals         
        workval = workval.toFixed(4) 
        exvat.val(workval);                            
    });
    
    $('.exvat').live('keyup',function(e){
        workval = $(this).val().replace(',','.').replace(/[^0-9.]+/,'');                                                 
        incvat   = $('.incvat',$(this).parent().parent());
        btw     = parseFloat($('#btw').val())
        workval = (workval * (btw/100));
        // round on max 2 decimals
        // workval = Math.round(workval*100)/100;
        // force 2 decimals         
        workval = workval.toFixed(4) 
        incvat.val(workval);                            
    });    

    $('.remove_relation').live('click',function(e){
        e.preventDefault();
        if(confirm('Zeker weten?')){
           $.fancybox.showActivity();
            send = {    _do         :   'remove_relation',                        
                        related     :   $(this).attr('rel'),
                        id          :   $('#fld_id').val(),
                        ajax        :   1
                    };
            $.post(request_uri,send,function(data){                
                $.fancybox.hideActivity();
                $('#module_relate_products').replaceWith(data.module_relate_products);
                $.bindFancybox();
            },'json');
        }        
    });
    $('.partsview').live('click',function(e){
       $('.partstbls').css('display','none');
       $('.'+$('.partsview:checked').val()).css('display','block');
       $('#'+$('.partsview:checked').val()+'btns').css('display','block');
    });
    
    $('.delete_product_part,.delete_product_option,.delete_partofproduct').live('click',function(e){
       if($(this).hasClass('delete_product_part')||$(this).hasClass('delete_partofproduct'))
           table = 'product_part';
       else
           table = 'product_option';
       e.preventDefault();
       if(confirm('Zeker weten?')){
           $.fancybox.showActivity();
            send = {_do     :   'delete_grouplink',
                        table   :   table,
                        linkid  :   $(this).attr('rel'),
                        id      :   $('#fld_id').val(),
                        ajax    :   1
                    };
            $.post(request_uri,send,function(data){

                $('.partsoftable').html(data.partsoftable);
                $('.haspartstable').html(data.haspartstable);
                $('.optionoftable').html(data.optionoftable);
                $.fancybox.hideActivity();
            },'json');
       }
    });

    $('.move_img_left,.delete_img,.move_img_right').live('click', function(e){
        e.preventDefault();
        send = {
            _do         : $(this).hasClass('delete_img')?'delete_img':'move_img',
            direction   : $(this).hasClass('move_img_left')?'left':'right',
            imgid       : $(this).attr('rel'),
            id          : $('#fld_id').val(),
            ajax        :   1
        }                
        $.post(request_uri,send,function(data){
            $('#multi_image_edit').html(data.multi_image_edit);
        },'json');
    });


    if($('#fld_article_number').attr('type')=='hidden'){
            $('#fld_article_name').trigger('focus');
    }else{
            $('#fld_article_number').trigger('focus');
    }
    if(parent.$.updateProductCatalog!=undefined)
        parent.$.updateProductCatalog();    
                        
    // Make the image field a file field.
    $('#upload-btn').click(function(e){
        e.preventDefault();        
        $('#uploadfield').trigger('click');
    });    
    if($('#fld_id').val()!='new' && $('#fld_view').val()=='stock_fill')
        parent.addProduct($('#fld_id').val());        
            
    $('.fld_store, #fld_store_next').click(function(e){
        $.fancybox.showActivity();
        $.scrollTo(0,300);
        $('#clearafterstore').val($(this).attr('id')=='fld_store_next');        
        e.preventDefault();
                   
        if($.trim($('#fld_article_number').val())==''){
            $.fancybox.hideActivity();
            return alert($('#err-no-art-num').html());        
        }else if($.trim($('#fld_article_name').val())==''){
            $.fancybox.hideActivity();
            return alert($('#err-prod-noname').html());
        }else if($('#fld_stock').val()>0 && $('#fld_configuration').val()==0){
            $.fancybox.hideActivity();
            return alert($('#err-stock-no-location').html());
        }
        if($.trim($('#fld_description').val())==''){
            $.fancybox.hideActivity();            
            $('body,html').animate({scrollTop: 0}, 800);
            return alert('Exactonline verplicht u om een omschrijving mee te geven.');
        }

        
        if($('#fld_id').val()=='new'){
            $.post(request_uri,{
                ajaxresult    :     1,
                articlenum    :     $('#fld_article_number').val(),
                _do           :     'check_article_num'                                
            }, function(data){
                if(data.duplicate){
                    $.fancybox.hideActivity();
                    return alert($('#err-dupl-artnum').html());
                }else                                        
                    $('#product_form').submit();                                                      
            },'json')
        }else
             $('#product_form').submit();                             
    });
    $('#fld_warehouse').live('change',function(e){
       e.preventDefault();
       $.fancybox.showActivity();
       data = {'_do':'warehouseconfig','location':$(this).val(),'ajaxresult':1};
       var options = [];
       $("#fld_configuration").html('<option>loading..</option>');
       $.post(request_uri,data,function(data){        
            for (var i = 0; i < data.warehouse.length; i++)
                options.push('<option value="',data.warehouse[i].id, '">' + data.warehouse[i].path+","+data.warehouse[i].rack+","+data.warehouse[i].shelf+'</option>');
            
            $("#fld_configuration").html(options.join(''));
            $.fancybox.hideActivity();
        
       },'json'); 
    });

    $('.delete_3dimage').live('click',function(e){
        e.preventDefault();
        if(confirm($('#sure-delete-3dimg').html())){
            send = {ajax:1,_do:"delete_img3d",id:$('#fld_id').val()};
            $.fancybox.showActivity();
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();
                $('#img3dactions').html('');
            },'json');
        }
    });

    duplicateArticle = function(alertexit){
        if(typeof(alertexit)=='undefined'){
            alertexit = false;
        }else{
            alert('Het opgegeven artikelnummer bestaat al');
        }
        initial_new_artnum  = $('#fld_article_number').val()+' (kopie)';
        article_number      = prompt('Geef het nieuwe artikelnummer op',initial_new_artnum);        
        if(article_number==null)
            return;                
        send                = {_do:'duplicate_article',ajax:1,articlenum:article_number,src_id:$('#fld_id').val(),copy_photos:$.duplicateProductPhotos};
        $.post(request_uri,send,function(data){
            if(data.duplicate){
                duplicateArticle(true);
            }else{
                alert('Het artikel is met succes gekopieerd!');
                if(confirm('Wilt u doorgestuurd worden naar het nieuwe artikel?'))
                    window.location = '/products/edit.html?id='+data.id;                
            }
        },'json');        
    }
    $('#fld_duplicate').live('click',function(e){
        e.preventDefault();
        if(confirm('Hiermee maakt u een kopie van dit product, wilt u een kopie maken?')){
            if(confirm('Wilt u de productfotos ook mee kopieren?')){
                $.duplicateProductPhotos = true;
            }else{
                $.duplicateProductPhotos = false;
            }        
            duplicateArticle();            
        }         
    });    
    
    
    
});