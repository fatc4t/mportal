$(document).ready(function(){

    window.addEventListener('popstate', function(event) {
        changeContents(location.pathname);
    },false );


});

$(document).keydown(ivnt_keydown);

function ivnt_keydown(e) {
    // ESCAPE key pressed
    if (e.keyCode == 27) {
        cancel();
    }
}

/**
 * データ更新
 */
function setDataForAjax( data, path, replacementArea, async )
{
    console.log(data);
    // 書き換えエリアの指定なし
    if ( replacementArea == null)
    {
        // 一覧エリアの書き換え
        replacementArea = 'jquery-replace-ajax';
    }
    
    // 同期処理指定なし
    if ( async == null)
    {
        // 一覧エリアの書き換え
        async = true;
    }

    // リプレースエリアが、全画面更新時の時
    if( replacementArea == 'ajaxScreenUpdate' && (-1 != path.indexOf("addInput") || -1 != path.indexOf("csvoutput") || -1 != path.indexOf("datadelete"))  )
    {
        // submit用のFROMを作成
        var ajaxForm = document.createElement("form");
        
        for ( var paraName in data )
        {
            var q = document.createElement('input');
            q.type = 'hidden';
            q.name = paraName;
            q.value = data[paraName];
            ajaxForm.appendChild(q);
        }
        
        // データ更新
        ajaxForm.method = 'POST'; // method(GET or POST)を設定する
        ajaxForm.action = path; // action(遷移先URL)を設定する
        
        document.body.appendChild(ajaxForm);
        ajaxForm.submit(); // submit する
        return true;
    }

    // リプレースエリアが検索エリアの場合
    if( replacementArea == 'jquery-replace-ajax' && -1 != path.indexOf("search") )
    {
        document.getElementById("search_dialog").textContent = '検索中・・・';
        $("#search_dialog").dialog('open');
    }

    /**
     * Ajax通信メソッド
     * @param type  : HTTP通信の種類
     * @param url   : リクエスト送信先のURL
     * @param data  : サーバに送信する値
     */
    $.ajax({
        type: "POST",
        url: path,
        data: data,
        async: async,
        /**
         * Ajax通信が成功した場合に呼び出されるメソッド
         */
        success: function( data, dataType )
        {
            // successのブロック内は、Ajax通信が成功した場合に呼び出される
            // PHPから返ってきたデータの表示
            if( -1 != path.indexOf("show") || -1 != path.indexOf("print") || -1 != path.indexOf("lumpDayRetouch") || -1 != path.indexOf("lumpApproval") )
            {
                // 新規表示し直しの場合
                document.write(data);
                document.close();
            }
            else if( -1 == data.indexOf(replacementArea) )
            {
                // セキュリティエラーではない（ログイン画面のロゴの画像指定があるかで判断）
                if( -1 == data.indexOf("main-logo.png") )
                {
                    // データの更新に失敗
                    document.getElementById("dialog").textContent = data;
                    $("#dialog").dialog('open');
                }
                else
                {
                    // セキュリティエラーの場合、ログイン画面へ遷移する
                    document.write(data);
                    document.close();
                }
            }
            else
            {
                // 検索以外の更新である
//                if( -1 == path.indexOf("search") )
                if( -1 == path.indexOf("search") && -1 == path.indexOf("inputArea") && -1 == path.indexOf("datadelete"))
                {
                    if( 0 < path.indexOf("addInput") )
                    {
                        document.write(data);
                        document.close();
                        return;
                    }
                    if( 0 < path.indexOf("datadelete") )
                    {
                        document.write(data);
                        document.close();
                        return;
                    }                    
                    if( 0 < path.indexOf("csvoutput") )
                    {
                        document.write(data);
                        document.close();
                        return;
                    } 
                    if( 0 < data.indexOf('ajaxScreenUpdate') )
                    {
                        document.getElementById("dialog_redirect").textContent='データを更新しました。';
                        $("#dialog_redirect").dialog('open');
                        return;
                    }
                    else
                    {
                        document.getElementById("dialog").textContent='データを更新しました。';
                        $("#dialog").dialog('open');
                    }
                }

                // 検索ダイアログを閉じる
                $("#search_dialog").dialog('close');
                
                if( -1 != path.indexOf("searchMst0801") || -1 != path.indexOf("searchMst1101") || -1 != path.indexOf("searchMst1201") )
                {
                    // 通知ダイアログを閉じる
                    $("#notification_dialog").dialog('close');
                }

                $('#add').prop('disabled', false);
                var areaName = "#" + replacementArea;
                jQuery( areaName ).html( data );
            }
        },
        /**
         * Ajax通信が失敗した場合に呼び出されるメソッド
         */
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {
            //通常はここでtextStatusやerrorThrownの値を見て処理を切り分けるか、単純に通信に失敗した際の処理を記述します。

            var errMsg = 'Error : ' + errorThrown;
            //エラーメッセージの表示
            document.getElementById("dialog").textContent = errMsg;
            $("#dialog").dialog('open');
        }
    });
}

