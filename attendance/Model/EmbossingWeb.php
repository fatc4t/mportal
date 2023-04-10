<?php
    /**
     * @file      Web用打刻管理モデル
     * @author    USE Y.Sakata
     * @date      2016/08/17
     * @version   1.00
     * @note      アプリ用打刻管理に必要なDBアクセスの制御を行う
     */

    // BaseEmbossing.phpを読み込む
    require './Model/BaseEmbossing.php';

    /**
     * 打刻管理クラス
     * @note   打刻管理に必要なDBアクセスの制御を行う
     */
    class EmbossingWeb extends BaseEmbossing
    {
        /**
         * コンストラクタ
         * @param    $companyID   会社ID
         */
        public function __construct( $companyID )
        {
            // ModelBaseのコンストラクタ
            parent::__construct();

            // スキーマ取得
            $_SESSION["SCHEMA"] = $this->getSchema( $companyID );
            // 祝日情報リストの設定
            $_SESSION["A_PUBLIC_HOLIDAY_LIST"] = $this->getPublicHolidayInfoList();
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
         * 認証キーのチェック
         * @return   打刻情報
         */
        public function getTenpoTokutei($authenticationKey)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTenpoTokutei");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT   *"
                  . "   FROM  m_organization "
                  . "  WHERE  authentication_key = :authentication_key";

            $parameters = array( ':authentication_key'  => $authenticationKey,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END getTenpoTokutei");
                return false;
            }else{
                $count = 0;
                
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    $count++;
                }
                
                if($count > 0){
                    $Log->trace("END getTenpoTokutei");
                    return true;
                }else{
                    $Log->trace("END getTenpoTokutei");
                    return false;
                }
            }
        }

        /**
         * 打刻履歴を取得
         * @return   打刻情報
         */
        public function getEmbossingList($authenticationKey)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getEmbossingList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT   to_char(date_time, 'yyyy年mm月dd日') || '('"
                  . "          || (ARRAY['日','月','火','水','木','金','土'])[EXTRACT(DOW FROM CAST(date_time AS DATE)) + 1] || ')'"
                  . "          || ' ' || to_char(date_time, 'HH24:MI:SS') as date_time"
                  . "         ,embossing_type"
                  . "         ,v.user_name"
                  . "   FROM  t_embossing as te LEFT JOIN (select d.* from m_user_detail as d"
                  . "                                        join (select user_id,max(application_date_start) as application_date_start"
                  . "                                                from m_user_detail"
                  . "                                               where application_date_start <= now()"
                  . "                                               group by user_id"
                  . "                                              ) as ud"
                  . "                                          on (d.user_id = ud.user_id and d.application_date_start = ud.application_date_start)"
                  . "                                     ) as v ON (te.user_id = v.user_id)"
                  . "  		                LEFT JOIN m_organization as o ON (te.organization_id = o.organization_id)"
                  . "  WHERE  o.authentication_key = :authentication_key"
                  . "  ORDER BY date_time desc limit 2000";

            $parameters = array( ':authentication_key'  => $authenticationKey,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'date_time'         => 0,
                            'embossing_type'    => 0,
                            'user_name'         => 0,
                        );
            if( $result === false )
            {
                $Log->trace("END getEmbossingList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getEmbossingList");
            return $ret;
            
        }

        /**
         * 打刻パスワードでスタッフ情報を取得
         * @return   スタッフ情報
         */
        public function getUserInfoEmPass($embossingCode)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserInfoEmPass");
            
            // 従業員Noから、ユーザIDを取得
            $sql  = " SELECT  user_id"
                  . "        ,user_name"
                  . "        ,employees_no"
                  . "        ,organization_id"
                  . "        ,organization_name"
                  . "   FROM  v_user"
