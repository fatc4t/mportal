<?php
    /**
     * @file      顧客情報
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      顧客情報テーブルの管理を行う
     */

    // CustomerInputData.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 顧客情報クラス
     * @note   顧客情報テーブルの管理を行う。
     */
    class DataMaintenance extends BaseModel
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
         public function insupddata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insupddata");
            $all_data = json_decode($_POST['new_data'],true);
            foreach( $all_data as $row){
                if(!$row['hideseq']){
                    // case: new row
                    $query   = "" ;
                    $query  .=  "insert into ". $_SESSION["SCHEMA"].".trn1601(" ;
                    $query  .=  "    insuser_cd" ;
                    $query  .=  "   ,insdatetime" ;
                    $query  .=  "   ,upduser_cd" ;
                    $query  .=  "   ,upddatetime" ;
                    $query  .=  "   ,disabled" ;
                    $query  .=  "   ,lan_kbn" ;
                    $query  .=  "   ,organization_id" ;
                    $query  .=  "   ,hideseq" ;
                    $query  .=  "   ,denno" ;
                    $query  .=  "   ,supp_cd" ;
                    $query  .=  "   ,ord_date" ;
                    $query  .=  "   ,arr_date" ;
                    $query  .=  "   ,contract_month" ;
                    $query  .=  "   ,split_cnt" ;
                    $query  .=  "   ,gyomu_data_kbn" ;
                    $query  .=  "   ,inp_date" ;
                    $query  .=  "   ,inp_time" ;
                    $query  .=  "   ,van_data_kbn" ;
                    $query  .=  "   ,van_data_date" ;
                    $query  .=  "   ,order_kbn" ;
                    $query  .=  "   ,send_fax_seq" ;
                    $query  .=  "   ,send_fax_kbn" ;
                    $query  .=  "   ,send_fax_date" ;
                    $query  .=  "   ,consproc_kbn" ;
                    $query  .=  "   ,consproc_date" ;
                    $query  .=  " )select  ";
                    $query  .=  " 	 :upduser_cd as insuser_cd ";
                    $query  .=  " 	,now() as insdatetime ";
                    $query  .=  " 	,:upduser_cd as upduser_cd ";
                    $query  .=  " 	,now() as upddatetime ";
                    $query  .=  " 	,'0'  as disabled ";
                    $query  .=  " 	,'0' as lan_kbn ";
                    $query  .=  " 	, :org_id as organization_id ";
                    $query  .=  " 	,coalesce(max(hideseq),0)+ 1 as hideseq ";
                    $query  .=  " 	,lpad((coalesce(max(hideseq),0)+ 1)::text, 6, '0') as denno ";
                    $query  .=  " 	,'".$row["supp_cd"]."' as supp_cd ";
                    $query  .=  " 	,".$row["ord_date"]." as ord_date ";
                    $query  .=  " 	,".$row["arr_date"]." as arr_date ";
                    $query  .=  " 	,'' as contract_month ";
                    $query  .=  " 	,'0' as split_cnt ";
                    $query  .=  " 	,'3' as gyomu_data_kbn ";
                    $query  .=  " 	,to_char(now(),'yyyymmdd') as inp_date ";
                    $query  .=  " 	,to_char(now(),'hh24mmss') as inp_time ";
                    $query  .=  " 	,'0' as van_data_kbn ";
                    $query  .=  " 	,'' as van_data_date ";
                    $query  .=  " 	,(select ord_form_kbn from ". $_SESSION["SCHEMA"].".mst1101 where supp_cd = :supp_cd and organization_id = :org_id) as order_kbn ";
                    $query  .=  " 	,'' as send_fax_seq ";
                    $query  .=  " 	,'0' as send_fax_kbn ";
                    $query  .=  " 	,'' as send_fax_date ";
                    $query  .=  " 	,'0' as consproc_kbn ";
                    $query  .=  " 	,'' as consproc_date ";
                    $query  .=  " from ". $_SESSION["SCHEMA"].".trn1601 where organization_id = :org_id  ";
                    $query  .=  " limit 1 ";

                    $param = [];
                    $param[":upduser_cd"]   =	$_SESSION["LOGIN"];
                    $param[":org_id"]       =	$row["org_id"];
                    $param[":supp_cd"]      =	$row["supp_cd"];
                    //$param[":ord_date"]     =	$row["ord_date"];
                    //$param[":arr_date"]     =	$row["arr_date"];
//print_r($query);
//print_r($param);                    
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
//print_r('result:');
//print_r($result);
                    
                    $query   = "";
                    $query  .=  " insert into ". $_SESSION["SCHEMA"].".trn1602 ( ";
                    $query  .=  " 	  insuser_cd ";
                    $query  .=  " 	, insdatetime ";
                    $query  .=  " 	, upduser_cd ";
                    $query  .=  " 	, upddatetime ";
                    $query  .=  " 	, disabled ";
                    $query  .=  " 	, lan_kbn ";
                    $query  .=  " 	, organization_id ";
                    $query  .=  " 	, hideseq ";
                    $query  .=  " 	, line_no ";
                    $query  .=  " 	, prod_cd ";
                    $query  .=  " 	, ord_amount ";
                    $query  .=  " 	, costprice ";
                    $query  .=  " 	, saleprice ";
                    $query  .=  " 	, auto_order_kbn ";
                    $query  .=  " 	, supp_state_kbn ";
                    $query  .=  " 	, eos_seq ";
                    $query  .=  " 	, eos_denno ";
                    $query  .=  " 	, eos_line_no ";
                    $query  .=  " 	, eos_rcv_date ";
                    $query  .=  " 	, eos_rcv_err_cd ";
                    $query  .=  " 	, costget_kbn ";
                    $query  .=  " 	) select  ";
                    $query  .=  " 	  :upduser_cd as insuser_cd ";
                    $query  .=  " 	, now() as insdatetime ";
                    $query  .=  " 	, :upduser_cd as upduser_cd ";
                    $query  .=  " 	, now() as upddatetime ";
                    $query  .=  " 	, '0' as disabled ";
                    $query  .=  " 	, '0' as lan_kbn ";
                    $query  .=  " 	, :org_id as organization_id ";
                    $query  .=  " 	, (select max(hideseq) from ". $_SESSION["SCHEMA"].".trn1601 where organization_id = :org_id limit 1) as hideseq ";
                    $query  .=  " 	, '1' as line_no ";
                    $query  .=  " 	, '".$row["prod_cd"]."' as prod_cd ";
                    $query  .=  " 	, :ord_amount as ord_amount ";
                    $query  .=  " 	, :costprice as costprice ";
                    $query  .=  " 	, mp.saleprice as saleprice ";
                    $query  .=  " 	, case  ";
                    $query  .=  " 		when mp.auto_order_kbn != '0' then mp.auto_order_kbn ";
                    $query  .=  " 		else mb1.strvalue ";
                    $query  .=  " 	  end as auto_order_kbn ";
                    $query  .=  " 	, '0' as supp_state_kbn ";
                    $query  .=  " 	, '-1' as eos_seq ";
                    $query  .=  " 	, ''  as eos_denno ";
                    $query  .=  " 	, '0' as eos_line_no ";
                    $query  .=  " 	, ''  as eos_rcv_date ";
                    $query  .=  " 	, ''  as eos_rcv_err_cd ";
                    $query  .=  " 	, '1' as costget_kbn ";
                    $query  .=  " from  ". $_SESSION["SCHEMA"].".mst0201 mp ";
                    $query  .=  " left join ". $_SESSION["SCHEMA"].".mst0011 mb1 on (mb1.organization_id = mp.organization_id and mb1.detail_cd = '102000015S' and mb1.reji_no = '01' ) ";
                    $query  .=  " where mp.prod_cd = :prod_cd and mp.organization_id = :org_id  ";
                    $query  .=  " order by hideseq desc limit 1 ";                    
                    
                    $param = [];
                    $param[":upduser_cd"]   =	$_SESSION["LOGIN"];
                    $param[":org_id"]       =	$row["org_id"];
                    $param[":prod_cd"]      =	$row["prod_cd"];
                    $param[":ord_amount"]   =	$row["amount"];
                    $param[":costprice"]    =	$row["costprice"];
                    /*
                    if($_POST['head']){
                        $param[":supp_state_kbn"] = '1';
                    }else{
                        $param[":supp_state_kbn"] = '0';
                    }*/
//print_r($query);
//print_r($param);                    
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    
//print_r('result:');
//print_r($result);
                }else{
                     // case update row
                    $query   = "";
                    $query  .=  " update ". $_SESSION["SCHEMA"].".trn1602 set  ";
                    $query  .=  " 	 upddatetime = now() ";
                    $query  .=  " 	,upduser_cd	= :upduser_cd ";
                    $query  .=  " 	,prod_cd = '".$row["prod_cd"]."' ";
                    $query  .=  " 	,ord_amount = :ord_amount ";
                    $query  .=  " 	,costprice = :costprice ";
                    $query  .=  " 	,saleprice = (select saleprice from ". $_SESSION["SCHEMA"].".mst0201 where prod_cd = :prod_cd::text and organization_id = :org_id) ";
                    $query  .=  " 	,supp_state_kbn = :supp_state_kbn ";
                    $query  .=  " 	,auto_order_kbn = (select  ";
                    $query  .=  "                               case  ";
                    $query  .=  "                                   when mp.auto_order_kbn != '0' then mp.auto_order_kbn  ";
                    $query  .=  "                                   else mb1.strvalue  ";
                    $query  .=  " 				end as auto_order_kbn  ";
                    $query  .=  " 			    from ". $_SESSION["SCHEMA"].".mst0201 mp ";
                    $query  .=  " 			    left join ". $_SESSION["SCHEMA"].".mst0011 mb1  ";
                    $query  .=  "                               on (mb1.organization_id = mp.organization_id and mb1.detail_cd = '102000015S' and mb1.reji_no = '01' ) ";
                    $query  .=  "                           where mp.prod_cd = :prod_cd and mp.organization_id = :org_id ";
                    $query  .=  "                           limit 1 ";
                    $query  .=  "                           ) ";
                    $query  .=  " where organization_id = :org_id and hideseq = :hideseq and line_no = :line_no ";
                    
                    $param = [];
                    $param[":upduser_cd"]   =	$_SESSION["LOGIN"];
                    $param[":org_id"]       =	$row["org_id"];
                    $param[":prod_cd"]      =	$row["prod_cd"];
                    $param[":ord_amount"]   =	$row["amount"];
                    $param[":costprice"]    =	$row["costprice"];
                    $param[":hideseq"]    =	$row["hideseq"];
                    $param[":line_no"]    =	$row["line_no"];
                    //echo 'head: '.$_POST['head'];
                    if($_POST['head']){
                        $param[":supp_state_kbn"] = '1';
                    }else{
                        $param[":supp_state_kbn"] = '0';
                    }
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    //print_r($query);
                    //print_r($param);
                    //print_r('result:');
                    //print_r($result);
                }

            }
            //print_r($query);
            //print_r($param); 
                   
