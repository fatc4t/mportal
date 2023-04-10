<?php
    /**
     * @file      薬剤管理　発注先設定
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2018/02/22
     * @version   1.00
     * @note      発注先マスタマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 発注先設定クラス
     * @note   発注先テーブルの管理を行う
     */
    class Recipient extends BaseModel
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
         * 薬局の一覧を取得
         * @param    $where 薬局名
         * @return   SQLの実行結果
         */
        public function getPharmacyList($where="")
		{
			global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPharmacyList");

			$params = array();

			$sql  = " SELECT * From ord_m_pharmacy where is_del=0";
			if(!empty($where)) {
				$sql .= " AND pha_name LIKE :pha_name";
				$params = array("pha_name"=>"%".$where."%");

			}
			$sql .= " ORDER BY pha_id";
			$result = $DBA->executeSQL($sql, $params);

            $ret = array();

            if( $result === false )
            {
                $Log->trace("END getPharmacyList");
                return $ret;
            }
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ret, $data);
            }

            $Log->trace("END getPharmacyList");

            return $ret;
		}


        /**
         * 未選択の取引先の取得
         * @param    $where 薬局名
         * @return   SQLの実行結果
         */
        public function getMakerList1($pha_id="")
		{
			global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMakerList1");

			$params = array();
			$ret = array();

			if($pha_id!="") {
				$sql  = " select ma.ma_cd ma_cd,ma.ma_name ma_name From ord_m_maker ma where is_del=0 and  not exists (";
				$sql .= "    select se.ma_cd from ord_m_makerset se where ma.ma_cd=se.ma_cd and se.pha_id=:pha_id ";
				$sql .= ") order by ma.ma_cd";

				$params = array("pha_id"=>$pha_id);

				$result = $DBA->executeSQL($sql, $params);
				if( $result === false )
				{
					$Log->trace("END getMakerList1");
					return $ret;
				}
				while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
				{
					array_push( $ret, $data);
				}
			}
            $Log->trace("END getMakerList1");

            return $ret;
		}
        /**
         * 選択済みの取引先の取得
         * @param    $where 薬局名
         * @return   SQLの実行結果
         */
        public function getMakerList2($pha_id="")
		{
			global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMakerList2");

			$params = array();
			$ret = array();

			if($pha_id!="") {
				$sql  = " select ma.ma_cd ma_cd,ma.ma_name ma_name From ord_m_maker ma where is_del=0 and exists (";
				$sql .= "    select se.ma_cd from ord_m_makerset se where ma.ma_cd=se.ma_cd and se.pha_id=:pha_id ";
				$sql .= ") order by ma.ma_cd";

				$sql  = " select ma.ma_cd ma_cd,ma.ma_name ma_name,se.postage postage,se.free_kin free_kin From ord_m_maker ma";
				$sql .= " inner join ord_m_makerset se on(ma.ma_cd=se.ma_cd and se.pha_id=:pha_id)";
				$sql .= " where ma.is_del=0 ";
				$sql .= " order by ma.ma_cd";

				$params = array("pha_id"=>$pha_id);

				$result = $DBA->executeSQL($sql, $params);
				if( $result === false )
				{
					$Log->trace("END getMakerList2");
					return $ret;
				}
				while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
				{
					array_push( $ret, $data);
				}
			}
            $Log->trace("END getMakerList2");

            return $ret;
		}
		/**
         * 発注先設定データの登録
         * @param    $Form POSTデータ
         * @return   SQLの実行結果
         */
        public function saveRecipientData($Form)
		    {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START saveMakerData");

			      $params = array("pha_id"=>$Form['pha_id']);

				//削除
  			$sql  = " DELETE FROM ord_m_makerset WHERE pha_id=:pha_id ";
  			$ret = $DBA->executeSQL( $sql, $params );

  			for($i=0;$i<count($Form['ma_cd']);$i++) {
  				$params = array(
                 "pha_id"   =>$Form['pha_id']
								,"ma_cd"    =>$Form['ma_cd'][$i]
					      ,"postage"  =>$this->changeZero($Form['postage'][$i])
    						,"free_kin" =>$this->changeZero($Form['free_kin'][$i])
						);
  				$sql  = " INSERT INTO ord_m_makerset ( ";
	        $sql .= "  pha_id";
  				$sql .= " ,ma_cd";
  				$sql .= " ,postage";
  				$sql .= " ,free_kin";
  				$sql .= " ,registration_time";
  				$sql .= " ) VALUES ( ";
	        $sql .= " :pha_id";
  				$sql .= ",:ma_cd";
  				$sql .= ",:postage";
  				$sql .= ",:free_kin";
  				$sql .= ",'".date("Y/m/d H:i:")."'";
  				$sql .= " ) ";

  				$ret = $DBA->executeSQL( $sql, $params );
  			}
  		}
		/**
         * 空の場合に0を返す
         * @param    文字列
         * @return   文字列
         */
        public function changeZero($param)
		{
            $rtnStr = $param;
			if(empty($rtnStr)) {
				$rtnStr = "0";
			}
		    return $rtnStr;
		}
    }
?>
