<?php
    /**
     * @file      顧客情報
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      顧客情報テーブルの管理を行う
     */

    // CustomerInputData.phpを読み込む
    require './Model/BaseCustomer.php';

    /**
     * 顧客情報クラス
     * @note   顧客情報テーブルの管理を行う。
     */
    class Customer extends BaseCustomer
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
         * 顧客情報データ取得
         * @param    $cust_cd
         * @return   SQLの実行結果
         */
        public function getCustomerData( $cust_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = ' SELECT * from mst0101';
            $sql .= '  WHERE cust_cd = :cust_cd  ';
            
            $searchArray = array( ':cust_cd' => $cust_cd );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }
        /**
         * 顧客情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
        public function getCustomersearchdata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = ' SELECT cust_cd,cust_nm,cust_kn,tel,tel4 from mst0101 where 1 = :cust_cd order by cust_cd desc';            
            $searchArray = array(':cust_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $customerDataList, $data);
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }	
        /**
         * 顧客情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
	        public function getCustomerlist()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = " SELECT string_agg(cust_cd,',') as list from mst0101 where 1 = :cust_cd ";            
            $searchArray = array(':cust_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }	
        
        /**
         * テーブルのデータを獲得します
         * @param    $user_detail_id, テーブル名、並び替え(例えば:"cust_cd,connect_kbn")
         * @return   SQLの実行結果
         */
        public function get_table_data( $cust_cd, $table_name, $orderby = "" )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");
            $field_nm   = "cust_cd";
            if($table_name == "mst0401"){$field_nm = "cust_type_cd";}
            $sql = " SELECT * from $table_name ";
            $sql .= "  WHERE $field_nm = :cust_cd  ";
			if ($orderby !== ""){
				$sql .= 'order by '.$orderby;
			}
            
            $searchArray = array( ':cust_cd' => $cust_cd );
//            echo "$sql // ";
//            echo "$cust_cd ..";
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $customerDataList, $data);
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }
        /**
         * テーブルのデータを獲得します
         * @param    $user_detail_id, テーブル名、並び替え(例えば:"cust_cd,connect_kbn")
         * @return   SQLの実行結果
         */
        public function get_jsk4110( $cust_cd)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");
            $field_nm   = "cust_cd";
            $sql  = " SELECT a.*,b.organization_name ";
            $sql .= " from jsk4110 a ";
            $sql .= " left join m_organization_detail b on b.organization_id = to_number(a.sender,'99')";       
            $sql .= "  WHERE $field_nm = :cust_cd  ";
            $sql .= " order by a.PROC_DATE desc,a.TRNTIME desc,a.HIDESEQ";
            $searchArray = array( ':cust_cd' => $cust_cd );
