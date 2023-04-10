<?php
    /**
     * @file      帳票 - 棚卸一覧表
     * @author    millionet oota
     * @date      2020/05/18
     * @version   1.00
     * @note      帳票 - 棚卸一覧表の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';
    // $schema = 'withs';     

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetInventory  extends BaseModel
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

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayInventory(帳票フォームデータ)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );
            
            $result = $DBA->executeSQL($sql, $searchArray);

            $displayInventory = array();

            if( $result === false )
            {
                $Log->trace("END getListData");
                return $displayInventory;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayInventory, $data);
            }

            $Log->trace("END getListData");
            return $displayInventory;
        }
        
         //ADDSTR 2020/07/06 kanderu
 //count sql
        public function pageMaxNo($postArray, $searchArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START pageMaxNo");
            // 検索条件
            $where1  = "";  
            //$where1  .= " WHERE 1 = :val and TRN1702.organization_id  <> -1 AND substr(TRN1701.stkdec_date,0,7 ) = '202005'  ";
           $where1  .= " where  1 = :val AND TRN1702.organization_id  <> -1 and substr(TRN1701.stkdec_date,0,7 ) = '".str_replace("/","",$postArray['start_date_m'])."'  "; 
           //$where1  .= " where  1 = :val AND TRN1702.organization_id  <> -1 and substr(TRN1701.stkdec_date,0,5 ) = '".substr(str_replace("/","",$postArray['start_date_m']),0,5)."'  ";
            
            //  対象条件のデフォルト
            // 店舗指定の場合
            if( $postArray['organization_id'] !== 'compile' && $postArray['organization_id'] !== 'all')
            {
                $where1  .= " AND TRN1702.organization_id in (".$postArray['organization_id'].") ";
            }
            
            // 商品指定の場合
            if( $postArray['prod_cd'] !== 'false' )
            {
                $where1  .= " AND TRN1702.prod_cd in (".$postArray['prod_cd'].") ";
            }
            
            // 部門指定の場合
            if( $postArray['sect_cd'] !== 'false' )
            {
                $where1  .= " AND TRN1702.sect_cd in (".$postArray['sect_cd'].") ";
            }

            // 分類指定の場合
            if( $postArray['prodclass_cd'] !== 'false' )
            {
                $where1  .= " AND mst0201.prod_t_cd1 in (".$postArray['prodclass_cd'].") ";
            }

            // 仕入先指定の場合
            if( $postArray['supp_cd'] !== 'false' )
            {
                $where1  .= " AND TRN1702.supp_cd in (".$postArray['supp_cd'].") ";
            }
            
            // 棚番指定の場合
            if( $postArray['amount_str'] !== '' or $postArray['amount_end'] !== '' )
            {
                // 棚番(初回)(最終)(その他)のそれぞれをターゲットにする
                // 棚番(初回)
                $where1  .= " AND ((TRN1702.shelf_no_fst >= '".$postArray['amount_str']."'";
                $where1  .= "      AND TRN1702.shelf_no_fst <= '".$postArray['amount_end']."')";

                // 棚番(最終)
                $where1  .= " OR  ( TRN1702.shelf_no_lst >= '".$postArray['amount_str']."'";
                $where1  .= "      AND TRN1702.shelf_no_lst <= '".$postArray['amount_end']."')";

                // 棚番(その他)
                $where1  .= " OR  ( TRN1702.shelf_no_otr >= '".$postArray['amount_str']."'";
                $where1  .= "      AND TRN1702.shelf_no_otr <= '".$postArray['amount_end']."'))";
            }

            // 在庫差異あり商品
            if ($postArray['amount_kbn'] === '1'){
                $where1  .= " AND (TRN1702.fin_amount - TRN1702.rea_amount) <> 0";
            }else if ($postArray['amount_kbn'] === '2'){
                $where1  .= " AND (TRN1702.fin_amount - TRN1702.rea_amount) = 0";
            }
            
            // 在庫ゼロ商品
            if ($postArray['zero_kbn'] === '1'){
                $where1  .= " AND TRN1702.rea_amount <> 0";
            }else if ($postArray['zero_kbn'] === '2'){
                $where1  .= " AND TRN1702.rea_amount = 0 ";
            }

            /*************** SQL本体 START **************************************************************************/
            $query  = "  SELECT  count(*) as rownos ";
            $query  .= "          FROM TRN1702 ";
            $query  .= "          left join TRN1701 on (TRN1701.hideseq = TRN1702.hideseq and TRN1701.organization_id = TRN1702.organization_id) ";
            $query  .= "          left join MST0201 on (MST0201.prod_cd = TRN1702.prod_cd and MST0201.organization_id = TRN1702.organization_id) ";
