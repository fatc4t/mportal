
$address = $argv[1];
$title = $argv[2];
$body = $argv[3];

require_once 'PHPMailer/PHPMailerAutoload.php';

$mailer = new PHPMailer();
$mailer->IsSMTP();
$mailer->Encoding = "7bit";
$mailer->CharSet = '"iso-2022-jp"';

$mailer->Host = 'smtp.gmail.com';
$mailer->Port = 587;
$mailer->SMTPAuth = TRUE;
$mailer->SMTPSecure = "tls";
$mailer->Username = 'saleswars.millionet@gmail.com'; // Gmail���O�C���A�h���X
$mailer->Password = 'kimi5869'; // Gmail���O�C���p�X���[�h

$mailer->From     = 'saleswars.millionet@gmail.com'; // From�A�h���X
$mailer->FromName = "M-PORTAL";

$mailer->Subject  = mb_encode_mimeheader($title);
$mailer->Body  = mb_convert_encoding($body, "ISO-2022-JP", "auto");
$mailer->AddAddress($address); // To�A�h���X


$mailer->Send();


