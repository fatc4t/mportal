
<?php


$string = "3701804901070125616シャルダン　ステキプラスクルマ専用　ジャスミンマリアｼｬﾙﾀﾞﾝ ｽﾃｷﾌﾟﾗｽｸﾙﾏ ｼﾞ2131970080                 160330013701804902720123549森永ＩＱサポート　もも＆りんご　１２５ｍｌ×３　    ﾓﾘﾅｶﾞIQｻﾎﾟｰﾄ ﾓﾓ&ﾘﾝｺﾞ1901030080                 1603300133701804987072042557噛むブレスケア　マスカット　２５粒　                ﾌﾞﾚｽｹｱｶﾑ 25T ﾏｽｶｯﾄ  2121070080                 16033001388";
$data = '';
for ($i = 0; $i < 128; $i++) {
    $char = mb_substr($string, $i, 1, 'UTF-8');
    if (strlen($char) == 2) {
        $data .= $char;
    } else {
        break;
    }
}
echo $data;
echo $char, PHP_EOL;
echo "<br>asdasdasdasdas";


?>