//            echo "$sql // ";
 //           echo "$cust_cd ..";
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList[] = $data;
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }          
        /**
         * テーブルのデータを獲得します
         * @param    $user_detail_id, テーブル名、並び替え(例えば:"cust_cd,connect_kbn")
         * @return   SQLの実行結果
         */
        public function get_jsk4120( $cust_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");
            $field_nm   = "cust_cd";
            $sql  = " SELECT a.*,b.organization_name ";
            $sql .= "  ,coalesce(NULLIF(c.PROD_NM,''),NULLIF(c.PROD_KN,''),'') as prod_nm ";            
            $sql .= "  ,coalesce(NULLIF(d.sect_nm,''),NULLIF(d.sect_kn,''),'') as sect_nm ";
            $sql .= " from jsk4120 a ";
            $sql .= " left join m_organization_detail b on b.organization_id = a.organization_id";
            $sql .= " left outer join MST0201 c on (c.PROD_CD = a.PROD_CD and a.organization_id = c.organization_id)";
            $sql .= " left outer join MST1201 d on (d.SECT_CD = a.SECT_CD and a.organization_id = c.organization_id)";          
            $sql .= "  WHERE $field_nm = :cust_cd  ";
            $sql .= " order by a.PROC_DATE desc,a.TRNTIME desc,a.HIDESEQ";
            $searchArray = array( ':cust_cd' => $cust_cd );
//            echo "$sql // ";
//            echo "$cust_cd ..";
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList[] = $data;
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }       

        /**
         * 地区データを獲得します
         * @param    
         * @return   SQLの実行結果
         */
        public function get_area_data( $area="" )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");
            if (count($area) > 1) {
            $sql = ' select t1.area_nm as area_nm1, t2.area_nm as area_nm2 from mst0501 t1 ';
			$sql .= ' left join mst0501 t2 on  t2.area_cd = :area02 ';
            $sql .= '  WHERE t1.AREA_CD = :area01  ';

            $searchArray = array( ':area01' => $area[0],':area02' => $area[1] );
            }
            elseif ($area[0] != ""){
            $sql = ' select area_nm as area_nm1 from mst0501';
            $sql .= '  WHERE AREA_CD = :area  ';

            $searchArray = array( ':area' => $area[0] );
            }
            else{
            $sql = ' select area_cd,area_nm,area_kn from mst0501';
            $sql .= '  WHERE 1 = :area  ';

            $searchArray = array( ':area' => 1 );                
            }
            //print_r('area: '.$sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if ($area[0] === ""){
                    $customerDataList[] = $data;
                }
                else{
                    $customerDataList = $data;
                }
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }
		
        /**
         * 顧客備考データを獲得します
         * @param    
         * @return   SQLの実行結果
         */
        public function get_note_data( $acust_b_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");
			$i=2;
			$scol = "";
			$scol  .= " t1.CUST_B_NM as cust_b_nm1 ";
			$sjoin = "";
                        $searchArray["cust_b_cd01"] = $acust_b_cd["cust_b_cd01"];
			for($i;$i<=count($acust_b_cd);$i++ )
			{
				$key 	= "cust_b_cd".sprintf('%02d', $i);
				$scol  .= " ,t".$i.".CUST_B_NM as cust_b_nm".$i;
				$sjoin .= " left join mst0701 t".$i." on  t".$i.".CUST_B_CD = :".$key;
				$searchArray[$key] = $acust_b_cd[$key];
			}
            $sql = ' select '.$scol.' from mst0701 t1 ';
            $sql .= $sjoin;
            $sql .= '  WHERE t1.CUST_B_CD = :cust_b_cd01  ';
//            echo $sql;
//            print_r($acust_b_cd);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            // 一覧表を格納する空の配列宣言
            $customerDataList = array();
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }
            $Log->trace("END getCustomerData");
            return $customerDataList;
        }

        /**
         * 顧客ランクデータを獲得します
         * @param    
         * @return   SQLの実行結果
         */
        public function get_rank_data( $CUST_RANK_CD )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = ' select CUST_RANK_NM  from mst8401';
            $sql .= '  WHERE CUST_RANK_CD = :CUST_RANK_CD  ';

            $searchArray = array( ':CUST_RANK_CD' => $CUST_RANK_CD );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
               array_push( $customerDataList, $data);
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }
	        /**
         * 顧客ランクデータを獲得します
         * @param    
         * @return   SQLの実行結果
         */
        public function get_Relationship_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = ' select relation_cd,relation_nm  from mst6101';
            $sql .= "  WHERE disabled = :disabled  ";

            $searchArray = array( ':disabled' => '0' );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $customerDataList, $data);
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        } 
        /**
         * 顧客ランクデータを獲得します
         * @param    
         * @return   SQLの実行結果
         */
        public function get_staff_data ($staff_search)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_staff_data");

            $sql  = ' select m.organization_id,m.staff_cd,m.staff_nm, o.organization_name  from mst0601 m';
            $sql .= "  left join m_organization_detail o on o.organization_id = m.organization_id";
            $sql .= "  where 1=:staff_search and (m.organization_id,m.staff_cd) in (values $staff_search)";
            $sql .= '  order by organization_id';
            $searchArray = array( ':staff_search' => 1 );
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
               array_push( $customerDataList, $data);
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }
        
        public function get_cust_size ()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_staff_data");

            $sql  = ' select custsize  from mst0010  where 1 = :organization_id limit 1';

            $searchArray = array( ':organization_id' => 1 );
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }        
        
        /**
         * 顧客情報新規データ登録
         * @param    $postArray(顧客情報、または従業員詳細マスタへの登録情報)
         * @param    $addFlag(新規登録時にはtrue)
         * @return   SQLの実行結果
         */
        public function insertdata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insertdata");

            // 操作対象テーブルが1個のためトランザクションは使用しない

            $query  = "";
            $query .= "insert into ". $_SESSION["SCHEMA"] .".mst0101 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CUST_CD           ";
            $query .= "    ,CUST_NM           ";
            $query .= "    ,CUST_KN           ";
            $query .= "    ,ZIP               ";
            $query .= "    ,ADDR1             ";
            $query .= "    ,ADDR2             ";
            $query .= "    ,ADDR3             ";
            $query .= "    ,TEL               ";
            $query .= "    ,TEL4              ";
            $query .= "    ,FAX               ";
            $query .= "    ,HPHONE            ";
            $query .= "    ,EMAIL             ";
            $query .= "    ,IMODE             ";
            $query .= "    ,BIRTH             ";
            $query .= "    ,BIRTH_MONTH       ";
            $query .= "    ,SEX               ";
            $query .= "    ,MEMORIAL          ";
            $query .= "    ,SHOP_CD           ";
            $query .= "    ,CUST_TYPE_CD      ";
            $query .= "    ,AREA_CD01         ";
            $query .= "    ,AREA_CD02         ";
            $query .= "    ,STAFF_CD          ";
            $query .= "    ,INTRODUCTER       ";
            $query .= "    ,DM_TYPE           ";
            $query .= "    ,DISSENDDM         ";
            $query .= "    ,DISSENDEMAIL      ";
            $query .= "    ,DISSENDIMODE      ";
            $query .= "    ,PERSON_KBN        ";
            $query .= "    ,FEES_KBN          ";
            $query .= "    ,FEES_RECE_KBN     ";
            $query .= "    ,CRED_IMPO_KBN     ";
            $query .= "    ,CUST_SUMDAY       ";
            $query .= "    ,DESIG_PRICE_KBN   ";
            $query .= "    ,POINT_MAGNI       ";
            $query .= "    ,ORDER_NO          ";
            $query .= "    ,OPTION_FLAG       ";
            $query .= "    ,SPCL_SALEPRICE_KBN";
            $query .= "    ,CUST_RES_CD1      ";
            $query .= "    ,CUST_RES_CD2      ";
            $query .= "    ,CUST_RES_CD3      ";
            $query .= "    ,CUST_RES_CD4      ";
            $query .= "    ,CUST_RES_CD5      ";
            $query .= "    ,CUST_RES_CD6      ";
            $query .= "    ,CUST_RES_CD7      ";
            $query .= "    ,CUST_RES_CD8      ";
            $query .= "    ,CUST_RES_CD9      ";
            $query .= "    ,CUST_RES_CD10     ";
            $query .= "    ,CUST_B_CD01       ";
            $query .= "    ,CUST_B_CD02       ";
            $query .= "    ,CUST_B_CD03       ";
            $query .= "    ,CUST_B_CD04       ";
            $query .= "    ,CUST_B_CD05       ";
            $query .= "    ,CUST_B_CD06       ";
            $query .= "    ,CUST_B_CD07       ";
            $query .= "    ,CUST_B_CD08       ";
            $query .= "    ,CUST_B_CD09       ";
            $query .= "    ,CUST_B_CD10       ";
            $query .= "    ,CUST_B_CD11       ";
            $query .= "    ,CUST_B_CD12       ";
            $query .= "    ,CUST_B_CD13       ";
            $query .= "    ,CUST_B_CD14       ";
            $query .= "    ,CUST_B_CD15       ";
            $query .= "    ,CUST_B_CD16       ";
            $query .= "    ,CUST_B_CD17       ";
            $query .= "    ,CUST_B_CD18       ";
            $query .= "    ,CUST_B_CD19       ";
            $query .= "    ,CUST_B_CD20       ";
            $query .= "    ,CUST_RANK_CD      ";
            $query .= "    ,CO_NAME           ";
            $query .= "    ,CO_ZIP            ";
            $query .= "    ,CO_ADDR1          ";
            $query .= "    ,CO_ADDR2          ";
            $query .= "    ,CO_ADDR3          ";
            $query .= "    ,CO_TEL            ";
            $query .= "    ,CO_FAX            ";
            $query .= "    ,NOTE1             ";
            $query .= "    ,NOTE2             ";
            $query .= "    ,NOTE3             ";
            $query .= "    ,NOTE4             ";
            $query .= "    ,NOTE5             ";
            $query .= "    ,PUBLISH           ";
            $query .= "    ,LIMITDATE         ";
            $query .= "    ,RENEW             ";
            $query .= "    ,sender            ";            
            $query .= "  ) values (            ";            
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CUST_CD           ";
            $query .= "    ,:CUST_NM           ";
            $query .= "    ,:CUST_KN           ";
            $query .= "    ,:ZIP               ";
            $query .= "    ,:ADDR1             ";
            $query .= "    ,:ADDR2             ";
            $query .= "    ,:ADDR3             ";
            $query .= "    ,:TEL               ";
            $query .= "    ,:TEL4              ";
            $query .= "    ,:FAX               ";
            $query .= "    ,:HPHONE            ";
            $query .= "    ,:EMAIL             ";
            $query .= "    ,:IMODE             ";
            $query .= "    ,:BIRTH             ";
            $query .= "    ,:BIRTH_MONTH       ";
            $query .= "    ,:SEX               ";
            $query .= "    ,:MEMORIAL          ";
            $query .= "    ,:SHOP_CD           ";
            $query .= "    ,:CUST_TYPE_CD      ";
            $query .= "    ,:AREA_CD01         ";
            $query .= "    ,:AREA_CD02         ";
            $query .= "    ,:STAFF_CD          ";
            $query .= "    ,:INTRODUCTER       ";
            $query .= "    ,:DM_TYPE           ";
            $query .= "    ,:DISSENDDM         ";
            $query .= "    ,:DISSENDEMAIL      ";
            $query .= "    ,:DISSENDIMODE      ";
            $query .= "    ,:PERSON_KBN        ";
            $query .= "    ,:FEES_KBN          ";
            $query .= "    ,:FEES_RECE_KBN     ";
            $query .= "    ,:CRED_IMPO_KBN     ";
            $query .= "    ,:CUST_SUMDAY       ";
            $query .= "    ,:DESIG_PRICE_KBN   ";
            $query .= "    ,:POINT_MAGNI       ";
            $query .= "    ,:ORDER_NO          ";
            $query .= "    ,:OPTION_FLAG       ";
            $query .= "    ,:SPCL_SALEPRICE_KBN";
            $query .= "    ,:CUST_RES_CD1      ";
            $query .= "    ,:CUST_RES_CD2      ";
            $query .= "    ,:CUST_RES_CD3      ";
            $query .= "    ,:CUST_RES_CD4      ";
            $query .= "    ,:CUST_RES_CD5      ";
            $query .= "    ,:CUST_RES_CD6      ";
            $query .= "    ,:CUST_RES_CD7      ";
            $query .= "    ,:CUST_RES_CD8      ";
            $query .= "    ,:CUST_RES_CD9      ";
            $query .= "    ,:CUST_RES_CD10     ";
            $query .= "    ,:CUST_B_CD01       ";
            $query .= "    ,:CUST_B_CD02       ";
            $query .= "    ,:CUST_B_CD03       ";
            $query .= "    ,:CUST_B_CD04       ";
            $query .= "    ,:CUST_B_CD05       ";
            $query .= "    ,:CUST_B_CD06       ";
            $query .= "    ,:CUST_B_CD07       ";
            $query .= "    ,:CUST_B_CD08       ";
            $query .= "    ,:CUST_B_CD09       ";
            $query .= "    ,:CUST_B_CD10       ";
            $query .= "    ,:CUST_B_CD11       ";
            $query .= "    ,:CUST_B_CD12       ";
            $query .= "    ,:CUST_B_CD13       ";
            $query .= "    ,:CUST_B_CD14       ";
            $query .= "    ,:CUST_B_CD15       ";
            $query .= "    ,:CUST_B_CD16       ";
            $query .= "    ,:CUST_B_CD17       ";
            $query .= "    ,:CUST_B_CD18       ";
            $query .= "    ,:CUST_B_CD19       ";
            $query .= "    ,:CUST_B_CD20       ";
            $query .= "    ,:CUST_RANK_CD      ";
            $query .= "    ,:CO_NAME           ";
            $query .= "    ,:CO_ZIP            ";
            $query .= "    ,:CO_ADDR1          ";
            $query .= "    ,:CO_ADDR2          ";
            $query .= "    ,:CO_ADDR3          ";
            $query .= "    ,:CO_TEL            ";
            $query .= "    ,:CO_FAX            ";
            $query .= "    ,:NOTE1             ";
            $query .= "    ,:NOTE2             ";
            $query .= "    ,:NOTE3             ";
            $query .= "    ,:NOTE4             ";
            $query .= "    ,:NOTE5             ";
            $query .= "    ,:PUBLISH           ";
            $query .= "    ,:LIMITDATE         ";
            $query .= "    ,:RENEW             ";
            $query .= "    ,'0'                ";            
            $query .= "  )                     ";
            
            $param[":INSUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":INSDATETIME"]	=	"now()";
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CUST_CD"]		=	$_POST["CUST_CD"];
            $param[":CUST_NM"]		=	$_POST["CUST_NM"];
            $param[":CUST_KN"]		=	$_POST["CUST_KN"];
            $param[":ZIP"]		=	$_POST["ZIP"];
            $param[":ADDR1"]		=	$_POST["ADDR1"];
            $param[":ADDR2"]		=	$_POST["ADDR2"];
            $param[":ADDR3"]		=	"";
            $param[":TEL"]		=	$_POST["TEL"];
            $param[":TEL4"]		=	$_POST["TEL4"];
            $param[":FAX"]		=	$_POST["FAX"];
            $param[":HPHONE"]		=	"";
            $param[":EMAIL"]		=	$_POST["EMAIL"];
            $param[":IMODE"]		=	$_POST["IMODE"];
            $param[":BIRTH"]		=	$_POST["BIRTH"];
//EDITSTR kanderu 2021/10/18
            //$param[":BIRTH_MONTH"]	=	$_POST["BIRTH_MONTH"];
            $param[":BIRTH_MONTH"]	=	$_POST["BIRTH_MON"];
//EDITEND kanderu 2021/10/18
            $param[":SEX"]		=	$_POST["SEX"];
            $param[":MEMORIAL"]		=	$_POST["MEMORIAL"];
            $param[":SHOP_CD"]		=	$_POST["SHOP_CD"];
            $param[":CUST_TYPE_CD"]	=	$_POST["CUST_TYPECD"];
            $param[":AREA_CD01"]	=	$_POST["AREA_CD01"];
            $param[":AREA_CD02"]	=	$_POST["AREA_CD02"];
            $param[":STAFF_CD"]		=	$_POST["STAFF_CD"];
            $param[":INTRODUCTER"]	=	"";
            $param[":DM_TYPE"]		=	"";
            $param[":DISSENDDM"]	=	$_POST["DISSENDDM"];
            $param[":DISSENDEMAIL"]	=	$_POST["DISSENDEMAIL"];
            $param[":DISSENDIMODE"]	=	$_POST["DISSENDIMODE"];
            $param[":PERSON_KBN"]	=	"";
            $param[":FEES_KBN"]		=	"";
            $param[":FEES_RECE_KBN"]	=	$_POST["FEES_RECE_KBN"];
            $param[":CRED_IMPO_KBN"]	=	$_POST["CRED_IMPO_KBN"];
            $param[":CUST_SUMDAY"]	=	$_POST["CUST_SUMDAY"];
            $param[":DESIG_PRICE_KBN"]	=	$_POST["DESIG_PRICE_KBN"];
            $param[":POINT_MAGNI"]	=	"0";
            $param[":ORDER_NO"]		=	"";
            $param[":OPTION_FLAG"]	=	$_POST["OPTION_FLAG"];
            $param[":SPCL_SALEPRICE_KBN"]=	$_POST["SPCL_SALEPRICE_KBN"];
            $param[":CUST_RES_CD1"]	=	$_POST["VIP_CD"];
            $param[":CUST_RES_CD2"]	=	"";
            $param[":CUST_RES_CD3"]	=	"";
            $param[":CUST_RES_CD4"]	=	"";
            $param[":CUST_RES_CD5"]	=	"";
            $param[":CUST_RES_CD6"]	=	"";
            $param[":CUST_RES_CD7"]	=	"";
            $param[":CUST_RES_CD8"]	=	"";
            $param[":CUST_RES_CD9"]	=	"";
            $param[":CUST_RES_CD10"]	=	"";
            $param[":CUST_B_CD01"]	=	$_POST["CUST_B_CD01"];
            $param[":CUST_B_CD02"]	=	$_POST["CUST_B_CD02"];
            $param[":CUST_B_CD03"]	=	$_POST["CUST_B_CD03"];
            $param[":CUST_B_CD04"]	=	$_POST["CUST_B_CD04"];
            $param[":CUST_B_CD05"]	=	$_POST["CUST_B_CD05"];
            $param[":CUST_B_CD06"]	=	$_POST["CUST_B_CD06"];
            $param[":CUST_B_CD07"]	=	$_POST["CUST_B_CD07"];
            $param[":CUST_B_CD08"]	=	$_POST["CUST_B_CD08"];
            $param[":CUST_B_CD09"]	=	$_POST["CUST_B_CD09"];
            $param[":CUST_B_CD10"]	=	$_POST["CUST_B_CD10"];
            $param[":CUST_B_CD11"]	=	"";
            $param[":CUST_B_CD12"]	=	"";
            $param[":CUST_B_CD13"]	=	"";
            $param[":CUST_B_CD14"]	=	"";
            $param[":CUST_B_CD15"]	=	"";
            $param[":CUST_B_CD16"]	=	"";
            $param[":CUST_B_CD17"]	=	"";
            $param[":CUST_B_CD18"]	=	"";
            $param[":CUST_B_CD19"]	=	"";
            $param[":CUST_B_CD20"]	=	"";
            $param[":CUST_RANK_CD"]	=	$_POST["RANK_CD"];
            $param[":CO_NAME"]		=	"";
            $param[":CO_ZIP"]		=	"";
            $param[":CO_ADDR1"]		=	"";
            $param[":CO_ADDR2"]		=	"";
            $param[":CO_ADDR3"]		=	"";
            $param[":CO_TEL"]		=	"";
            $param[":CO_FAX"]		=	"";
            $param[":NOTE1"]		=	$_POST["MEMO1"];
            $param[":NOTE2"]		=	$_POST["MEMO2"];
            $param[":NOTE3"]		=	$_POST["MEMO3"];
            $param[":NOTE4"]		=	$_POST["MEMO4"];
            $param[":NOTE5"]		=	$_POST["MEMO5"];
            $param[":PUBLISH"]		=	"";
            $param[":LIMITDATE"]	=	"";
            $param[":RENEW"]		=	"";
            
            //print_r($param);
            //echo $query;
            // SQLの実行
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            //console.log($result);
            $param  = "";
            $query  = "";
            $query .= " delete from mst9999_mportal ";
            $query .= " where cust_cd = :cust_cd and table_name = :table_name";
            $param["cust_cd"] = $_POST["CUST_CD"];
            $param["table_name"] = "MST0101";
            $result = $DBA->executeSQL($query, $param);            
        }
        
        /**
         * 顧客情報新規データ登録
         * @param    $postArray(顧客情報、または従業員詳細マスタへの登録情報)
         * @param    $addFlag(新規登録時にはtrue)
         * @return   SQLの実行結果
         */
        public function updatedata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START updatedata");        
            $strcust_cd = $_POST['CUST_CD'];
            $query  = "";
            $query  = "update ". $_SESSION["SCHEMA"].".mst0101 set  ";            
            $query .= "UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "DISABLED          = 	:DISABLED          , ";
            $query .= "LAN_KBN           = 	:LAN_KBN           , ";
            $query .= "CUST_NM           = 	:CUST_NM           , ";
            $query .= "CUST_KN           = 	:CUST_KN           , ";
            $query .= "ZIP               = 	:ZIP               , ";
            $query .= "ADDR1             = 	:ADDR1             , ";
            $query .= "ADDR2             = 	:ADDR2             , ";
            $query .= "TEL               = 	:TEL               , ";
            $query .= "TEL4              = 	:TEL4              , ";
            $query .= "FAX               = 	:FAX               , ";
            $query .= "EMAIL             = 	:EMAIL             , ";
            $query .= "IMODE             = 	:IMODE             , ";
            $query .= "BIRTH             = 	:BIRTH             , ";
            $query .= "BIRTH_MONTH       = 	:BIRTH_MONTH       , ";
            $query .= "SEX               = 	:SEX               , ";
            $query .= "MEMORIAL          = 	:MEMORIAL          , ";
            $query .= "SHOP_CD           = 	:SHOP_CD           , ";
            $query .= "CUST_TYPE_CD      = 	:CUST_TYPE_CD      , ";
            $query .= "AREA_CD01         = 	:AREA_CD01         , ";
            $query .= "AREA_CD02         = 	:AREA_CD02         , ";
            $query .= "DISSENDDM         = 	:DISSENDDM         , ";
            $query .= "DISSENDEMAIL      = 	:DISSENDEMAIL      , ";
            $query .= "DISSENDIMODE      = 	:DISSENDIMODE      , ";
            $query .= "FEES_RECE_KBN     = 	:FEES_RECE_KBN     , ";
            $query .= "CRED_IMPO_KBN     = 	:CRED_IMPO_KBN     , ";
            $query .= "CUST_SUMDAY       = 	:CUST_SUMDAY       , ";
            $query .= "DESIG_PRICE_KBN   = 	:DESIG_PRICE_KBN   , ";
            $query .= "OPTION_FLAG       = 	:OPTION_FLAG       , ";
            $query .= "SPCL_SALEPRICE_KBN= 	:SPCL_SALEPRICE_KBN, ";
            $query .= "CUST_RES_CD1      = 	:CUST_RES_CD1      , ";
            $query .= "CUST_B_CD01       = 	:CUST_B_CD01       , ";
            $query .= "CUST_B_CD02       = 	:CUST_B_CD02       , ";
            $query .= "CUST_B_CD03       = 	:CUST_B_CD03       , ";
            $query .= "CUST_B_CD04       = 	:CUST_B_CD04       , ";
            $query .= "CUST_B_CD05       = 	:CUST_B_CD05       , ";
            $query .= "CUST_B_CD06       = 	:CUST_B_CD06       , ";
            $query .= "CUST_B_CD07       = 	:CUST_B_CD07       , ";
            $query .= "CUST_B_CD08       = 	:CUST_B_CD08       , ";
            $query .= "CUST_B_CD09       = 	:CUST_B_CD09       , ";
            $query .= "CUST_B_CD10       = 	:CUST_B_CD10       , ";
            $query .= "CUST_RANK_CD      = 	:CUST_RANK_CD      , ";
            $query .= "NOTE1             = 	:NOTE1             , ";
            $query .= "NOTE2             = 	:NOTE2             , ";
            $query .= "NOTE3             = 	:NOTE3             , ";
            $query .= "NOTE4             = 	:NOTE4             , ";
            $query .= "NOTE5             = 	:NOTE5             , ";
            $query .= "sender            = 	'0'                  ";
            $query .= " where CUST_CD    =      '$strcust_cd'        ";
            
            
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CUST_NM"]          =	$_POST["CUST_NM"];
            $param[":CUST_KN"]          =	$_POST["CUST_KN"];
            $param[":ZIP"]              =	$_POST["ZIP"];
            $param[":ADDR1"]            =	$_POST["ADDR1"];
            $param[":ADDR2"]            =	$_POST["ADDR2"];
            $param[":TEL"]              =	$_POST["TEL"];
            $param[":TEL4"]             =	$_POST["TEL4"];
            $param[":FAX"]              =	$_POST["FAX"];
            $param[":EMAIL"]            =	$_POST["EMAIL"];
            $param[":IMODE"]            =	$_POST["IMODE"];
            $param[":BIRTH"]            =	$_POST["BIRTH"];
