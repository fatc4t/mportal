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
				required: "�Ȃ񂩑I��"
			},
			kousu: {
				required: "������",
			}
		}
	});
});