//print_r($result);  
            $Log->trace("END insupddata"); 
        }
         public function virtualdeletedata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START virtualdeletedata");        
            $query  = "";
            $query .= "update ". $_SESSION["SCHEMA"].".trn1602 set  ";
            $query .= "supp_state_kbn = '1' , ";
            $query .= "disabled = '2' ";
            $query .= " where (organization_id,hideseq,line_no) in ".$_POST["key_list"];
            $query .= " and supp_state_kbn = :supp_state_kbn ";

            $param[":supp_state_kbn"]	= '0';
            //print_r($query);
            //print_r($param); 
            $result = $DBA->executeSQL_no_searchpath($query, $param);                    
//print_r($result);  
            $Log->trace("END virtualdeletedata"); 
        }        
         public function deletedata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deletedata");        
            $query  = "";
            $query .= "delete from  ". $_SESSION["SCHEMA"].".trn1602 where  ";
            $query .= " (organization_id,hideseq,line_no) in ".$_POST["key_list"];
            $query .= " and supp_state_kbn = :supp_state_kbn ";

            $param[":supp_state_kbn"]	= '0';
            //print_r($query);
            //print_r($param); 
            $result = $DBA->executeSQL_no_searchpath($query, $param); 
            // delete from trn1601 all hideseq that are not in trn1602
            $query  = "";
            $query .= "delete from  ". $_SESSION["SCHEMA"].".trn1601 where  ";
            $query .= " (organization_id,hideseq) not in ";
            $query .= " (select distinct organization_id, hideseq from ". $_SESSION["SCHEMA"].".trn1602)"; 
            $query .= " and disabled = :disabled "; 
            $param = [];
            $param[":disabled"]	= '0';
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL_no_searchpath($query, $param); 
//print_r($result);  
            $Log->trace("END deletedata"); 
        }                  
        public function getdata(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getdata");
            $sql  = '';
            $sql .= " select  ";
            $sql .= " 	distinct on(t2.hideseq,t2.line_no) ";
            $sql .= " 	 t2.hideseq,t2.line_no ";
            $sql .= " 	,t1.ORD_DATE ";
            $sql .= " 	,t1.insuser_cd ";
            $sql .= " 	,t1.organization_id as org_id ";
            $sql .= " 	,o.abbreviated_name as org_nm ";
            $sql .= " 	,t1.supp_cd ";
            $sql .= " 	,coalesce(ms.supp_nm,ms.supp_kn,'') as supp_nm ";
            $sql .= " 	,t2.prod_cd ";
            $sql .= " 	,coalesce(mp.prod_nm,mp.prod_kn,'') as prod_nm ";
            $sql .= " 	,mp.PROD_CAPA_KN ";
            $sql .= " 	,t2.ord_amount as amount ";
            $sql .= " 	,t2.COSTPRICE ";
            $sql .= " 	,t1.ARR_DATE ";
            $sql .= " 	,mp2.TOTAL_STOCK_AMOUT ";
            $sql .= " 	,t2.SUPP_STATE_KBN ";
            $sql .= " 	,t2.disabled ";
            $sql .= " 	,case ";
            $sql .= " 		when t2.disabled = '2' then '本部取消' ";            
            $sql .= " 		when t2.SUPP_STATE_KBN = '1' then '本部' ";
            $sql .= " 		else '' ";
            $sql .= " 	 end as status ";            
            $sql .= " 	,case ";
            $sql .= " 		when mb1.strvalue = '0' then '0'  ";
            $sql .= " 		when mp.AUTO_ORDER_KBN = '1' then mp.JUST_STOCK_AMOUT ";
            $sql .= " 		else mp2.FILL_AMOUNT ";
            $sql .= " 	end as ORDER_POINT ";
            $sql .= " 	,case ";
            $sql .= " 		when mb2.strvalue != '0' and m2.order_lot is not null then  m2.order_lot ";
            $sql .= " 		else mp.order_lot ";
            $sql .= " 	end as order_lot ";
            $sql .= " 	,peri_costprice ";
            $sql .= "  from TRN1601 t1 ";
            $sql .= "  left join m_organization_detail o on (o.organization_id = t1.organization_id) ";
            $sql .= "  left join TRN1602 t2  on (t1.organization_id = t2.organization_id and t1.HIDESEQ = t2.HIDESEQ) ";
            $sql .= "  left join MST0201 mp  on (t1.organization_id = mp.organization_id and t2.PROD_CD = mp.PROD_CD) ";
            $sql .= "  left join MST1101 ms  on (t1.organization_id = ms.organization_id and t1.SUPP_CD = ms.SUPP_CD) ";
            $sql .= "  left join MST0204 mp2 on (t1.organization_id = mp2.organization_id and mp2.PROD_CD = mp.PROD_CD and mp2.LOCATION_NO = '' ) ";
            $sql .= "  left join mst0011 mb1 on (mb1.organization_id = t1.organization_id and mb1.detail_cd = '102000015S' and mb1.reji_no = '01' ) ";
            $sql .= "  left join mst0011 mb2 on (mb2.organization_id = t1.organization_id and mb2.detail_cd = '102000030S' and mb2.reji_no = '20' ) ";
            $sql .= "  left join MST1401 m2  on (	   m2.prod_cd = t2.prod_cd  ";
            $sql .= "  						   and m2.supp_cd = t1.supp_cd  ";
            $sql .= "  						   and m2.order_date_str <= t1.ORD_DATE  ";
            $sql .= "  						   and m2.order_date_end >= t1.ORD_DATE  ";
            $sql .= "  						   and m2.organization_id = t1.organization_id  ";
            $sql .= "  						   and m2.peri_costprice = t2.costprice ";
            $sql .= "  						  )	    ";
            $sql .= "  where 1 = :val ";
            $sql .= " 	and t1.VAN_DATA_KBN   <> '1' ";
            $sql .= " 	and t1.SEND_FAX_KBN   <> '1' ";
            $sql .= " order by t2.hideseq,t2.line_no,peri_costprice desc "; 
            
            $param[":val"] = 1;
            
            //print_r($sql);
            //print_r($param);            
            $result = $DBA->executeSQL($sql, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getdata");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['ord_date']][$data['org_id']][] = $data;
//print_r($data); 
            }            
//print_r($Datas);                   
            $Log->trace("END getdata");
            return $Datas;
        }            
        public function getprod_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getprod_detail");
            $sql  = "";
            $sql .= " select ";
            $sql .= " 	distinct on( mp.organization_id,mp.prod_cd)";
            $sql .= " 	 mp.organization_id as org_id";
            $sql .= " 	,o.abbreviated_name as org_nm";
            $sql .= " 	,mp.prod_cd";
            $sql .= " 	,regexp_replace (replace(coalesce(mp.prod_nm,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_nm";
            $sql .= " 	,regexp_replace (replace(coalesce(mp.prod_kn,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_kn";
            $sql .= " 	,mp.head_supp_cd as supp_cd";
            $sql .= " 	,mp.sect_cd";
            $sql .= " 	,mp.appo_prod_kbn";
            $sql .= " 	,coalesce(mp2.TOTAL_STOCK_AMOUT,0) as TOTAL_STOCK_AMOUT";
            $sql .= " 	,case";
            $sql .= " 		when mb1.strvalue = '0' then '0' ";
            $sql .= " 		when mp.AUTO_ORDER_KBN = '1' then mp.JUST_STOCK_AMOUT";
            $sql .= " 		else mp2.FILL_AMOUNT";
            $sql .= " 	 end as ORDER_POINT";
            $sql .= " 	,case";
            $sql .= " 		when mb2.strvalue != '0' and m2.order_lot is not null then  m2.order_lot";
            $sql .= " 		else mp.order_lot";
            $sql .= " 	 end as order_lot";
            $sql .= " 	,m2.peri_costprice";
            $sql .= " 	,case";
            $sql .= " 		when coalesce(mb2.strvalue,'0') = '0' then mp.head_costprice";
            $sql .= " 		when m2.peri_costprice is not null then m2.peri_costprice";
            $sql .= " 		when mb2.strvalue = '1' and mp.contract_price != 0 then mp.contract_price";
            $sql .= " 		when mb2.strvalue = '1' and mp.contract_price  = 0 then mp.head_costprice";
            $sql .= " 		when mb2.strvalue = '2' and mp.head_costprice != 0 then mp.head_costprice";
            $sql .= " 		when mb2.strvalue = '2' and mp.head_costprice  = 0 then mp.contract_price";
            $sql .= " 	 end as costprice";
            $sql .= " 	,m2.order_date_str";
            $sql .= " 	,m2.order_date_end";
            $sql .= " from mst0201 mp ";            
            $sql .= " left join (select  ";
            $sql .= " 				 organization_id ";
            $sql .= " 				,prod_cd ";
            $sql .= " 				,sum(FILL_AMOUNT) as FILL_AMOUNT ";
            $sql .= " 				,sum(TOTAL_STOCK_AMOUT) as TOTAL_STOCK_AMOUT ";
            $sql .= " 			from mst0204 ";
            $sql .= " 			group by  ";
            $sql .= " 				 organization_id ";
            $sql .= " 				,prod_cd ";
            $sql .= " 			order by  ";
            $sql .= " 				 organization_id ";
            $sql .= " 				,prod_cd ";
            $sql .= " 			) mp2 on mp2.organization_id = mp.organization_id and mp.prod_cd = mp2.prod_cd ";
            $sql .= " left join m_organization_detail o on (o.organization_id = mp.organization_id)";
            $sql .= " left join mst0011 mb2 on (mb2.organization_id = mp.organization_id and mb2.detail_cd = '102000030S' and mb2.reji_no = '20' )";
            $sql .= " left join mst0011 mb1 on (mb1.organization_id = mp.organization_id and mb1.detail_cd = '102000015S' and mb1.reji_no = '01' )";
            $sql .= " left join MST1401 m2  on (	   m2.prod_cd = mp.prod_cd ";
            $sql .= "  						   and m2.supp_cd = mp.head_supp_cd ";
            $sql .= "  						   and m2.organization_id = mp.organization_id ";
            $sql .= "  						  )	";
            $sql .= " where 1 = :val";
            $sql .= " 	and   mp.insdatetime <> mp.upddatetime";
            $sql .= " 	or (mp2.prod_cd = mp.prod_cd)";
            $sql .= " order by mp.organization_id, mp.prod_cd,peri_costprice desc";
            $param[":val"] = 1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($sql, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getprod_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas['search_info'][$data['org_id']][$data['supp_cd']][] = $data;
                $Datas['detail_info'][$data['org_id']][$data['prod_cd']][$data['supp_cd']][] = $data;
            }            
                   
            $Log->trace("END getprod_detail");
            return $Datas;
        }         
        
    }
?>