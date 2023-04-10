$(document).ready(function()
{
	$(".gyoumu").change(function(){
		var id=$(this).val();
		var dataString = 'id='+ id;
		$.ajax({
			type: "POST",
			url: "ajax_sagyou.php",
			data: dataString,
			cache: false,
			success: function(html){
				$(".sagyou").html(html);
			} 
		});
 
	});


	$(".customer").change(function(){
		var id=$(this).val();
		var dataString = 'id='+ id;
 
		$.ajax({
			type: "POST",
			url: "ajax_prj.php",
			data: dataString,
			cache: false,
			success: function(html){
				$(".prj").html(html);
			} 
		});
 
	});

	$(".customer2").change(function(){
		var id=$(this).val();
		var dataString = 'id='+ id;
 
		$.ajax({
			type: "POST",
			url: "ajax_prj.php",
			data: dataString,
			cache: false,
			success: function(html){
				$(".prj").html(html);
			} 
		});
 
	});

});
