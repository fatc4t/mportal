<?php
    /**
     * @file      帳票 - 売上対比実績
     * @author    millionet kanderu
     * @date      2020/07/30
     * @version   1.00
     * @note      帳票 - 売上対比実績の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetSalesResultsCompare extends BaseModel
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

        /**
         * 最大ページ数を取得するSQL
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   最大ページ数を取得するSQL文
         */
        public function pageMaxNo($postArray, $searchArray)
        {
         global $DBA, $Log; // グローバル変数宣言
         $Log->trace("START pageMaxNo"); 

            $sql .= " select count (*) as rownos  ";
	    $sql .= "   from trn0101 t1  ";
	    $sql .= "   left join trn0102 t2 on t1.organization_id=t2.organization_id and t2.hideseq=t2.hideseq and t1.account_no=t2.account_no  ";
	    $sql .= "	left join mst1201 m1 on t2.sect_cd=m1.sect_cd and t2.organization_id=m1.organization_id  ";
            $sql .= "  where  1 = :val and proc_date = '20191220' and  t1.organization_id = '2'   ";

            $searchArray[':val'] = 1 ;

            $Datas = [];

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言

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
            // 検索条件
            $where  = "";
            $where2  = "";

            // 部門指定の場合
            if( $postArray['sect_cd'] !== 'false' )
            {
                $where .= " AND t.sect_cd in (".$postArray['sect_cd'].") ";
                $where2 .= " AND t.sect_cd in (".$postArray['sect_cd'].") ";
            }

            // 店舗指定の場合
            if( $postArray['t.organization_id'] !== 'compile' && $postArray['organization_id'] !== 'all')
            {
                $where .= " AND t.organization_id in (".$postArray['organization_id'].") ";
                $where2 .= " AND t.organization_id in (".$postArray['organization_id'].") ";
            }
              
            // 対象日付条件の設定 FROM
            if( $postArray['start_date_r'] !== '')
            {
                $where .= " and  t1.proc_date >= '".str_replace("/","",$postArray['start_date_r'])."'";
            }  
            
            // 対象日付条件の設定 TO
            if( $postArray['end_date_r'] !== '')
            {
                $where .= " AND  t1.proc_date <= '".str_replace("/","",$postArray['end_date_r'])."'";
            }  
            // 対象日付条件の設定 FROM
            if( $postArray['start_date_t'] !== '')
            {
                $where2 .= " AND  t1.proc_date >= '".str_replace("/","",$postArray['start_date_t'])."'";
            }  
            
            // 対象日付条件の設定 TO
             // 対象日付条件の設定 FROM
            if( $postArray['end_date_t'] !== '')
            {
                $where2 .= " AND  t1.proc_date <= '".str_replace("/","",$postArray['end_date_t'])."'";
            }  
            /*************** SQL本体 START **************************************************************************/
             $sql = " select                                                                                                                                          ";
             $sql .= "   *                                                                                                                                            ";
             $sql .= "  ,round( case c.cnt2 when 0 then 0 else round(b.cnt / c.cnt2 * 100, 2) end , 2 ) as cntper                                                        ";
             $sql .= " , round(b.pure_total + b.utax)/(c.pure_total2+c.utax2)*100 as res1per1                                                                            ";
             $sql .= "    , round( case c.pure_total_i2 when 0 then 0 else round(b.pure_total_i / c.pure_total_i2 * 100, 2) end , 2 ) as pure_total_iper              ";                                                        
             $sql .= "  , round( case c.tax_total_082 when 0 then 0 else round(b.tax_total_08 / c.tax_total_082 * 100, 2) end , 2 ) as tax_total_08per                ";                                                      
             $sql .= "   , round( case c.tax_total_102 when 0 then 0 else round(b.tax_total_10 / c.tax_total_102 * 100 , 2 ) end , 2 ) as tax_total_10per             ";                                                       
             $sql .= " , round( case c.total_profit_i2 when 0 then 0 else round(b.total_profit_i / c.total_profit_i2 * 100, 2) end , 2 ) as total_profit_iper         ";                                                    
             $sql .= "  , round( case c.total_amount2 when 0 then 0 else round(b.total_amount / c.total_amount2 * 100, 2 ) end , 2 ) as total_amountper               ";                                           
            // $sql .= " , round( case c.cnt2 when 0 then 0 else round(b.cnt / c.cnt2 * 100, 2) end , 2 ) as total_cntper                                               ";                
             $sql .= " , round( case c.res32 when 0 then 0 else round(b.res3 / c.res32 * 100, 2) end , 2) as res3per                                                 ";                  
             $sql .= "  , round( case c.res42 when 0 then 0 else round(b.res4 / c.res42 * 100, 2) end , 2 ) as res4per                                                ";                   
             $sql .= " , round( case c.res52 when 0 then 0 else round(b.res5 / c.res52 * 100, 2) end , 2 ) as res5per                                                 ";                  
             $sql .= " , round( case c.res62 when 0 then 0 else round(b.res6 / c.res62 * 100, 2) end , 2 ) as res6per                                                 ";
             $sql .= " from (                                                                                                                           ";
             $sql .= "    select                                                                                                                        ";
             $sql .= "    ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) as cnt                                                                                            ";
             $sql .= "   , o.abbreviated_name as org_nm                                                                                                 ";
             $sql .= "   , t.organization_id                                                                                                            ";
             $sql .= "   , sum(t.pure_total) as pure_total                                                                                              ";
             $sql .= "   , sum(t.pure_total_i) as pure_total_i                                                                                          ";
             $sql .= "   , sum(t.profit_i) as total_profit_i                                                                                            ";
             $sql .= "   , sum(t.amount) as total_amount                                                                                                ";
             $sql .= "   , sum( case  when t2.tax_type = '1' then t2.tax_total  else 0 end) as utax                                                     ";
             $sql .= "   , sum(   case when t2.tax_type = '2' then t2.tax_total  else 0  end) as itax                                                   ";
             $sql .= "   , sum(   case when t2.tax_rate = '10' then t2.tax_total else 0 end) as tax_total_10                                            ";
             $sql .= "   , sum(   case when t2.tax_rate = '8' then t2.tax_total  else 0 end) as tax_total_08                                            ";
             $sql .= "   , round( case sum(t.pure_total_i) when 0 then 0 else round(sum(t.profit_i) / sum(t.pure_total_i) * 100, 2)  end , 2) as res3   ";
             $sql .= " , round( case sum(t.amount) when 0 then 0 else round(sum(t.pure_total_i) /  sum(t.amount) , 2)  end , 2) as res6                 ";
             $sql .= " , round( case ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) when 0 then 0 else round(sum(t.pure_total_i) / ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) , 2)  end , 2) as res4 ";
             $sql .= "  ,round(case  ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) when 0 then 0 else round(sum(t.amount)/ ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) , 2)  end , 2) as res5 ";
             $sql .= " from                                                                                                                             ";
             $sql .= "   trn0102 t                                                                                                                      ";
             $sql .= "   left join m_organization_detail o                                                                                              ";
             $sql .= "     on (o.organization_id = t.organization_id)                                                                                   ";
             $sql .= "   left join trn0103 t2                                                                                                           ";
             $sql .= "     on t.organization_id = t2.organization_id                                                                                    ";
             $sql .= "     and t.line_no = t2.line_no                                                                                                   ";
             $sql .= "     and t.hideseq = t2.hideseq                                                                                                   ";
             $sql .= " 	and t.reji_no = t2.reji_no                                                                                                     ";
             $sql .= "   left join trn0101 t1                                                                                                           ";
             $sql .= "     on (t.organization_id = t1.organization_id and t.hideseq = t1.hideseq)                                                       ";
             $sql .= " WHERE                                                                                                                            ";
             $sql .= "   t.organization_id <> - 1                                                                                                       ";
             $sql .= "   and t.cancel_kbn = '0'                                                                                                         ";
             $sql .= " $where ";
             $sql .= "   and t1.stop_kbn = '0'                                                                                                          ";
             $sql .= " group by                                                                                                                         ";
             $sql .= "   t.organization_id, org_nm )  b                                                                                                 ";
             $sql .= "                                                                                                                                  ";
             $sql .= "   left join (select                                                                                                              ";
             $sql .= "  ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) as cnt2                                                                                            ";
             $sql .= "   , o.abbreviated_name as org_nm                                                                                                 ";
             $sql .= "   , t.organization_id                                                                                                            ";
             $sql .= "   , sum(t.pure_total) as pure_total2                                                                                              ";
             $sql .= "   , sum(t.pure_total_i) as pure_total_i2                                                                                          ";
             $sql .= "   , sum(t.profit_i) as total_profit_i2                                                                                            ";
             $sql .= "   , sum(t.amount) as total_amount2                                                                                                ";
             $sql .= "   , sum( case  when t2.tax_type = '1' then t2.tax_total  else 0 end) as utax2                                                     ";
             $sql .= "   , sum(   case when t2.tax_type = '2' then t2.tax_total  else 0  end) as itax2                                                   ";
             $sql .= "   , sum(   case when t2.tax_rate = '10' then t2.tax_total else 0 end) as tax_total_102                                            ";
             $sql .= "   , sum(   case when t2.tax_rate = '8' then t2.tax_total  else 0 end) as tax_total_082                                            ";
             $sql .= "   , round( case sum(t.pure_total_i) when 0 then 0 else round(sum(t.profit_i) / sum(t.pure_total_i) * 100, 2)  end , 2) as res32   ";
             $sql .= " , round( case sum(t.amount) when 0 then 0 else round(sum(t.pure_total_i) /  sum(t.amount) , 2)  end , 2) as res62                 ";
              $sql .= " , round( case ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) when 0 then 0 else round(sum(t.pure_total_i) / ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) , 2)  end , 2) as res42 ";
             $sql .= "  ,round(case  ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) when 0 then 0 else round(sum(t.amount)/ ( count(distinct t.hideseq  ) - COUNT(distinct t1.hideseq ) filter (where t1.return_hideseq<>'0' or null)) , 2)  end , 2) as res52 ";
             $sql .= " from                                                                                                                             ";
             $sql .= "   trn0102 t                                                                                                                      ";
             $sql .= "   left join m_organization_detail o                                                                                              ";
             $sql .= "     on (o.organization_id = t.organization_id)                                                                                   ";
             $sql .= "   left join trn0103 t2                                                                                                           ";
             $sql .= "     on t.organization_id = t2.organization_id                                                                                    ";
             $sql .= "     and t.line_no = t2.line_no                                                                                                   ";
             $sql .= "     and t.hideseq = t2.hideseq                                                                                                   ";
             $sql .= " 	and t.reji_no = t2.reji_no                                                                                                     ";
             $sql .= "   left join trn0101 t1                                                                                                           ";
             $sql .= "     on (t.organization_id = t1.organization_id and t.hideseq = t1.hideseq)                                                       ";
             $sql .= " WHERE                                                                                                                            ";
             $sql .= "   t.organization_id <> - 1                                                                                                       ";
             $sql .= "   and t.cancel_kbn = '0'                                                                                                         ";
            $sql .= " $where2                                                                                               ";
             $sql .= "   and t1.stop_kbn = '0'                                                                                                          ";
             $sql .= " group by                                                                                                                         ";
             $sql .= "   t.organization_id, org_nm ) c on (c.organization_id=b.organization_id)                                                         ";
             $sql .= " limit ".$postArray['limit'];
             $sql .= " offset ".$postArray['offset'];
             $Log->trace("END creatSQL");
            return $sql;
                }
            }
?>
