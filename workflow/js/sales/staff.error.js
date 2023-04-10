$(document).ready(function() {
    $("#dataForm").validate({
        rules : {
            user_name: {
                required: true,
                minlength: 2
            },
            password: {
                required: true,
                minlength: 2
            },
            name: {
                required: true,
                minlength: 2
            },
            busho: {
                required: true
            }
       },
        messages: {
            user_name: {
                required: "‚È‚ñ‚©“ü—Í‚µ‚ë",
                minlength: "‚¿‚á‚ñ‚Æ“ü‚ê‚ë"
            },
            password: {
                required: "–¢“ü—Í",
                minlength: "‚¿‚á‚ñ‚Æ“ü‚ê‚ë"
            },
            name: {
                required: "–¢“ü—Í",
                minlength: $.format("{0}•¶šˆÈã“ü‚ê‚ë")
            },
            busho: {
                required: "‚¿‚á‚ñ‚Æ‘I‚×"
            }
        }
    });
});
