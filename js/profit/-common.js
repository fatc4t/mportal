$(document).ready(function(){


});

/**
 * データ更新
 */
function setDataForAjax( data, path, replacementArea, async )
{
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
                if( -1 == path.indexOf("search") )
                {
                    if( 0 < path.indexOf("addInput") )
                    {
                        document.write(data);
                        document.close();
                        return;
                    }
                    if( 0 < data.indexOf('ajaxScreenUpdate') )
                    {
                        document.getElementById("dialog_redirect").textContent='データを更新致しました。';
                        $("#dialog_redirect").dialog('open');
                        return;
                    }
                    else
                    {
                        document.getElementById("dialog").textContent='データを更新致しました。';
                        $("#dialog").dialog('open');
                    }
                }
                $('#add').prop('disabled', false);
                var areaName = "#" + replacementArea;
                jQuery( areaName ) . html( data );
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

/**
 * 更新確認用ダイアログ
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
