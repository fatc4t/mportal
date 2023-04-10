<?php
  /**
   * @file      薬剤管理　発注先マスタ
   * @author    USE K.Kanda(media-craft.co.jp)
   * @date      2018/02/22
   * @version   1.00
   * @note      発注先マスタマスタテーブルの管理を行う
   */

  // BaseModel.phpを読み込む
  require './Model/Common/BaseModel.php';

  /**
   * アクセス権限マスタクラス
   * @note   アクセス権限マスタテーブルの管理を行う
   */
  class Maker extends BaseModel
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
		public function getMakerList($where="")
    {
			global $DBA, $Log; // グローバル変数宣言
      $Log->trace("START getMakerList");

			$params = array();

			$sql  = " SELECT * From ord_m_maker where is_del=0";
			if($where!=="") {
				$sql .= " AND ma_name LIKE :ma_name";
				$params = array("ma_name"=>"%".$where."%");

			}
			$sql .= " ORDER BY ma_cd";
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
     * 発注先データの取得
     * @param    $where 発注先名
     * @return   SQLの実行結果
     */
		public function getMakerData($ma_cd)
    {
			global $DBA, $Log; // グローバル変数宣言
      $Log->trace("START getMakerData");

			$params = array();

			$sql  = " SELECT * From ord_m_maker where is_del=0";
			$sql .= " AND ma_cd=:ma_cd";
			$sql .= " ORDER BY ma_cd";
			$params = array("ma_cd"=>$ma_cd);
			$result = $DBA->executeSQL($sql,$params);
      $ret = $result->fetch(PDO::FETCH_ASSOC);

      $Log->trace("END getMakerData");

      return $ret;
    }
		/**
     * 発注先データの保存
     * @param    $ma_cd 発注先ID
     * @return   SQLの実行結果
     */
		public function saveMakerData($Form)
    {
			global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START saveMakerData");

			$params = array();

			$str1 = "";

			for($i=0;$i<=6;$i++) {
				//$Log->debug("get_day:".$Form['get_day'][$i]);
				if(!empty($Form['get_day'][$i])) {
					$str1 .= ",".$i;
				}
			}

			if(empty($Form['ma_cd']))
			{
				//新規作成
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
        $sql .= ",'".$Form['login_pass']."'";
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
        $sql .= ",'".date("Y/m/d")."'";
        $sql .= ",3";
        $sql .= ",8";
        $sql .= ",1";
        $sql .= ",1";
        $sql .= ",1";
        $sql .= ",8";
        $sql .= ",'".$Form['ma_name']."'";
        $sql .= " ) ";
        $ret = $this->executeOneTableSQL($sql);

        $Log->debug("new test  ".$user_id.":".$user_detail_id);

				$sql  = " SELECT max(ma_cd) ma_cd From ord_m_maker ";
				$result = $DBA->executeSQL($sql,$params);
	            $row = $result->fetch(PDO::FETCH_ASSOC);

				$Form['ma_cd'] = $row['ma_cd']+1;

				$sql  = " INSERT INTO ord_m_maker ( ";
				$sql .= "  ma_cd";
				$sql .= " ,ma_name";
				$sql .= " ,registration_time";
				$sql .= " ,is_del";
				$sql .= " ,get_day";
        $sql .= " ,login_id";
        $sql .= " ,login_pass";
        $sql .= " ,mail_addr";
        $sql .= " ,url1";
        $sql .= " ,url2";
        $sql .= " ,user_id";
        $sql .= " ,overview";
        $sql .= " ,description";
        $sql .= " ,address";
        $sql .= " ,representative";
				$sql .= " ) VALUES ( ";
				$sql .= " :ma_cd";
				$sql .= ",:ma_name";
				$sql .= ",'".date("Y/m/d H:i:s")."'";
				$sql .= ",0";
				$sql .= ",:get_day";
        $sql .= ",:login_id";
        $sql .= ",:login_pass";
        $sql .= ",:mail_addr";
        $sql .= ",:url1";
        $sql .= ",:url2";
        $sql .= ",:user_id";
        $sql .= ",:overview";
        $sql .= ",:description";
        $sql .= ",:address";
        $sql .= ",:representative";
				$sql .= " ) ";
				$params = array(
								 "ma_cd"=>$Form['ma_cd']
								,"ma_name"=>$Form['ma_name']
								,"get_day"=>$str1
                ,"login_id"=>$Form['login_id']
                ,"login_pass"=>$Form['login_pass']
                ,"mail_addr"=>$Form['mail_addr']
                ,"url1"=>$Form['url1']
                ,"url2"=>$Form['url2']
                ,"user_id"=>$user_id
                ,"overview"=>$Form['overview']
                ,"description"=>$Form['description']
                ,"address"=>$Form['address']
                ,"representative"=>$Form['representative']
								);
				$ret = $this->executeOneTableSQL( $sql, $params );
			}else{

        //user_idの取得
        $sql  = " SELECT user_id FROM ord_m_maker WHERE ma_cd=".$Form['ma_cd'];
        $result = $DBA->executeSQL($sql,$params);
	      $row = $result->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['user_id'];

        $sql  = " UPDATE m_login SET login_id=:login_id,password=:password WHERE user_id=:user_id";
        $params = array(
								 "login_id"=>$Form['login_id']
								,"password"=>$Form['login_pass']
								,"user_id"=>$user_id
								);
				$ret = $this->executeOneTableSQL( $sql, $params );

				//更新
				$sql  = " UPDATE ord_m_maker SET ";
				$sql .= "  ma_name=:ma_name";
				$sql .= " ,get_day=:get_day";
				$sql .= " ,update_time='".date("Y/m/d H:i:s")."'";
        $sql .= " ,login_id=:login_id";
        $sql .= " ,login_pass=:login_pass";
        $sql .= " ,mail_addr=:mail_addr";
        $sql .= " ,url1=:url1";
        $sql .= " ,url2=:url2";
        $sql .= " ,overview=:overview";
        $sql .= " ,description=:description";
        $sql .= " ,address=:address";
        $sql .= " ,representative=:representative";
				$sql .= " WHERE ma_cd=:ma_cd";
				$params = array(
								 "ma_cd"=>$Form['ma_cd']
								,"ma_name"=>$Form['ma_name']
								,"get_day"=>$str1
                ,"login_id"=>$Form['login_id']
                ,"login_pass"=>$Form['login_pass']
                ,"mail_addr"=>$Form['mail_addr']
                ,"url1"=>$Form['url1']
                ,"url2"=>$Form['url2']
                ,"overview"=>$Form['overview']
                ,"description"=>$Form['description']
                ,"address"=>$Form['address']
                ,"representative"=>$Form['representative']
								);
				$ret = $this->executeOneTableSQL( $sql, $params );
			}

      //CSV用フォルダの作成
      $path = self::CSVFLD.$_POST['ma_cd'].'/';
      if(!file_exists($path))
      {
          mkdir($path);
      }

      $Log->trace("END saveMakerData");
      return $ret;
		}
		/**
   * 発注先データの削除
   * @param    $ma_cd 発注先ID
   * @return   SQLの実行結果
   */
		public function deleteMakerData($ma_cd) {
			global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deleteMakerData");

			$params = array();

			//$sql  = " UPDATE ord_m_maker SET is_del=1,update_time='".date("Y/m/d H:i:s")."'";
      $sql  = " SELECT user_id FROM ord_m_maker WHERE ma_cd=:ma_cd";
      $params = array(":ma_cd"=>$ma_cd);
      $result = $DBA->executeSQL($sql,$params);
      $row = $result->fetch(PDO::FETCH_ASSOC);
      $user_id = $row['user_id'];

      $sql = " UPDATE m_user SET is_del=1 WHERE user_id=:user_id";
      $params = array(":user_id"=>$user_id);
      $result = $DBA->executeSQL($sql,$params);


      $sql  = " UPDATE ord_m_maker SET is_del=1";
			$sql .= " WHERE ma_cd=:ma_cd";
			$params = array(":ma_cd"=>$ma_cd);
			$ret = $DBA->executeSQL($sql,$params);

            $Log->trace("END deleteMakerData");

            return $ret;
		}
  }
?>
