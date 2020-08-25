$.updateChart = function(elementId){
    type = $('input:radio:checked.radio_type').val();
    if(type=="staff" || type=="location")
        $('#display_view').css('display','block');
    else
        $('#display_view').css('display','none');
    



    if(type=='turnover')
        grapher = "MSArea2D";
    else if(type=='staff')
        grapher = "MSColumn3D";
    
	$(elementId).insertFusionCharts({
		swfPath: root+"/fc/Charts/",
        type: grapher,
		data: root+"/salesxml.php?type="+type+'&year='+$('#fld_year').val()+'&month='+$('#fld_month').val()+'&day='+$('#fld_day').val(),
		dataFormat: "URIData",
		width: "700",
		height: "400"
	});        
}
$.updateDays = function(){
       days = {1:31,2:29,3:31,4:30,5:31,6:30,7:31,8:31,9:30,10:31,11:30,12:31};
       options = '<option value="">'+$('#choose_a_day').html()+'</option>'+"\n";
       for(x=1;x<=days[$('#fld_month').val()];x++)
            options = options+'<option>'+x+'</option>'+"\n";
                  
       $('#fld_day').html(options);         
} 
$(document).ready(function() {
    $('#fld_month').change(function(e){
        $.updateDays();           
    });
    
    $('.mutations').change(function(){
        $.updateChart('#mainreport','turnover');
    });
    $.updateDays(); 
    $.updateChart('#mainreport','turnover');
});