//            $query  .= "          left join MST0200 as honbu on (honbu.prod_cd = TRN1702.prod_cd ) ";
            $query  .= "          left join MST1101 on (MST1101.supp_cd = TRN1702.supp_cd and MST1101.organization_id = TRN1702.organization_id) ";
            $query  .= "          left join MST1201 on (MST1201.sect_cd = TRN1702.sect_cd and MST1201.organization_id = TRN1702.organization_id) ";
            $query  .= "          left join (select * from MST0801 where prod_t_cd2 = '' and prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_1 on (MST0801_1.prod_t_cd1 = MST0201.prod_t_cd1 and MST0801_1.organization_id = MST0201.organization_id) ";
            $query  .= "          left join (select * from MST0801 where prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_2 on (MST0801_2.prod_t_cd1 = MST0201.prod_t_cd1 and MST0801_2.prod_t_cd2 = MST0201.prod_t_cd2 and MST0801_2.organization_id = MST0201.organization_id) ";
            $query  .= "          left join (select * from MST0801 where prod_t_cd4 = '' ) as MST0801_3 on (MST0801_3.prod_t_cd1 = MST0201.prod_t_cd1 and MST0801_3.prod_t_cd2 = MST0201.prod_t_cd2 and MST0801_3.prod_t_cd3 = MST0201.prod_t_cd3 and MST0801_3.organization_id = MST0201.organization_id) ";
            $query  .= "          left join (select * from MST0801 where prod_t_cd4 != '' ) as MST0801_4 on (MST0801_4.prod_t_cd1 = MST0201.prod_t_cd1 and MST0801_4.prod_t_cd2 = MST0201.prod_t_cd2 and MST0801_4.prod_t_cd3 = MST0201.prod_t_cd3 and MST0801_4.prod_t_cd4 = MST0201.prod_t_cd4 and MST0801_4.organization_id = MST0201.organization_id) ";
            $query  .= "          left join MST5401 on (MST5401.jicfs_class_cd = TRN1702.jicfs_class_cd and MST5401.organization_id = TRN1702.organization_id) ";
            $query  .= "          left join MST5501 on (MST5501.priv_class_cd = TRN1702.priv_class_cd and MST5501.organization_id = TRN1702.organization_id) ";
            $query  .= "          left join v_organization v on (v.organization_id = TRN1702.organization_id) ";
           // $sql  .= "          WHERE 1 = :val AND TRN1702.organization_id  <> -1  AND substr(TRN1701.stkdec_date,0,7 ) = '202005' ";
            $query .= " $where1 ";
            $searchArray[':val'] = 1 ;
           // print_r($query);
           // print_r($searchArray);
            $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($query, $searchArray);
            
            // 一覧表を格納する空の配列宣言

            //print_r($result);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END pageMaxNo");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }

            $Log->trace("END pageMaxNo");

            return $Datas;
        }
        
 //ADDEND 2020/07/06 kanderu    
        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
 private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            //print_r($_POST);
            // 検索条件
            $where  = "";

            //  対象条件のデフォルト
            $where .= " WHERE TRN1702.organization_id  <> -1 AND substr(TRN1701.stkdec_date,0,7 ) = '".str_replace("/","",$postArray['start_date_m'])."'  ";

            // 店舗指定の場合
            if( $postArray['organization_id'] !== 'compile' && $postArray['organization_id'] !== 'all')
            {
                $where .= " AND TRN1702.organization_id in (".$postArray['organization_id'].") ";
            }
            
            // 商品指定の場合
            if( $postArray['prod_cd'] !== 'false' )
            {
                $where .= " AND TRN1702.prod_cd in (".$postArray['prod_cd'].") ";
            }
            
            // 部門指定の場合
            if( $postArray['sect_cd'] !== 'false' )
            {
                $where .= " AND TRN1702.sect_cd in (".$postArray['sect_cd'].") ";
            }

            // 分類指定の場合
            if( $postArray['prodclass_cd'] !== 'false' )
            {
                $where .= " AND mst0201.prod_t_cd1 in (".$postArray['prodclass_cd'].") ";
            }

            // 仕入先指定の場合
            if( $postArray['supp_cd'] !== 'false' )
            {
                $where .= " AND TRN1702.supp_cd in (".$postArray['supp_cd'].") ";
            }
            
            // 棚番指定の場合
            if( $postArray['amount_str'] !== '' or $postArray['amount_end'] !== '' )
            {
                // 棚番(初回)(最終)(その他)のそれぞれをターゲットにする
                // 棚番(初回)
                $where .= " AND ((TRN1702.shelf_no_fst >= '".$postArray['amount_str']."'";
                $where .= "      AND TRN1702.shelf_no_fst <= '".$postArray['amount_end']."')";

                // 棚番(最終)
                $where .= " OR  ( TRN1702.shelf_no_lst >= '".$postArray['amount_str']."'";
                $where .= "      AND TRN1702.shelf_no_lst <= '".$postArray['amount_end']."')";

                // 棚番(その他)
                $where .= " OR  ( TRN1702.shelf_no_otr >= '".$postArray['amount_str']."'";
                $where .= "      AND TRN1702.shelf_no_otr <= '".$postArray['amount_end']."'))";
            }

            // 在庫差異あり商品
            if ($postArray['amount_kbn'] === '1'){
                $where .= " AND (TRN1702.fin_amount - TRN1702.rea_amount) <> 0";
            }else if ($postArray['amount_kbn'] === '2'){
                $where .= " AND (TRN1702.fin_amount - TRN1702.rea_amount) = 0";
            }
            
            // 在庫ゼロ商品
            if ($postArray['zero_kbn'] === '1'){
                $where .= " AND TRN1702.rea_amount <> 0";
            }else if ($postArray['zero_kbn'] === '2'){
                $where .= " AND TRN1702.rea_amount = 0 ";
            }

            /*************** SQL本体 START **************************************************************************/
            $sql  = " SELECT ";
            
            // 企業計以外かで対応
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "    v.organization_id ";
                $sql .= "   ,v.abbreviated_name ";
            }
            $sql .= "   ,TRN1702.shelf_no_fst ";
            $sql .= "   ,TRN1702.shelf_no_lst ";
            $sql .= "   ,TRN1702.shelf_no_otr ";
            
            // 賞味期限表示
            if ($postArray['bb_date_kbn'] === '0' && $postArray['bb_date_dspkbn'] === '0'){
                $sql .= "   ,CASE WHEN COALESCE(TRN1702.bb_date, '') = '' THEN '' ";
                $sql .= "         ELSE TO_CHAR(TO_DATE(TRN1702.bb_date, 'YYYYMMDD'), 'YYYY/MM/DD') ";
                $sql .= "    END AS bb_date ";
            }
            $sql .= "   ,TRN1702.prod_cd ";
            $sql .= "   ,MST0201.prod_nm ";
            $sql .= "   ,MST0201.prod_tax ";
            $sql .= "   ,TRN1702.supp_cd ";
            $sql .= "   ,MST1101.supp_nm ";
            $sql .= "   ,TRN1702.sect_cd ";
            $sql .= "   ,MST1201.sect_nm ";
            $sql .= "   ,MST0201.prod_t_cd1 ";
            $sql .= "   ,MST0801_1.prod_t_nm as prod_t_nm1 ";
            $sql .= "   ,MST0201.prod_t_cd2 ";
            $sql .= "   ,MST0801_2.prod_t_nm as prod_t_nm2 ";
            $sql .= "   ,MST0201.prod_t_cd3 ";
            $sql .= "   ,MST0801_3.prod_t_nm as prod_t_nm3 ";
            $sql .= "   ,MST0201.prod_t_cd4 ";
            $sql .= "   ,MST0801_4.prod_t_nm as prod_t_nm4 ";
            $sql .= "   ,TRN1702.priv_class_cd ";
            $sql .= "   ,MST5501.priv_class_nm ";
            $sql .= "   ,TRN1702.jicfs_class_cd ";
            $sql .= "   ,MST5401.jicfs_class_nm ";
            $sql .= "   ,TRN1702.rea_amount ";
            $sql .= "   ,TRN1702.fin_amount - TRN1702.rea_amount as difference ";
            $sql .= "   ,MST0201.head_costprice ";
            $sql .= "   ,TRN1702.costprice ";
            // 売価単価フラグ
            if ($postArray['saleprice_kbn'] === '0' ){
                // 税込
                $sql .= "   ,TRN1702.saleprice_ex as saleprice";
                $sql .= "   ,TRN1702.rea_amount * TRN1702.saleprice_ex as saletotal ";
            }else{
                // 税抜
                $sql .= "   ,TRN1702.saleprice  as saleprice";
                $sql .= "   ,TRN1702.rea_amount * TRN1702.saleprice as saletotal ";
            }
            $sql .= "   ,TRN1702.rea_amount * MST0201.head_costprice as costtotal_honbu ";
            $sql .= "   ,TRN1702.rea_amount * TRN1702.costprice as costtotal ";
            $sql .= "   ,TRN1702.rea_amount * MST0201.head_costprice as cost_difference ";
            $sql .= "   ,(TRN1702.rea_amount * MST0201.head_costprice) - (TRN1702.rea_amount * TRN1702.costprice) as actual_shelf_difference ";
            $sql .= "   FROM TRN1702 ";
            $sql .= "   left join TRN1701 on (TRN1701.hideseq = TRN1702.hideseq and TRN1701.organization_id = TRN1702.organization_id) ";
            $sql .= "   left join MST0201 on (MST0201.prod_cd = TRN1702.prod_cd and MST0201.organization_id = TRN1702.organization_id) ";
