//�����N���G�C�g�𖳌��ɂ���
Dropzone.autoDiscover = false;
//DropZone��������^�O��ID����A�I�u�W�F�N�g���擾
var div_element = document.getElementById("uploadFile");
//DropZone�̃N���G�C�g���s��
var dropzone = new Dropzone(div_element, {
	url:"index.php?param=FileUpload/upload",
	paramName : "file",
	addRemoveLinks:true,
	maxFiles:1,
	dictDefaultMessage:"���̗̈�ɂЂȌ`�t�@�C�����h���b�O���h���b�v�A<br>�܂��͂��̗̈���N���b�N���Ă��������B",
	dictCancelUpload:'�L�����Z��',
	dictRemoveFile:"�폜",
	dictMaxFilesExceeded: "�Y�t�\�t�@�C�����́A1�t�@�C���ł��B",
	uploadprogress:function(_file, _progress, _size){
		_file.previewElement.querySelector("[data-dz-uploadprogress]").style.width = "" + _progress + "%";
	},
	//�t�@�C�����h���b�v�����ۂ̃C�x���g���X�i�[
	accept:function(file, done)
	{
		var obj = document.getElementsByName(file.name);
		//�I�u�W�F�N�g�̗v�f�������ď�����؂�ւ���
		if(obj.length == 0)
		{
			//sending�ɏ�����n��
			return done();
		}
		else
		{
			//�o�^�ς݃A���[�g��\��
			alert("���ɃA�b�v���[�h�Ώۂɓo�^����Ă���ׁA\n�A�b�v���[�h�ł��܂���B");
			//�Ώۂ�\���������
			(ref = file.previewElement) != null ? ref.parentNode.removeChild(file.previewElement) : void 0;
		}
	},
	//�t�@�C���̃A�b�v���[�h�����������ۂ̃C�x���g���X�i�[
	success:function(_upfile, _return, e){

		//�t�@�C�������g���q�Ȃ��Őݒ肷��
		var name = _upfile.name.split(".");
		//�Ώۂ�Hidden���ڃ^�O��ǉ�
		make_hidden( name[0], _upfile.name, "addForm");
		//Preview�G�������g�ɃZ�b�g
		_upfile.previewElement.classList.add("dz-success");
	},
	//�t�@�C�����폜�����ۂ̃C�x���g���X�i�[
	removedfile:function(_file){
		//�Ώۂ�\���������
		(ref = _file.previewElement) != null ? ref.parentNode.removeChild(_file.previewElement) : void 0;
		//�Ώۂ�Hidden���ڃ^�O���폜
		//�t�@�C�������g���q�Ȃ��Őݒ肷��
		var name = _file.name.split(".");
		remove_hidden( name[0], "addForm");
	}
});

//�t�@�C�����A�b�v���[�h����O�̃C�x���g���X�i�[
dropzone.on("sending", function(file, xhr, formData) {
	// Will send the filesize along with the file as POST data.
	formData.append("upfile", file);
});
