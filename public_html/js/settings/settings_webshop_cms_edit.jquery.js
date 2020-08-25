$(document).ready(function(){
	$('#fld_title').live('keyup',function(){
		newTag = $('#fld_title').val().toString().replace(';','').replace('.','').replace('&','').replace('?','').toLowerCase().replace(/\W/g, '-').replace(/-$/, '').replace('--', '-').replace('--', '-').replace('--', '-');
		console.log(newTag);
		$('#fld_url').val(newTag);
	});
	$('.delimage').live('click',function(e){	
		if(confirm($('#sure-delete-3dimg').html())){	
            $.fancybox.showActivity();  
			send = {_do:'del_img',id:$(this).attr('rel'),ajax:1};
			$.post(root+'/settings/webshop_cms_edit.html',send,function(e){ 
				window.location = '/settings/webshop_cms_edit.html?_do=edit&id='+$('#cid').val()+'webshop_id='+$('#fld_webshop_id').val();	
			},'json');			
		}
	});
	
	$('.updatetag').live('click',function(e){	
		if(confirm($('#sure-update-tag').html())){	
            $.fancybox.showActivity();
			var tag_ele= "tag_"+$(this).attr('rel');
			var alt_val=$('input[id='+tag_ele+']').val(); //alert(tag_ele+' '+alt_val);
			send = {_do:'update_tag',id:$(this).attr('rel'),alttag:alt_val,ajax:1};
			$.post(root+'/settings/webshop_cms_edit.html',send,function(e){ 
				
				 window.location = '/settings/webshop_cms_edit.html?_do=edit&id='+$('#cid').val()+'webshop_id='+$('#fld_webshop_id').val();	
			},'json');			
		}
	});    
    
});    