<?php
    /**
     * @file      就業規則マスタ
     * @author    USE S.Kasai
     * @date      2016/06/29
     * @version   1.00
     * @note      就業規則マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/LaborRegulationsInputData.php';

    /**
     * 就業規則マスタクラス
     * @note   就業規則マスタテーブルの管理を行う
     */
    class LaborRegulations extends LaborRegulationsInputData
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
         * 就業規則マスタ新規データ登録
         * @param    $postArray(就業規則マスタ、または就業規則詳細マスタへの登録情報)
         * @param    $addFlag(新規登録時にはtrue)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray, $allArray, $allTimeArray, $allBreakArray, $allBreakZoneArray, $allHourlyChange, $addFlag)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            if($DBA->beginTransaction())
            {
                $ret = "";
                // 就業規則マスタ追加/更新振り分け後、対象の就業規則ID取得
                $laborRegId = "";
                $ret = $this->laborRegulationsProcessingDistribution($postArray, $laborRegId, $addFlag);
                // 就業規則マスタの追加/更新の結果がfalseの場合ロールバック
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "就業規則マスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                // 就業規則適用期間マスタ追加/更新振り分け後、対象の適用期間ID取得
                $applicationDateId = "";
                $ret = $this->applicationDatePreErrorCheck($postArray, $laborRegId, $applicationDateId, $addFlag);
                // 就業規則適用期間マスタの追加/更新の結果がfalseの場合ロールバック
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "就業規則適用期間マスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                
                // ワークファイルの削除
                $this->modDel( $applicationDateId, $laborRegId );
                
                // 就業規則時間マスタ・休憩時間マスタ・休憩時間帯マスタ・休憩シフトマスタ・時給変更マスタの追加/更新/削除処理
                $ret = $this->addNewAllTimeData($postArray, $allTimeArray, $allBreakArray, $allBreakZoneArray, $allHourlyChange, $laborRegId, $applicationDateId );
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "就業規則時間マスタ・休憩時間マスタ・休憩時間帯マスタ・休憩シフトマスタ・時給変更マスタの追加/更新/削除処理に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                
                // 就業規則手当マスタ・残業時間マスタの追加/更新/削除処理
                $ret = $this->addNewAllowanceAndOvertimeData($postArray, $allArray, $laborRegId, $applicationDateId);
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "就業規則手当マスタ・残業時間マスタの追加/更新/削除処理に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                
                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "就業規則マスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "適用期間ID：" . $postarray['application_date_id']. "就業規則名：" . $postarray['labor_regulations_name'] . "サーバエラー";
                $Log->fatalDetail($errMsg);
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END addNewData");
            return $ret;
        }
        
        /**
         * 就業規則手当マスタ・残業時間マスタ新規データ登録
         * @param    $postArray, $allArray, $laborRegId, $applicationDateId
         * @return   MSG_BASE_0000
         */
        private function addNewAllowanceAndOvertimeData($postArray, $allArray, $laborRegId, $applicationDateId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewAllowanceAndOvertimeData");
            
            // 就業規則手当マスタの追加/更新/削除処理
            $ret = $this->workRulesAllowanceProcessingDistribution($postArray, $laborRegId, $applicationDateId);
            // 就業規則手当マスタの追加/更新/削除に失敗した場合ロールバック
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "就業規則手当マスタへの登録更新処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewAllowanceAndOvertimeData");
                return $ret;
            }
            // 残業時間マスタの追加/更新/削除処理
            $closingDateSetID = $postArray['closing_date_set_id'] == '' ? 0 : $postArray['closing_date_set_id'];
            $ret = $this->overtimeProcessingDistribution($postArray, $allArray, $laborRegId, $applicationDateId, $closingDateSetID);
            // 残業時間マスタの追加/更新/削除に失敗した場合ロールバック
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "残業時間マスタの処理に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewAllowanceAndOvertimeData");
                return $ret;
            }
            
            $Log->trace("END addNewAllowanceAndOvertimeData");
            return "MSG_BASE_0000";
        }
        
        /**
         * 就業規則時間マスタ・休憩時間マスタ・休憩時間帯マスタ・休憩シフトマスタ・時給変更マスタ新規データ登録
         * @param    $postArray, $allArray, $laborRegId, $applicationDateId
         * @return   MSG_BASE_0000
         */
        private function addNewAllTimeData($postArray, $allTimeArray, $allBreakArray, $allBreakZoneArray, $allHourlyChange, $laborRegId, $applicationDateId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewAllTimeData");
            
            // 就業規則時間マスタの追加/更新/削除処理
            $ret = $this->workRulesTimeProcessingDistribution($postArray, $laborRegId, $applicationDateId);
            // 就業規則時間マスタの追加/更新/削除に失敗した場合ロールバック
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "就業規則時間マスタへの登録更新処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewAllTimeData");
                return $ret;
            }
            // 就業規則休憩時間マスタの追加/更新/削除処理
            $ret = $this->workRulesBreakTimeProcessingDistribution($postArray, $allTimeArray, $laborRegId, $applicationDateId);
            // 就業規則休憩時間マスタの追加/更新/削除に失敗した場合ロールバック
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "就業規則休憩時間マスタの処理に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewAllTimeData");
                return $ret;
            }
            // 就業規則休憩時間帯マスタの追加/更新/削除処理
            $ret = $this->breakTimeZoneProcessingDistribution($postArray, $allBreakZoneArray, $laborRegId, $applicationDateId);
            // 就業規則休憩時間帯マスタの追加/更新/削除に失敗した場合ロールバック
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "就業規則休憩時間帯マスタの処理に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewAllTimeData");
                return $ret;
            }
            // 就業規則休憩シフトマスタの追加/更新/削除処理
            $ret = $this->workRulesShiftBreakProcessingDistribution($postArray, $allBreakArray, $laborRegId, $applicationDateId);
            // 就業規則休憩シフトマスタの追加/更新/削除に失敗した場合ロールバック
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "就業規則休憩シフトマスタの処理に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewAllTimeData");
                return $ret;
            }
            // 就業規則時給変更マスタの追加/更新/削除処理
            $ret = $this->hourlyWageChangeProcessingDistribution($postArray, $allHourlyChange, $laborRegId, $applicationDateId);
            // 就業規則時給変更マスタの追加/更新/削除に失敗した場合ロールバック
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "就業規則時給変更マスタの処理に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewAllTimeData");
                return $ret;
            }
            
            $Log->trace("END addNewAllTimeData");
            return "MSG_BASE_0000";
        }
        
        /**
         * 就業規則マスタ追加/更新処理振り分け
         * @param    $postArray(就業規則マスタ、または就業規則適用期間マスタへの登録情報)
         * @param    $addFlag
         * @param    $userId(参照して引き渡すため空文字)
         * @return   SQL実行結果（定型文）
         */
        private function laborRegulationsProcessingDistribution($postArray, &$laborRegId, $addFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START laborRegulationsProcessingDistribution");

            // 新規登録か既存組織の適用予定作成かの判定
            if($addFlag)
            {
                // 新規登録の場合就業規則マスタへ登録
                $ret = $this->addLaborRegulationsNewData($postArray, $laborRegId);
            }
            else
            {
                // 就業規則マスタ更新
                $ret = $this->modLaborRegulationsUpdateData($postArray);
                $laborRegId = $postArray['up_labor_regulations_id'];
                
            }

            $Log->trace("END laborRegulationsProcessingDistribution");
            return $ret;
        }
        
        /**
         * 就業規則適用期間マスタ追加/更新処理振り分け
         * @param    $postArray(就業規則マスタ、または就業規則適用期間マスタへの登録情報)
         * @param    $laborRegId
         * @param    $addFlag
         * @param    $applicationDateId (参照して引き渡すため空文字)
         * @return   SQL実行結果（定型文）
         */
        private function applicationDatePreErrorCheck($postArray, $laborRegId, &$applicationDateId, $addFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START applicationDatePreErrorCheck");

            // 更新予定適用開始日をUnixtimestamp形式に変換
            $regApplicationUNIX = strtotime($postArray['application_date_start']);
            // 既存登録適用開始日をUnixtimestamp形式に変換
            $comApplicationUNIX = strtotime($postArray['up_application_date_start']);
            // 現在日時をUnixtimestamp形式にて取得
            $timestamp = time();
            // 新規登録時以外、変更した適用開始時が現在日時より過去だった場合エラーとする
            if(empty($addFlag))
            {
                if($regApplicationUNIX != $comApplicationUNIX && $timestamp > $regApplicationUNIX)
                {
                    $Log->trace("START applicationDatePreErrorCheck");
                    return "MSG_ERR_3093";
                }
            }
            // POSTで送られてきた配列に登録済みの就業規則適用期間IDを入れ込む
            $postArray['regLaborRegulationsId'] = $laborRegId;
            // 登録更新の振り分け処理
            $ret = $this->applicationDateProcessingDistribution($postArray, $applicationDateId, $regApplicationUNIX, $comApplicationUNIX, $timestamp);
            // 返り値
            $Log->trace("END applicationDatePreErrorCheck");
            return $ret;
        }
        
        /**
         * 就業規則適用期間マスタ追加/更新処理振り分け
         * @param    $postArray(就業規則適用期間詳細マスタへの登録情報に登録済みの適用期間IDを加えたもの)
         * @param    $applicationDateId (参照して引き渡すため空文字)
         * @param    $regApplicationUNIX
         * @param    $comApplicationUNIX
         * @param    $timestamp
         * @return   SQL実行結果（定型文）
         */
        private function applicationDateProcessingDistribution($postArray, &$applicationDateId, $regApplicationUNIX, $comApplicationUNIX, $timestamp)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START applicationDateProcessingDistribution");

            if($regApplicationUNIX == $comApplicationUNIX)
            {
                // 更新予定の適用開始日と既存登録の適用開始日が同じの場合、UPDATE処理
                $ret = $this->modApplicationDateUpdateData($postArray);
                // 成功の場合更新対象の適用期間IDを参照渡しさせる
                $applicationDateId = $postArray['up_application_date_id'];
            }
            else
            {
                if($timestamp < $comApplicationUNIX)
                {
                    // 就業規則適用期間マスタへの更新
                    $ret = $this->modApplicationDateUpdateData($postArray);
                    // 成功の場合更新対象の適用期間IDを参照渡しさせる
                    $applicationDateId = $postArray['up_application_date_id'];
                }
                else
                {
                    // 既存の適用予定情報の存在有無を判定し、なければ新規で適用予定情報を作成する。
                    $applicationCnt = $this->generationSQL->getRegistrationCount($postArray['regLaborRegulationsId'], "m_application_date", "labor_regulations_id", true);
                    if(empty($applicationCnt))
                    {
                        // 就業規則適用期間マスタへの登録
                        $ret = $this->addApplicationDateNewData($postArray, $applicationDateId);
                    }
                    else
                    {
                        $Log->trace("END applicationDateProcessingDistribution");
                        return "MSG_ERR_3094";
                    }
                }
            }

            $Log->trace("END applicationDateProcessingDistribution");
            return $ret;
        }
        
        /**
         * 就業規則時間マスタ追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function workRulesTimeProcessingDistribution($postArray, $laborRegId, $applicationDateId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START workRulesTimeProcessingDistribution");
            
            // POSTで送られてきた配列に登録済みの就業規則適用期間IDを入れ込む
            $postArray['regLaborRegulationsId'] = $laborRegId;
            $postArray['regApplicationDateId'] = $applicationDateId;

            // 就業規則時間マスタへ登録
            $ret = $this->addWorkRulesTimeNewData($postArray);

            $Log->trace("END workRulesTimeProcessingDistribution");
            return $ret;
        }
        
        /**
         * 就業規則休憩時間マスタ追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function workRulesBreakTimeProcessingDistribution($postArray, $allTimeArray, $laborRegId, $applicationDateId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START workRulesBreakTimeProcessingDistribution");
            
            $ret = "MSG_BASE_0000";
            
            // POSTで送られてきた配列に登録済みの就業規則適用期間IDを入れ込む
            $postArray['regLaborRegulationsId'] = $laborRegId;
            $postArray['regApplicationDateId'] = $applicationDateId;
            
            // 就業規則休憩時間マスタへ登録
            $ret = $this->addWorkRulesBreakNewData($postArray, $allTimeArray);

            $Log->trace("END workRulesBreakTimeProcessingDistribution");
            return $ret;
        }
        
        /**
         * 就業規則休憩時間帯マスタ追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function breakTimeZoneProcessingDistribution($postArray, $allBreakZoneArray, $laborRegId, $applicationDateId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START breakTimeZoneProcessingDistribution");
            
            $ret = "MSG_BASE_0000";
            
            // POSTで送られてきた配列に登録済みの就業規則適用期間IDを入れ込む
            $postArray['regLaborRegulationsId'] = $laborRegId;
            $postArray['regApplicationDateId'] = $applicationDateId;

            // 就業規則休憩時間帯マスタへ登録
            $ret = $this->addBreakTimeZoneNewData($postArray, $allBreakZoneArray);

            $Log->trace("END breakTimeZoneProcessingDistribution");
            return $ret;
        }
        
        /**
         * 就業規則休憩シフトマスタ追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function workRulesShiftBreakProcessingDistribution($postArray, $allBreakArray, $laborRegId, $applicationDateId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START workRulesShiftBreakProcessingDistribution");
            
            $ret = "MSG_BASE_0000";
            
            // POSTで送られてきた配列に登録済みの就業規則適用期間IDを入れ込む
            $postArray['regLaborRegulationsId'] = $laborRegId;
            $postArray['regApplicationDateId'] = $applicationDateId;

            // 新規登録の場合就業規則休憩シフトマスタへ登録
            $ret = $this->addWorkRulesShiftBreakNewData($postArray, $allBreakArray);

            $Log->trace("END workRulesShiftBreakProcessingDistribution");
            return $ret;
        }
        
        /**
         * 就業規則手当マスタ追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function workRulesAllowanceProcessingDistribution($postArray, $laborRegId, $applicationDateId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START workRulesAllowanceProcessingDistribution");
            
            // POSTで送られてきた配列に登録済みの就業規則適用期間IDを入れ込む
            $postArray['regLaborRegulationsId'] = $laborRegId;
            $postArray['regApplicationDateId'] = $applicationDateId;

            // 新規登録の場合就業規則手当マスタへ登録
            $ret = $this->addWorkRulesAllowanceNewData($postArray);

            $Log->trace("END workRulesAllowanceProcessingDistribution");
            return $ret;
        }
        
        /**
         * 就業規則時給変更マスタ追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function hourlyWageChangeProcessingDistribution($postArray, $allHourlyChange, $laborRegId, $applicationDateId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START hourlyWageChangeProcessingDistribution");
            
            // POSTで送られてきた配列に登録済みの就業規則適用期間IDを入れ込む
            $postArray['regLaborRegulationsId'] = $laborRegId;
            $postArray['regApplicationDateId'] = $applicationDateId;

            // 新規登録の場合就業規則時給変更マスタへ登録
            $ret = $this->addHourlyWageChangeNewData($postArray, $allHourlyChange);

            $Log->trace("END hourlyWageChangeProcessingDistribution");
            return $ret;
        }
        
        /**
         * 残業時間マスタ追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function overtimeProcessingDistribution($postArray, $allArray, $laborRegId, $applicationDateId, $closingDateSetID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START overtimeProcessingDistribution");
            
            $ret = "MSG_BASE_0000";
            
            // POSTで送られてきた配列に登録済みの就業規則適用期間IDを入れ込む
            $postArray['regLaborRegulationsId'] = $laborRegId;
            $postArray['regApplicationDateId'] = $applicationDateId;
            
            // 残業時間詳細IDを設定
            $overtimeDetailID = $this->getOverTimeDetailID( $postArray['overtime_setting'] );
            
            $index = 0;
            $addVal = "";
            foreach ( $allArray as $val )
            {
                if( !($index % 2) )
                {
                    $addVal = $val;
                }
                else
                {
                    if( $addVal != '' && $val != '' )
                    {
                        // 新規登録の場合残業マスタへ登録
                        $ret = $this->addOvertimeNewData($postArray, $addVal, $val, $overtimeDetailID, $closingDateSetID );
                        $overtimeDetailID++;
                        $addVal = "";
                    }
                }
                
                $index++;
            }
            
            $Log->trace("END overtimeProcessingDistribution");
            return $ret;
        }

        
        /**
         * 残業時間詳細IDを取得
         * @param    $overtimeSetting  残業設定ID
         * @return   残業時間詳細ID
         */
        private function getOverTimeDetailID( $overTimeSetting )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOvertimeDetailID");
            
            $overTimeDetailIDList = array (
                                                '1'   => 2,
                                                '2'   => 3,
                                                '3'   => 1,
                                                '4'   => 2,
                                                '5'   => 4,
                                                '6'   => 12,
                                                '7'   => 16,
                                                '8'   => 1,
                                          );
            $ret = 1;
            if( array_key_exists( $overTimeSetting, $overTimeDetailIDList ) )
            {
                $ret = $overTimeDetailIDList[$overTimeSetting];
            }
            
            $Log->trace("END getOvertimeDetailID");
            
            return $ret;
        }
        
    }
?>