//EDITSTR kanderu 2021/10/18
            // $param[":BIRTH_MONTH"]	    =	$_POST["BIRTH_MON"];
            $param[":BIRTH_MONTH"]	    =	$_POST["BIRTH_MON"];
//EDITEND kanderu 2021/10/18
            $param[":SEX"]              =	$_POST["SEX"];
            $param[":MEMORIAL"]         =	$_POST["MEMORIAL"];
            $param[":SHOP_CD"]          =	$_POST["SHOP_CD"];
            $param[":CUST_TYPE_CD"]	=	$_POST["CUST_TYPECD"];
            $param[":AREA_CD01"]	=	$_POST["AREA_CD01"];
            $param[":AREA_CD02"]	=	$_POST["AREA_CD02"];
            $param[":DISSENDDM"]	=	$_POST["DISSENDDM"];
            $param[":DISSENDEMAIL"]	=	$_POST["DISSENDEMAIL"];
            $param[":DISSENDIMODE"]	=	$_POST["DISSENDIMODE"];
            $param[":FEES_RECE_KBN"]	=	$_POST["FEES_RECE_KBN"];
            $param[":CRED_IMPO_KBN"]	=	$_POST["CRED_IMPO_KBN"];
            $param[":CUST_SUMDAY"]	=	$_POST["CUST_SUMDAY"];
            $param[":DESIG_PRICE_KBN"]	=	$_POST["DESIG_PRICE_KBN"];
            $param[":OPTION_FLAG"]	=	$_POST["OPTION_FLAG"];
            $param[":SPCL_SALEPRICE_KBN"]=	$_POST["SPCL_SALEPRICE_KBN"];
            $param[":CUST_RES_CD1"]	=	$_POST["VIP_CD"];
            $param[":CUST_B_CD01"]	=	$_POST["CUST_B_CD01"];
            $param[":CUST_B_CD02"]	=	$_POST["CUST_B_CD02"];
            $param[":CUST_B_CD03"]	=	$_POST["CUST_B_CD03"];
            $param[":CUST_B_CD04"]	=	$_POST["CUST_B_CD04"];
            $param[":CUST_B_CD05"]	=	$_POST["CUST_B_CD05"];
            $param[":CUST_B_CD06"]	=	$_POST["CUST_B_CD06"];
            $param[":CUST_B_CD07"]	=	$_POST["CUST_B_CD07"];
            $param[":CUST_B_CD08"]	=	$_POST["CUST_B_CD08"];
            $param[":CUST_B_CD09"]	=	$_POST["CUST_B_CD09"];
            $param[":CUST_B_CD10"]	=	$_POST["CUST_B_CD10"];
            $param[":CUST_RANK_CD"]	=	$_POST["RANK_CD"];
            $param[":NOTE1"]            =	$_POST["MEMO1"];
            $param[":NOTE2"]            =	$_POST["MEMO2"];
            $param[":NOTE3"]            =	$_POST["MEMO3"];
            $param[":NOTE4"]            =	$_POST["MEMO4"];
            $param[":NOTE5"]            =	$_POST["MEMO5"];
            
           // print_r($_POST);
            //echo $query;
            //print_r($param);
            // SQLの実行
            $result = $DBA->executeSQL_no_searchpath($query, $param); 
            //print_r($result);
        }
        
        public function deldata($cust_cd){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deldata");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0101 ";
            $query .= " set UPDUSER_CD='delete' where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst0101 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            
            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".JSK4160 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param); 
            
            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".JSK4150 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);  
            
            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".JSK4140 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);  

            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".JSK4130 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);  

            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".JSK4120 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);  

            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".JSK4110 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);  

            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".MST0103 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            
            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".MST1701 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);

            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".MST1901 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);
           
            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".MST2001 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);
  
            $param  = "";
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".MST0101_CHANGED ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            
            $param  = "";
            $query  = "";
            $query .= " insert into mst9999_mportal (table_name, organization_id, cust_cd) values ";
            $query .= " (:table_name, :organization_id, :cust_cd) ";
            $param["table_name"] = "JSK4140";
            $param["organization_id"] = 0;
            $param["cust_cd"] = $cust_cd;
            $result = $DBA->executeSQL($query, $param);
            
            $param["table_name"] = "JSK4130";
            $result = $DBA->executeSQL($query, $param);
            
            $param["table_name"] = "JSK4110";
            $result = $DBA->executeSQL($query, $param);
                        
            $param["table_name"] = "MST0101";
            $result = $DBA->executeSQL($query, $param); 

            $param["table_name"] = "MST0103";
            $result = $DBA->executeSQL($query, $param); 

            $param["table_name"] = "MST1901";
            $result = $DBA->executeSQL($query, $param);             

            $param["table_name"] = "MST2001";
            $result = $DBA->executeSQL($query, $param); 
            
            $Log->trace("START deldata");             
        }
        
        public function add_area($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_area");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst0501 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,area_cd            ";
            $query .= "    ,area_nm            ";
            $query .= "    ,area_kn            ";
            $query .= "    ,sender             ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:area_cd           ";
            $query .= "    ,:area_nm           ";
            $query .= "    ,:area_kn           ";
            $query .= "    ,:sender            ";
            $query .= "     )                  ";
//print_r($data); 
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){

                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['area_cd'])){
                    $param[":INSUSER_CD"]	=	$_SESSION["LOGIN"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":area_cd"]		=	$data[$sind]['area_cd'];
                    $param[":area_nm"]		=	$data[$sind]['area_nm'];
                    $param[":area_kn"]		=	$data[$sind]['area_kn'];
                    $param[":sender"]		=	'0';                    
                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param = [];
                    $param[":area_cd"] = $data[$sind]['area_cd'];                    
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where area_cd = :area_cd";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            }
            $Log->trace("END add_area"); 
        }
        public function del_area($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_area");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0501 ";
            $query .= " set UPDUSER_CD='delete' where area_cd = :area_cd";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST0501_CHANGED ";
            $query_del .= " where area_cd = :area_cd";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST0501";
            $query_del_1 .= " where area_cd = :area_cd";
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['area_cd'])){            
                    $param["area_cd"] = $data[$sind]['area_cd'];
//print_r("del upd:".$query);
//print_r("delchange:".$query_del);
//print_r("delete:".$query_del_1);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del_1, $param);
                }
            }
            $Log->trace("END del_area"); 
        }
        public function upd_area($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_area");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0501 set  ";            
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "  DISABLED          = 	:DISABLED          , ";
            $query .= "  LAN_KBN           = 	:LAN_KBN           , ";
            $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , ";            
            
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":SENDER"]   	=       '0';
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
//print_r($data);
                if(is_numeric ($data[$sind]['area_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "area_cd"){
                            $param[':'.$key] = $value;
                            continue;
                        }
                        if($value === ""||$value){
                            $do_query = 1;
                            $query .= "  $key =    :$key , ";
                            $param[':'.$key] = $value;                        
                        }
                    }
                    
                    if($do_query === 0){
                        continue;
                    }
                    $query .= "  SENDER = :SENDER ";                    
                    $query .= " where area_cd = :area_cd";
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $param = [];
                    $param[":area_cd"] = $data[$sind]['area_cd'];
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where area_cd = :area_cd";
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_area");
        }
        public function del_mst0103(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_mst0103");
            $query  = "";  
            $query .= " update ". $_SESSION["SCHEMA"].".mst0103 ";
            $query .= " set UPDUSER_CD='delete' where cust_cd = :cust_cd";
            $param["cust_cd"] = $_POST["CUST_CD"];
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst0103 ";
            $query .= " where cust_cd = :cust_cd";
            $param["cust_cd"] = $_POST["CUST_CD"];
            $result = $DBA->executeSQL_no_searchpath($query, $param);            
            
            $Log->trace("END del_mst0103");
        }
        public function add_mst0103(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_mst0103");
            $query  = "";
            $query .= "insert into ". $_SESSION["SCHEMA"] .".mst0103 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CUST_CD           ";
            $query .= "    ,fam_seq           ";
            $query .= "    ,fam_cd            ";
            $query .= "    ,fam_nm            ";
            $query .= "    ,fam_kn            ";
            $query .= "    ,sex               ";
            $query .= "    ,birth             ";
            $query .= "    ,birth_month       ";
            $query .= "    ,sender            ";            
            $query .= "  ) values (           ";            
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CUST_CD          ";
            $query .= "    ,:fam_seq          ";
            $query .= "    ,:fam_cd           ";
            $query .= "    ,:fam_nm           ";
            $query .= "    ,:fam_kn           ";
            $query .= "    ,:sex              ";
            $query .= "    ,:birth            ";
            $query .= "    ,:birth_month      ";            
            $query .= "    ,'0'               ";            
            $query .= "  )                    ";            
            //print_r($query);
            
            $param[":INSUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":INSDATETIME"]	=	"now()";
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CUST_CD"]		=	$_POST["CUST_CD"];
            $j=1;
            for ($i=1;$i<9;$i++){
                //if($_POST["FAM_SEX".$i] && $_POST["FAM_SEX".$i]>0){
                    
                    $param[":fam_seq"]		=	"";
                    $param[":fam_cd"]		=	"";
                    $param[":fam_nm"]		=	"";
                    $param[":fam_kn"]		=	"";
                    $param[":sex"]              =	"";
                    $param[":birth"]		=	"";
                    $param[":birth_month"]	=	"";  
                    
                    $param[":fam_seq"]		=	$j;
                    $param[":fam_cd"]		=	$_POST["FAM_CD".$i];
                    $param[":fam_nm"]		=	$_POST["FAM_NM".$i];
                    $param[":fam_kn"]		=	$_POST["FAM_KN".$i];
                    $param[":sex"]		=	$_POST["FAM_SEX".$i];
                    $param[":birth"]		=	str_replace('/','',$_POST["BIRTH".$i]);
                    $param[":birth_month"]	=	$_POST["BIRTH_MON".$i];
                    $j++;
                    
                    //print_r($param);
                    //print_r($query);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                //}
            }
            //print_r($_POST);
            $Log->trace("END add_mst0103");
        }
    }
?>