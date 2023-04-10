$(document).ready(function() {
    $("#dataForm").validate({
        rules : {
		action: {
			required: true
		}
	},
        messages: {
		action: {
			required: "‘I‚×I‚Î‚Ÿ`‚©"
		}
        }
    });
});
