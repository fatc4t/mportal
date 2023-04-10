<?php
    /**
     * @file      パンくずリスト(View)
     * @author    millionet oota
     * @date      2016/07/01
     * @version   1.00
     * @note      パンくずリスト
     */
?>

            <!-- menuNameArea -->
            <div class="menuNameArea">
                <h2>
                    <a href="#"><span class="menu" /></span></a>
                    <?php 
                        // 現在のパスを設定
                        $path = $_SERVER['REQUEST_URI'];
                        
                        // 機能基準パスを設定
                        // パラメータ取得
                        $param = isset( $_GET['param'] ) ? parent::escStr( $_GET['param'] ) : null;

                        // パラメータ分割
                        $params = array();
                        if ('' != $param)
                        {
                            // パラメーターを / で分割
                            $params = explode('/', $param);
                        }

                        // 一覧のパスを取得
                        $pathShow = SystemParameters::$V_P_BASE_PASH . $params[0] . '/show';
                        
                        // 勤怠管理のトップパスを設定
                        $breadcrumns[SystemParameters::$V_P_TOP] = $_SESSION["P_TOP_MENU"][SystemParameters::$V_P_TOP];
                        
                        if($path !== $_SESSION["P_TOP_MENU"][SystemParameters::$V_P_TOP])
                        {
                            if( array_key_exists( "$pathShow", $_SESSION["P_MANAGEMENT_MENU"] ) )
                            {
                                $breadcrumns["#"] = "メンテナンス";
                                $breadcrumns[$pathShow] = $_SESSION["P_MANAGEMENT_MENU"][$pathShow];
                            }
                            if( array_key_exists( "$pathShow", $_SESSION["REPORT_MENU"] ) )
                            {
                                $breadcrumns["#"] = "売上報告書";
                                $breadcrumns[$pathShow] = $_SESSION["REPORT_MENU"][$pathShow];
                            }
                            if( array_key_exists( "$pathShow", $_SESSION["LEDGER_SHEET_MENU"] ) )
                            {
                                $breadcrumns["#"] = "売上帳票";
                                $breadcrumns[$pathShow] = $_SESSION["LEDGER_SHEET_MENU"][$pathShow];
                            }

                            //if( array_key_exists( "$path", $_SESSION["A_INPUT_MENU"] ) )
                            //{
                            //    $breadcrumns[$path] = $_SESSION["A_INPUT_MENU"][$path];
                            //}
                        }
                        
                        $screenName = '';
                        foreach($breadcrumns as $key => $val)
                        {
                            if($key == $path)
                            {
                                echo("<a href=\"{$key}\">{$val}</a>");
                            } else {
                                echo("<a href=\"{$key}\">{$val}</a> &gt; ");
                            }
                            // 画面の最後のみ保存する（ダイアログ表示用）
                            $screenName = $val;
                        }

                    ?>
                </h2>
            </div><!-- /.menuNameArea -->

