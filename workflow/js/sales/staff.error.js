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
                required: "なんか入力しろ",
                minlength: "ちゃんと入れろ"
            },
            password: {
                required: "未入力",
                minlength: "ちゃんと入れろ"
            },
            name: {
                required: "未入力",
                minlength: $.format("{0}文字以上入れろ")
            },
            busho: {
                required: "ちゃんと選べ"
            }
        }
    });
});
