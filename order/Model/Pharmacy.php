<?php
    /**
     * @file      薬剤管理　薬局マスタ
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2018/02/22
     * @version   1.00
     * @note      薬局マスタマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * アクセス権限マスタクラス
     * @note   アクセス権限マスタテーブルの管理を行う
     */
    class Pharmacy extends BaseModel
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
         * 薬局一覧の取得
         * @param    $where 薬局名
         * @return   SQLの実行結果
         */
		public function getPharmacyList($where="") {
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
     * 薬局データの取得
     * @param    $where 薬局名
     * @return   SQLの実行結果
     */
		public function getPharmacyData($pha_id) {
			global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPharmacyData");

			$params = array();

			$sql  = " SELECT pha.*,log.login_id,log.password From ord_m_pharmacy pha ";
      $sql .= " INNER JOIN m_login log ON (pha.user_id=log.user_id)";
      $sql .= " where is_del=0";
			$sql .= " AND pha_id=:pha_id";
			$sql .= " ORDER BY pha_id";
			$params = array("pha_id"=>$pha_id);
			$result = $DBA->executeSQL($sql,$params);
      $ret = $result->fetch(PDO::FETCH_ASSOC);
      $Log->trace("END getPharmacyData");
      return $ret;
		}
		/**
     * 薬局データの保存
     * @param    $pha_id 薬局ID
     * @return   SQLの実行結果
     */
		public function savePharmacyData($Form) {
			global $DBA, $Log; // グローバル変数宣言
      $Log->trace("START savePharmacyData");

			$params = array();
			/*
			$sql  = " UPDATE ord_m_pharmacy SET is_del=1,update_time='".date("Y/m/d H:i:s")."'";
			$sql .= " WHERE pha_id=:pha_id";
			*/

			if(empty($Form['pha_id']))
			{

        //ユーザーの追加
        $sql  = " SELECT max(user_id) user_id From m_user ";
        $result = $DBA->executeSQL($sql,$params);
	      $row = $result->fetch(PDO::FETCH_ASSOC);

        $user_id = $row['user_id'] + 1;
        //ユーザーマスタ
        $sql  = " INSERT INTO m_user (";
        $sql .= " user_id";
        $sql .= ",is_del";
        $sql .= ",registration_time";
        $sql .= ",registration_user_id";
        $sql .= ",registration_organization";
        $sql .= ",update_time";
        $sql .= ",update_user_id";
        $sql .= ",update_organization";
        $sql .= ") VALUES (";
        $sql .= $user_id;
        $sql .= ",0";
        $sql .= ",'".date("Y/m/d H:i:s")."'";
        $sql .= ",1";
        $sql .= ",1";
        $sql .= ",'".date("Y/m/d H:i:s")."'";
        $sql .= ",1";
        $sql .= ",1";
        $sql .= ")";
        $ret = $this->executeOneTableSQL($sql);
        //ログインアカウントマスタ
        $sql  = " INSERT INTO m_login ( ";
        $sql .= "  login_id";
        $sql .= " ,password";
        $sql .= " ,user_id";
        $sql .= " ) VALUES ( ";
        $sql .= "'".$Form['login_id']."'";
        $sql .= ",'".$Form['password']."'";
        $sql .= ",".$user_id;
        $sql .= ")";
        $ret = $this->executeOneTableSQL($sql);
        //ユーザー権限マスタ
        $sql  = " SELECT max(user_detail_id) user_detail_id From m_user_detail ";
        $result = $DBA->executeSQL($sql,$params);
	      $row = $result->fetch(PDO::FETCH_ASSOC);

        $user_detail_id = $row['user_detail_id']+1;

        $sql  = " INSERT INTO m_user_detail (";
        $sql .= "  user_detail_id";
        $sql .= " ,user_id";
        $sql .= " ,application_date_start";
        $sql .= " ,employees_no";
        $sql .= " ,organization_id";
        $sql .= " ,position_id";
        $sql .= " ,employment_id";
        $sql .= " ,wage_form_id";
        $sql .= " ,security_id";
        $sql .= " ,user_name";
        $sql .= " ) VALUES ( ";
        $sql .= " ".$user_detail_id;
        $sql .= ",".$user_id;
        $sql .= ",'".$Form['start_ymd']."'";
        $sql .= ",3";
        $sql .= ",9";
        $sql .= ",1";
        $sql .= ",1";
        $sql .= ",1";
        $sql .= ",9";
        $sql .= ",'".$Form['pha_name']."'";
        $sql .= " ) ";
        $ret = $this->executeOneTableSQL($sql);

        $Log->debug("new test  ".$user_id.":".$user_detail_id);


				//新規作成
				$sql  = " SELECT max(pha_id) pha_id From ord_m_pharmacy ";
				$result = $DBA->executeSQL($sql,$params);
	            $row = $result->fetch(PDO::FETCH_ASSOC);

				$Form['pha_id'] = $row['pha_id']+1;

				$sql  = " INSERT INTO ord_m_pharmacy ( ";
				$sql .= "  pha_id";
				$sql .= " ,pha_name";
				$sql .= " ,start_ymd";
				$sql .= " ,registration_time";
				$sql .= " ,is_del";
        $sql .= " ,user_id";
        $sql .= " ,mail_addr";
        $sql .= " ,person_name";
				$sql .= " ) VALUES ( ";
				$sql .= " :pha_id";
				$sql .= ",:pha_name";
				$sql .= ",:start_ymd";
				$sql .= ",'".date("Y/m/d H:i:s")."'";
				$sql .= ",0";
        $sql .= ",:user_id";
        $sql .= ",:mail_addr";
        $sql .= ",:person_name";
				$sql .= " ) ";
				$params = array(
								 "pha_id"=>$Form['pha_id']
								,"pha_name"=>$Form['pha_name']
								,"start_ymd"=>$Form['start_ymd']
                ,"user_id"=>$user_id
                ,"mail_addr"=>$Form['mail_addr']
                ,"person_name"=>$Form['person_name']
								);
				$ret = $this->executeOneTableSQL( $sql, $params );
			}else{

        //user_idの取得
        $sql  = " SELECT user_id FROM ord_m_pharmacy WHERE pha_id=".$Form['pha_id'];
        $result = $DBA->executeSQL($sql,$params);
	      $row = $result->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['user_id'];

        $sql  = " UPDATE m_login SET login_id=:login_id,password=:password WHERE user_id=:user_id";
        $params = array(
								 "login_id"=>$Form['login_id']
								,"password"=>$Form['password']
								,"user_id"=>$user_id
								);
				$ret = $this->executeOneTableSQL( $sql, $params );

        $sql  = " UPDATE m_user_detail SET application_date_start=:start_ymd,user_name=:user_name WHERE user_id=:user_id";
        $params = array(
								 "start_ymd"=>$Form['start_ymd']
								,"user_id"=>$user_id
                ,"user_name"=>$Form['pha_name']
								);
				$ret = $this->executeOneTableSQL( $sql, $params );

				//更新
				$sql  = " UPDATE ord_m_pharmacy SET ";
				$sql .= "  pha_name=:pha_name";
				$sql .= " ,start_ymd=:start_ymd";
				$sql .= " ,update_time='".date("Y/m/d H:i:s")."'";
        $sql .= " ,mail_addr=:mail_addr";
        $sql .= " ,person_name=:person_name";
				$sql .= " WHERE pha_id=:pha_id";
				$params = array(
								 "pha_id"=>$Form['pha_id']
								,"pha_name"=>$Form['pha_name']
								,"start_ymd"=>$Form['start_ymd']
                ,"mail_addr"=>$Form['mail_addr']
                ,"person_name"=>$Form['person_name']
								);
				$ret = $this->executeOneTableSQL( $sql, $params );
			}
      $Log->trace("END savePharmacyData");

      return $ret;
		}
		/**
    * 薬局データの削除
    * @param    $pha_id 薬局ID
    * @return   SQLの実行結果
    */
		public function deletePharmacyData($pha_id) {
			global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deletePharmacyData");

			$params = array();

      $sql  = " SELECT user_id FROM ord_m_pharmacy WHERE pha_id=:pha_id";
      $params = array(":pha_id"=>$pha_id);
      $result = $DBA->executeSQL($sql,$params);
      $row = $result->fetch(PDO::FETCH_ASSOC);
      $user_id = $row['user_id'];

      $sql = " UPDATE m_user SET is_del=1 WHERE user_id=:user_id";
      $params = array(":user_id"=>$user_id);
      $result = $DBA->executeSQL($sql,$params);
      $ret = $this->executeOneTableSQL( $sql, $params );

			$sql  = " UPDATE ord_m_pharmacy SET is_del=1,update_time='".date("Y/m/d H:i:s")."'";
			$sql .= " WHERE pha_id=:pha_id";
			$params = array("pha_id"=>$pha_id);
			$ret = $this->executeOneTableSQL( $sql, $params );

      $Log->trace("END deletePharmacyData");

      return $ret;
		}
  }
?>
