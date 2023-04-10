<?php
    /**
     * @file      帳票 - 販売員別選定品(税込)
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 販売員別選定品(税込)の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetBySalesStaffSelectedItem extends BaseModel
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
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getFormListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );
            //print_r('$sql: '.$sql);
            //print_r($searchArray);
            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getListData");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getListData");
            return $displayItemDataList;
        }

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $where = " WHERE  (t.cancel_kbn != '1' AND t2.stop_kbn != '1' AND t2.proc_date is not null AND t2.proc_date != '')";
            //$where .= " AND   (t2.staff_cd is not null AND t2.staff_cd != '')";
            if( !empty( $postArray['start_date'] ) )
            {
                $where .= ' AND date(t2.proc_date) >= date(:start_date) ';
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= ' AND date(t2.proc_date) <= date(:end_date) ';
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if(  $postArray['organization_id'] !== 'false' )
            {
                $where .= ' AND t.organization_id in ( '.$postArray['organization_id'].' )';
            }

            if( $postArray['prod_cd'] !== 'false' )
            {
                $where .= ' AND t.prod_cd in ( '.$postArray['prod_cd'].' )';
            }
            
            if(  $postArray['sect_cd'] !== 'false' )
            {
                $where .= ' AND t.sect_cd in ( '.$postArray['sect_cd'].' )';
            }
            
            if( $postArray['staff_cd'] !== 'false' )
            {
                $where .= ' AND t2.staff_cd in ( '.$postArray['staff_cd'].' )';
            }
            
       /*    指定商品区分を選択するコード
        if($_POST['radio'] == '0'){
            $where .= " AND mst0201.appo_prod_kbn = '0' ";}

            if($_POST['radio'] == '1'){
            $where .= " AND mst0201.appo_prod_kbn = '1' ";}

            if($_POST['radio'] == '2'){
            $where .= " AND mst0201.appo_prod_kbn = '2' ";}

            if($_POST['radio'] == '3'){
            $where .= " AND mst0201.appo_prod_kbn = '3' ";}
            
            if($_POST['radio'] == '4'){
            $where .= " AND mst0201.appo_prod_kbn = '4' ";}

            if($_POST['radio'] == '5'){
            $where .= " AND mst0201.appo_prod_kbn = '5' ";}

            if($_POST['radio'] == '6'){
            $where .= " AND mst0201.appo_prod_kbn = '6' ";}

            if($_POST['radio'] == '7'){
            $where .= " AND mst0201.appo_prod_kbn = '7' ";}

            if($_POST['radio'] == '8'){
            $where .= " AND mst0201.appo_prod_kbn = '8' ";}

            if($_POST['radio'] == '9'){
            $where .= " AND mst0201.appo_prod_kbn = '9' ";}        */    

