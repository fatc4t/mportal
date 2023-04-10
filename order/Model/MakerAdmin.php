<?php
  /**
   * @file      メーカー管理画面　受注処理
   * @author    USE K.Kanda(media-craft.co.jp)
   * @date      2018/12/14
   * @version   1.00
   * @note      発注処理を管理する
   */

  // BaseModel.phpを読み込む
  require './Model/Common/BaseModel.php';

  /**
   * アクセス権限マスタクラス
   * @note   アクセス権限マスタテーブルの管理を行う
   */
  class MakerAdmin extends BaseModel
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
     * 取引先一覧の取得
     * @param    なし
     * @return   SQLの実行結果
     */
     public function getPhaList() {
       global $DBA, $Log; // グローバル変数宣言
       $Log->trace("START getPhaList");

       $params = array();

       $sql  = " SELECT ";
       $sql .= "  ms.pha_id pha_id";
       $sql .= " ,ph.pha_name pha_name";
       $sql .= " From ord_m_makerset ms" ;
       $sql .= " INNER JOIN ord_m_pharmacy ph ON (ms.pha_id=ph.pha_id)";
       $sql .= " WHERE ph.is_del=0";
       $sql .= " AND ms.ma_cd=".$_SESSION['MA_CD'];
       if(!empty($where)) {
         $sql .= " AND ma_name LIKE :ma_name";
         $params = array("ma_name"=>"%".$where."%");
       }
       $sql .= " ORDER BY ma_cd";
       $result = $DBA->executeSQL($sql, $params);

       $ret = array();

       if( $result === false )
       {
           $Log->trace("END getPhaList");
           return $ret;
       }
       while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
       {
           array_push( $ret, $data);
       }

       $Log->trace("END getPhaList");

       return $ret;
     }
     /**
     * 取引先一覧の取得
     * @param    $where 検索条件
     * @return   SQLの実行結果
     */
    public function getOrdersList($where) {
      global $DBA, $Log; // グローバル変数宣言
      $Log->trace("START getOrdersList");

      $sql  = " SELECT ";
      $sql .= "  max(pha.pha_name) pha_name ";
      $sql .= " ,hed.pha_id pha_id ";
			$sql .= " ,hed.ord_no ord_no ";
			$sql .= " ,max(hed.postage) souryo ";
			$sql .= " ,sum(det.kin) syokei ";
			$sql .= " ,sum(det.suu) suu ";
			$sql .= " ,sum(det.hakosuu) hakosuu ";
			$sql .= " ,max(hed.order_time) order_time";
      $sql .= " ,case max(hed.kbn)";
      $sql .= "   WHEN '1' THEN '注文'";
      $sql .= "   WHEN '2' THEN '受注受付'";
      $sql .= "   WHEN '3' THEN '出荷済'";
      $sql .= "   WHEN '9' THEN 'キャンセル'";
      $sql .= "  end kbn_name";
      $sql .= " FROM ord_t_head hed ";
      $sql .= " INNER JOIN ord_m_pharmacy pha on (hed.pha_id=pha.pha_id) ";
      $sql .= " INNER JOIN ord_t_details det on (hed.pha_id=det.pha_id and hed.ord_no=det.ord_no) ";
      $sql .= " WHERE hed.ma_cd = :ma_cd ";
      //$sql .= " AND   hed.kbn = '1' ";
      $sql .= " AND   hed.kbn <> '0' ";
      $sql .= " AND   hed.is_del = 0 ";
      if($where['pha_id']!="")
      {
        $sql .= " AND hed.pha_id=".$where['pha_id'];
      }
      if($where['ymd1']!="")
      {
        $sql .= " AND hed.order_time >= '".$where['ymd1']." 00:00:00'";
      }
      if($where['ymd2']!="")
      {
        $sql .= " AND hed.order_time <= '".$where['ymd2']." 99:99:99'";
      }
      if($where['kbn']!="")
      {
        $sql .= " AND hed.kbn='".$where['kbn']."'";
      }

      $sql .= " GROUP BY hed.pha_id,hed.ord_no ";

      $params = array(":ma_cd"=>$_SESSION['MA_CD']);

      $result = $DBA->executeSQL($sql, $params);

      $ret = array();

      if( $result === false )
      {
          $Log->trace("END getOrdersList");
          return $ret;
      }
      while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
      {
          array_push( $ret, $data);
      }

      $Log->trace("END getOrdersList");

      return $ret;
    }

    /**
    * 取引先一覧の取得
    * @param    $where 検索条件
    * @return   SQLの実行結果
    */
   public function getOrdersData($pha_id,$ord_no) {
     global $DBA, $Log; // グローバル変数宣言
     $Log->trace("START getOrderData");

     $sql  = " SELECT ";
     $sql .= " pha.pha_name ";
     $sql .= ",hed.order_time ";
     $sql .= ",det.* ";
     $sql .= "FROM ord_t_details det ";
     $sql .= "INNER JOIN ord_t_head hed ON (det.pha_id=hed.pha_id AND det.ord_no=hed.ord_no) ";
     $sql .= "INNER JOIN ord_m_pharmacy pha ON (det.pha_id=pha.pha_id) ";
     $sql .= "WHERE det.pha_id=:pha_id ";
     $sql .= "AND det.ord_no=:ord_no";
     $sql .= " ORDER BY det.gyo";

     $params = array("pha_id"=>$pha_id,":ord_no"=>$ord_no);
     $result = $DBA->executeSQL($sql, $params);

     $ret = array();

     if( $result === false )
     {
         $Log->trace("END getOrdersList");
         return $ret;
     }
     while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
     {
         array_push( $ret, $data);
     }

     $Log->trace("END   getOrderData");
     return $ret;
   }

  }
?>
