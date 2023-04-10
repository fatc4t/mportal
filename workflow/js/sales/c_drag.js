      $(function() {
        var c_droppable = $("#c_droppable");

         // File API ���g�p�ł��Ȃ��ꍇ
        if(!window.FileReader) {
          alert("File API ���T�|�[�g����Ă��܂���B");
          return false;
        }

         // �C�x���g���L�����Z������n���h��
        var cancelEvent = function(event) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        }

         // dragenter, dragover �C�x���g�̃f�t�H���g�������L�����Z��
        c_droppable.bind("dradenter", cancelEvent);
        c_droppable.bind("dragover", cancelEvent);

         // �h���b�v���̃C�x���g�n���h����ݒ�
        var handleDroppedFile = function(event) {

          // �t�@�C���͕����h���b�v�����\������B�܂��� 1 ��������
          var file = event.originalEvent.dataTransfer.files[0];

           // �t�@�C���̓��e�� FileReader �œǂݍ���
          var fileReader = new FileReader();
          fileReader.onload = function(event) {
            // event.target.result �ɓǂݍ��񂾃t�@�C���̓��e�������Ă���
            // �h���b�O���h���b�v�Ńt�@�C���A�b�v���[�h����ꍇ�� result �̓��e�� Ajax �ŃT�[�o�ɑ��M
            $("#c_droppable").text("[" + file.name + "]" + event.target.result);
          }
          fileReader.readAsText(file);
           // �f�t�H���g�̏������L�����Z��
          cancelEvent(event);
          return false;
        }
         // �h���b�v���̃C�x���g�n���h����ݒ�
        c_droppable.bind("drop", handleDroppedFile);
      });