//                  . "  WHERE user_id = (select user_id from m_user_embossing where embossing_code =:embossing_code)";
                  . "  WHERE employees_no =:employees_no";

            $parameters = array( ':employees_no'  => $embossingCode,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'user_id'           => 0,
                            'user_name'         => 0,
                            'employees_no'      => 0,
                            'organization_id'   => 0,
                            'organization_name' => 0,
                        );
            if( $result === false )
            {
                $Log->trace("END getUserInfoEmPass");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = array(
                                'user_id'           => $data['user_id'],
                                'user_name'         => $data['user_name'],
                                'employees_no'      => $data['employees_no'],
                                'organization_id'   => $data['organization_id'],
                                'organization_name' => $data['organization_name'],
                            );
            }

            $Log->trace("END getUserInfoEmPass");
            return $ret;
            
        }
        
        /**
         * 勤怠一括更新処理
         */
        public function massUpdate()
        {
            global $DBA, $Log, $workTimeReg; // グローバル変数宣言
            
            $Log->info( "MSG_INFO_1808" );
            $Log->trace("START massUpdate");

            // 契約企業の一覧を取得
            $sql =  ' SELECT company_name FROM public.m_company_contract ';
            $parameters = array();
            $result = $DBA->executeSQL( $sql, $parameters );
            // SQLエラー
            if( $result === false )
            {
                $errMsg = "SQLエラー" . $sql;
                $Log->fatalDetail($errMsg);
                $Log->trace("END massUpdate");
                return;
            }

            $companyList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $companyList, $data['company_name'] );
            }

            foreach( $companyList as $company )
            {
                // スキーマ取得
                $_SESSION["SCHEMA"] = $company;
                // 一企業単位で、勤怠更新
                $this->massCompanyUpdate();
            }
            
            $Log->trace("END massUpdate");
        }

        /**
         * 企業単位で勤怠一括更新処理
         * @return   なし(バッチ処理で、ログ出力のみ)
         */
        private function massCompanyUpdate()
        {
            global $DBA, $Log, $workTimeReg; // グローバル変数宣言
            $Log->trace("START massCompanyUpdate");

            // 勤怠テーブルに反映していない、打刻情報を取得
            $sql =  ' SELECT vu.user_id, vu.employment_id, vu.position_id, vu.organization_id, te.embossing_id, '
                 .  '        te.organization_id as te_organization_id, te.date_time, te.embossing_type '
                 .  " FROM t_embossing te INNER JOIN v_user vu ON te.user_id = vu.user_id "
                 .  " WHERE te.is_attendance = 0 AND vu.eff_code = '適用中' ORDER BY embossing_id ";

            $parameters = array();
            $result = $DBA->executeSQL($sql, $parameters);

            // SQLエラー
            if( $result === false )
            {
                $errMsg = "SQLエラー" . $sql;
                $Log->fatalDetail($errMsg);
                $Log->trace("END massCompanyUpdate");
                return;
            }

            $embossInfoList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $embossInfoList, $data );
            }

            if( $DBA->beginTransaction() )
            {
                // 勤怠テーブル非更新分ループ
                foreach( $embossInfoList as $embossInfo )
                {
                    $userInfo = array(
                                'user_id'           => $embossInfo['user_id'],
                                'employment_id'     => $embossInfo['employment_id'],
                                'position_id'       => $embossInfo['position_id'],
                                'organization_id'   => $embossInfo['organization_id'],
                            );
                    // 勤怠テーブルの更新
                    $attendanceID = $this->exeAttendance( $userInfo, $embossInfo['te_organization_id'], $embossInfo['embossing_type'], $embossInfo['embossing_id'], $embossInfo['date_time'] );
                    // 勤怠テーブルの更新失敗
                    if( 0 == $attendanceID )
                    {
                        // SQL実行エラー　ロールバック対応
                        $DBA->rollBack();
                        // SQL実行エラー
                        $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                        $errMsg = "SQL実行に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END massCompanyUpdate");
                        return;
                    }

                    // 打刻テーブルに、勤怠テーブル登録済みフラグを立てる
                    if( !$this->setIsAttendance( $embossInfo['embossing_id'] ) )
                    {
                        // SQL実行エラー　ロールバック対応
                        $DBA->rollBack();
                        $Log->trace("END massCompanyUpdate");
                        return;
                    }

                    // 勤怠時間の更新を行う
                    $workTimeReg->setAttendanceInfo( $attendanceID );
                }
                
                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END massCompanyUpdate");
                    return;
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END massCompanyUpdate");
                return;
            }
            
            $Log->trace("END massCompanyUpdate");
            return;
        }

        /**
         * 打刻情報(勤怠テーブル更新済みフラグ)を更新する
         * @param   $embossingID       勤怠ID
         * @return  更新結果 true：OK  false：NG
         */
        private function setIsAttendance( $embossingID )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START setEmbossing");

            $sql  = " UPDATE t_embossing SET is_attendance = 1 WHERE embossing_id = :embossing_id ";
            $parameters = array( ':embossing_id'    =>  $embossingID, );

            // SQL実行 (打刻情報テーブルの更新)
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "SQL実行に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END setEmbossing");
                return false;
            }

            $Log->trace("END   setEmbossing");

            return true;
        }

    }
?>
