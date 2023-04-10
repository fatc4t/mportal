//自動クリエイトを無効にする
Dropzone.autoDiscover = false;
//DropZone化させるタグのIDから、オブジェクトを取得
var div_element = document.getElementById("uploadFile");
//DropZoneのクリエイトを行う
var dropzone = new Dropzone(div_element, {
	url:"index.php?param=FileUpload/upload",
	paramName : "file",
	addRemoveLinks:true,
	maxFiles:1,
	dictDefaultMessage:"この領域にひな形ファイルをドラッグ＆ドロップ、<br>またはこの領域をクリックしてください。",
	dictCancelUpload:'キャンセル',
	dictRemoveFile:"削除",
	dictMaxFilesExceeded: "添付可能ファイル数は、1ファイルです。",
	uploadprogress:function(_file, _progress, _size){
		_file.previewElement.querySelector("[data-dz-uploadprogress]").style.width = "" + _progress + "%";
	},
	//ファイルをドロップした際のイベントリスナー
	accept:function(file, done)
	{
		var obj = document.getElementsByName(file.name);
		//オブジェクトの要素数を見て処理を切り替える
		if(obj.length == 0)
		{
			//sendingに処理を渡す
			return done();
		}
		else
		{
			//登録済みアラートを表示
			alert("既にアップロード対象に登録されている為、\nアップロードできません。");
			//対象を表示から消す
			(ref = file.previewElement) != null ? ref.parentNode.removeChild(file.previewElement) : void 0;
		}
	},
	//ファイルのアップロードが成功した際のイベントリスナー
	success:function(_upfile, _return, e){

		//ファイル名を拡張子なしで設定する
		var name = _upfile.name.split(".");
		//対象のHidden項目タグを追加
		make_hidden( name[0], _upfile.name, "addForm");
		//Previewエレメントにセット
		_upfile.previewElement.classList.add("dz-success");
	},
	//ファイルを削除した際のイベントリスナー
	removedfile:function(_file){
		//対象を表示から消す
		(ref = _file.previewElement) != null ? ref.parentNode.removeChild(_file.previewElement) : void 0;
		//対象のHidden項目タグを削除
		//ファイル名を拡張子なしで設定する
		var name = _file.name.split(".");
		remove_hidden( name[0], "addForm");
	}
});

//ファイルをアップロードする前のイベントリスナー
dropzone.on("sending", function(file, xhr, formData) {
	// Will send the filesize along with the file as POST data.
	formData.append("upfile", file);
});