//            $sql .= "   left join MST0200 as honbu on (honbu.prod_cd = TRN1702.prod_cd ) ";
            $sql .= "   left join MST1101 on (MST1101.supp_cd = TRN1702.supp_cd and MST1101.organization_id = TRN1702.organization_id) ";
            $sql .= "   left join MST1201 on (MST1201.sect_cd = TRN1702.sect_cd and MST1201.organization_id = TRN1702.organization_id) ";
            $sql .= "   left join (select * from MST0801 where prod_t_cd2 = '' and prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_1 on (MST0801_1.prod_t_cd1 = MST0201.prod_t_cd1 and MST0801_1.organization_id = MST0201.organization_id) ";
            $sql .= "   left join (select * from MST0801 where prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_2 on (MST0801_2.prod_t_cd1 = MST0201.prod_t_cd1 and MST0801_2.prod_t_cd2 = MST0201.prod_t_cd2 and MST0801_2.organization_id = MST0201.organization_id) ";
            $sql .= "   left join (select * from MST0801 where prod_t_cd4 = '' ) as MST0801_3 on (MST0801_3.prod_t_cd1 = MST0201.prod_t_cd1 and MST0801_3.prod_t_cd2 = MST0201.prod_t_cd2 and MST0801_3.prod_t_cd3 = MST0201.prod_t_cd3 and MST0801_3.organization_id = MST0201.organization_id) ";
            $sql .= "   left join (select * from MST0801 where prod_t_cd4 != '' ) as MST0801_4 on (MST0801_4.prod_t_cd1 = MST0201.prod_t_cd1 and MST0801_4.prod_t_cd2 = MST0201.prod_t_cd2 and MST0801_4.prod_t_cd3 = MST0201.prod_t_cd3 and MST0801_4.prod_t_cd4 = MST0201.prod_t_cd4 and MST0801_4.organization_id = MST0201.organization_id) ";
            $sql .= "   left join MST5401 on (MST5401.jicfs_class_cd = TRN1702.jicfs_class_cd and MST5401.organization_id = TRN1702.organization_id) ";
            $sql .= "   left join MST5501 on (MST5501.priv_class_cd = TRN1702.priv_class_cd and MST5501.organization_id = TRN1702.organization_id) ";
            $sql .= "   left join v_organization v on (v.organization_id = TRN1702.organization_id) ";
            $sql .= $where;

            /*************** SQL本体 END **************************************************************************/

