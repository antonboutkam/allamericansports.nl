$(document).ready(function(){
   $('#fld_updatepass').click(function(e){
      e.preventDefault();
      if($('#fld_newpass').val()!=$('#fld_newpassverify').val())
           return alert($('#err-nomatch').html());
       if($('#fld_currentpass').val()=='')
           return alert($('#err-nocurr').html());
       if($('#fld_newpass').val()=='')
           return alert($('#err-nonew').html());
      $.fancybox.showActivity();
      
      send      = {_do:"changepass",newpass:$("#fld_newpass").val(), currentpass:$("#fld_currentpass").val(),ajax:1};
      doWhenDone  = function(data){
          $.fancybox.hideActivity();
          if(data.changepass)
            return alert($('#err-pwchanged').html());
          else
            return alert($('#err-pwincorrect').html());
      }
      $.post(request_uri,send,doWhenDone,"json");
   });
});