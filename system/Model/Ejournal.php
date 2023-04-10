<?php
    /**
     * @file      システム管理 - 電子ジャーナル検索
     * @author    millionet oota
     * @date      2020/03/25
     * @version   1.00
     * @note      システム管理 - 電子ジャーナル検索の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class Ejournal extends BaseModel
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
         * 電子ジャーナル一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getFormListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

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
         * 電子ジャーナル一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            // 検索条件設定
            $where1 = '';
            $where2 = '';
            $where3 = '';
            
            // 開始日
            if( !empty( $postArray['start_date'] ) )
            {
                $where1 .= " AND date(insdatetime) >= :start_date ";
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            // 終了日
            if( !empty( $postArray['end_date'] ) )
            {
                $where1 .= " AND date(insdatetime) <= :end_date ";
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            // 店舗
            if( $postArray['organization_id'] !== 'false' )
            {
                $where2 .= " AND organization_id in (".$postArray['organization_id'].") ";
            }
            
            // レシートNo.
            if( $postArray['rn1'] !== '' && $postArray['rn2'] !== '' )
            {
                $where2 .= " AND receipt_data LIKE '%".trim($postArray['rn1'])."-".trim($postArray['rn2'])."%' ";
            }
            
            // 時刻(時)
            if( $postArray['t_time_h'] !== '' )
            {
                $where2 .= " AND receipt_data LIKE '%".trim($postArray['t_time_h'])."時%' ";
            }
            
            // 時刻(分)
            if( $postArray['t_time_m'] !== '' )
            {
                $where2 .= " AND receipt_data LIKE '%".trim($postArray['t_time_m'])."分%' ";
            }
            
            // キーワード
            if( $postArray['keyword'] !== '' )
            {
                $where2 .= " AND receipt_data LIKE '%".trim($postArray['keyword'])."%' ";
            }
            
            // 商品選択
            if( $postArray['prod_cd'] !== 'false' )
            {
                $where2 .= " AND receipt_info LIKE '%".str_replace('\'', '', trim($postArray['prod_cd']))."%' ";
            }
            
            // 担当者
            if( $postArray['staff_cd'] !== 'false' )
            {
                $where2 .= " AND receipt_data LIKE '%".str_replace('\'', '', trim($postArray['$staff_nm_lst']))."%' ";
            }
            
            // 取引中止
            if( $postArray['mode_chk'] !== '' )
            {
                $where3 .= " WHERE d.organization_id is null ";
            }
            
            //　表示順指定
            $order = ' order by ';
            if($_POST["sort_table"]){
//                $order .= $_POST['sort_table']; 
                $order .= 'organization_id, hideseq, line_no, reji_no';
            }else{
                $order .= 'organization_id, hideseq, line_no, reji_no';
            }            

            // SQL本体
                $sql  = " ";
                $sql .= " SELECT ";
                $sql .= "	v.organization_id,";
                $sql .= "	v.abbreviated_name AS abbreviated_name,";
                $sql .= "	a.hideseq,";
                $sql .= "	a.line_no,";
                $sql .= "	a.reji_no,";
                $sql .= "	a.receipt_info,";
                $sql .= "	a.receipt_data,";
                $sql .= "	a.van_data_kbn,";
                $sql .= "	a.van_data_date";
                $sql .= "  FROM trn9101 as a";
                $sql .= "  JOIN ( SELECT organization_id, hideseq, reji_no   FROM trn9101";
                $sql .= "          WHERE receipt_info LIKE '%*S%' ";
                $sql .= $where1;
                $sql .= "            GROUP BY organization_id, hideseq, reji_no) as b ON (a.organization_id = b.organization_id AND a.hideseq = b.hideseq AND a.reji_no = b.reji_no)";
                $sql .= "  JOIN ( SELECT organization_id, hideseq, reji_no   FROM trn9101";
                $sql .= "          WHERE 1 = 1 ";
                $sql .= $where1;
                $sql .= $where2;
                $sql .= "            GROUP BY organization_id, hideseq, reji_no) as c ON (a.organization_id = c.organization_id AND a.hideseq = c.hideseq AND a.reji_no = c.reji_no)";
            // 取引中止
            if( $postArray['mode_chk'] !== '' )
            {
                $sql .= "  LEFT JOIN ( SELECT organization_id, hideseq, reji_no   FROM trn9101";
                $sql .= "          WHERE receipt_data LIKE '%取引中止%' ";
                $sql .= $where1;
                $sql .= "            GROUP BY organization_id, hideseq, reji_no) as d ON (a.organization_id = d.organization_id AND a.hideseq = d.hideseq AND a.reji_no = d.reji_no)";
            }
                $sql .= ' JOIN v_organization v ON v.organization_id = a.organization_id ';
                $sql .= $where3;
                $sql .= $order;
                
            $Log->trace("END creatSQL");
            //print_r($sql);
            return $sql;
        }

    }
?>
