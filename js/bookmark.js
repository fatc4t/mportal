function bookmark()
{
	$.ajax({
		url: '/mportal/sales/public/ajax_bookmark.php',
		type: "GET",
		data: {
			user_name: document.getElementById("login_staff").value,
			title: document.getElementById("bookmark_title").value,
			url: document.getElementById("bookmark_url").value,
			bookmark_f: document.getElementById("button_bookmark").innerHTML
		},
		success: function (data, dataType) {
			if (document.getElementById("button_bookmark").innerHTML == "ﾌﾞｯｸﾏｰｸ削除"){
				document.getElementById("button_bookmark").innerHTML = 'ﾌﾞｯｸﾏｰｸ登録';
			}else{
				document.getElementById("button_bookmark").innerHTML = 'ﾌﾞｯｸﾏｰｸ削除';
			}
		}
	});
}