//            $sql  = ' SELECT 
//                    t.sect_cd AS sect_cd,
//                    b.sect_nm AS sect_nm,
//                    t.prod_cd AS prod_cd,
//                    REPLACE(a.prod_nm, $$　$$, $$ $$) AS prod_nm,
//                    d.staff_cd AS staff_cd,
//                    SUM(t.pure_total_i) AS subtotal
//
//                    FROM  trn0102 t
//                    LEFT JOIN trn0101 t2 ON (t.hideseq = t2.hideseq AND t.organization_id = t2.organization_id) 
//                    LEFT JOIN mst0201 a ON (t.prod_cd = a.prod_cd AND t.organization_id = a.organization_id) 
//                    LEFT JOIN mst1201 b ON (t.sect_cd = b.sect_cd AND t.organization_id = b.organization_id) 
//                    LEFT JOIN mst0601 d ON (t2.staff_cd = d.staff_cd AND t2.organization_id = d.organization_id) 
//                    LEFT JOIN v_organization o ON (t.organization_id = o.organization_id) ';
            $sql  = " SELECT "
                    . "      t.sect_cd AS sect_cd "
                    . "     ,b.sect_nm AS sect_nm "
                    . "     ,t.prod_cd AS prod_cd "
                    . '     ,REPLACE(a.prod_nm, $$　$$, $$ $$) AS prod_nm '
                    . "     ,COALESCE(t2.staff_cd, '') AS staff_cd "
                    . "     ,SUM(t.pure_total) AS pure_total "
                    . "     ,t.organization_id AS organization_id "
                    . "     ,o.abbreviated_name AS abbreviated_name "

                    . " FROM  trn0102 t "
                    . " LEFT JOIN trn0101 t2 ON (t.hideseq = t2.hideseq AND t.organization_id = t2.organization_id) "
                    . " LEFT JOIN mst0201 a ON (t.prod_cd = a.prod_cd AND t.organization_id = a.organization_id) "
                    . " LEFT JOIN mst1201 b ON (t.sect_cd = b.sect_cd AND t.organization_id = b.organization_id) "
                    . " LEFT JOIN mst0601 d ON (t2.staff_cd = d.staff_cd AND t2.organization_id = d.organization_id) "
                    . " LEFT JOIN v_organization o ON (t.organization_id = o.organization_id) ";
            $sql .= $where;
            //$sql .= " 	GROUP BY t.sect_cd, b.sect_nm, t.prod_cd, a.prod_nm, d.staff_cd ";
            //$sql .= " 	ORDER BY t.prod_cd, d.staff_cd ";
            $sql .= "   GROUP BY t.sect_cd, b.sect_nm, t.prod_cd, a.prod_nm, t2.staff_cd, t.organization_id, o.abbreviated_name ";
            // 並び順
            $sql .= "   ORDER BY ";
            // 店舗(降順)
            if($postArray['sort'] == '1'){
                $sql .= '   abbreviated_name DESC ';
                $sql .= ' , sect_cd ';
                $sql .= ' , prod_nm ';
                $sql .= ' , prod_cd ';
            }
            // 店舗(昇順)
            else if($postArray['sort'] == '2'){
                $sql .= '   abbreviated_name ';
                $sql .= ' , sect_cd ';
                $sql .= ' , prod_nm ';
                $sql .= ' , prod_cd ';
            }
            // 部門コード(降順)
            else if($postArray['sort'] == '3'){
                $sql .= '   sect_cd DESC ';
                $sql .= ' , abbreviated_name ';
                $sql .= ' , prod_nm ';
                $sql .= ' , prod_cd ';
            }
            // 部門コード(昇順)
            else if($postArray['sort'] == '4'){
                $sql .= '   sect_cd ';
                $sql .= ' , abbreviated_name ';
                $sql .= ' , prod_nm ';
                $sql .= ' , prod_cd ';
            }
            // 部門名(降順)
            else if($postArray['sort'] == '5'){
                $sql .= '   sect_nm DESC ';
                $sql .= ' , sect_cd ';
                $sql .= ' , abbreviated_name ';
                $sql .= ' , prod_nm ';
                $sql .= ' , prod_cd ';
            }
            // 部門名(昇順)
            else if($postArray['sort'] == '6'){
                $sql .= '   sect_nm ';
                $sql .= ' , sect_cd ';
                $sql .= ' , abbreviated_name ';
                $sql .= ' , prod_nm ';
                $sql .= ' , prod_cd ';
            }
            // 商品
            else if($postArray['sort'] == '7' || $postArray['sort'] == '8' || $postArray['sort'] == '9' || $postArray['sort'] == '10'){
                if ($postArray['bef_sort'] === $postArray['orgn_sort']){
                    if ($postArray['orgn_sort'] == '1'){
                        $sql .= '   abbreviated_name DESC ';
                    }
                    else{
                        $sql .= '   abbreviated_name ';
                    }
                    if ($postArray['sect_sort'] == '3'){
                        $sql .= ' , sect_cd DESC ';
                    }
                    else{
                        $sql .= ' , sect_cd ';
                    }
                }
                if ($postArray['bef_sort'] === $postArray['sect_sort']){
                    if ($postArray['sect_sort'] == '3'){
                        $sql .= '   sect_cd DESC ';
                    }
                    else{
                        $sql .= '   sect_cd ';
                    }
                    if ($postArray['orgn_sort'] == '1'){
                        $sql .= ' , abbreviated_name DESC ';
                    }
                    else{
                        $sql .= ' , abbreviated_name ';
                    }
                }
                // 商品コード降順
                if($postArray['sort'] == '7'){
                    $sql .= ' , prod_cd DESC ';
                }
                // 商品コード昇順
                else if($postArray['sort'] == '8'){
                    $sql .= ' , prod_cd ';
                }
                else if($postArray['sort'] == '9'){
                    $sql .= ' , prod_nm DESC ';
                    $sql .= ' , prod_cd ';
                }
                // 商品名昇順
                else{
                    $sql .= ' , prod_nm ';
                    $sql .= ' , prod_cd ';
                }
            }
            else{
                //$sql .= "       organization_id ";
                $sql .= "       abbreviated_name ";
                $sql .= "     , sect_cd ";
                //$sql .= "     , prod_cd ";
                $sql .= "     , prod_nm ";
                $sql .= "     , prod_cd ";
                //$sql .= "     , staff_cd ";
                $sql .= "     , CASE WHEN t2.staff_cd IS NULL THEN '1' ";
                $sql .= "       ELSE '0' ";
                $sql .= "       END ";
                $sql .= "     , t2.staff_cd ASC ";
            }

            
            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getStaffListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getStaffListData");

            $searchArray = array();
            $sql = $this->creatSSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getStaffListData");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //array_push( $displayItemDataList, $data);
                array_push( $displayItemDataList, $data['staff_cd']);
            }

            $Log->trace("END getStaffListData");
            return $displayItemDataList;
        }

        /**
         * 担当者情報取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $where = " WHERE  (t.cancel_kbn != '1' AND t2.stop_kbn != '1'  AND t2.proc_date is not null AND t2.proc_date != '')";
            //$where .= " AND   (t2.staff_cd is not null AND t2.staff_cd != '')";
            if( !empty( $postArray['start_date'] ) )
            {
                $where .= ' AND date(t2.proc_date) >= date(:start_date) ';
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= ' AND date(t2.proc_date) <= date(:end_date) ';
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if(  $postArray['organization_id'] !== 'false' )
            {
                $where .= ' AND t.organization_id in ( '.$postArray['organization_id'].' )';
            }

            if( $postArray['prod_cd'] !== 'false' )
            {
                $where .= ' AND t.prod_cd in ( '.$postArray['prod_cd'].' )';
            }
            
            if(  $postArray['sect_cd'] !== 'false' )
            {
                $where .= ' AND t.sect_cd in ( '.$postArray['sect_cd'].' )';
            }           
            if( $postArray['staff_cd'] !== 'false' )
            {
                $where .= ' AND t2.staff_cd in ( '.$postArray['staff_cd'].' )';
            }
            
       /*    指定商品区分を選択するコード
        if($_POST['radio'] == '0'){
            $where .= " AND mst0201.appo_prod_kbn = '0' ";}

            if($_POST['radio'] == '1'){
            $where .= " AND mst0201.appo_prod_kbn = '1' ";}

            if($_POST['radio'] == '2'){
            $where .= " AND mst0201.appo_prod_kbn = '2' ";}

            if($_POST['radio'] == '3'){
            $where .= " AND mst0201.appo_prod_kbn = '3' ";}
            
            if($_POST['radio'] == '4'){
            $where .= " AND mst0201.appo_prod_kbn = '4' ";}

            if($_POST['radio'] == '5'){
            $where .= " AND mst0201.appo_prod_kbn = '5' ";}

            if($_POST['radio'] == '6'){
            $where .= " AND mst0201.appo_prod_kbn = '6' ";}

            if($_POST['radio'] == '7'){
            $where .= " AND mst0201.appo_prod_kbn = '7' ";}

            if($_POST['radio'] == '8'){
            $where .= " AND mst0201.appo_prod_kbn = '8' ";}

            if($_POST['radio'] == '9'){
            $where .= " AND mst0201.appo_prod_kbn = '9' ";}        */    

