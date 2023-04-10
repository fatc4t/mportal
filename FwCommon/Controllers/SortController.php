<?php
    /**
     * @file    ソートコントローラ(Controller)
     * @author  USE M.Higashihara
     * @date    2016/06/22
     * @version 1.00
     * @note    適用開始が絡む機能（従業員マスタ等）の一覧画面ソート機能共通処理を定義
     */

    // FwBaseClassの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseClass.php';

    /**
     * ページコントローラ
     * @note    各画面で共通使用するページングの処理を定義
     */
    class SortController extends FwBaseClass
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();
        }
        
        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseClassのデストラクタ
            parent::__destruct();
        }

        /**
         * ソート条件を取得（SORT_ASC/SORT_DESC）
         * @note     配列のソート条件指定
         * @param    $sortFlag
         * @return   $sortConditions
         */
        public function getSortConditions($sortFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getSortConditions");

            if(($sortFlag % 2) != 0)
            {
                $sortConditions = SORT_DESC;
            }
            else
            {
                $sortConditions = SORT_ASC;
            }

            $Log->trace("END getSortConditions");

            return $sortConditions;
        }

        /**
         * 指定したキーによるリストのソート
         * @note     一覧表示用のリストの作成
         * @param    $userAllList
         * @param    $sortFlag
         */
        public function sortArrayByKey(&$list, $designationKey, $conditions, $comparison)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START sortArrayByKey");

            $tmpArray = array();
            $tmpArray = array_column($list, $designationKey);

            array_multisort($tmpArray, $conditions, $comparison, $list );
            unset( $tmpArray );

            $Log->trace("END sortArrayByKey");
        }

        /**
         * ヘッダタグの押された項目回数によってソートマークを設定する
         * @note     ソートのマークの設定
         * @param    $userAllList
         * @param    $sortFlag
         */
        public function sortMarkInsert($headerArray, $sortNo, $sortList)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START sortMarkInsert");

            $temp = "";
            if(array_key_exists($sortNo, $sortList))
            {
                $temp = $sortList[$sortNo];
            }

            $mark = SystemParameters::$SORT_ASC_MARK;

            if( $sortNo % 2 != 0 )
            {
                $mark = SystemParameters::$SORT_DESC_MARK;
            }

            $headerArray[$temp] = $mark;

            $Log->trace("END sortMarkInsert");

            return $headerArray;
        }

    }
?>
