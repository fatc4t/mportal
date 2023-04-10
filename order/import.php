<?php
/**
 * @file      インポート用ファイル
 * @author    USE K.Kanda(media-craft.co.jp)
 * @date      2018/04/20
 * @version   1.00
 * @note      サーバーのcron等から定期的に呼び出すことを想定
 */
require_once './OrderDispatcher.php';
require_once SystemParameters::$FW_COMMON_PATH . 'Log.php';
//DBアクセス
require_once 'Model/Common/DBAccess.php';

class Import {
  //定数
  const CSVFLD = '/var/www/tmp/';

  /**
   * コンストラクタ
   */
  public function __construct()
  {
    global $DBA;  // グローバル変数宣言
    // DBAccessClassの初期化
    $DBA = new DBAccess();
    global $Log;    // グローバル変数宣言

    // Log4phpの初期化
    if( is_null( $Log ) )
    {
        $Log = new Log();
    }
  }

  /**
   * デストラクタ
   */
  public function __destruct()
  {
    global $DBA;  // グローバル変数宣言
    $DBA = null;
  }
  /**
   * インポート開始
   * @return   無し
   */
  public function startImport()
  {
    $fileList = $this->getFileList();

    foreach($fileList as $index => $file)
    {
      $type = $this->getFileType($file['file']);
      switch($type) {
          case "1":
            //商品データのインポート
            $this->syohinImport($file['file'],$file['ma_cd']);
            break;
          case "2":
            break;
          default:
            continue;
      }
    }
  }
  /**
   * 商品データのインポート
   * @param    $filename ファイル名 $ma_cd メーカーコード
   * @return   無し
   */
  private function syohinImport($filename,$ma_cd)
  {
    global $DBA;

    if(!file_exists($filename)) {
      return;
    }
    $file = file_get_contents($filename);
    $expArr = array("\r\n","\r","\n");
		$file = str_replace($expArr,"\n",mb_convert_encoding($file,"utf-8","SJIS-win"));
		$csvArr = explode("\n",$file);

    for($i=0;$i<count($csvArr);$i++)
    {
        //print($csvArr[$i]).'<Br>';

        //「,」で分解
        list($pha_id,$jan_cd,$pro_name,$youryo,$irisuu,$tanka,$gs1_code,$syosai,$min_lot,$url1,$sakujo_flg) = explode(",",$csvArr[$i]);

        //必須項目が空の場合は登録しない
        if($pha_id=="" || $jan_cd=="" || $pro_name=="" || $tanka=="" || $min_lot)
        {
          continue;
        }
        //削除フラグが空の場合は0->登録・変更
        if($sakujo_flg=="")
        {
          $sakujo_flg = '0';
        }
        //データが存在するかどうか
        $params = array(
           ":ma_cd"  => $ma_cd
          ,":pha_id" => $pha_id
          ,":jan_cd" => $jan_cd
        );

        $sql  = " SELECT ";
        $sql .= " * ";
        $sql .= " FROM ord_m_products";
        $sql .= " WHERE ma_cd=:ma_cd";
        $sql .= " AND   pha_id=:pha_id";
        $sql .= " AND   jan_cd=:jan_cd";

				$result = $DBA->executeSQL($sql,$params);
	      $row = $result->fetch(PDO::FETCH_ASSOC);
        if($row)
        {
          $pro_cd = $row['pro_cd'];

          $params = array(
             ":ma_cd"    => $ma_cd
            ,":pha_id"   => $pha_id
            ,":pro_cd"   => $pro_cd
            ,":jan_cd"   => $jan_cd
            ,":pro_name" => $pro_name
            ,":youryo"   => $youryo
            ,":irisuu"   => $this->changeZero($irisuu)
            ,":tanka"    => $this->changeZero($tanka)
            ,":is_del"   => $this->changeZero($is_del)
            ,":gs1_code" => $gs1_code
            ,":min_lot"  => $this->changeZero($min_lot)
            ,":syosai"   => $syosai
            ,":url1"     => $url1
          );

          $sql  = " UPDATE ord_m_products SET ";
          $sql .= "   jan_cd=:jan_cd";
          $sql .= "  ,pro_name=:pro_name";
          $sql .= "  ,youryo=:youryo";
          $sql .= "  ,irisuu=:irisuu";
          $sql .= "  ,tanka=:tanka";
          $sql .= "  ,update_time='".date("Y/m/d H:i:s")."'";
          $sql .= "  ,is_del=:is_del";
          $sql .= "  ,gs1_code=:gs1_code";
          $sql .= "  ,min_lot=:min_lot";
          $sql .= "  ,syosai=:syosai";
          $sql .= "  ,url1=:url1";
          $sql .= " WHERE ma_cd=:ma_cd";
          $sql .= " AND   pha_id=:pha_id";
          $sql .= " AND   pro_cd=:pro_cd";
          $result = $DBA->executeSQL($sql,$params);

        } else {
          //コード生成
          $sql  = " SELECT" ;
          $sql .= " max(pro_cd) pro_cd";
          $sql .= " FROM ord_m_products";
          $sql .= " WHERE ma_cd=:ma_cd";
          $sql .= " AND   pha_id=:pha_id";
          $params = array(
             ":ma_cd"  => $ma_cd
            ,":pha_id" => $pha_id
          );
          $result = $DBA->executeSQL($sql,$params);
  	      $row = $result->fetch(PDO::FETCH_ASSOC);
          $pro_cd = $row['pro_cd'] + 1;

          $sql  = " INSERT INTO ord_m_products ( ";
          $sql .= "  ma_cd";
          $sql .= " ,pha_id";
          $sql .= " ,pro_cd";
          $sql .= " ,jan_cd";
          $sql .= " ,pro_name";
          $sql .= " ,youryo";
          $sql .= " ,irisuu ";
          $sql .= " ,tanka";
          $sql .= " ,registration_time";
          $sql .= " ,is_del";
          $sql .= " ,gs1_code";
          $sql .= " ,min_lot";
          $sql .= " ,syosai";
          $sql .= " ,url1";
          $sql .= " ) VALUES ( ";
          $sql .= " :ma_cd";
          $sql .= " ,:pha_id";
          $sql .= " ,:pro_cd";
          $sql .= " ,:jan_cd";
          $sql .= " ,:pro_name";
          $sql .= " ,:youryo";
          $sql .= " ,:irisuu ";
          $sql .= " ,:tanka";
          $sql .= ",'".date("Y/m/d H:i:s")."'";
          $sql .= " ,:is_del";
          $sql .= " ,:gs1_code";
          $sql .= " ,:min_lot";
          $sql .= " ,:syosai";
          $sql .= " ,:url1";
          $sql .= ")";

          $params = array(
             ":ma_cd"    => $ma_cd
            ,":pha_id"   => $pha_id
            ,":pro_cd"   => $pro_cd
            ,":jan_cd"   => $jan_cd
            ,":pro_name" => $pro_name
            ,":youryo"   => $youryo
            ,":irisuu"   => $this->changeZero($irisuu)
            ,":tanka"    => $this->changeZero($tanka)
            ,":is_del"   => $this->changeZero($is_del)
            ,":gs1_code" => $gs1_code
            ,":min_lot"  => $this->changeZero($min_lot)
            ,":syosai"   => $syosai
            ,":url1"     => $url1
          );

          $result = $DBA->executeSQL($sql,$params);
        }
    }
  }
  /**
   * データタイプの判別
   * @param    $filename ファイル名
   * @return   データタイプ 1:商品データ 2:広告データ
   */
  private function getFileType($filename)
  {
    if($filename=="") {
      return '';
    }
    $arr = explode("/",$filename);
    $count = count($arr);
    $rtnFileName = $arr[$count-1];

    if(strstr($rtnFileName,'SYOHIN'))
    {
      return "1";
    } else if(strstr($rtnFileName,'CM'))
    {
      return "2";
    }
  }
  /**
   * ファイル一覧の取得
   * @return   配列
   */
  private function getFileList() {
    $dir_path = self::CSVFLD;

    $fileArr = array();
    if( is_dir ( $dir_path ) && $handle = opendir ( $dir_path ) )
    {
      while( ($dir = readdir($handle)) !== false )
      {
        if(is_numeric($dir))
        {
          //フォルダ内のCSVファイルを取得
          //foreach(glob($dir_path.$dir.'{*.csv,*.CSV}',GLOB_BRACE) as $file)
          if($handle2 = opendir($dir_path.$dir)) {
            while( ($file = readdir($handle2)) !== false )
            {
              if($file!="." && $file!="..")
              {
                $fileArr[] = array("file"=>$dir_path.$dir."/".$file,"ma_cd"=>$dir);
              }
            }
          }
        }
  		}
    }
    return $fileArr;
  }

  /**
   * 0変換
   * @param    $val　数値 or 文字列
   * @return   文字列
   */
  private function changeZero($val) {
    $rtnVal = $val;
    if($rtnVal=="") {
      $rtnVal = 0;
    }
    return $rtnVal;
  }
}

//インポートの開始
$import = new Import();

echo 'インポート開始<br>';

$import->startImport();

?>
