<?php
    
//DBアクセス
require_once("DBAccess_Function.php");
$prod =$_POST['prod'];
$jaiko =$_POST['jaiko'];
if ($prod === 'prod'){
  create_csv_string($data);
}

if ($jaiko === 'jaiko'){
  create_csv_string1($data);
}
//create_csv_string($data);

function create_csv_string($data) {
$jr =$_POST['jr'];
$fuji =$_POST['fuji'];
$withs =$_POST['withs'];

if ($jr === 'jrkusuri'){
   $schema ='jrkusuri'; 
}
if ($fuji === 'fuji'){
   $schema ='fuji'; 
}
if ($withs === 'withs'){
   $schema ='withs'; 
}
 $sql = "SELECT m1.organization_name,mst0201.prod_cd,mst0201.jan_cd,mst0201.prod_nm,mst0201.sect_cd,mst0201.prod_t_cd1"
         . ",mst1201.sect_nm, mst0801.prod_t_nm "
         . ",mst0201.head_supp_cd,mst0201.shop_supp_cd,mst0201.fixeprice ,mst0201.base_saleprice ,mst0201.head_costprice,mst0201.head_costprice,mst0201.saleprice" 
         . " FROM ".$schema.".mst0201 "
         . "left join ".$schema.".mst1201   using (organization_id,sect_cd) "
         . "left join ".$schema.".m_organization_detail m1  using (organization_id)"
         . " left join ".$schema.".mst0801  using (prod_t_cd1)  "
         . " where mst0201.organization_id <> -1 and mst0201.organization_id <> 1 "
         . "order by mst0201.organization_id  ";

    // SQLの実行
    $data = getList($sql);
    //$data = sqlExec($sql);
     $today = date("Ymd");       
    // ファイル名
    $file_path = "$today.csv";

    // CSVに出力するヘッダ行
    $export_csv_title = array();

    // 先頭固定ヘッダ追加
    array_push($export_csv_title, "店舗名");
    array_push($export_csv_title, "商品コード");
    array_push($export_csv_title, "JANコード");
    array_push($export_csv_title, "商品名 ");
//    array_push($export_csv_title, "商品名略 ");
//    array_push($export_csv_title, "商品カナ ");
//    array_push($export_csv_title, "商品カナ略 ");
//    array_push($export_csv_title, "商品容量名 ");
//    array_push($export_csv_title, "商品容量カナ ");
    array_push($export_csv_title, "部門コード");
    array_push($export_csv_title, "部門名");
    array_push($export_csv_title, "分類コード ");
//    array_push($export_csv_title, "商品中分類コード ");
//    array_push($export_csv_title, "商品小分類コード ");
//    array_push($export_csv_title, "メーカーコード ");
    array_push($export_csv_title, "本部仕入先コード ");
    array_push($export_csv_title, "店舗仕入先コード ");
    array_push($export_csv_title, "定価 ");
    array_push($export_csv_title, "基準売価 ");
    array_push($export_csv_title, "本部原価 ");
    array_push($export_csv_title, "店舗原価1 ");
    array_push($export_csv_title, "売価 ");
//    array_push($export_csv_title, "会員売価 ");
//    array_push($export_csv_title, "発注ロット ");
//    array_push($export_csv_title, "返品ロット ");
//    array_push($export_csv_title, "指定商品区分 ");
//    array_push($export_csv_title, "指定医薬品区分 ");
//    array_push($export_csv_title, "税種別区分 ");
//    array_push($export_csv_title, "発注停止区分 ");
//    array_push($export_csv_title, "返品不可区分 ");
//    array_push($export_csv_title, "得点区分 ");
//    array_push($export_csv_title, "自動発注区分 ");
//    array_push($export_csv_title, "リスク分類 ");
//    array_push($export_csv_title, "発注済区分 ");
//    array_push($export_csv_title, "直送区分 ");
//    array_push($export_csv_title, "税率 ");
//    array_push($export_csv_title, "スイッチOTC薬控除 ");

    if( touch($file_path) ){
        // オブジェクト生成
        $file = new SplFileObject( $file_path, "w" );$str = chr(239).chr(187).chr(191);$file = fopen($file_path, 'a');fwrite($file,$str); // add モンターニュ 2020/02/03
        // タイトル行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
        foreach( $export_csv_title as $key => $val ){
            $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8'); 

        }
        // エンコードしたタイトル行を配列ごとCSVデータ化
        $file = fopen($file_path, 'a');fwrite($file,implode(',',$export_csv_title)."\r");                

        foreach($data as $rows ){
            // 垂直ヘッダ
            $str = '"'.$rows['organization_name'];
            $str = $str.'","'.$rows['prod_cd'];
            $str = $str.'","'.$rows['jan_cd'];//商品名 
            $str = $str.'","'.$rows['prod_nm'];//商品名 
//            $str = $str.'","'.$rows['prod_kn_rk'];//商品名略 
//            $str = $str.'","'.$rows['prod_kn'];//商品カナ 
//            $str = $str.'","'.$rows['prod_kn_rk'];//商品カナ略 
//            $str = $str.'","'.$rows['prod_capa_nm'];//商品容量名 
//            $str = $str.'","'.$rows['prod_capa_kn'];//商品容量カナ 
            $str = $str.'","'.$rows['sect_cd'];//bumon_cd
            $str = $str.'","'.$rows['sect_nm'];//bumon_nm                    
            $str = $str.'","'.$rows['prod_t_cd1'];//商品大分類コード 
//            $str = $str.'","'.$rows['prod_t_cd2'];//商品中分類コード 
//            $str = $str.'","'.$rows['prod_t_cd3'];//商品小分類コード 
//            $str = $str.'","'.$rows['maker_cd'];//メーカーコード 
            $str = $str.'","'.$rows['head_supp_cd'];//本部仕入先コード 
            $str = $str.'","'.$rows['shop_supp_cd'];//店舗仕入先コード 
            $str = $str.'","'.$rows['fixeprice'];//定価 
            $str = $str.'","'.$rows['base_saleprice'];//基準売価 
            $str = $str.'","'.$rows['head_costprice'];//本部原価 
            $str = $str.'","'.$rows['head_costprice'];//店舗原価1 
            $str = $str.'","'.$rows['saleprice'];//売価 
//            $str = $str.'","'.$rows['cust_saleprice'];//会員売価 
//            $str = $str.'","'.$rows['order_lot'];//発注ロット 
//            $str = $str.'","'.$rows['return_lot'];//返品ロット 
//            $str = $str.'","'.$rows['appo_prod_kbn'];//指定商品区分 
//            $str = $str.'","'.$rows['appo_medi_kbn'];//指定医薬品区分 
//            $str = $str.'","'.$rows['tax_type'];//税種別区分 
//            $str = $str.'","'.$rows['order_stop_kbn'];//発注停止区分 
//            $str = $str.'","'.$rows['noreturn_kbn'];//返品不可区分 
//            $str = $str.'","'.$rows['point_kbn'];//得点区分 
//            $str = $str.'","'.$rows['auto_order_kbn'];//自動発注区分 
//            $str = $str.'","'.$rows['risk_type_kbn'];//リスク分類 
//            $str = $str.'","'.$rows['risk_type_kbn'];//発注済区分 
//            $str = $str.'","'.$rows['resale_kbn'];//直送区分 
//            $str = $str.'","'.$rows['prod_tax'];//税率 
//            $str = $str.'","'.$rows['switch_otc_kbn'];//スイッチOTC薬控除   

            // 配列に変換
            $str = $str.'"';                    

            // 内容行を1行ごとにCSVデータへ
            $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
                
        }
    }
    //ダウンロード
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers 
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=商品マスタ一覧表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
    header("Content-Transfer-Encoding: binary");
    readfile("$file_path");
    
       ("END create_csv_string");

}

