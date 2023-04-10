$(document).ready(function(){


});

/**
 * データ更新
 */
function setDataForAjax( data, path )
{
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
        /**
         * Ajax通信が成功した場合に呼び出されるメソッド
         */
        success: function(data, dataType)
        {
            // successのブロック内は、Ajax通信が成功した場合に呼び出される
            // PHPから返ってきたデータの表示

            if( -1 == data.indexOf("jquery-replace-ajax") )
            {
                // データの更新に失敗
                //alert(data);
            }
            else
            {
                // 検索以外の更新である
                if( -1 == path.indexOf("search") )
                {
                    alert("※データを更新致しました。");
                }
                $('#add').prop('disabled', false);
                jQuery( '#jquery-replace-ajax' ) . html( data );
            }
        },
        /**
         * Ajax通信が失敗した場合に呼び出されるメソッド
         */
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {
            //通常はここでtextStatusやerrorThrownの値を見て処理を切り分けるか、単純に通信に失敗した際の処理を記述します。

            //エラーメッセージの表示
            alert('※エラー : ' + errorThrown);
        }
    });
}

/**
 * カスタムダイアログ
 */
function customDialog( title, msg )
{
    $.confirm({
      'title'     : title,
      'message'   : msg,
      'buttons'   : {
         'OK': function() {
           //OKボタンの処理
           $(this).dialog('close');
         },
         '戻る': function() {
           //戻るボタンの処理
           $(this).dialog('close');
         },
         'キャンセル': function() {
           //キャンセルボタンの処理
           $(this).dialog('close');
         }
      }
   });
}
