$(function() {
	//last-child
	$('body :last-child').addClass('last-child');
	$(".toggleBtn").addClass("close");
	$("header .toggleBtn").click(function(e) {
		e.preventDefault();
		$(header, this).toggleClass("toggle");
		$("nav").slideToggle('fast');
		if($(this).hasClass("close")) {
    		$(this).removeClass("close").addClass("open");
		} else {
    		$(this).removeClass("open").addClass("close");
		}

		//if($(this).hasClass("close")) {
		//}
		setTimeout(function() {
			$("body").animate({ scrollTop: 0 }, 500, "swing");
		}, 1000);

		/*$(".overlay").fadeToggle("fast");*/
	});
	$('a[href^=#]').click(function() {
		// スクロールの速度
		var speed = 400; // ミリ秒
		// アンカーの値取得
		var href = $(this).attr("href");
		// 移動先を取得
		var target = $(href == "#" || href == "" ? 'html' : href);
		// 移動先を数値で取得
		var position = target.offset().top;
		// スムーススクロール
		$('body,html').animate({
			scrollTop: position
		}, speed, 'swing');
		return false;
	});
	$(".accordion").on("click", function() {
		$(this).next().slideToggle();
	});
});
$(function() {
	hTop = $('#header').offset().top;
});
$(window).scroll(function() {
	if ($(window).scrollTop() > hTop - 0) {
		$('#header').css('position', 'fixed');
		$('#header').css('top', '0px');
		$('nav').css('top', '64px');
	} else {
		$('#header').css('position', 'absolute');
	}
});