/**
 * 新規作成ダイアログ
 */
/*
$(function() {
    $( "#dialog_add" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
          "OK": function(){
              setAddData();
              $(this).dialog('close');
          },
          "戻る": function(){
              $(this).dialog('close');
          },
          "キャンセル": function(){
              cancel();
              $(this).dialog('close');
          },
        }
    });
});
*/
$(function() {
    $( "#dialog_add" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
          "OK": function(){
              setAddData();
              $(this).dialog('close');
          },
          "キャンセル": function(){
              $(this).dialog('close');
          },
        }
    });
});

/**
 * 更新確認用ダイアログ
 */
/*
$(function() {
    $( "#dialog_mod" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
          "OK": function(){
              setModData();
              $(this).dialog('close');
          },
          "戻る": function(){
              $(this).dialog('close');
          },
          "キャンセル": function(){
              cancel();
              $(this).dialog('close');
          },
        }
    });
});
*/
$(function() {
    $( "#dialog_mod" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
          "OK": function(){
              setModData();
              $(this).dialog('close');
          },
          "キャンセル": function(){
              $(this).dialog('close');
          },
        }
    });
});

/**
 * 確認ダイアログ
 */
$(function() {
    $( "#dialog" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
          "OK": function(){
              $(this).dialog('close');
          },
        }
    });
});


/**
 * リダイレクト確認ダイアログ
 */
$(function() {
    $( "#dialog_redirect" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
          "OK": function()
          {
              moveRedirect();
              $(this).dialog('close');
          },
        }
    });
});

/**
 * 検索ダイアログ
 */
$(function() {
    $( "#search_dialog" ).dialog({
        autoOpen: false,
        modal: true,
    });
});

/**
 * 通知ダイアログ
 */
$(function() {
    $( "#notification_dialog" ).dialog({
        autoOpen: false,
        modal: true,
    });
});

/**
 * 出力確認用ダイアログ
 */
$(function() {
    $( "#dialog_output" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
          "YES": function(){
              excAction();
              $(this).dialog('close');
          },
          "NO": function(){
              $(this).dialog('close');
          },
        }
    });
});

/**
 * フォームにhiddenを作成する
 */
function make_hidden( name, value, formname ){
    var q = document.createElement('input');
    q.type = 'hidden';
    q.name = name;
    q.value = value;
    if (formname)
    {
        document.forms[formname].appendChild(q);
    }
    else
    {
        document.forms[1].appendChild(q);
    }
}

/**
 * フォームからhiddenを削除する
 */
function remove_hidden( name, formname)
{
    var obj = document.getElementsByName(name)[0];
    if (formname)
    {
        document.forms[formname].removeChild(obj);
    }
    else
    {
        document.forms[1].removeChild(obj);
    }

}
