(function($) {
	$(document).ready(function() {
		$('a[href^="#"]').on('touchend click', function(e) {
			e.preventDefault();
			var id = $(this).attr("href");
			var offset = 70;
			var target = $(id).offset().top - offset;
			
			$('html, body').animate({scrollTop:target}, 500);
		});
	});
}) (jQuery);