//            $sql  = ' SELECT 
//                    t2.staff_cd
//                    FROM  trn0101 t2
//                    LEFT JOIN trn0102 t ON (t.hideseq = t2.hideseq AND t.organization_id = t2.organization_id) 
//                    LEFT JOIN mst0201 a ON (t.prod_cd = a.prod_cd AND t.organization_id = a.organization_id) 
//                    LEFT JOIN mst1201 b ON (t.sect_cd = b.sect_cd AND t.organization_id = b.organization_id) 
//                    LEFT JOIN mst1101 c ON (a.head_supp_cd = c.supp_cd AND a.organization_id = c.organization_id) 
//                    LEFT JOIN mst0601 d ON (t2.staff_cd = d.staff_cd AND t2.organization_id = d.organization_id) 
//                    LEFT JOIN v_organization o ON (t.organization_id = o.organization_id) ';
//            $sql .= $where;
//            $sql .= " 	GROUP BY t2.staff_cd ";
//            $sql .= " 	ORDER BY t2.staff_cd ";

            $sql  = " SELECT 
                    COALESCE(t2.staff_cd, '') AS staff_cd
                    FROM  trn0101 t2
                    LEFT JOIN trn0102 t ON (t.hideseq = t2.hideseq AND t.organization_id = t2.organization_id) 
                    LEFT JOIN mst0201 a ON (t.prod_cd = a.prod_cd AND t.organization_id = a.organization_id) 
                    LEFT JOIN mst1201 b ON (t.sect_cd = b.sect_cd AND t.organization_id = b.organization_id) 
                    LEFT JOIN mst1101 c ON (a.head_supp_cd = c.supp_cd AND a.organization_id = c.organization_id) 
                    LEFT JOIN mst0601 d ON (t2.staff_cd = d.staff_cd AND t2.organization_id = d.organization_id) 
                    LEFT JOIN v_organization o ON (t.organization_id = o.organization_id) ";
            $sql .= $where;
            $sql .= " 	GROUP BY t2.staff_cd ";
            $sql .= " 	ORDER BY ";
            $sql .= "       CASE WHEN t2.staff_cd IS NULL THEN '1' ";
            $sql .= "       ELSE '0' ";
            $sql .= "       END ";
            $sql .= "     , t2.staff_cd ASC ";

            $Log->trace("END creatSQL");
            //print_r("ssql: ".$sql);
            return $sql;
        }

    }
?>
