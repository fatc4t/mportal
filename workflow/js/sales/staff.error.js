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
                required: "�Ȃ񂩓��͂���",
                minlength: "�����Ɠ����"
            },
            password: {
                required: "������",
                minlength: "�����Ɠ����"
            },
            name: {
                required: "������",
                minlength: $.format("{0}�����ȏ�����")
            },
            busho: {
                required: "�����ƑI��"
            }
        }
    });
});
