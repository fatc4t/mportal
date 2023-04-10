<?php
    /**
     * @file      帳票 - 店間移動明細表
     * @author    millionet bhattarai
     * @date      2020/07/09
     * @version   1.00
     * @note      帳票 - 店間移動明細表の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetInterShopTransfer extends BaseModel
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // ModelBaseのデストラクタ
            parent::__destruct();
        }  
        public function get_data($joken)
       {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_data");
            //print_r($joken);
            $searchArray = array();
            
            $where  = "";
            //検索条件店舗コード
            if( $joken['org_id'] !== 'false')
            {
                if( $joken['org_select'] === 'empty'){
                    $where .= " AND TRN1401.ORGANIZATION_ID in (".$joken['org_id'].")   ";
                }else{
                    $where .= " AND TRN1401.ORGANIZATION_ID in ('".$joken['org_select']."')  ";
                }
            }
            //検索条件仕入先コード
            if( $joken['supp_cd'] !== 'false')
            {
                if( $joken['supp_select'] === 'empty'){
                    $where .= " AND TRN1401.SUPP_CD  in (".$joken['supp_cd'].")   ";
                }else{
                    $where .= " AND TRN1401.SUPP_CD  in ('".$joken['supp_select']."')  ";
                }
            }
            //検索条件商品コード
            if( $joken['prod_cd'] !== 'false')
            {
                if( $joken['prod_select'] === 'empty'){
                    $where .= " AND TRN1402.PROD_CD in (".$joken['prod_cd'].")   ";
                }else{
                    $where .= " AND TRN1402.PROD_CD in ('".$joken['prod_select']."')  ";
                }
            }
            
            //検索条件出力区分
            if($joken['fin_kbn'] == '0'){
                $where .= " AND TRN1401.FIN_KBN = '0'  ";
            }
            if($joken['fin_kbn'] == '1'){
                $where .= " AND TRN1401.FIN_KBN = '1' ";
            }
            if($joken['fin_kbn'] == '3'){
                $where .= " ";
            }
            
            $sql .= "SELECT ";
            $sql .= "  TRN1401.PROC_DATE                     AS MOVEDATE       ";//移動日
            $sql .= "  ,v.abbreviated_name                   AS FROM_SHOP_NM   ";//移動元店舗コード
            $sql .= "  ,TRN1401.ORGANIZATION_ID              AS ORG_ID         ";//店舗コード
            $sql .= "  ,TRN1401.SUPP_CD                      AS SUPP_CD        ";//仕入先コード
            $sql .= "  ,MST1101.SUPP_NM                      AS TO_SHOP_NM     ";//移動元店舗名
            $sql .= "  ,TRN1401.PROC_KBN                     AS PROC_KBN       ";//入出庫区分
            $sql .= "  ,TRN1402.PROD_CD                      AS PROC_CD        ";//移動先店舗コード
            $sql .= "  ,MST0201.PROD_NM                      AS PROD_NM        ";//商品名
            $sql .= "  ,MST0201.PROD_CAPA_NM                 AS PROD_CAPA_NM   ";//商品容量
            $sql .= "  ,ABS(TRN1402.AMOUNT)                  AS AMOUNT         ";//入庫数量
            $sql .= "  ,TRN1402.UNITPRICE                    AS UNITPRICE      ";//単価
            $sql .= "  ,CASE   ";
            $sql .= "      WHEN TRN1401.GYOMU_DATA_KBN = '00' then '画面作成'   ";//業務データ作成区分00の場合は画面生成を取得
            $sql .= "      WHEN TRN1401.GYOMU_DATA_KBN = '02' then 'ハンディー' ";//業務データ作成区分02の場合はハンディを取得
            $sql .= "      WHEN TRN1401.GYOMU_DATA_KBN = '03' then 'オンライン' ";//業務データ作成区分03の場合はオンラインを取得
            $sql .= "  ELSE '' END                            AS INPUT_FORM    ";//四つでもない場合は空を取得
            $sql .= "  ,CASE ";
            $sql .= "      WHEN TRN1401.FIN_KBN = '0' then '' ELSE '確定' END AS RECEIPT_CONF  ";//棚卸確定区分が0の場合空を取得0じゃない場合確定取得
            $sql .= "  ,(TRN1402.UNITPRICE * ABS(TRN1402.AMOUNT)) AS RECEIPT_ACCOUNT ";//入庫金額
            //入出庫伝票
            $sql .= "FROM TRN1401 ";
            //組織IDとレコード番号を使用して入出庫明細と内部結合
            $sql .= "	LEFT JOIN TRN1402 ON (TRN1402.ORGANIZATION_ID = TRN1401.ORGANIZATION_ID AND TRN1401.HIDESEQ = TRN1402.HIDESEQ ) ";
            //組織IDと仕入先コードを使用して仕入先マスタと内部結合
            $sql .= "	LEFT JOIN MST1101 ON (MST1101.ORGANIZATION_ID = TRN1401.ORGANIZATION_ID AND TRN1401.SUPP_CD = MST1101.SUPP_CD ) ";
            //組織IDと商品コードを使用して商品マスタと左結合
            $sql .= "   LEFT  JOIN MST0201 ON (MST0201.ORGANIZATION_ID = TRN1401.ORGANIZATION_ID AND TRN1402.PROD_CD = MST0201.PROD_CD)  ";
            //組織IDを使用して組織マスタと左結合
            $sql .= "   LEFT  JOIN m_organization_detail v ON (TRN1401.ORGANIZATION_ID = v.ORGANIZATION_ID) ";
            //条件
            $sql .= "WHERE  ";
            /*画面で選択された日付条件*/
            $sql .= "	TRN1401.PROC_DATE between :start_date and :end_date ";
            $sql .= "	AND TRN1401.CONNECT_KBN           =  '0'  ";//送信区分0のみ
            $sql .= "   AND TRN1401.GYOMU_DATA_KBN       <>  '01' ";//業務データ作成区分を00:画面作成/02:ハンディ/03:オンラインのみ 
            $sql .= "	AND MST1101.SUPP_KBN              =  '0'  ";//仕入先区分0（社内）のみ
            $sql .= "   AND TRN1401.PROC_KBN             <>  '11'  ";
            $sql .= $where;         
            $sql .= "order by ";
            $sql .= "        TRN1401.PROC_DATE ";
            $sql .= "       ,TRN1401.HIDESEQ   ";
            $sql .= "       ,TRN1402.LINE_NO   ";          
            $searchArray[':start_date'] = str_replace('/','',$joken['start_date']);
            $searchArray[':end_date']  = str_replace('/','',$joken['end_date']); 
            //print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納ad
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $Datas[$data['movedate']][$data['from_shop_nm']][$data['to_shop_nm']][$data['prod_cd']][] = $data;
            }

            $Log->trace("END get_data");

            return $Datas;
        }
    }   
?>
