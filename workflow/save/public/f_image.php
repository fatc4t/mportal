<?php

function f_image_min($input_file, $output_file)
{
// �o�͂���摜�T�C�Y�̎w��
$width = 100;
$height = 100;

// �T�C�Y���w�肵�āA�w�i�p�摜�𐶐�
$canvas = imagecreatetruecolor($width, $height);

// �R�s�[���摜�̎w��
$targetImage = $input_file;
// �t�@�C��������A�摜�C���X�^���X�𐶐�
$image = imagecreatefromjpeg($targetImage);
// �R�s�[���摜�̃t�@�C���T�C�Y���擾
list($image_w, $image_h) = getimagesize($targetImage);

if ($image_w > $width || $image_h > $height){

		// �w�i�摜�ɁA�摜���R�s�[����
		imagecopyresampled($canvas,  // �w�i�摜
		                   $image,   // �R�s�[���摜
		                   0,        // �w�i�摜�� x ���W
		                   0,        // �w�i�摜�� y ���W
		                   0,        // �R�s�[���� x ���W
		                   0,        // �R�s�[���� y ���W
		                   $width,   // �w�i�摜�̕�
		                   $height,  // �w�i�摜�̍���
		                   $image_w, // �R�s�[���摜�t�@�C���̕�
		                   $image_h  // �R�s�[���摜�t�@�C���̍���
		                  );

		// �摜���o�͂���
		imagejpeg($canvas,           	// �w�i�摜
		          $output_file,    	// �o�͂���t�@�C�����i�ȗ�����Ɖ�ʂɕ\������j
		          100                	// �摜���x
		         );
		         
}
// ���������J������
imagedestroy($canvas); 
}


?>