//            if ($postArray['organization_id'] !== 'compile'){
//                $sql .= "   GROUP BY ";
//                $sql .= "    TRN1702.organization_id ";
//                $sql .= "   ,v.abbreviated_name, ";
//            }

            // 表示順設定
            $sql .= "   ORDER BY ";
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "    TRN1702.organization_id, ";
            }
            
            $sql .= "    TRN1702.prod_cd ";
            if ($postArray['bb_date_kbn'] === '0' && $postArray['bb_date_dspkbn'] === '0'){
               $sql .= "   ,TRN1702.bb_date ";
            }
//ADDSTR 2020/06 kanderu*************************************************************************
            $sql .= " limit ".$postArray['limit'];
            $sql .= " offset ".$postArray['offset'];
//ADDEND 2020/06 kanderu*************************************************************************
         //  print_r($sql);
//            print_r($searchArray);
            $Log->trace("END creatSQL");
           // echo $sql;
            return $sql;
        }
    
        
                /**
         * POS予約商品マスタ一覧画面一覧表
         * @param    $postArray    入力パラメータ
         * @param    $effFlag      状態フラグ
         * @param    $statusFlag   在籍状況フラグ
         * @return   成功時：$userDataList 失敗：無
         */
        public function getListDataCnt( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            // 一覧検索用のSQL文と検索条件が入った配列の生成
            $searchArray = array();
            $sql = $this->creatSQLCnt( $postArray, $searchArray );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 件数を格納する
            $cnt = 0;

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $cnt;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $cnt = $cnt + $data["count"];
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $cnt;
        }
        
        /**
         * システムマスタ：賞味期限利用区分判定
         * @param    なし
         * @return   0:利用する / 1:利用しない
         */
        public function chk_BB_DATE_KBN()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START chk_BB_DATE_KBN");

            $searchArray = array();
            $sql  = "";
            $sql .= " SELECT DISTINCT bb_date_kbn FROM mst0010 WHERE disabled = '0' ";

            $result = $DBA->executeSQL($sql, $searchArray);

            if( $result === false )
            {
                $Log->trace("END chk_BB_DATE_KBN");
                return '1';     // 利用しない7
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if ($data['bb_date_kbn'] === '0'){
                    $Log->trace("END chk_BB_DATE_KBN");
                    return '0';     // 利用する
                }
            }

            $Log->trace("END chk_BB_DATE_KBN");
            return '1';     // 利用しない
        }
    }
?>
