<?php
/**
 * @file      薬剤管理　発注画面
 * @author    USE K.Kanda(media-craft.co.jp)
 * @date      2018/02/22
 * @version   1.00
 * @note      発注を行う
 */

// BaseModel.phpを読み込む
require './Model/Common/BaseModel.php';

/**
* 発注画面クラス
* @note   発注テーブルの管理を行う
*/
class Order extends BaseModel
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
 * 発注先一覧の取得
 * @param    $where 発注先名
 * @return   SQLの実行結果
 */
	public function getMakerList($pha_id) {
		global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getMakerList");

    			$params = array();

    			$sql  = " SELECT mak.ma_cd,ma_name,rse.postage,rse.free_kin,mak.mail_addr,mak.url1,mak.url2,mak.description From ord_m_maker mak";
    			$sql .= " INNER JOIN ord_m_makerset rse on(mak.ma_cd=rse.ma_cd and rse.pha_id=:pha_id)";
    			$sql .= " where mak.is_del=0";
    			$sql .= " ORDER BY mak.ma_cd";

    			$params = array("pha_id"=>$pha_id);
    			$result = $DBA->executeSQL($sql, $params);

          $ret = array();

          if( $result === false )
          {
              $Log->trace("END getMakerList");
              return $ret;
          }
          while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
          {
              array_push( $ret, $data);
          }

          $Log->trace("END getMakerList");

          return $ret;
	}
 /**
 * 商品一覧の取得
 * @param    POSTの内容
 * @return   SQLの実行結果
 */
  public function getProductsList($Form) {
		global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getProductsList");

    			$params = array();

    			$sql  = " SELECT pro.pro_cd,pro.jan_cd,pro.pro_name,pro.youryo,pro.irisuu,pro.tanka,mak.ma_name,pro.gs1_code gs1_code,pro.syosai,pro.min_lot,pro.url1,pro.teika From ord_m_products pro";
    			$sql .= " INNER JOIN ord_m_makerset se ON (pro.ma_cd=se.ma_cd AND se.pha_id=:pha_id)";
    			$sql .= " INNER JOIN ord_m_maker mak ON (se.ma_cd=mak.ma_cd)";
    			$sql .= " WHERE pro.is_del=0";
    			$sql .= " AND pro.pha_id=:pha_id";
    			$sql .= " AND pro.ma_cd=:ma_cd";
    			$sql .= " ORDER BY pro.jan_cd";

    			$params = array(
                               "pha_id"=>$Form['pha_id']
    						   ,"ma_cd"=>$Form['ma_cd']
                               );

    			$result = $DBA->executeSQL($sql, $params);

          $ret = array();

          if( $result === false )
          {
              $Log->trace("END getProductsList");
              return $ret;
          }
          while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
          {
              array_push( $ret, $data);
          }

          $Log->trace("END getProductsList");

          return $ret;
	}

  /**
   * 商品一覧の検索結果の取得
   * @param    POSTの内容
   * @return   SQLの実行結果
   */
	public function getProductsSearch($Form) {
		global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getProductsList");

		$params = array();

		$sql  = " SELECT pro.pro_cd";
                $sql .= "       ,pro.jan_cd";
                $sql .= "       ,CASE WHEN LENGTH(pro.pro_name) >= 16 THEN substr(pro.pro_name,0,16) || '...' ";
                $sql .= "        ELSE pro.pro_name END as pro_name";
                $sql .= "       ,pro.youryo";
                $sql .= "       ,pro.irisuu";
                $sql .= "       ,pro.tanka";
                $sql .= "       ,mak.ma_name";
                $sql .= "       ,pro.gs1_code";
                $sql .= "       ,pro.url1";
                $sql .= "       ,pro.min_lot";
                $sql .= "       ,pro.teika";
                $sql .= "       ,pro.case_num";
                $sql .= "       ,split_part(pro.imgs,',',2) as imgs";
                $sql .= "   FROM ord_m_products pro";
		$sql .= "  INNER JOIN ord_m_makerset se ON (pro.ma_cd=se.ma_cd AND se.pha_id=:pha_id)";
		$sql .= "  INNER JOIN ord_m_maker mak ON (se.ma_cd=mak.ma_cd)";
		$sql .= "  WHERE pro.is_del=0";
		$sql .= "    AND pro.pha_id = :pha_id";
		$sql .= "    AND pro.ma_cd = :ma_cd";

                // メーカーコードと薬局コードは固定
		$params = array(
                         "pha_id"=>$Form['pha_id']
					   ,"ma_cd"=>$Form['ma_cd']
                         );

                // キーワード検索
                if(!empty($Form['search_keyword']))
		{
                    $sql .= " AND ( coalesce(pro.pro_name,'') ";      //商品名
                    $sql .= "  || coalesce(pro.prod_kn,'') ";        //商品名カナ
                    $sql .= "  || coalesce(pro.syosai1,'') ";         //詳細1:商品特徴
                    $sql .= "  || coalesce(pro.syosai2,'') ";         //詳細2:
                    $sql .= "  || coalesce(pro.syosai3,'') ";         //詳細3
                    $sql .= "  || coalesce(pro.syosai4,'') ";         //詳細4
                    $sql .= "  || coalesce(pro.syosai5,'') ";         //詳細5
                    $sql .= "  || coalesce(pro.use_scene,'') ";       //利用シーン
                    $sql .= "  || coalesce(pro.method,'') ";          //召し上がり方
                    $sql .= "  || coalesce(pro.store_temp,'') ";      //温度
                    $sql .= "  || coalesce(pro.patent,'') ";          //特許
                    $sql .= "  || coalesce(pro.genzairyomei,'') ";    //原材料
                    $sql .= "  || coalesce(pro.gensan,'') ";          //原産国
                    $sql .= "  || coalesce(pro.hannbaimoto,'') ";     //販売元
                    $sql .= "  || coalesce(pro.eiyouseibun,'') ";     //栄養成分表示
                    $sql .= "  || coalesce(pro.siyoujyounotyui,'')) "; //使用上の注意
                    $sql .= "LIKE :search_keyword";
                    $params = array_merge($params,array("search_keyword"=>"%".$Form['search_keyword']."%"));
		}
                
                //カテゴリ第1階層
                if(!empty($Form['prod_kbn_1']) && $Form['prod_kbn_1'] != "no")
		{
                    $sql .= " AND pro.prod_k_cd1 = :prod_kbn_1";
                    $params = array_merge($params,array("prod_kbn_1"=>$Form['prod_kbn_1']));
		}

                //カテゴリ第2階層
                if(!empty($Form['prod_kbn_2']) && $Form['prod_kbn_2'] != "no")
		{
                    $sql .= " AND pro.prod_k_cd2 = :prod_kbn_2";
                    $params = array_merge($params,array("prod_kbn_2"=>$Form['prod_kbn_2']));
		}
                
                //カテゴリ第3階層
                if(!empty($Form['prod_kbn_3']) && $Form['prod_kbn_3'] != "no")
		{
                    $sql .= " AND pro.prod_k_cd3 = :prod_kbn_3";
                    $params = array_merge($params,array("prod_kbn_3"=>$Form['prod_kbn_3']));
		}
                
                //カテゴリ第4階層
                if(!empty($Form['prod_kbn_4']) && $Form['prod_kbn_4'] != "no")
		{
                    $sql .= " AND pro.prod_k_cd4 = :prod_kbn_4";
                    $params = array_merge($params,array("prod_kbn_4"=>$Form['prod_kbn_4']));
		}
                
                //カテゴリ第5階層
                if(!empty($Form['prod_kbn_5']) && $Form['prod_kbn_5'] != "no")
		{
                    $sql .= " AND pro.prod_k_cd5 = :prod_kbn_5";
                    $params = array_merge($params,array("prod_kbn_5"=>$Form['prod_kbn_5']));
		}

                //ソート                
		$sql .= " ORDER BY pro.jan_cd";

                $Log->debug("Product Search SQL".$sql);
                
		$result = $DBA->executeSQL($sql, $params);

          $ret = array();

          if( $result === false )
          {
              $Log->trace("END getProductsList");
              return $ret;
          }
          while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
          {
              array_push( $ret, $data);
          }

          $Log->trace("END getProductsList");

          return $ret;
	}
        
  /**
   * 商品データの取得
   * @param    POSTの内容
   * @return   SQLの実行結果
   */
	public function getProductData($pha_id,$ma_cd,$code)
	{
		global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getProductData");

		$sql  = " SELECT *";
		$sql .= " FROM ord_m_products";
		$sql .= " WHERE is_del=0";
		$sql .= " AND ma_cd=:ma_cd";
		$sql .= " AND pha_id=:pha_id";
		$sql .= " AND (jan_cd=:code or gs1_code=:code)";

		$params = array("pha_id"=>$pha_id,"ma_cd"=>$ma_cd,"code"=>$code);
		$result = $DBA->executeSQL($sql,$params);
          $ret = $result->fetch(PDO::FETCH_ASSOC);

		$Log->trace("END getProductData");

		return $ret;
	}
	/**
       * 発注ヘッダデータの取得
       * @param    POSTの内容
       * @return   SQLの実行結果
       */
	public function getOrdHead($Form)
	{
		global $DBA, $Log; // グローバル変数宣言
    $Log->trace("START getProductData");

		$sql  = " SELECT hed.*,mak.ma_name";
		$sql .= " FROM ord_t_head hed";
		$sql .= " INNER JOIN ord_m_maker mak ON (hed.ma_cd=mak.ma_cd)";
		$sql .= " WHERE hed.is_del=0";
		$sql .= " AND hed.pha_id=:pha_id";
		$sql .= " AND hed.ord_no=:ord_no";

		$params = array("pha_id"=>$Form['pha_id'],"ord_no"=>$Form['ord_no']);
		$result = $DBA->executeSQL($sql,$params);
    $ret = $result->fetch(PDO::FETCH_ASSOC);

		$Log->trace("END getProductData");

		return $ret;
	}
	 /**
   * 発注明細データの取得
   * @param    POSTの内容
   * @return   SQLの実行結果
   */
	public function getOrdDetails($Form) {
		global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getOrdDetails");

		$sql  = " SELECT * FROM ord_t_details";
		$sql .= " WHERE pha_id=:pha_id";
		$sql .= " AND ord_no=:ord_no";
		$sql .= " ORDER BY gyo";

		$params = array(
					    "pha_id"=>$Form['pha_id']
              ,"ord_no"=>$Form['ord_no']
                         );

		$result = $DBA->executeSQL($sql, $params);

          $ret = array();

          if( $result === false )
          {
              $Log->trace("END getProductsList");
              return $ret;
          }
          while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
          {
              array_push( $ret, $data);
          }

          $Log->trace("END getOrdDetails");

          return $ret;
	}

	/**
 * 注文
 * @param    POSTの内容
 * @return   SQLの実行結果
 */
	public function startOrder($Form,$kbn="1")
	{
		global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START startOrder");

		$tag_no = $Form['tag_no'];

    //$Log->debug($tag_no);

		if($Form['ord_no'.$tag_no]!="")
		{
			$ord_no = $Form['ord_no'.$tag_no];

			$sql  = " DELETE FROM ord_t_head ";
			$sql .= " WHERE pha_id=:pha_id";
			$sql .= " AND ord_no=:ord_no";
			$params = array("pha_id"=>$Form['pha_id'],"ord_no"=>$ord_no);
			$result = $DBA->executeSQL($sql,$params);

			$sql  = " DELETE FROM ord_t_details ";
			$sql .= " WHERE pha_id=:pha_id";
			$sql .= " AND ord_no=:ord_no";
			$params = array("pha_id"=>$Form['pha_id'],"ord_no"=>$ord_no);
			$result = $DBA->executeSQL($sql,$params);

		}
		else
		{

			//注文番号の取得
			$sql  = " SELECT";
			$sql .= " max(ord_no) ord_no";
			$sql .= " From ord_t_head ";
			$sql .= " WHERE pha_id=:pha_id";
			$params = array("pha_id"=>$Form['pha_id']);
			$result = $DBA->executeSQL($sql,$params);
			$ret = $result->fetch(PDO::FETCH_ASSOC);

			$ord_no = $ret['ord_no']+1;
		}



		//発注ヘッダの登録
		$sql  = " INSERT INTO ord_t_head ( ";
    $sql .= "  pha_id";
		$sql .= " ,ord_no";
    $sql .= " ,ma_cd";
		$sql .= " ,kbn";
		$sql .= " ,registration_time";
		$sql .= " ,registration_user_id";
		$sql .= " ,order_time";
		$sql .= " ,order_user_id ";
		$sql .= " ,postage";
		$sql .= " ,free_kin";
		$sql .= " ) VALUES ( ";
    $sql .= " :pha_id";
    $sql .= ",:ord_no";
    $sql .= ",:ma_cd";
		$sql .= ",'".$kbn."'";
		$sql .= ",'".date("Y/m/d H:i:s")."'";
		$sql .= ",1";
		$sql .= ",'".date("Y/m/d H:i:s")."'";
		$sql .= ",1";
		$sql .= ",:postage";
		$sql .= ",:free_kin";
		$sql .= " ) ";
		$params = array(
							"ord_no"=>$ord_no
							,"pha_id"=>$Form['pha_id']
              ,"ma_cd"=>$Form['ma_cd']
							,"postage"=>$Form['postage']
							,"free_kin"=>$Form['free_kin']
							);
		$ret = $this->executeOneTableSQL( $sql, $params );
		//明細の登録
		for($i=1;$i<=50;$i++) {
			$pro_cd = $Form['pro_cd_'.$Form['tag_no'].'_'.$i];
			//$Log->debug($i.":tag_no=".$Form['tag_no']." "."pro_cd=".$pro_cd);
			//登録行が空の場合
			if($pro_cd=="")
			{
				continue;
			}
			//商品情報の取得
			$sql  = " SELECT *";
			$sql .= " FROM ord_m_products";
			$sql .= " WHERE is_del=0";
			$sql .= " AND ma_cd=:ma_cd";
			$sql .= " AND pha_id=:pha_id";
			$sql .= " AND pro_cd=:pro_cd";
			$params = array("pha_id"=>$Form['pha_id'],"ma_cd"=>$Form['ma_cd'],"pro_cd"=>$pro_cd);
			$result = $DBA->executeSQL($sql,$params);
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$jan_cd  = $Form['jan_cd_'.$Form['tag_no'].'_'.$i];
			$suu     = $Form['suu_'.$Form['tag_no'].'_'.$i];
			if($suu=="")
			{
				$suu = 0;
			}

			$hakosuu = $Form['case_'.$Form['tag_no'].'_'.$i];
			if($hakosuu=="")
			{
				$hakosuu = 0;
			}

			//金額の計算
			$tanka  = $row['tanka'];
			$irisuu = $row['irisuu'];
			$kin = 0;
			if($suu > 0)
			{
				$kin = $tanka * $suu;
			}
			if($hakosuu > 0)
			{
				$kin = $tanka * $hakosuu * $irisuu;
			}

			$sql  = " INSERT INTO ord_t_details ( ";
			$sql .= "  pha_id";
			$sql .= " ,ord_no";
			$sql .= " ,gyo";
			$sql .= " ,jan_cd";
			$sql .= " ,pro_cd";
			$sql .= " ,pro_name";
			$sql .= " ,youryo";
			$sql .= " ,irisuu";
			$sql .= " ,tanka";
			$sql .= " ,kin";
			$sql .= " ,suu";
			$sql .= " ,hakosuu";
			$sql .= " ) VALUES ( ";
			$sql .= "  :pha_id";
			$sql .= " ,:ord_no";
			$sql .= " ,".$i;
			$sql .= " ,:jan_cd";
			$sql .= " ,:pro_cd";
			$sql .= " ,:pro_name";
			$sql .= " ,:youryo";
			$sql .= " ,:irisuu";
			$sql .= " ,:tanka";
			$sql .= " ,:kin";
			$sql .= " ,:suu";
			$sql .= " ,:hakosuu";
			$sql .= " ) ";
			$params = array(
							 "pha_id"=>$Form['pha_id']
							,"ord_no"=>$ord_no
							,"jan_cd"=>$jan_cd
							,"pro_cd"=>$pro_cd
							,"pro_name"=>$row['pro_name']
							,"youryo"=>$row['youryo']
							,"irisuu"=>$row['irisuu']
							,"tanka"=>$tanka
							,"kin"=>$kin
							,"suu"=>$suu
							,"hakosuu"=>$hakosuu
							);
			$ret = $this->executeOneTableSQL( $sql, $params );
		}
		$Log->trace("END   startOrder");

		return $ret;
	}
	/**
   * 広告一覧の取得
   * @param    POSTの内容
   * @return   SQLの実行結果
   */
	public function getCmList($pha_id)
	{
		global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getCmList");

		$sql  = " SELECT ";
		$sql .= "  cm.* ";
		$sql .= " ,ma.ma_name  ";
		$sql .= " FROM ord_t_cm cm ";
		$sql .= " INNER JOIN ord_m_maker ma ON (cm.ma_cd=ma.ma_cd) ";
		$sql .= " WHERE cm.is_del=0 ";
		$sql .= " AND   cm.pha_id=:pha_id ";
		$sql .= " AND   cm.kigen_ymd > '".date("Y/m/d")."' ";
		$sql .= " ORDER BY random() ";
		$sql .= "LIMIT 3 ";

		$params = array("pha_id"=>$pha_id);
		$result = $DBA->executeSQL($sql,$params);
          $ret = array();

          if( $result === false )
          {
              $Log->trace("END getProductsList");
              return $ret;
          }
          while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
          {
              array_push( $ret, $data);
          }

          $Log->trace("END getOrdDetails");

          return $ret;

		$Log->trace("END getCmList");

		return $ret;
	}
        
	/**
 * 発注キャンセル
 * @param    POSTの内容
 * @return   SQLの実行結果
 */
	public function cancelOrder($Form)
	{
		global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START cancelOrder");

		$tag_no = $Form['tag_no'];

                $ord_no = $Form['ord_no'];

                $sql  = " DELETE FROM ord_t_head ";
                $sql .= " WHERE pha_id=:pha_id";
                $sql .= " AND ord_no=:ord_no";
                $params = array("pha_id"=>$Form['pha_id'],"ord_no"=>$ord_no);
                $result = $DBA->executeSQL($sql,$params);

                $sql  = " DELETE FROM ord_t_details ";
                $sql .= " WHERE pha_id=:pha_id";
                $sql .= " AND ord_no=:ord_no";
                $params = array("pha_id"=>$Form['pha_id'],"ord_no"=>$ord_no);
                $result = $DBA->executeSQL($sql,$params);

		$Log->trace("END   startOrder");

		return $ret;
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
//        $sql.= ' AND ma_cd = ' .$ma_cd;
        $sql.= ' AND ma_cd = 12';
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
     * 仕入発注品データの取得
     * @param    $prod_cd 商品コード
     * @return   SQLの実行結果
     */
    public function getOrderProductData($jan_cd,$ma_cd,$pha_id)
    {
	global $DBA, $Log; // グローバル変数宣言
        $Log->trace("START getOrderProductData");

	$sql  = " SELECT * From ord_m_products where is_del=0";
	$sql .= " AND jan_cd=:jan_cd";
        $sql .= " AND ma_cd=:ma_cd";
        $sql .= " AND pha_id=:pha_id";
	$sql .= " ORDER BY pro_cd";
	$params = array("jan_cd"=>$jan_cd,
                        "ma_cd"=>$ma_cd,
                        "pha_id"=>$pha_id,
                        );
	$result = $DBA->executeSQL($sql,$params);
        $ret = $result->fetch(PDO::FETCH_ASSOC);

        $Log->trace("END getOrderProductData");

        return $ret;
    }
        
}
?>
