$(document).ready(function() {
	$("#dataForm").validate({
		rules : {
			gyoumu: {
				required: true,
			},
			kousu: {
				required: true,
			}
		},
		messages: {
			gyoumu: {
				required: "なんか選べ"
			},
			kousu: {
				required: "入れろよ",
			}
		}
	});
});
