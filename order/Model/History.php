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
    class History extends BaseModel
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
		 * 発注履歴一覧の取得
		 */
	    public function getHistoryList($Form)
		{
			global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMakerList");

			$params = array();

			$sql  = " select ";
			$sql .= "  hed.ma_cd ma_cd ";
			$sql .= " ,hed.ord_no ord_no ";
			$sql .= " ,max(hed.kbn) kbn ";
			$sql .= " ,max(mak.ma_name) ma_name ";
			$sql .= " ,max(hed.postage) souryo ";
			$sql .= " ,sum(det.kin) syokei ";
			$sql .= " ,sum(det.suu) suu ";
			$sql .= " ,sum(det.hakosuu) hakosuu ";
			$sql .= " ,max(hed.order_time) order_time";
			$sql .= " from ord_t_head hed ";
			$sql .= " inner join ord_t_details det on (hed.pha_id=det.pha_id and hed.ord_no=det.ord_no) ";
			$sql .= " inner join ord_m_maker mak on (hed.ma_cd=mak.ma_cd) ";
			$sql .= " where hed.pha_id=:pha_id ";
			$sql .= " and hed.is_del=0 ";
			if($Form['kbn']!=="") {
				$sql .= " and hed.kbn='".$Form['kbn']."'";
			}
			if($Form['ma_name']!="") {
				$sql .= " and mak.ma_name LIKE '%".$Form['ma_name']."%'";
			}
			if($Form['ymd1']!="") {
				$sql .= " and hed.order_time >='".$Form['ymd1']." 00:00:00'";
			}
			if($Form['ymd2']!="") {
				$sql .= " and hed.order_time <='".$Form['ymd2']." 23:59:59'";
			}
			$sql .= " group by hed.ma_cd,hed.ord_no ";
			$sql .= " order by order_time desc";

			$params = array("pha_id"=>$Form['pha_id']);
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

    }
?>
