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
				required: "‚È‚ñ‚©‘I‚×"
			},
			kousu: {
				required: "“ü‚ê‚ë‚æ",
			}
		}
	});
});