function create_csv_string1($data) {
$jr =$_POST['jr'];
$fuji =$_POST['fuji'];
$withs =$_POST['withs'];

if ($jr === 'jrkusuri'){
   $schema ='jrkusuri'; 
}
if ($fuji === 'fuji'){
   $schema ='fuji'; 
}
if ($withs === 'withs'){
   $schema ='withs'; 
}
$sql = "select * from "
. "(select "
  . "m_organization_detail.organization_id as org_id "
  . ",m_organization_detail.organization_name as org_nm "
  . ",mst0204.prod_cd as prod_cd "
  . ",CASE WHEN mst0201.prod_nm = '' THEN mst0201.prod_kn ELSE mst0201.prod_nm END as prod_nm "
  . ",mst0201.prod_tax as tax "
  . ",mst0204.bb_date as bb_date "
  . ",CASE WHEN mst0201.prod_capa_nm = '' THEN mst0201.prod_kn ELSE mst0201.prod_capa_kn END as capa_amt "
  . ",mst0201.head_costprice as cost_price "
  . ",mst0201.saleprice as sale_price "
  . ",mst0204.endmon_amount as amount "
  . ",mst0201.head_costprice * mst0204.endmon_amount as et_amount "
  . ",mst0201.saleprice * mst0204.endmon_amount as endmon_amt "
  . "from (select * from ".$schema.".mst0204 where organization_id <> -1) as mst0204 "
  . "left join (select * from ".$schema.".mst0201 where organization_id <> -1 ) as mst0201 on(mst0204.prod_cd = mst0201.prod_cd and mst0204.organization_id = mst0201.organization_id ) "
  . "left join ".$schema.".m_organization_detail on (mst0201.organization_id = m_organization_detail.organization_id) "
  . ") as a "
  . "where amount <> 0 "
  . "and org_id != 4 "
  . "and prod_nm is not null "
  . "and org_id is not null "
  . "order by org_id, prod_cd "; 
 
    // SQLの実行
    $data = getList($sql);
    //$data = sqlExec($sql);
     $today = date("Ymd");       
    // ファイル名
    $file_path = "$today.csv";

    // CSVに出力するヘッダ行
    $export_csv_title = array();
//店舗id	店舗名	商品コード	商品名	税率 	賞味期限	 容量	 原単価	売単価	在庫数	原価金額	 売価金額

    // 先頭固定ヘッダ追加
    array_push($export_csv_title, "店舗id");
    array_push($export_csv_title, "店舗名");
    array_push($export_csv_title, "商品コード");
    array_push($export_csv_title, "商品名 ");
    array_push($export_csv_title, "税率 ");
    array_push($export_csv_title, "賞味期限 ");
    array_push($export_csv_title, "容量 ");
    array_push($export_csv_title, "原単価 ");
    array_push($export_csv_title, "売単価 ");
    array_push($export_csv_title, "在庫数");
    array_push($export_csv_title, "原価金額");
    array_push($export_csv_title, "売価金額 ");
    

    if( touch($file_path) ){
        // オブジェクト生成
        $file = new SplFileObject( $file_path, "w" );$str = chr(239).chr(187).chr(191);$file = fopen($file_path, 'a');fwrite($file,$str); // add モンターニュ 2020/02/03
        // タイトル行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
        foreach( $export_csv_title as $key => $val ){
            $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8'); 

        }
        // エンコードしたタイトル行を配列ごとCSVデータ化
        $file = fopen($file_path, 'a');fwrite($file,implode(',',$export_csv_title)."\r");                

        foreach($data as $rows ){
            // 垂直ヘッダ
            $str = '"'.$rows['org_id'];
            $str = $str.'","'.$rows['org_nm'];
            $str = $str.'","'.$rows['prod_cd'];
            $str = $str.'","'.$rows['prod_nm'];
            $str = $str.'","'.$rows['tax'];
            $str = $str.'","'.$rows['bb_date'];
            $str = $str.'","'.$rows['capa_amt'];
            $str = $str.'","'.$rows['cost_price'];
            $str = $str.'","'.$rows['sale_price'];
            $str = $str.'","'.$rows['amount'];
            $str = $str.'","'.$rows['et_amount'];                 
            $str = $str.'","'.$rows['endmon_amt'];

            // 配列に変換
            $str = $str.'"';                    
            // 内容行を1行ごとにCSVデータへ
            $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");   
        }
    }
    //ダウンロード
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers 
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=在庫数一覧表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
    header("Content-Transfer-Encoding: binary");
    readfile("$file_path");
        

    ("END create_csv_string");
}

 // create_csv_string1($data);

?>
