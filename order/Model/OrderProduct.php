<?php
  /**
   * @file      仕入発注品マスタ
   * @author    尉
   * @date      2019/07/03
   * @version   1.00
   * @note      仕入発注品マスタテーブルの管理を行う
   */

  // BaseModel.phpを読み込む
  require './Model/Common/BaseModel.php';
  require '../modal/Model/Modal.php';

  /**
   * アクセス権限マスタクラス
   * @note   アクセス権限マスタテーブルの管理を行う
   */
  class OrderProduct extends BaseModel
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
   * 仕入発注品一覧の取得
   * @param    $postArray
   * @return   SQLの実行結果
   */
    public function getOrderProductList($postArray)
    {
	global $DBA, $Log; // グローバル変数宣言
        $Log->trace("START getOrderProductList");

	$params = array();

        $sql  = " SELECT * From ord_m_products where is_del=0";
        $sql .= " AND ma_cd = ".(empty($postArray['ma_cd']) ? $this->getMaCd() : $postArray['ma_cd']);
        $sql .= " AND pha_id = ".$this->getPhaId();
	if($postArray['prod_name']!=="") {
            $sql .= " AND pro_name LIKE :pro_name";
            $params = array("pro_name"=>"%".$postArray['prod_name']."%");

	}
        if(!empty($postArray['prod_list'])){
            $sql .= " AND CAST(pro_cd as bigint) in (".substr($postArray['prod_list'], 1).")";
        }
        if($postArray['sort'] == '1' or $postArray['sort'] == '2' or $postArray['sort'] == ''){
            $sql .= " ORDER BY pro_cd, pro_name";
        }else if($postArray['sort'] == '3' or $postArray['sort'] == '4'){
            $sql .= " ORDER BY pro_name, pro_cd";
        }
	$result = $DBA->executeSQL($sql, $params);

        $ret = array();

        if( $result === false )
        {
            $Log->trace("END getOrderProductList");
            return $ret;
        }
        while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
        {
            array_push( $ret, $data);
        }
        if($postArray['sort'] == '1' or $postArray['sort'] == '3'){
            $ret = array_reverse($ret);
        }

        $Log->trace("END getOrderProductList");

        return $ret;
    }
    
    /**
     * 仕入発注品データの取得
     * @param    $prod_cd 商品コード
     * @return   SQLの実行結果
     */
    public function getOrderProductData($prod_cd, $ma_cd)
    {
	global $DBA, $Log; // グローバル変数宣言
        $Log->trace("START getOrderProductData");

        //メーカーコード取得
        if(empty($Form['ma_cd'])){
            $Form['ma_cd'] =  $this->getMaCd();
        }
        
        //phaID取得
        $Form['pha_id'] = $this->getPhaId();

	$sql  = " SELECT * From ord_m_products where is_del=0";
	$sql .= " AND pro_cd=:pro_cd";
        $sql .= " AND ma_cd=:ma_cd";
        $sql .= " AND pha_id=:pha_id";
	$sql .= " ORDER BY pro_cd";
	$params = array("pro_cd"=>$prod_cd,
                        "ma_cd"=>(empty($ma_cd) ? $Form['ma_cd'] : $ma_cd),
                        "pha_id"=>$Form['pha_id'],
                        );
	$result = $DBA->executeSQL($sql,$params);
        $ret = $result->fetch(PDO::FETCH_ASSOC);

        $Log->trace("END getOrderProductData");

        return $ret;
    }
    
    /**
     * 仕入発注品データの保存
     * @param    $ma_cd 発注先ID
     * @return   SQLの実行結果
     */
    public function saveOrderProductData($Form)
    {
	global $DBA, $Log; // グローバル変数宣言
        $Log->trace("START saveOrderProductData");

	$params = array();
        //メーカーコード取得
        //$Form['ma_cd'] =  $this->getMaCd();
        
        //phaID取得
        $Form['pha_id'] = $this->getPhaId();
        
        if($Form['mode'] == '新規登録')
	{
            //新規作成
            $Log->debug("new test  ".$user_id.":".$user_detail_id);

            //INSERT処理
            $sql  = " INSERT INTO ord_m_products ( ";
            $sql .= "  ma_cd";
            $sql .= " ,pha_id";
            $sql .= " ,pro_cd";
            $sql .= " ) VALUES ( ";
            $sql .= " :ma_cd";
            $sql .= ",:pha_id";
            $sql .= ",:pro_cd";
            $sql .= " ) ";
            
            $params = array("ma_cd"=>$_SESSION["USER_ID"]
                ,"pha_id"=>$Form['pha_id']
                ,"pro_cd"=>$Form['prod_cd']
                ,"jan_cd"=>$Form['jan_cd']
                ,"pro_name"=>$Form['prod_nm']
                ,"youryo"=>$Form['youryo']
                ,"irisuu"=>$Form['irisuu']
                ,"tanka"=>empty($Form['tanka']) ? 0 : str_replace(',', '', $Form['tanka'])
                ,"gs1_code"=>$Form['gs1_cd']
                ,"url1"=>$Form['url1']
                ,"syosai1"=>$Form['syosai1']
                ,"syosai2"=>$Form['syosai2']
                ,"syosai3"=>$Form['syosai3']
                ,"syosai4"=>$Form['syosai4']
                ,"syosai5"=>$Form['syosai5']
                ,"min_lot"=>$Form['min_lot']
                ,"teika"=>empty($Form['teika']) ? 0 : str_replace(',', '', $Form['teika'])
                ,"imgs"=>$Form['imgs']
                ,"case_num"=>$Form['case_num']
                ,"prod_kn"=>$Form['prod_kn']
                ,"prod_k_cd1"=>$Form['prodkcd1_select']
                ,"prod_k_cd2"=>$Form['prodkcd2_select']
                ,"prod_k_cd3"=>$Form['prodkcd3_select']
                ,"prod_k_cd4"=>$Form['prodkcd4_select']
                ,"prod_k_cd5"=>$Form['prodkcd5_select']
                ,"use_scene"=>$Form['use_scene']
                ,"method"=>$Form['method']
                ,"store_temp"=>$Form['store_temp']
                ,"patent"=>$Form['patent']
                ,"best_before"=>$Form['best_before']
                ,"prod_size"=>$Form['prod_size']
                ,"order_unit"=>$Form['order_unit']
                ,"allergy_material"=>$Form['allergy_material']   
                ,"genzairyomei"=>$Form['genzairyomei']   
                ,"gensan"=>$Form['gensan']   
                ,"hannbaimoto"=>$Form['hannbaimoto']   
                ,"eiyouseibun"=>$Form['eiyouseibun']   
                ,"siyoujyounotyui"=>$Form['siyoujyounotyui']   
            );
            $ret = $this->executeOneTableSQL( $sql, $params );
	}else{
            //更新
            $sql  = " UPDATE ord_m_products SET ";
            $sql .= "  jan_cd=:jan_cd";
            $sql .= " ,pro_name=:pro_name";
            $sql .= " ,youryo=:youryo";
            $sql .= " ,irisuu=:irisuu";
            $sql .= " ,tanka=:tanka";
            $sql .= " ,update_time= '".date("Y/m/d H:i:s")."'";
            $sql .= " ,update_user_id=".$_SESSION["USER_ID"];
            $sql .= " ,gs1_code=:gs1_code";
            $sql .= " ,url1=:url1";
            $sql .= " ,syosai1=:syosai1";
            $sql .= " ,syosai2=:syosai2";
            $sql .= " ,syosai3=:syosai3";
            $sql .= " ,syosai4=:syosai4";
            $sql .= " ,syosai5=:syosai5";
            $sql .= " ,min_lot=:min_lot";
            $sql .= " ,teika=:teika";
            $sql .= " ,imgs=:imgs";
            $sql .= " ,case_num=:case_num";
            $sql .= " ,prod_kn=:prod_kn";
            $sql .= " ,prod_k_cd1=:prod_k_cd1";
            $sql .= " ,prod_k_cd2=:prod_k_cd2";
            $sql .= " ,prod_k_cd3=:prod_k_cd3";
            $sql .= " ,prod_k_cd4=:prod_k_cd4";
            $sql .= " ,prod_k_cd5=:prod_k_cd5";
            $sql .= " ,use_scene=:use_scene";
            $sql .= " ,method=:method";
            $sql .= " ,store_temp=:store_temp";
            $sql .= " ,patent=:patent";
            $sql .= " ,best_before=:best_before";
            $sql .= " ,prod_size=:prod_size";
            $sql .= " ,order_unit=:order_unit";
            $sql .= " ,allergy_material=:allergy_material";
            $sql .= " ,genzairyomei=:genzairyomei";
            $sql .= " ,gensan=:gensan";
            $sql .= " ,hannbaimoto=:hannbaimoto";
            $sql .= " ,eiyouseibun=:eiyouseibun";
            $sql .= " ,siyoujyounotyui=:siyoujyounotyui";
            $sql .= " WHERE ma_cd=:ma_cd";
            $sql .= "   AND pha_id=:pha_id";
            $sql .= "   AND pro_cd=:pro_cd";
            $params = array(
                "jan_cd"=>$Form['jan_cd']
                ,"pro_name"=>$Form['prod_nm']
                ,"youryo"=>$Form['youryo']
                ,"irisuu"=>$Form['irisuu']
                ,"tanka"=>empty($Form['tanka']) ? 0 : str_replace(',', '', $Form['tanka'])
                ,"gs1_code"=>$Form['gs1_cd']
                ,"url1"=>$Form['url1']
                ,"syosai1"=>$Form['syosai1']
                ,"syosai2"=>$Form['syosai2']
                ,"syosai3"=>$Form['syosai3']
                ,"syosai4"=>$Form['syosai4']
                ,"syosai5"=>$Form['syosai5']
                ,"min_lot"=>$Form['min_lot']
                ,"teika"=>empty($Form['teika']) ? 0 : str_replace(',', '', $Form['teika'])
                ,"imgs"=>$Form['imgs']
                ,"case_num"=>$Form['case_num']
                ,"prod_kn"=>$Form['prod_kn']
                ,"ma_cd"=>$Form['ma_cd']
		,"pha_id"=>$Form['pha_id']
                ,"pro_cd"=>$Form['prod_cd']
                ,"prod_k_cd1"=>$Form['prodkcd1_select']
                ,"prod_k_cd2"=>$Form['prodkcd2_select']
                ,"prod_k_cd3"=>$Form['prodkcd3_select']
                ,"prod_k_cd4"=>$Form['prodkcd4_select']
                ,"prod_k_cd5"=>$Form['prodkcd5_select']
                ,"use_scene"=>$Form['use_scene']
                ,"method"=>$Form['method']
                ,"store_temp"=>$Form['store_temp']
                ,"patent"=>$Form['patent']
                ,"best_before"=>$Form['best_before']
                ,"prod_size"=>$Form['prod_size']
                ,"order_unit"=>$Form['order_unit']
                ,"allergy_material"=>$Form['allergy_material']
                ,"genzairyomei"=>$Form['genzairyomei']
                ,"gensan"=>$Form['gensan']
                ,"hannbaimoto"=>$Form['hannbaimoto']
                ,"eiyouseibun"=>$Form['eiyouseibun']
                ,"siyoujyounotyui"=>$Form['siyoujyounotyui']
            );
            $ret = $this->executeOneTableSQL( $sql, $params );
        }

    $Log->trace("END saveOrderProductData");
    return $ret;
}


    /**
   * 発注先データの削除
   * @param    $ma_cd 発注先ID
   * @return   SQLの実行結果
   */
    public function deleteOrderProductData($prod_cd, $ma_cd) {
        global $DBA, $Log; // グローバル変数宣言
        $Log->trace("START deleteOrderProductData");

	$params = array();

        $sql  = " UPDATE ord_m_products SET is_del=1";
	$sql .= " WHERE pro_cd=:pro_cd";
        $sql .= " AND ma_cd = ".$ma_cd;
        $sql .= " AND pha_id = ".$this->getPhaId();
	$params = array(":pro_cd"=>$prod_cd);
	$ret = $DBA->executeSQL($sql,$params);
        $Log->trace("END deleteOrderProductData");
        return $ret;
    }
                
    /**
    * JICFSのプルダウン
    * @param     
    * @return   JICFSリスト  (JICFSCD、JICFS名) 
    */
    public function getJicfsList()
    {
        global $DBA, $Log;  // グローバル変数宣言
        $Log->trace("START getJicfsList");

        $jicfsList = array();

        $sql = ' SELECT jicfs_class_cd, jicfs_class_nm FROM mst5401 ORDER BY jicfs_class_cd';
        $parametersArray = array();
        $result = $DBA->executeSQL($sql, $parametersArray);

        if( $result === false )
        {
            $Log->trace("END getJicfsList");
            return $jicfsList;
        }
            
        while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
        {
            array_push($jicfsList, $data);
        }

        $Log->trace("END getJicfsList");
        return $jicfsList;
    }
        
    /**
    * 商品区分のプルダウン
    * @param     
    * @return   商品区分リスト  (商品区分CD、商品区分名) 
    */
    public function getProdKbnCdList($ma_cd, $prod_type, $prod_kbn_list)
    {
        global $DBA, $Log;  // グローバル変数宣言
        $Log->trace("START getProdKbnCdList");

        $prodKbnList = array();

        $sql = ' SELECT prod_k_type, prod_k_cd'.($prod_type).' as prod_k_cd ,prod_k_nm FROM ord_m_prod_kbn';
        $sql.= ' WHERE prod_k_type = :prod_type';
        $sql.= ' AND ma_cd = ' .(empty($ma_cd) ? $this->getMaCd() : $ma_cd);
        if(count($prod_kbn_list) > 0){
            for ($x = 0; $x < count($prod_kbn_list); $x++) {
                $sql.= " AND prod_k_cd".($x + 1). " = '".($prod_kbn_list[$x])."'";
            }
        }
        $sql.= ' ORDER BY prod_k_cd'.$prod_type;
        $params = array(":prod_type"=>$prod_type);
        $result = $DBA->executeSQL($sql, $params);

        if( $result === false )
        {
            $Log->trace("END getProdKbnCdList");
            return $prodKbnList;
        }
            
        while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
        {
            array_push($prodKbnList, $data);
        }

        $Log->trace("END getProdKbnCdList");
        return $prodKbnList;
    }
        
     /**
    * メーカーコード取得
    * @param     
    * @return   メーカーコード  (ma_cd) 
    */
    private function getMaCd()
    {
        global $DBA, $Log;  // グローバル変数宣言
        $Log->trace("START getMaCd");

        $params = array();
        //メーカーコード取得
        $sql  = " SELECT ma_cd ma_cd From ord_m_maker where user_id =  ";
        $sql .= $_SESSION["USER_ID"];
        $result = $DBA->executeSQL($sql,$params);
	$row = $result->fetch(PDO::FETCH_ASSOC);
        $ma_cd = $row['ma_cd'];

        $Log->trace("END getMaCd");
        return $ma_cd;
    }
    
     /**
    * PHAID取得
    * @param     
    * @return   PHAID  (pha_id) 
    */
    private function getPhaId()
    {
        global $DBA, $Log;  // グローバル変数宣言
        $Log->trace("START getPhaId");

        $params = array();
        
        //phaID取得
        $sql  = " SELECT pha_id pha_id From ord_m_pharmacy where user_id =  ";
        $sql .= $_SESSION["USER_ID"];
        $result = $DBA->executeSQL($sql,$params);
	$row = $result->fetch(PDO::FETCH_ASSOC);
        $pha_id = $row['pha_id'];

        $Log->trace("END getPhaId");
//        return $pha_id;
        return 1;
    }
    
    /**
    * 最新商品コード取得
    * @param     
    * @return   商品コード  (prod_cd) 
    */
    public function getLastProdCd($ma_cd)
    {
        global $DBA, $Log;  // グローバル変数宣言
        $Log->trace("START getLastProdCd");

        $params = array();
        //商品コード取得
        $sql  = " SELECT max(substring(pro_cd,9,4)) pro_cd From ord_m_products WHERE ma_cd = ".$ma_cd;
        $result = $DBA->executeSQL($sql,$params);
	$row = $result->fetch(PDO::FETCH_ASSOC);
        $prod_cd = $row['pro_cd'];

        $Log->trace("END getLastProdCd");
        return $prod_cd;
    }
    
    /**
    * 国コード取得
    * @param     
    * @return   国コード  (country_cd) 
    */
    public function getCountryCd($ma_cd)
    {
        global $DBA, $Log;  // グローバル変数宣言
        $Log->trace("START getCountryCd");

        $params = array();
        //国コード取得
        $sql  = " SELECT country_cd From ord_m_maker WHERE ma_cd = ".$ma_cd;
        $result = $DBA->executeSQL($sql,$params);
	$row = $result->fetch(PDO::FETCH_ASSOC);
        $country_cd = $row['country_cd'];

        $Log->trace("END getCountryCd");
        return $country_cd;
    }
    
    /**
    * 事業者コード取得
    * @param     
    * @return   事業者コード  (business_cd) 
    */
    public function getBusinessCd($ma_cd)
    {
        global $DBA, $Log;  // グローバル変数宣言
        $Log->trace("START getBusinessCd");

        $params = array();
        //国コード取得
        $sql  = " SELECT business_cd From ord_m_maker WHERE ma_cd = ".$ma_cd;
        $result = $DBA->executeSQL($sql,$params);
	$row = $result->fetch(PDO::FETCH_ASSOC);
        $business_cd = $row['business_cd'];

        $Log->trace("END getBusinessCd");
        return $business_cd;
    }
    
    /**
         * 業者リストデータ取得
         * @return   SQLの実行結果
         */
        public function getMakerList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMakerList");
            
            $sql  = ' SELECT'
                        . '  ord_m_maker.ma_cd ma_cd'
                        . ', ord_m_maker.ma_name ma_name'
                        . '  FROM ord_m_maker'
                        . '  INNER JOIN m_user_detail ON ord_m_maker.user_id = m_user_detail.user_id'
                        . '  WHERE 0 = ord_m_maker.is_del and m_user_detail.security_id > '.$this->getSecurityId()
                        . '  ORDER BY'
                        . '  ord_m_maker.ma_cd';

            $searchArray = array();                

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $makerList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMakerList");
                return $makerList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $makerList, $data);
            }

            $Log->trace("END getMakerList");

            return $makerList;
        }
        
        /**
        * セキュリティコード取得
        * @param     
        * @return   セキュリティコード  (security_id) 
        */
        private function getSecurityId()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSecurityId");

            $params = array();
            //メーカーコード取得
            $sql  = " SELECT security_id security_id From m_user_detail where user_id =  ";
            $sql .= $_SESSION["USER_ID"];
            $result = $DBA->executeSQL($sql,$params);
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $security_id = $row['security_id'];

            $Log->trace("END getSecurityId");
            return $security_id;
        }
  }
?>
