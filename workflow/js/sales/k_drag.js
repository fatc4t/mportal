      $(function() {
        var k_droppable = $("#k_droppable");

         // File API が使用できない場合
        if(!window.FileReader) {
          alert("File API がサポートされていません。");
          return false;
        }

         // イベントをキャンセルするハンドラ
        var cancelEvent = function(event) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        }

         // dragenter, dragover イベントのデフォルト処理をキャンセル
        k_droppable.bind("dradenter", cancelEvent);
        k_droppable.bind("dragover", cancelEvent);

         // ドロップ時のイベントハンドラを設定
        var handleDroppedFile = function(event) {

          // ファイルは複数ドロップされる可能性あり。まずは 1 つだけ処理
          var file = event.originalEvent.dataTransfer.files[0];

           // ファイルの内容は FileReader で読み込む
          var fileReader = new FileReader();
          fileReader.onload = function(event) {
            // event.target.result に読み込んだファイルの内容が入っている
            // ドラッグ＆ドロップでファイルアップロードする場合は result の内容を Ajax でサーバに送信
            $("#k_droppable").text("[" + file.name + "]" + event.target.result);
          }
          fileReader.readAsText(file);
           // デフォルトの処理をキャンセル
          cancelEvent(event);
          return false;
        }
         // ドロップ時のイベントハンドラを設定
        k_droppable.bind("drop", handleDroppedFile);
      });
