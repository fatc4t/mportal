<?php
    /**
     * @file      パンくずリスト(View)
     * @author    USE Y.Sakata
     * @date      2016/06/03
     * @version   1.00
     * @note      パンくずリスト
     */
?>

            <!-- menuNameArea -->
            <div class="menuNameArea">
                <h2>
                    <a href="#"><span class="menu"></span></a>
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
                        $pathShow = SystemParameters::$V_A_BASE_PASH . $params[0] . '/show';
                        
                        // 勤怠管理のトップパスを設定
                        $breadcrumns[SystemParameters::$V_S_TOP] = $_SESSION["C_TOP_MENU"][SystemParameters::$V_S_TOP];
                        
                        if($path !== $_SESSION["V_TOP_MENU"][SystemParameters::$V_S_TOP])
                        {
                            if( array_key_exists( "$pathShow", $_SESSION["V_MANAGEMENT_MENU"] ) )
                            {
                                $breadcrumns["#"] = "システム管理";
                                $breadcrumns[$pathShow] = $_SESSION["V_MANAGEMENT_MENU"][$pathShow];
                            }
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

