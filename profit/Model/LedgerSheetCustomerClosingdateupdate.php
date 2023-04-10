<?php
    /**
     * @file      締日更新処理 [M]
     * @author    バッタライ
     * @date      2020/03/19
     * @version   1.00
     * @note      締日更新処理の管理を行う
     */
         
    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';
        
    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetCustomerClosingdateupdate extends BaseModel
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
         * 締処理日取得
         * @param    
         * @return   締処理日配列
         */
        public function get_data($joken)
       {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_data");     
            //print_r($joken);
            $searchArray = array(
                ':PROC_DATE'  => $joken['WELLSET_DATE_NOW'],
            );            
            $where  = "";
            //検索条件顧客
            if( isset($joken['CUST_SUMDAY']) ){
                $where .= " AND M1.CUST_SUMDAY = '".$joken['CUST_SUMDAY']."' ";
            }
            //検索条件顧客機
            if( $joken['CUST_CD'] !== 'false')
            {
                if( $joken['CUST_SELECT'] === 'empty'){
                    $where .= " AND M1.CUST_CD in (".$joken['CUST_CD'].") ";
                }else{
                    $where .= " AND M1.CUST_CD in ('".$joken['CUST_SELECT']."') ";
                }
            }
            $sql .= "select distinct on (M1.CUST_CD) ";
            $sql .= "     M1.CUST_CD as CUST_CD  ";
            $sql .= "     ,regexp_replace(coalesce(replace (M1.CUST_NM, '\"', '\\\"'), ''), '''', ' ')as CUST_NM  ";
            $sql .= "     , M1.CUST_SUMDAY               as CUST_SUMDAY ";
            $sql .= "     , max(J1.WELLSET_DATE)         as WELLSET_DATE ";
            $sql .= "     , max(J1.NOW_BALANCE)          as BEF_BALANCE ";
            $sql .= "     , sum(T1.PURE_TOTAL -T1.ITAX)  as SALE_TOTAL ";  
            $sql .= "     , sum(T1.ITAX + T1.UTAX)       as TAX_TOTAL ";
            $sql .= "     , sum(T4.CREDIT_MONEY + T4.GENKIN + T4.GIFT_MONEY + T4.POINT_MONEY) - sum(T3.CREDIT_MONEY) + sum( case when T5.RECE_KBN <> '8' then T5.RECE_TOTAL else 0 end) as RECE_TOTAL ";          
            $sql .= "     , sum(case when T5.RECE_KBN = '8' then T5.RECE_TOTAL else 0 end) as RECE_DISC ";
            $sql .= "     , 'checked' as CHECK_FLG ";
            $sql .= "from  ";
                          // MST0101（顧客マスタ）と結合
            $sql .= "     MST0101 M1 ";
                          //
            $sql .= "     left join (select * from TRN0101 where STOP_KBN = '0'  and WELLSET_DATE ='' and PROC_DATE <=:PROC_DATE ) T1 on (T1.CUST_CD = M1.CUST_CD ) ";
                          // 
            $sql .= "     left join (select * from TRN0101 where STOP_KBN = '0') T1_2  on (T1_2.ORGANIZATION_ID = T1.ORGANIZATION_ID  and T1_2.RETURN_HIDESEQ = T1.HIDESEQ  and T1_2.WELLSET_DATE = T1.WELLSET_DATE ) ";
                          // 
            $sql .= "     left join (select count(*) as count_2, ORGANIZATION_ID, HIDESEQ from TRN0102 where J_HIDESEQ is not null  group by ORGANIZATION_ID, HIDESEQ) T2  on (T1_2.ORGANIZATION_ID = T2.ORGANIZATION_ID and T1_2.HIDESEQ = T2.HIDESEQ  and T1_2.WELLSET_DATE = T1.WELLSET_DATE ) ";
            $sql .= "     left join TRN0201 T3  on (T1.ORGANIZATION_ID = T3.ORGANIZATION_ID  and T1.HIDESEQ = T3.HIDESEQ ) ";
            $sql .= "     left join (select * from TRN0203 where CREDIT_MONEY <> 0) T4  on (T1.ORGANIZATION_ID = T4.ORGANIZATION_ID and T1.HIDESEQ = T4.HIDESEQ ) ";
                          // 
            $sql .= "     left join TRN0301 T5  on (T1.ORGANIZATION_ID = T5.ORGANIZATION_ID  and T1.HIDESEQ = T5.HIDESEQ ) ";
                          // 
            $sql .= "     left join (select  distinct on (CUST_CD) CUST_CD, WELLSET_DATE, sum(NOW_BALANCE) as NOW_BALANCE from JSK4150   group by CUST_CD, WELLSET_DATE order by CUST_CD, WELLSET_DATE desc) J1 on (M1.CUST_CD = J1.CUST_CD ) ";
            $sql .= "where ";
            $sql .= "     coalesce(T2.count_2, 0) = 0 ";
        //    $sql .= "and   ";
            $sql .= $where;
            $sql .= "and 1=:val ";
            $sql .= "and J1.wellset_date <> '' ";
            $sql .= "group by ";
            $sql .= "        M1.CUST_CD ";
            $sql .= "      , M1.CUST_NM ";
            $sql .= "      , M1.CUST_SUMDAY ";
            $sql .= "order by CUST_CD ";            
             //print_r($sql);
            $searchArray[':val']=1;
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$searchArray);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_data");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END get_data");
            return $Datas;
        }      
        /*解除処理開始*/
        /*顧客売掛取引税別明細-削除*/
        public function Delete_JSK4150($POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START Delete_JSK4150");
            $param = array(
                ':CUST_CD'         => $POST['CUST_CD'],
                ':WELLSET_DATE'    => $POST['WELLSET_DATE'],
           //     ':ORGANIZATION_ID'  => $orgArray['ORGANIZATION_ID'],
            );

            $sql  = " ";
            $sql .= "delete from ". $_SESSION["SCHEMA"] . ".JSK4150 ";
            $sql .= "where CUST_CD        = :CUST_CD ";
         //   $sql .= "and ORGANIZATION_ID  = :ORGANIZATION_ID ";
            $sql .= "and WELLSET_DATE     = :WELLSET_DATE ";
            print_r($sql);
 
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);

            $Log->trace("END Delete_JSK4150");
           /* return true;*/
        }
        
        /*顧客売掛取引-削除*/
        public function Delete_JSK4160($POST) {
            // グローバル変数宣言
            global $DBA, $Log;           
            $Log->trace("START Delete_JSK4160");
                
            $param = array(
                ':CUST_CD'          => $POST['CUST_CD'],
                ':WELLSET_DATE'     => $POST['WELLSET_DATE'],
          //      ':ORGANIZATION_ID'  => $orgArray['ORGANIZATION_ID'],
            );

            $sql  = " ";
            $sql .= "delete from  ". $_SESSION["SCHEMA"] . ".JSK4160 ";
            $sql .= "where CUST_CD           = :CUST_CD ";
         //   $sql .= "and ORGANIZATION_ID     = :ORGANIZATION_ID ";
            $sql .= "and WELLSET_DATE        = :WELLSET_DATE ";
            print_r($sql);    
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
                
            $Log->trace("END Delete_JSK4160");
           /* return true;*/
        }   
        /*顧客売掛-削除*/
        public function Delete_JSK4170($POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START Delete_JSK4170");
                
            $param = array(
                ':CUST_CD'          => $POST['CUST_CD'],
                ':WELLSET_DATE'     => $POST['WELLSET_DATE'],
             //   ':ORGANIZATION_ID'  => $orgArray['ORGANIZATION_ID'],
            );

            $sql  = " ";
            $sql .= "delete from  ". $_SESSION["SCHEMA"] . ".JSK4170 ";
            $sql .= "where CUST_CD       = :CUST_CD ";
            //$sql .= "and ORGANIZATION_ID = :ORGANIZATION_ID ";
            $sql .= "and WELLSET_DATE    = :WELLSET_DATE ";
            print_r($sql);    
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            
            $Log->trace("END Delete_JSK4170");
        }   
        public function UPD_TRN0101($POST){
            // グローバル変数宣言
            global $DBA, $Log;             
            $Log->trace("START upd_trn0101");
            $param = array(
                ':CUST_CD'          => $POST['CUST_CD'],
                ':WELLSET_DATE'     => $POST['WELLSET_DATE'],
          //      ':ORGANIZATION_ID'  => $orgArray['ORGANIZATION_ID'],
                ':UPDUSER_CD'       => 'MPORTAL',
                ':UPDDATETIME'      => "now()",
            );
            
            $sql  .="UPDATE  ". $_SESSION["SCHEMA"] . ".TRN0101 set ";
            $sql  .="    UPDUSER_CD        = :UPDUSER_CD  ";
            $sql  .="   ,UPDDATETIME       = :UPDDATETIME  ";
            $sql  .="	,WELLSET_DATE      = '' ";
            $sql  .= "where CUST_CD        = :CUST_CD ";
         //   $sql  .= "and ORGANIZATION_ID  = :ORGANIZATION_ID ";
            $sql  .= "and WELLSET_DATE     = :WELLSET_DATE  ";
            print_r($sql);
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END upd_trn0101");
        }         
        //J受注伝票-締処理日クリア
        public function UPD_TRN7101($POST){
            // グローバル変数宣言
            global $DBA, $Log;             
            $Log->trace("START UPD_TRN7101");
            $param = [];
            $param[":CUST_CD"]          = $POST['CUST_CD'];
            $param[":WELLSET_DATE"]     = $POST['WELLSET_DATE'];
         //   $param[":ORGANIZATION_ID"]   = $orgArray['ORGANIZATION_ID'];
            $param[":UPDUSER_CD"]       = 'MPORTAL';
            $param[":UPDDATETIME"]      = 'now()';
            
            $sql  .="UPDATE  ". $_SESSION["SCHEMA"] . ".TRN7101 set ";
            $sql  .="    UPDUSER_CD         = :UPDUSER_CD ";
            $sql  .="   ,UPDDATETIME        = :UPDDATETIME";
            $sql  .="	,WELLSET_DATE       = '' ";
            $sql  .= "where CUST_CD         = :CUST_CD ";
          //  $sql  .= "and ORGANIZATION_ID   = :ORGANIZATION_ID ";
            $sql  .= "and WELLSET_DATE      = :WELLSET_DATE ";
            print_r($sql);
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);

            $Log->trace("END UPD_TRN7101");
        }         
        /*顧客実績アップデート*/
        public function upd_JSK4130($POST){
            // グローバル変数宣言
            global $DBA, $Log;            
           $Log->trace("START upd_JSK4130");  
            $param = array(
                ':CUST_CD'        => $POST['CUST_CD'],
                ':UPDUSER_CD'     => 'MPORTAL' ,
                ':UPDDATETIME'    => "now()" ,
                ':DISABLED'       => 0 ,
                ':LAN_KBN'        => 0 ,
                ':WELLSET_DATE'   => $POST['WELLSET_DATE'] ,
            );                     
            $sql  .=" ";
            $sql  .="update  ". $_SESSION["SCHEMA"] . ".JSK4130 set ";
            $sql  .="     UPDUSER_CD   = :UPDUSER_CD ";
            $sql  .="    ,UPDDATETIME  = :UPDDATETIME ";
            $sql  .="    ,DISABLED     = :DISABLED ";
            $sql  .="    ,LAN_KBN      = :LAN_KBN ";
            $sql  .="    ,WELLSET_DATE  = (select WELLSET_DATE from ". $_SESSION["SCHEMA"].".JSK4150 where cust_cd = :CUST_CD  order by WELLSET_DATE desc limit 1 ) ";
            $sql  .="where cust_cd         = :CUST_CD ";
            $sql  .="and WELLSET_DATE      = :WELLSET_DATE ";
            print_r($sql);
            
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);

            $Log->trace("END upd_JSK4130");
        }
    /*（実行）*/
        /*組織マスタから組織IDを取得*/
        public function SELECT_ORG_ID() {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_ORG_ID");
            
              $sql .="select organization_id as organization_id from m_organization_detail where upper_level_organization <> 0 order by organization_id ";         
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_ORG_ID");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_ORG_ID");
            return $Datas;
        }        
        //データ取得(売上)/*データ取得(前回残高)*/
        public function SELECT_TRN0101($POST,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_TRN0101");
            /*パラメータを設定する*/
            $param = array(
                ':CUST_CD'           => $POST['CUST_CD'],
                ':WELLSET'           => $POST['WELLSET_DATE'],
                ':WELLSET_DATE_NOW'  => $POST['WELLSET_DATE_NOW'],
                ':ORGANIZATION_ID'   => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select ";
            $sql .="PROC_DATE ";
            $sql .=",ORGANIZATION_ID ";
            $sql .=",CUST_CD ";
            $sql .=",HIDESEQ    as HIDESEQ ";
            $sql .=",PURE_TOTAL as SALE_TOTAL ";
            $sql .=",UTAX       as SALE_UTAX "; 
            $sql .=",ITAX       as SALE_ITAX ";
            $sql .=",RETURN_HIDESEQ as RETURN_HIDESEQ ";
            $sql .="from TRN0101 ";
            $sql .="where STOP_KBN          = '0' ";
            $sql .="and   WELLSET_DATE      = '' ";
            $sql .="and   CUST_CD           = :CUST_CD ";
            $sql .="and   ORGANIZATION_ID   = :ORGANIZATION_ID ";
            $sql .="and   PROC_DATE         <= :WELLSET_DATE_NOW ";
            $sql .="and   PROC_DATE         > :WELLSET ";
            $sql .="order by HIDESEQ ";           
            print_r($sql);

            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_data");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_TRN0101");
            return $Datas;
        }        
        /*jskの最大のhideseqを取得する*/
        public function SELECT_JSK_HIDESEQ($orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_JSK_HIDESEQ");
           /*パラメータを設定する*/
            $param = array(
                ':ORGANIZATION_ID'   => $orgArray['ORGANIZATION_ID'],
            );              
            $sql .="select max(hideseq) as hideseq from jsk4160 where organization_id = :ORGANIZATION_ID ";         
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_JSK_HIDESEQ");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_JSK_HIDESEQ");
            return $Datas;
        }
        /*jskの最大のhideseqを取得する*/
        public function SELECT_JSK4170_HIDESEQ($orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_JSK_HIDESEQ");
           /*パラメータを設定する*/
            $param = array(
                ':ORGANIZATION_ID'   => $orgArray['ORGANIZATION_ID'],
            );              
            $sql .="select max(hideseq) as hideseq from jsk4170 where organization_id = :ORGANIZATION_ID ";         
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_JSK_HIDESEQ");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_JSK_HIDESEQ");
            return $Datas;
        }        
        /*jskの最大のhideseqを取得する*/
        public function SELECT_BILLNO($orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_BILLNO");
           /*パラメータを設定する*/
            $param = array(
                ':ORGANIZATION_ID'   => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select max(bill_no) as bill_no from jsk4150 where organization_id = :ORGANIZATION_ID ";
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_BILLNO");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_BILLNO");
            return $Datas;
        }        
        /*データ取得(前回残高)*/
        public function SELECT_BEF_BALANCE($POST,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_BEF_BALANCE");
            /*パラメータを設定する*/
            $param = array(
                ':CUST_CD'                => $POST['CUST_CD'],
                ':WELLSET_DATE'           => $POST['WELLSET_DATE'],
                ':ORGANIZATION_ID'        => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select ORGANIZATION_ID ,NOW_BALANCE ,WELLSET_DATE from JSK4150 where  CUST_CD = :CUST_CD and ORGANIZATION_ID = :ORGANIZATION_ID and WELLSET_DATE = :WELLSET_DATE  ";
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_BEF_BALANCE");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_BEF_BALANCE");
            return $Datas;
        }        
        /*掛売上金額取得*/
        public function SELECT_CREDIT_MONEY($searchArray,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_CREDIT_MONEY");
            /*パラメータを設定する*/
            $param = array(
                ':HIDESEQ'           => $searchArray['HIDESEQ'],
                ':ORGANIZATION_ID'   => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select organization_id ,sum(CREDIT_MONEY) as CREDIT_MONEY from TRN0201 where  HIDESEQ = :HIDESEQ and ORGANIZATION_ID = :ORGANIZATION_ID   and connect_kbn = '0' and   CREDIT_KBN = '3' group by ORGANIZATION_ID, HIDESEQ ";         
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_CREDIT_MONEY");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_CREDIT_MONEY");
            return $Datas;
        }
        /*掛売上金額取得*/
        public function SELECT_RECE_MONEY($searchArray,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_RECE_MONEY");
            /*パラメータを設定する*/
            $param = array(
                ':HIDESEQ'             => $searchArray['HIDESEQ'],
                ':ORGANIZATION_ID'     => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select sum(GENKIN + CREDIT_MONEY + GIFT_MONEY + POINT_MONEY) as RECE_TOTAL from TRN0203 where HIDESEQ = :HIDESEQ and ORGANIZATION_ID = :ORGANIZATION_ID and connect_kbn = '0' group by HIDESEQ ,ORGANIZATION_ID ";         
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_RECE_MONEY");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_RECE_MONEY");
            return $Datas;
        }          
        
        
        /*掛売上金額取得*/
        public function SELECT_TRNDATA($searchArray,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;
            $Log->trace("START SELECT_TRNDATA");
            $param = array(
                ':HIDESEQ'          => $searchArray['HIDESEQ'],
                ':CUST_CD'          => $searchArray['CUST_CD'],
                ':ORGANIZATION_ID'  => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select "; 
            $sql .="'MPORTAL'               as INSUSER_CD      ";
            $sql .=",'MPORTAL'              as UPDUSER_CD      ";
            $sql .=",'0'                    as DISABLED        ";
            $sql .=",T1.LAN_KBN             as LAN_KBN         ";
            $sql .=",T1.CONNECT_KBN         as CONNECT_KBN     ";
            $sql .=",T1.ORGANIZATION_ID     as ORGANIZATION_ID ";
            $sql .=",T1.CUST_CD             as CUST_CD         ";
            $sql .=",T1.REJI_NO             as REJI_NO         ";
            $sql .=",T1.PROC_DATE           as PROC_DATE       ";
            $sql .=",T1.OPEN_CNT            as OPEN_CNT        ";
            $sql .=",T1.TRNDATE             as TRNDATE         ";
            $sql .=",T1.TRNTIME             as TRNTIME         ";
            $sql .=",T1.ACCOUNT_NO          as ACCOUNT_NO      ";
            $sql .=",'0'                    as ACCOUNT_KBN     ";
            $sql .=",T2.SECT_CD             as SECT_CD         ";
            $sql .=",T2.PROD_CD             as PROD_CD         ";
            $sql .=",T2.TAX_TYPE            as TAX_TYPE        ";
            $sql .=",T2.AMOUNT              as AMOUNT          ";
            $sql .=",T2.SALEPRICE           as SALEPRICE       ";
            $sql .=",(T2.NEBIKI+T2.WARIBIKI+T2.MOD_NEBIKI+T2.S_MIXMATCH_NEBIKI+T2.S_NEBIKI+T2.S_WARIBIKI+T2.S_TYOSEIBIKI) as DISCTOTAL  ";
            $sql .=",T2.PURE_TOTAL                 as PURE_TOTAL     ";
            $sql .=",T2.SWITCH_OTC_KBN             as SWITCH_OTC_KBN ";
            $sql .=",T2.TAX_RATE                   as TAX_RATE       ";
            $sql .=",coalesce(T1.DEL_CUST_CD,'')   as DEL_CUST_CD   ";
            $sql .=",T1.HIDESEQ                    as SALE_HIDESEQ  ";
            $sql .="from TRN0101 T1 , TRN0102 T2  ";
            $sql .="where T1.HIDESEQ         = T2.HIDESEQ  ";
            $sql .="and   T1.ORGANIZATION_ID       = T2.ORGANIZATION_ID  ";   
             //$sql .="and   T1.ACCOUNT_NO       = T2.ACCOUNT_NO   "; 
            $sql .="and   T1.HIDESEQ         = :HIDESEQ  ";
            $sql .="and   T2.CANCEL_KBN      = '0'  ";
            $sql .="and   T1.STOP_KBN        = '0'  ";
            $sql .="and   T1.CUST_CD         = :CUST_CD  ";
            $sql .="and   T1.ORGANIZATION_ID = :ORGANIZATION_ID  ";
          //  $sql .="and   T1.LINE_NO         = T2.LINE_NO  ";
            $sql .="order by T1.HIDESEQ, T2.LINE_NO  ";
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_TRNDATA");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }

            $Log->trace("END SELECT_TRNDATA");            

            return $Datas;
        }

        /*インサートファッション*/
        public function INSERT_JSK4160($searchArray1,$POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START INSERT_JSK4160");    
            $param = array(
                ':INSUSER_CD'         => $searchArray1['INSUSER_CD'],
                ':UPDUSER_CD'         => $searchArray1['UPDUSER_CD'],
                ':DISABLED'           => $searchArray1['DISABLED'],
                ':LAN_KBN'            => $searchArray1['LAN_KBN'],
                ':CONNECT_KBN'        => $searchArray1['CONNECT_KBN'],
                ':ORGANIZATION_ID'    => $searchArray1['ORGANIZATION_ID'],
                ':HIDESEQ'            => $searchArray1['HIDESEQ_1'],
                ':WELLSET_DATE'       => $POST['WELLSET_DATE_NOW'],
                ':CUST_CD'            => $searchArray1['CUST_CD'],
                ':REJI_NO'            => $searchArray1['REJI_NO'],
                ':PROC_DATE'          => $searchArray1['PROC_DATE'],
                ':OPEN_CNT'           => $searchArray1['OPEN_CNT'],
                ':TRNDATE'            => $searchArray1['TRNDATE'],
                ':TRNTIME'            => $searchArray1['TRNTIME'],
                ':ACCOUNT_NO'         => $searchArray1['ACCOUNT_NO'],
                ':ACCOUNT_KBN'        => $searchArray1['ACCOUNT_KBN'],
                ':SECT_CD'            => $searchArray1['SECT_CD'],
                ':PROD_CD'            => $searchArray1['PROD_CD'],
                ':TAX_TYPE'           => $searchArray1['TAX_TYPE'],
                ':AMOUNT'             => $searchArray1['AMOUNT'],
                ':SALEPRICE'          => $searchArray1['SALEPRICE'],
                ':DISCTOTAL'          => $searchArray1['DISCTOTAL'],
                ':PURE_TOTAL'         => $searchArray1['PURE_TOTAL'],
                ':DEN_PURE_TOTAL'     => $searchArray1['DEN_PURE_TOTAL'],
                ':DEN_TAX'            => $searchArray1['DEN_TAX'],
                ':RECE_KBN'           => $searchArray1['RECE_KBN'],
                ':RECE_TOTAL'         => $searchArray1['RECE_TOTAL'],
                'SWITCH_OTC_KBN'      => $searchArray1['SWITCH_OTC_KBN'],
                ':TAX_RATE'           => $searchArray1['TAX_RATE'],
                ':DEL_CUST_CD'        => $searchArray1['DEL_CUST_CD'],
                ':SALE_HIDESEQ'       => $searchArray1['SALE_HIDESEQ'],
            );
            $sql .=" insert into  ". $_SESSION["SCHEMA"] . ".JSK4160 ( ";
            $sql .=" insuser_cd         ";//登録者コード
            $sql .=",insdatetime        ";//登録日時
            $sql .=",upduser_cd         ";//更新者コード
            $sql .=",upddatetime        ";//更新日時
            $sql .=",disabled           ";//データ区分
            $sql .=",lan_kbn            ";//LAN区分
            $sql .=",connect_kbn        ";//送信区分
            $sql .=",organization_id    ";//組織ID
            $sql .=",hideseq            ";//レコード番号
            $sql .=",wellset_date       ";//締処理日
            $sql .=",cust_cd            ";//顧客コード
            $sql .=",reji_no            ";//レジ番号
            $sql .=",proc_date          ";//営業日
            $sql .=",open_cnt           ";//開設回数
            $sql .=",trndate            ";//処理年月日
            $sql .=",trntime            ";//処理時刻
            $sql .=",account_no         ";//取引番号
            $sql .=",account_kbn        ";//取引区分
            $sql .=",sect_cd            ";//部門コード
            $sql .=",prod_cd            ";//商品コード
            $sql .=",tax_type           ";//税種別区分
            $sql .=",amount             ";//明細数量
            $sql .=",saleprice          ";//明細売価金額
            $sql .=",disctotal          ";//明細値引金額
            $sql .=",pure_total         ";//明細売上金額
            $sql .=",den_pure_total     ";//伝票-売上金額
            $sql .=",den_tax            ";//伝票-内消費税SEQ)
            $sql .=",rece_kbn           ";//入金区分
            $sql .=",rece_total         ";//入金金額
            $sql .=",switch_otc_kbn     ";//スイッチOTC薬控除
            $sql .=",tax_rate           ";//明細税率
            $sql .=",del_cust_cd        ";//納品先得意先コード
            $sql .=",sale_hideseq       ";//売上伝票番号
            $sql .=")values(            ";
            $sql .=":INSUSER_CD         ";
            $sql .=",now()              ";
            $sql .=",:UPDUSER_CD        ";
            $sql .=",now()              ";
            $sql .=",:DISABLED          ";
            $sql .=",:LAN_KBN           ";
            $sql .=",:CONNECT_KBN       ";
            $sql .=",:ORGANIZATION_ID   ";
            $sql .=",:HIDESEQ           ";
            $sql .=",:WELLSET_DATE      ";
            $sql .=",:CUST_CD           ";
            $sql .=",:REJI_NO           ";
            $sql .=",:PROC_DATE         ";
            $sql .=",:OPEN_CNT          ";
            $sql .=",:TRNDATE           ";
            $sql .=",:TRNTIME           ";
            $sql .=",:ACCOUNT_NO        ";
            $sql .=",:ACCOUNT_KBN       ";
            $sql .=",:SECT_CD           ";
            $sql .=",:PROD_CD           ";
            $sql .=",:TAX_TYPE          ";
            $sql .=",:AMOUNT            ";
            $sql .=",:SALEPRICE         ";
            $sql .=",:DISCTOTAL         ";
            $sql .=",:PURE_TOTAL        ";
            $sql .=",:DEN_PURE_TOTAL    ";
            $sql .=",:DEN_TAX           ";
            $sql .=",:RECE_KBN          ";
            $sql .=",:RECE_TOTAL        ";
            $sql .=",:SWITCH_OTC_KBN    ";
            $sql .=",:TAX_RATE          ";
            $sql .=",:DEL_CUST_CD       ";
            $sql .=",:SALE_HIDESEQ      ";
            $sql .=") ";
            
            print_r($sql);
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql, $param);
            $Log->trace("END INSERT_JSK4160");
        }
        /*掛売上金額取得*/
        public function SELECT_TRN0101_data($searchArray,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;
            $Log->trace("START SELECT_TRN0101_data");
            $param = array(
                ':HIDESEQ'             => $searchArray['HIDESEQ'],
               ':CUST_CD'             => $searchArray['CUST_CD'],
                ':ORGANIZATION_ID'     => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select "; 
            $sql .="'MPORTAL'               as INSUSER_CD      ";
            $sql .=",now()                  as INSDATETIME     ";
            $sql .=",'MPORTAL'              as UPDUSER_CD      ";
            $sql .=",now()                  as UPDDATETIME     ";
            $sql .=",'0'                    as DISABLED        ";
            $sql .=",T1.LAN_KBN             as LAN_KBN         ";
            $sql .=",T1.CONNECT_KBN         as CONNECT_KBN     ";
            $sql .=",T1.ORGANIZATION_ID     as ORGANIZATION_ID ";
            $sql .=",T1.CUST_CD             as CUST_CD         ";
            $sql .=",T1.REJI_NO             as REJI_NO         ";
            $sql .=",T1.PROC_DATE           as PROC_DATE       ";
            $sql .=",T1.OPEN_CNT            as OPEN_CNT        ";
            $sql .=",T1.TRNDATE             as TRNDATE         ";
            $sql .=",T1.TRNTIME             as TRNTIME         ";
            $sql .=",T1.ACCOUNT_NO          as ACCOUNT_NO      ";
            $sql .=",coalesce(T1.DEL_CUST_CD,'')   as DEL_CUST_CD   ";
            $sql .=",T1.HIDESEQ                    as SALE_HIDESEQ  ";
            $sql .="from TRN0101 T1 ";
            $sql .="where T1.HIDESEQ           = :HIDESEQ  ";
           $sql .="and   T1.CUST_CD           = :CUST_CD  ";
            $sql .="and   T1.ORGANIZATION_ID   = :ORGANIZATION_ID  ";
            $sql .="order by T1.HIDESEQ  ";
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_TRN0101_data");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }

            $Log->trace("END SELECT_TRN0101_data");            

            return $Datas;
        } 
        /*インサートファッション*/
        public function INSERT_JSK4160_1($searchArray1_1,$POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START INSERT_JSK4160_1");    
            $param = array(
                ':INSUSER_CD'         => $searchArray1_1['INSUSER_CD'],
                ':INSDATETIME'        => $searchArray1_1['INSDATETIME'],
                ':UPDUSER_CD'         => $searchArray1_1['UPDUSER_CD'],
                ':UPDDATETIME'        => $searchArray1_1['INSDATETIME'],
                ':DISABLED'           => $searchArray1_1['DISABLED'],
                ':LAN_KBN'            => $searchArray1_1['LAN_KBN'],
                ':CONNECT_KBN'        => $searchArray1_1['CONNECT_KBN'],
                ':ORGANIZATION_ID'    => $searchArray1_1['ORGANIZATION_ID'],
                ':HIDESEQ'            => $searchArray1_1['HIDESEQ_1'],
                ':WELLSET_DATE'       => $POST['WELLSET_DATE_NOW'],
                ':CUST_CD'            => $searchArray1_1['CUST_CD'],
                ':REJI_NO'            => $searchArray1_1['REJI_NO'],
                ':PROC_DATE'          => $searchArray1_1['PROC_DATE'],
                ':OPEN_CNT'           => $searchArray1_1['OPEN_CNT'],
                ':TRNDATE'            => $searchArray1_1['TRNDATE'],
                ':TRNTIME'            => $searchArray1_1['TRNTIME'],
                ':ACCOUNT_NO'         => $searchArray1_1['ACCOUNT_NO'],
                ':ACCOUNT_KBN'        => $searchArray1_1['ACCOUNT_KBN'],
                ':SECT_CD'            => $searchArray1_1['SECT_CD'],
                ':PROD_CD'            => $searchArray1_1['PROD_CD'],
                ':TAX_TYPE'           => $searchArray1_1['TAX_TYPE'],
                ':AMOUNT'             => $searchArray1_1['AMOUNT'],
                ':SALEPRICE'          => $searchArray1_1['SALEPRICE'],
                ':DISCTOTAL'          => $searchArray1_1['DISCTOTAL'],
                ':PURE_TOTAL'         => $searchArray1_1['PURE_TOTAL'],
                ':DEN_PURE_TOTAL'     => $searchArray1_1['DEN_PURE_TOTAL'],
                ':DEN_TAX'            => $searchArray1_1['DEN_TAX'],
                ':RECE_KBN'           => $searchArray1_1['RECE_KBN'],
                ':RECE_TOTAL'         => $searchArray1_1['RECE_TOTAL'],
                'SWITCH_OTC_KBN'      => $searchArray1_1['SWITCH_OTC_KBN'],
                ':TAX_RATE'           => $searchArray1_1['TAX_RATE'],
                ':DEL_CUST_CD'        => $searchArray1_1['DEL_CUST_CD'],
                ':SALE_HIDESEQ'       => $searchArray1_1['SALE_HIDESEQ'],
            );
            $sql .=" insert into  ". $_SESSION["SCHEMA"] . ".JSK4160 ( ";
            $sql .=" INSUSER_CD         ";//登録者コード
            $sql .=",INSDATETIME        ";//登録日時
            $sql .=",UPDUSER_CD         ";//更新者コード
            $sql .=",UPDDATETIME        ";//更新日時
            $sql .=",DISABLED           ";//データ区分
            $sql .=",LAN_KBN            ";//LAN区分
            $sql .=",CONNECT_KBN        ";//送信区分
            $sql .=",ORGANIZATION_ID    ";//組織ID
            $sql .=",HIDESEQ            ";//レコード番号
            $sql .=",WELLSET_DATE       ";//締処理日
            $sql .=",CUST_CD            ";//顧客コード
            $sql .=",REJI_NO            ";//レジ番号
            $sql .=",PROC_DATE          ";//営業日
            $sql .=",OPEN_CNT           ";//開設回数
            $sql .=",TRNDATE            ";//処理年月日
            $sql .=",TRNTIME            ";//処理時刻
            $sql .=",ACCOUNT_NO         ";//取引番号
            $sql .=",ACCOUNT_KBN        ";//取引区分
            $sql .=",SECT_CD            ";//部門コード
            $sql .=",PROD_CD            ";//商品コード
            $sql .=",TAX_TYPE           ";//税種別区分
            $sql .=",AMOUNT             ";//明細数量
            $sql .=",SALEPRICE          ";//明細売価金額
            $sql .=",DISCTOTAL          ";//明細値引金額
            $sql .=",PURE_TOTAL         ";//明細売上金額
            $sql .=",DEN_PURE_TOTAL     ";//伝票-売上金額
            $sql .=",DEN_TAX            ";//伝票-内消費税SEQ)
            $sql .=",RECE_KBN           ";//入金区分
            $sql .=",RECE_TOTAL         ";//入金金額
            $sql .=",SWITCH_OTC_KBN     ";//スイッチOTC薬控除
            $sql .=",TAX_RATE           ";//明細税率
            $sql .=",DEL_CUST_CD        ";//納品先得意先コード
            $sql .=",SALE_HIDESEQ       ";//売上伝票番号
            $sql .=")values(            ";
            $sql .=":INSUSER_CD         ";
            $sql .=",:INSDATETIME       ";
            $sql .=",:UPDUSER_CD        ";
            $sql .=",:UPDDATETIME       ";
            $sql .=",:DISABLED          ";
            $sql .=",:LAN_KBN           ";
            $sql .=",:CONNECT_KBN       ";
            $sql .=",:ORGANIZATION_ID   ";
            $sql .=",:HIDESEQ           ";
            $sql .=",:WELLSET_DATE      ";
            $sql .=",:CUST_CD           ";
            $sql .=",:REJI_NO           ";
            $sql .=",:PROC_DATE         ";
            $sql .=",:OPEN_CNT          ";
            $sql .=",:TRNDATE           ";
            $sql .=",:TRNTIME           ";
            $sql .=",:ACCOUNT_NO        ";
            $sql .=",:ACCOUNT_KBN       ";
            $sql .=",:SECT_CD           ";
            $sql .=",:PROD_CD           ";
            $sql .=",:TAX_TYPE          ";
            $sql .=",:AMOUNT            ";
            $sql .=",:SALEPRICE         ";
            $sql .=",:DISCTOTAL         ";
            $sql .=",:PURE_TOTAL        ";
            $sql .=",:DEN_PURE_TOTAL    ";
            $sql .=",:DEN_TAX           ";
            $sql .=",:RECE_KBN          ";
            $sql .=",:RECE_TOTAL        ";
            $sql .=",:SWITCH_OTC_KBN    ";
            $sql .=",:TAX_RATE          ";
            $sql .=",:DEL_CUST_CD       ";
            $sql .=",:SALE_HIDESEQ      ";
            $sql .=") ";
            
            print_r($sql);
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END INSERT_JSK4160_1");
        } 
        /*顧客売掛取引(売上伝票)取得*/
        
        /*掛売上金額取得*/
        public function SELECT_TRN0101_data_1($searchArray,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;
            $Log->trace("START SELECT_TRN0101_data_1");
            $param = array(
                ':HIDESEQ'          => $searchArray['HIDESEQ'],
                ':CUST_CD'          => $searchArray['CUST_CD'],
                ':ORGANIZATION_ID'  => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select "; 
            $sql .="'MPORTAL'               as INSUSER_CD      ";
            $sql .=",now()                  as INSDATETIME     ";
            $sql .=",'MPORTAL'              as UPDUSER_CD      ";
            $sql .=",now()                  as UPDDATETIME     ";
            $sql .=",'0'                    as DISABLED        ";
            $sql .=",T1.LAN_KBN             as LAN_KBN         ";
            $sql .=",T1.CONNECT_KBN         as CONNECT_KBN     ";
            $sql .=",T1.ORGANIZATION_ID     as ORGANIZATION_ID ";
            $sql .=",T1.CUST_CD             as CUST_CD         ";
            $sql .=",T1.REJI_NO             as REJI_NO         ";
            $sql .=",T1.PROC_DATE           as PROC_DATE       ";
            $sql .=",T1.OPEN_CNT            as OPEN_CNT        ";
            $sql .=",T1.TRNDATE             as TRNDATE         ";
            $sql .=",T1.TRNTIME             as TRNTIME         ";
            $sql .=",T1.ACCOUNT_NO          as ACCOUNT_NO      ";
            $sql .=",(T1.PURE_TOTAL )       as den_pure_total      ";  
            $sql .=",(T1.UTAX + T1.ITAX)       as den_tax          ";
            $sql .=",coalesce(T1.DEL_CUST_CD,'')   as DEL_CUST_CD  ";
            $sql .=",T1.HIDESEQ                    as SALE_HIDESEQ ";
            $sql .="from TRN0101 T1                 ";
            $sql .="where T1.HIDESEQ            = :HIDESEQ  ";
            $sql .="and   T1.CUST_CD            = :CUST_CD  ";
            $sql .="and   T1.ORGANIZATION_ID    = :ORGANIZATION_ID  ";
            $sql .="order by T1.HIDESEQ,T1.ORGANIZATION_ID          ";
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_TRN0101_data_1");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }

            $Log->trace("END SELECT_TRN0101_data_1");            

            return $Datas;
        } 
        /*インサートファッション*/
        public function INSERT_JSK4160_2($searchArray1_2,$POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START INSERT_JSK4160_2");    
            $param = array(
                ':INSUSER_CD'         => $searchArray1_2['INSUSER_CD'],
                ':INSDATETIME'        => $searchArray1_2['INSDATETIME'],
                ':UPDUSER_CD'         => $searchArray1_2['UPDUSER_CD'],
                ':UPDDATETIME'        => $searchArray1_2['INSDATETIME'],
                ':DISABLED'           => $searchArray1_2['DISABLED'],
                ':LAN_KBN'            => $searchArray1_2['LAN_KBN'],
                ':CONNECT_KBN'        => $searchArray1_2['CONNECT_KBN'],
                ':ORGANIZATION_ID'    => $searchArray1_2['ORGANIZATION_ID'],
                ':HIDESEQ'            => $searchArray1_2['HIDESEQ_1'],
                ':WELLSET_DATE'       => $POST['WELLSET_DATE_NOW'],
                ':CUST_CD'            => $searchArray1_2['CUST_CD'],
                ':REJI_NO'            => $searchArray1_2['REJI_NO'],
                ':PROC_DATE'          => $searchArray1_2['PROC_DATE'],
                ':OPEN_CNT'           => $searchArray1_2['OPEN_CNT'],
                ':TRNDATE'            => $searchArray1_2['TRNDATE'],
                ':TRNTIME'            => $searchArray1_2['TRNTIME'],
                ':ACCOUNT_NO'         => $searchArray1_2['ACCOUNT_NO'],
                ':ACCOUNT_KBN'        => $searchArray1_2['ACCOUNT_KBN'],
                ':SECT_CD'            => $searchArray1_2['SECT_CD'],
                ':PROD_CD'            => $searchArray1_2['PROD_CD'],
                ':TAX_TYPE'           => $searchArray1_2['TAX_TYPE'],
                ':AMOUNT'             => $searchArray1_2['AMOUNT'],
                ':SALEPRICE'          => $searchArray1_2['SALEPRICE'],
                ':DISCTOTAL'          => $searchArray1_2['DISCTOTAL'],
                ':PURE_TOTAL'         => $searchArray1_2['PURE_TOTAL'],
                ':DEN_PURE_TOTAL'     => $searchArray1_2['DEN_PURE_TOTAL'],
                ':DEN_TAX'            => $searchArray1_2['DEN_TAX'],
                ':RECE_KBN'           => $searchArray1_2['RECE_KBN'],
                ':RECE_TOTAL'         => $searchArray1_2['RECE_TOTAL'],
                'SWITCH_OTC_KBN'      => $searchArray1_2['SWITCH_OTC_KBN'],
                ':TAX_RATE'           => $searchArray1_2['TAX_RATE'],
                ':DEL_CUST_CD'        => $searchArray1_2['DEL_CUST_CD'],
                ':SALE_HIDESEQ'       => $searchArray1_2['SALE_HIDESEQ'],
            );
            $sql .=" insert into  ". $_SESSION["SCHEMA"] . ".JSK4160 ( ";
            $sql .=" INSUSER_CD         ";//登録者コード
            $sql .=",INSDATETIME        ";//登録日時
            $sql .=",UPDUSER_CD         ";//更新者コード
            $sql .=",UPDDATETIME        ";//更新日時
            $sql .=",DISABLED           ";//データ区分
            $sql .=",LAN_KBN            ";//LAN区分
            $sql .=",CONNECT_KBN        ";//送信区分
            $sql .=",ORGANIZATION_ID    ";//組織ID
            $sql .=",HIDESEQ            ";//レコード番号
            $sql .=",WELLSET_DATE       ";//締処理日
            $sql .=",CUST_CD            ";//顧客コード
            $sql .=",REJI_NO            ";//レジ番号
            $sql .=",PROC_DATE          ";//営業日
            $sql .=",OPEN_CNT           ";//開設回数
            $sql .=",TRNDATE            ";//処理年月日
            $sql .=",TRNTIME            ";//処理時刻
            $sql .=",ACCOUNT_NO         ";//取引番号
            $sql .=",ACCOUNT_KBN        ";//取引区分
            $sql .=",SECT_CD            ";//部門コード
            $sql .=",PROD_CD            ";//商品コード
            $sql .=",TAX_TYPE           ";//税種別区分
            $sql .=",AMOUNT             ";//明細数量
            $sql .=",SALEPRICE          ";//明細売価金額
            $sql .=",DISCTOTAL          ";//明細値引金額
            $sql .=",PURE_TOTAL         ";//明細売上金額
            $sql .=",DEN_PURE_TOTAL     ";//伝票-売上金額
            $sql .=",DEN_TAX            ";//伝票-内消費税SEQ)
            $sql .=",RECE_KBN           ";//入金区分
            $sql .=",RECE_TOTAL         ";//入金金額
            $sql .=",SWITCH_OTC_KBN     ";//スイッチOTC薬控除
            $sql .=",TAX_RATE           ";//明細税率
            $sql .=",DEL_CUST_CD        ";//納品先得意先コード
            $sql .=",SALE_HIDESEQ       ";//売上伝票番号
            $sql .=")values(            ";
            $sql .=":INSUSER_CD         ";
            $sql .=",:INSDATETIME       ";
            $sql .=",:UPDUSER_CD        ";
            $sql .=",:UPDDATETIME       ";
            $sql .=",:DISABLED          ";
            $sql .=",:LAN_KBN           ";
            $sql .=",:CONNECT_KBN       ";
            $sql .=",:ORGANIZATION_ID   ";
            $sql .=",:HIDESEQ           ";
            $sql .=",:WELLSET_DATE      ";
            $sql .=",:CUST_CD           ";
            $sql .=",:REJI_NO           ";
            $sql .=",:PROC_DATE         ";
            $sql .=",:OPEN_CNT          ";
            $sql .=",:TRNDATE           ";
            $sql .=",:TRNTIME           ";
            $sql .=",:ACCOUNT_NO        ";
            $sql .=",:ACCOUNT_KBN       ";
            $sql .=",:SECT_CD           ";
            $sql .=",:PROD_CD           ";
            $sql .=",:TAX_TYPE          ";
            $sql .=",:AMOUNT            ";
            $sql .=",:SALEPRICE         ";
            $sql .=",:DISCTOTAL         ";
            $sql .=",:PURE_TOTAL        ";
            $sql .=",:DEN_PURE_TOTAL    ";
            $sql .=",:DEN_TAX           ";
            $sql .=",:RECE_KBN          ";
            $sql .=",:RECE_TOTAL        ";
            $sql .=",:SWITCH_OTC_KBN    ";
            $sql .=",:TAX_RATE          ";
            $sql .=",:DEL_CUST_CD       ";
            $sql .=",:SALE_HIDESEQ      ";
            $sql .=") ";
            
            print_r($sql);
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END INSERT_JSK4160_2");
        }       
       /**/        
        /*顧客売掛取引税別明細データ取得*/
        public function SELECT_TAXDATA($searchArray,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;
            $Log->trace("START SELECT_TAXDATA");
            $param = array(
                ':HIDESEQ'          => $searchArray['HIDESEQ'],
                ':CUST_CD'          => $searchArray['CUST_CD'],
                ':ORGANIZATION_ID'  => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select "; 
            $sql .="'MPORTAL'               as INSUSER_CD      ";//登録者コード
            $sql .=",now()                  as INSDATETIME     ";//登録日時
            $sql .=",'MPORTAL'              as UPDUSER_CD      ";//更新者コード
            $sql .=",now()                  as UPDDATETIME     ";//更新日時
            $sql .=",T1.DISABLED            as DISABLED        ";//データ区分
            $sql .=",T1.LAN_KBN             as LAN_KBN         ";//LAN区分
            $sql .=",T1.CONNECT_KBN         as CONNECT_KBN     ";//送信区分
            $sql .=",T1.ORGANIZATION_ID     as ORGANIZATION_ID ";//組織ID
            $sql .=",T1.CUST_CD             as CUST_CD         ";//顧客コード
            $sql .=",T3.LINE_NO             as LINE_NO         ";//行番号
            $sql .=",T1.REJI_NO             as REJI_NO         ";//レジ番号
            $sql .=",T1.TRNDATE             as TRNDATE         ";//処理年月日
            $sql .=",T1.TRNTIME             as TRNTIME         ";//処理時刻
            $sql .=",T1.ACCOUNT_NO          as ACCOUNT_NO      ";//取引番号
            $sql .=",T3.TAX_TYPE            as TAX_TYPE        ";//税種別区分
            $sql .=",T3.TAX_RATE            as TAX_RATE        ";//税率
            $sql .=",T3.PURE_TOTAL          as PURE_TOTAL      ";//税別税率売上合計
            $sql .=",T3.TAX_TOTAL           as TAX_TOTAL       ";//税別税率税額合計
            $sql .="from TRN0101 T1,TRN0103 T3  ";
            $sql .="where T1.HIDESEQ          = T3.HIDESEQ          ";
            $sql .="and   T1.ORGANIZATION_ID  = T3.ORGANIZATION_ID  ";
         //   $sql .="and   T1.ACCOUNT_NO       = T3.ACCOUNT_NO   "; 
            $sql .="and   T1.CUST_CD          = :CUST_CD            ";
            $sql .="and   T1.HIDESEQ          = :HIDESEQ            ";
            $sql .="and   T1.ORGANIZATION_ID  = :ORGANIZATION_ID    ";
            //$sql .="and   T1.LINE_NO  = T3.LINE_NO                  ";
            $sql .="order by T1.HIDESEQ,T3.LINE_NO                  ";

            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_TAXDATA");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }

            $Log->trace("END SELECT_TAXDATA");                

            return $Datas;
        }        
        /*登録(売上伝票税別明細)*/
        public function INSERT_JSK4170($searchArray2,$POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START INSERT_JSK4170"); 
            $param = array(
                ':WELLSET_DATE'      => $POST['WELLSET_DATE_NOW'],
                ':INSUSER_CD'        => $searchArray2['INSUSER_CD'],
                ':UPDUSER_CD'        => $searchArray2['UPDUSER_CD'],
                ':DISABLED'          => $searchArray2['DISABLED'],
                ':LAN_KBN'           => $searchArray2['LAN_KBN'],
                ':CONNECT_KBN'       => $searchArray2['CONNECT_KBN'],
                ':ORGANIZATION_ID'   => $searchArray2['ORGANIZATION_ID'],
                ':HIDESEQ'           => $searchArray2['HIDESEQ_2'],
                ':CUST_CD'           => $searchArray2['CUST_CD'],
                ':LINE_NO'           => $searchArray2['LINE_NO'],
                ':REJI_NO'           => $searchArray2['REJI_NO'],
                ':TRNDATE'           => $searchArray2['TRNDATE'],
                ':TRNTIME'           => $searchArray2['TRNTIME'],
                ':ACCOUNT_NO'        => $searchArray2['ACCOUNT_NO'],
                ':TAX_TYPE'          => $searchArray2['TAX_TYPE'],
                ':TAX_RATE'          => $searchArray2['TAX_RATE'],
                ':PURE_TOTAL'        => $searchArray2['PURE_TOTAL'],
                ':TAX_TOTAL'         => $searchArray2['TAX_TOTAL'],
            );
            $sql .="insert into ". $_SESSION["SCHEMA"].".JSK4170( ";
            $sql .="insuser_cd               ";//登録者コード
            $sql .=",insdatetime             ";//登録日時
            $sql .=",upduser_cd              ";//更新者コード
            $sql .=",upddatetime             ";//更新日時
            $sql .=",disabled                ";//データ区分
            $sql .=",lan_kbn                 ";//LAN区分
            $sql .=",connect_kbn             ";//送信区分
            $sql .=",organization_id         ";//組織ID
            $sql .=",hideseq                 ";//レコード番号
            $sql .=",wellset_date            ";//締処理日
            $sql .=",cust_cd                 ";//顧客コード
            $sql .=",line_no                 ";//行番号
            $sql .=",reji_no                 ";//レジ番号
            $sql .=",trndate                 ";//処理年月日
            $sql .=",trntime                 ";//処理時刻
            $sql .=",account_no              ";//取引番号
            $sql .=",tax_type                ";//税種別区分
            $sql .=",tax_rate                ";//税率
            $sql .=",pure_total              ";//税別税率売上合計
            $sql .=",tax_total               ";//税別税率税額合計
            $sql .=")values(                 ";
            $sql .=":INSUSER_CD              ";
            $sql .=",now()                   ";
            $sql .=",:UPDUSER_CD             ";
            $sql .=",now()                   ";
            $sql .=",:DISABLED               ";
            $sql .=",:LAN_KBN                ";
            $sql .=",:CONNECT_KBN            ";
            $sql .=",:ORGANIZATION_ID        ";  
            $sql .=",:HIDESEQ                ";
            $sql .=",:WELLSET_DATE           ";
            $sql .=",:CUST_CD                ";
            $sql .=",:LINE_NO                ";
            $sql .=",:REJI_NO                ";
            $sql .=",:TRNDATE                ";
            $sql .=",:TRNTIME                ";
            $sql .=",:ACCOUNT_NO             ";
            $sql .=",:TAX_TYPE               ";
            $sql .=",:TAX_RATE               ";
            $sql .=",:PURE_TOTAL             ";
            $sql .=",:TAX_TOTAL              ";
            $sql .=")  ";
            print_r($sql);
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END INSERT_JSK4170");            
        }
        /*データ取得(掛入金)*/
        public function SELECT_TRN0301($searchArray,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_TRN0301");
            /*パラメータを設定する*/
            $param = array(
                ':HIDESEQ'            => $searchArray['HIDESEQ'],
                ':ORGANIZATION_ID'    => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select ";
            $sql .="sum(CASE WHEN RECE_KBN <> '8' THEN RECE_TOTAL  ELSE 0 END)  as RECE_TOTAL ";
            $sql .=",sum(CASE WHEN RECE_KBN =  '8' THEN RECE_TOTAL  ELSE 0 END) as RECE_DISC ";
            $sql .=",count(RECE_TOTAL)  as RECE_CNT ";
            $sql .="from TRN0301 ";
            $sql .="where HIDESEQ = :HIDESEQ ";
            $sql .="and   ORGANIZATION_ID    = :ORGANIZATION_ID ";
            $sql .="group by ORGANIZATION_ID ";    
            //$sql .="group by HIDESEQ,ORGANIZATION_ID ";           
            print_r($sql);

            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_TRN0301");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }
            $Log->trace("END SELECT_TRN0301");
            return $Datas;
        }        
        /*顧客売掛取引(売上伝票)取得*/
        public function SELECT_RACE_DATA($searchArray,$orgArray){
            // グローバル変数宣言
            global $DBA, $Log;
            $Log->trace("START SELECT_RACE_DATA");
            $param = array(
                ':HIDESEQ'             => $searchArray['HIDESEQ'],
                ':ORGANIZATION_ID'     => $orgArray['ORGANIZATION_ID'],
            );            
            $sql .="select "; 
            $sql .="'MPORTAL'               as INSUSER_CD      ";
            $sql .=",now()                  as INSDATETIME     ";
            $sql .=",'MPORTAL'              as UPDUSER_CD      ";
            $sql .=",now()                  as UPDDATETIME     ";
            $sql .=",'0'                    as DISABLED        ";
            $sql .=",T1.LAN_KBN             as LAN_KBN         ";
            $sql .=",T1.CONNECT_KBN         as CONNECT_KBN     ";
            $sql .=",T1.ORGANIZATION_ID     as ORGANIZATION_ID ";
            $sql .=",T1.CUST_CD             as CUST_CD         ";
            $sql .=",T1.REJI_NO             as REJI_NO         ";
            $sql .=",T1.PROC_DATE           as PROC_DATE       ";
            $sql .=",T1.OPEN_CNT            as OPEN_CNT        ";
            $sql .=",T1.TRNDATE             as TRNDATE         ";
            $sql .=",T1.TRNTIME             as TRNTIME         ";
            $sql .=",T1.ACCOUNT_NO          as ACCOUNT_NO      ";
            $sql .=",T3.RECE_KBN            as RECE_KBN        ";
            $sql .=",T3.RECE_TOTAL          as RECE_TOTAL      ";
            $sql .=",coalesce(T1.DEL_CUST_CD,'')   as DEL_CUST_CD     ";
            $sql .=",T1.HIDESEQ                    as SALE_HIDESEQ    ";
            $sql .="from TRN0101 T1 , TRN0301 T3        ";
            $sql .="where T1.HIDESEQ           = T3.HIDESEQ  ";
            $sql .="and   T1.ORGANIZATION_ID   = T3.ORGANIZATION_ID    ";
            $sql .="and   T1.HIDESEQ           = :HIDESEQ    ";
            $sql .="and   T1.ORGANIZATION_ID   = :ORGANIZATION_ID    ";
           // $sql .="and   T1.LINE_NO           = T3.LINE_NO    ";
            $sql .="order by T1.HIDESEQ, T3.LINE_NO      ";
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_RACE_DATA");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }

            $Log->trace("END SELECT_RACE_DATA");            

            return $Datas;
        }       
        /*顧客売掛取引(掛入金)*/       
        /*インサートファッション*/
        public function INSERT_JSK4160_3($searchArray1_3,$POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START INSERT_JSK4160_3");    
            $param = array(
                ':INSUSER_CD'         => $searchArray1_3['INSUSER_CD'],
                ':INSDATETIME'        => $searchArray1_3['INSDATETIME'],
                ':UPDUSER_CD'         => $searchArray1_3['UPDUSER_CD'],
                ':UPDDATETIME'        => $searchArray1_3['INSDATETIME'],
                ':DISABLED'           => $searchArray1_3['DISABLED'],
                ':LAN_KBN'            => $searchArray1_3['LAN_KBN'],
                ':CONNECT_KBN'        => $searchArray1_3['CONNECT_KBN'],
                ':ORGANIZATION_ID'    => $searchArray1_3['ORGANIZATION_ID'],
                ':HIDESEQ'            => $searchArray1_3['HIDESEQ_3'],
                ':WELLSET_DATE'       => $POST['WELLSET_DATE_NOW'],
                ':CUST_CD'            => $searchArray1_3['CUST_CD'],
                ':REJI_NO'            => $searchArray1_3['REJI_NO'],
                ':PROC_DATE'          => $searchArray1_3['PROC_DATE'],
                ':OPEN_CNT'           => $searchArray1_3['OPEN_CNT'],
                ':TRNDATE'            => $searchArray1_3['TRNDATE'],
                ':TRNTIME'            => $searchArray1_3['TRNTIME'],
                ':ACCOUNT_NO'         => $searchArray1_3['ACCOUNT_NO'],
                ':ACCOUNT_KBN'        => $searchArray1_3['ACCOUNT_KBN'],
                ':SECT_CD'            => $searchArray1_3['SECT_CD'],
                ':PROD_CD'            => $searchArray1_3['PROD_CD'],
                ':TAX_TYPE'           => $searchArray1_3['TAX_TYPE'],
                ':AMOUNT'             => $searchArray1_3['AMOUNT'],
                ':SALEPRICE'          => $searchArray1_3['SALEPRICE'],
                ':DISCTOTAL'          => $searchArray1_3['DISCTOTAL'],
                ':PURE_TOTAL'         => $searchArray1_3['PURE_TOTAL'],
                ':DEN_PURE_TOTAL'     => $searchArray1_3['DEN_PURE_TOTAL'],
                ':DEN_TAX'            => $searchArray1_3['DEN_TAX'],
                ':RECE_KBN'           => $searchArray1_3['RECE_KBN'],
                ':RECE_TOTAL'         => $searchArray1_3['RECE_TOTAL'],
                'SWITCH_OTC_KBN'      => $searchArray1_3['SWITCH_OTC_KBN'],
                ':TAX_RATE'           => $searchArray1_3['TAX_RATE'],
                ':DEL_CUST_CD'        => $searchArray1_3['DEL_CUST_CD'],
                ':SALE_HIDESEQ'       => $searchArray1_3['SALE_HIDESEQ'],
            );
            $sql .=" insert into  ". $_SESSION["SCHEMA"] . ".JSK4160 ( ";
            $sql .=" INSUSER_CD         ";//登録者コード
            $sql .=",INSDATETIME        ";//登録日時
            $sql .=",UPDUSER_CD         ";//更新者コード
            $sql .=",UPDDATETIME        ";//更新日時
            $sql .=",DISABLED           ";//データ区分
            $sql .=",LAN_KBN            ";//LAN区分
            $sql .=",CONNECT_KBN        ";//送信区分
            $sql .=",ORGANIZATION_ID    ";//組織ID
            $sql .=",HIDESEQ            ";//レコード番号
            $sql .=",WELLSET_DATE       ";//締処理日
            $sql .=",CUST_CD            ";//顧客コード
            $sql .=",REJI_NO            ";//レジ番号
            $sql .=",PROC_DATE          ";//営業日
            $sql .=",OPEN_CNT           ";//開設回数
            $sql .=",TRNDATE            ";//処理年月日
            $sql .=",TRNTIME            ";//処理時刻
            $sql .=",ACCOUNT_NO         ";//取引番号
            $sql .=",ACCOUNT_KBN        ";//取引区分
            $sql .=",SECT_CD            ";//部門コード
            $sql .=",PROD_CD            ";//商品コード
            $sql .=",TAX_TYPE           ";//税種別区分
            $sql .=",AMOUNT             ";//明細数量
            $sql .=",SALEPRICE          ";//明細売価金額
            $sql .=",DISCTOTAL          ";//明細値引金額
            $sql .=",PURE_TOTAL         ";//明細売上金額
            $sql .=",DEN_PURE_TOTAL     ";//伝票-売上金額
            $sql .=",DEN_TAX            ";//伝票-内消費税SEQ)
            $sql .=",RECE_KBN           ";//入金区分
            $sql .=",RECE_TOTAL         ";//入金金額
            $sql .=",SWITCH_OTC_KBN     ";//スイッチOTC薬控除
            $sql .=",TAX_RATE           ";//明細税率
            $sql .=",DEL_CUST_CD        ";//納品先得意先コード
            $sql .=",SALE_HIDESEQ       ";//売上伝票番号
            $sql .=")values(            ";
            $sql .=":INSUSER_CD         ";
            $sql .=",:INSDATETIME       ";
            $sql .=",:UPDUSER_CD        ";
            $sql .=",:UPDDATETIME       ";
            $sql .=",:DISABLED          ";
            $sql .=",:LAN_KBN           ";
            $sql .=",:CONNECT_KBN       ";
            $sql .=",:ORGANIZATION_ID   ";
            $sql .=",:HIDESEQ           ";
            $sql .=",:WELLSET_DATE      ";
            $sql .=",:CUST_CD           ";
            $sql .=",:REJI_NO           ";
            $sql .=",:PROC_DATE         ";
            $sql .=",:OPEN_CNT          ";
            $sql .=",:TRNDATE           ";
            $sql .=",:TRNTIME           ";
            $sql .=",:ACCOUNT_NO        ";
            $sql .=",:ACCOUNT_KBN       ";
            $sql .=",:SECT_CD           ";
            $sql .=",:PROD_CD           ";
            $sql .=",:TAX_TYPE          ";
            $sql .=",:AMOUNT            ";
            $sql .=",:SALEPRICE         ";
            $sql .=",:DISCTOTAL         ";
            $sql .=",:PURE_TOTAL        ";
            $sql .=",:DEN_PURE_TOTAL    ";
            $sql .=",:DEN_TAX           ";
            $sql .=",:RECE_KBN          ";
            $sql .=",:RECE_TOTAL        ";
            $sql .=",:SWITCH_OTC_KBN    ";
            $sql .=",:TAX_RATE          ";
            $sql .=",:DEL_CUST_CD       ";
            $sql .=",:SALE_HIDESEQ      ";
            $sql .=") ";            
            print_r($sql);
            /* sqlを実行 */
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END INSERT_JSK4160_3");
        }    
        /*売上伝票-締処理日更新*/ 
        public function UPDATE_TRN0101($POST,$orgArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START UPDATE_TRN0101");
            $param = array(
                ':CUST_CD'          => $POST['CUST_CD'],
                ':WELLSET'          => $POST['WELLSET_DATE'],
                ':UPDUSER_CD'       => 'MPORTAL' ,
                ':UPDDATETIME'      => "now()" ,
                ':LAN_KBN'          => 0 ,
                ':WELLSET_DATE_NOW' => $POST['WELLSET_DATE_NOW'],
                ':WELLSET_DATE_NOW_IN' => $POST['WELLSET_DATE_NOW'],
                ':ORGANIZATION_ID'   => $orgArray['ORGANIZATION_ID'],
            );
            $sql .="update ". $_SESSION["SCHEMA"].".TRN0101 set ";
            $sql .="  UPDUSER_CD     = :UPDUSER_CD ";
            $sql .="  ,UPDDATETIME   = :UPDDATETIME ";
            $sql .="  ,LAN_KBN       = :LAN_KBN ";
            $sql .="  ,WELLSET_DATE  = :WELLSET_DATE_NOW_IN ";
            $sql .="  where WELLSET_DATE   = '' ";
            $sql .="  and   STOP_KBN       = '0' ";
            $sql .="  and   PROC_DATE      > :WELLSET ";            
            $sql .="  and   PROC_DATE      <= :WELLSET_DATE_NOW ";            
            $sql .="  and CUST_CD          = :CUST_CD ";
            $sql .="  and ORGANIZATION_ID  = :ORGANIZATION_ID ";

            
            print_r($sql);
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END UPDATE_TRN0101");   
        }     
        /*J受注伝票-締処理日更新*/
        public function UPDATE_TRN7101($POST,$searchArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START UPDATE_TRN7101"); 
            $param = array(
                ':UPDUSER_CD'       => 'MPORTAL' ,
                ':UPDDATETIME'      => "now()" ,
                ':LAN_KBN'          => 0 ,
                ':WELLSET_DATE_NOW' => $POST['WELLSET_DATE_NOW'] ,
                ':HIDESEQ'          => $searchArray['HIDESEQ'],
            );
            
            $sql .="update ". $_SESSION["SCHEMA"].".TRN7101 set ";
            $sql .="UPDUSER_CD      = :UPDUSER_CD ";
            $sql .=",UPDDATETIME    = :UPDDATETIME ";
            $sql .=",LAN_KBN        = :LAN_KBN ";
            $sql .=",WELLSET_DATE   = :WELLSET_DATE_NOW ";
            $sql .="where HIDESEQ  in( ";
            $sql .="select J_HIDESEQ from ". $_SESSION["SCHEMA"].".TRN0102 ";
            $sql .="where HIDESEQ = :HIDESEQ) ";
            
            print_r($sql);
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END UPDATE_TRN7101");            
        }
        /*顧客売掛*/
        public function INSERT_JSK4150($searchArray3,$POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START INSERT_JSK4150");
            $param1 = array(
                ':INSUSER_CD'          => 'MPORTAL',
                ':UPDUSER_CD'          => 'MPORTAL',
                ':DISABLED'            => '0',
                ':LAN_KBN'             => '0',
                ':CONNECT_KBN'         => '0',
                ':ORGANIGATION_ID'     => $searchArray3['ORGANIGATION_ID'],
                ':CUST_CD'             => $POST['CUST_CD'],
                ':WELLSET_DATE'        => $POST['WELLSET_DATE_NOW'],
                ':BILL_NO'             => $searchArray3['BILL_NO'],
                ':BEF_BALANCE'         => $searchArray3['BEF_BALANCE'],
                ':SALE_TOTAL'          => $searchArray3['SALE_TOTAL'],
                ':SALE_TAX'            => $searchArray3['SALE_TAX'],
                ':RECE_TOTAL'          => $searchArray3['RECE_TOTAL'],
                ':RECE_DISC'           => $searchArray3['RECE_DISC'],
                ':NOW_BALANCE'         => $searchArray3['NOW_BALANCE'],
                ':SALE_CNT'            => $searchArray3['SALE_CNT'],
                ':RECE_CNT'            => $searchArray3['RECE_CNT'], 
            );   
            $sql .="insert into ". $_SESSION["SCHEMA"].".JSK4150 ( ";
            $sql .="insuser_cd         ";//登録者コード
            $sql .=",insdatetime       ";//登録日時
            $sql .=",upduser_cd        ";//更新者コード
            $sql .=",upddatetime       ";//更新日時
            $sql .=",disabled          ";//データ区分
            $sql .=",lan_kbn           ";//LAN区分
            $sql .=",connect_kbn       ";//送信区分
            $sql .=",organization_id   ";//組織ID
            $sql .=",cust_cd           ";//顧客コード
            $sql .=",wellset_date      ";//締処理日
            $sql .=",bill_no           ";//請求番号
            $sql .=",bef_balance       ";//前回残高
            $sql .=",sale_total        ";//買上金額
            $sql .=",sale_tax          ";//買上消費税
            $sql .=",rece_total        ";//入金金額
            $sql .=",rece_disc         ";//入金値引
            $sql .=",now_balance       ";//売掛残高
            $sql .=",sale_cnt          ";//売上伝票数
            $sql .=",rece_cnt          ";//入金伝票数
            $sql .=") values (         ";
            $sql .=":INSUSER_CD        ";
            $sql .=",now()             ";
            $sql .=",:UPDUSER_CD       ";
            $sql .=",now()             ";
            $sql .=",:DISABLED         ";
            $sql .=",:LAN_KBN          ";
            $sql .=",:CONNECT_KBN      ";
            $sql .=",:ORGANIGATION_ID  ";
            $sql .=",:CUST_CD          ";
            $sql .=",:WELLSET_DATE     ";
            $sql .=",:BILL_NO          ";
            $sql .=",:BEF_BALANCE      ";
            $sql .=",:SALE_TOTAL       ";
            $sql .=",:SALE_TAX         ";
            $sql .=",:RECE_TOTAL       ";
            $sql .=",:RECE_DISC        ";
            $sql .=",:NOW_BALANCE      ";
            $sql .=",:SALE_CNT         ";
            $sql .=",:RECE_CNT         ";
            $sql .=")                  ";
            print_r($sql);
            $result = $DBA->executeSQL_no_searchpath($sql,$param1);
            $Log->trace("END INSERT_JSK4150");    
        }
        /*顧客実績存在チェック*/
        public function SELECT_JSK4130($POST){
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START SELECT_JSK4130"); 
            $param = array(
                ':CUST_CD'          => $POST['CUST_CD'],
            );            
            $sql .="select CUST_CD  ";
            $sql .="from JSK4130 ";
            $sql .="where ";
            $sql .="CUST_CD = :CUST_CD ";
            
            print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql,$param);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END SELECT_JSK4130");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //print_r($data);
                $Datas[] = $data;
            }

            $Log->trace("END SELECT_JSK4130");            

            return $Datas;            
        }
        /*データなし-空レコード作成*/
        public function INSERT_JSK4130($POST,$searchArray) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START INSERT_JSK4130"); 
            $param = array(
                ':CUST_CD'             => $POST['CUST_CD'], 
                ':ORGANIZATION_ID'     => $searchArray['organization_id'],
            );    
            $sql .="insert into ". $_SESSION["SCHEMA"].".JSK4130( ";
            $sql .="insuser_cd      ";//登録者コード
            $sql .=",insdatetime    ";//登録日時
            $sql .=",upduser_cd     ";//更新者コード
            $sql .=",upddatetime    ";//更新日時
            $sql .=",disabled       ";//データ区分
            $sql .=",lan_kbn        ";//LAN区分
            $sql .=",connect_kbn    ";//送信区分
            $sql .=",cust_cd        ";//顧客コード
            $sql .=",last_date      ";//最新来店日
            $sql .=",sum_cnt        ";//累計来店回数
            $sql .=",sum_total      ";//累計純売上金額
            $sql .=",sum_profit     ";//前回残高
            $sql .=",sum_point      ";//買上金額
            $sql .=",etc_cnt        ";//買上消費税
            $sql .=",etc_total      ";//入金金額
            $sql .=",etc_profit     ";//入金値引
            $sql .=",wellset_date   ";//売掛残高
            $sql .=",now_balance    ";//売上伝票数
            $sql .=",p_limit_dt     ";//入金伝票数
            $sql .=",sender         ";
            $sql .=")values(        ";
            $sql .=" 'MPORTAL'      ";
            $sql .=",now()          ";
            $sql .=",'MPORTAL'      ";
            $sql .=",now()          ";
            $sql .=",'0'            ";
            $sql .=",'0'            ";
            $sql .=",'0'            ";
            $sql .=",:CUST_CD       ";
            $sql .=",''             ";
            $sql .=",0              ";
            $sql .=",0              ";
            $sql .=",0              ";
            $sql .=",0              ";
            $sql .=",0              ";
            $sql .=",0              ";
            $sql .=",0              ";
            $sql .=",''             ";
            $sql .=",0              ";
            $sql .=",''             ";
            $sql .=",:ORGANIZATION_ID  ";
            $sql .=")  ";
            print_r($sql);
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END INSERT_JSK4130");    
        } 
        /*顧客データある場合更新日を更新する*/
        public function UPDATE_JSK4130($POST) {
            // グローバル変数宣言
            global $DBA, $Log;            
            $Log->trace("START UPDATE_JSK4130"); 
            $param = array(
                ':UPDUSER_CD'       => 'MPORTAL' ,
                ':UPDDATETIME'      => "now()" ,
                ':LAN_KBN'          => 0 ,
                ':CUST_CD'          => $POST['CUST_CD'],
                ':WELLSET_DATE_NOW' => $POST['WELLSET_DATE_NOW'] ,
            );
            
            $sql .="update ". $_SESSION["SCHEMA"].".JSK4130 set ";
            $sql .="UPDUSER_CD      = :UPDUSER_CD ";
            $sql .=",UPDDATETIME    = :UPDDATETIME ";
            $sql .=",LAN_KBN        = :LAN_KBN ";
            $sql .=",WELLSET_DATE   = :WELLSET_DATE_NOW ";
            $sql .="where  ";
            $sql .="CUST_CD = :CUST_CD  ";
            
            print_r($sql);
            $result = $DBA->executeSQL_no_searchpath($sql,$param);
            $Log->trace("END UPDATE_JSK4130");                 
        }
    }    
?>