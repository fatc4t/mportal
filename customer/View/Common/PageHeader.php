<?php
    /**
     * @file      ページ共通ヘッダ(View)
     * @author    USE Y.Sakata
     * @date      2016/04/27
     * @version   1.00
     * @note      ページ共通ヘッダ
     */
?>

    <!-- Navbar -->
    <nav class="navbar navbar-default navbar-fixed-top sb-slide" role="navigation">
        <!-- Left Control -->
        <div class="sb-toggle-left navbar-left">
            <div class="navicon-line"></div>
            <div class="navicon-line"></div>
            <div class="navicon-line"></div>
        </div><!-- /.sb-control-left -->
        
        <!-- Right Control -->
        <div class="sb-toggle-right navbar-right">
            <div class="navicon-line"></div>
            <div class="navicon-line"></div>
            <div class="navicon-line"></div>
        </div><!-- /.sb-control-right -->

        <div class="container">
            <!-- Logo -->
            <div id="logo" class="navbar-left">
                <a href="#"><img src="../img/top-menu-logo.png" alt="HOME" width="118" height="40"></a>
            </div><!-- /.logo -->
            
            <!-- Menu -->
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#">ﾌﾞｯｸﾏｰｸ登録</a></li>
                <li><a href="../home/index.php?param=Home/show">ホーム</a></li>
                <li><a href="#"><?php hsc( $_SESSION["SECURITY_NAME"] ); ?></a></li>
                <li><a href="#"><?php hsc( $_SESSION["USER_NAME"] ); ?></a></li>
                <li><a href="<?php hsc( SystemParameters::$LOGOUT_PATH ); ?>">ログアウト</a></li>
                <li><a id="top-arrow" href="#top">＾</a></li>
            </ul><!-- /.Menu -->
        </div>
    </nav>

    <!-- Slidebars -->
    <!-- sb-left -->
    <div class="sb-slidebar sb-left">
        <nav>
            <ul class="sb-menu">
                <li><img src="../img/logo-attendance.png" alt="Slidebars" width="118" height="40"></li>
                <li class="sb-close"><a href="<?php hsc(SystemParameters::$V_C_TOP); ?>">トップページ</a></li>

                <div onclick="drop_menu();">
                    <li class="sb-close_menu"><a>勤怠</a></li>
                </div><!-- /.drop_menu() -->
                <div id="drop_menu">
                    <?php 
                        foreach( $_SESSION["A_TIME_MENU"] as $key => $val )
                        {
                            if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                            {
                    ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                    <?php
                            }
                        }
                    ?>
                </div><!-- /.drop_menu -->
                
                <div onclick="drop_menu1();">
                    <li class="sb-close_menu"><a>シフト作成機能</a></li>
                </div><!-- /.drop_menu1() -->
                <div id="drop_menu1">
                    <?php 
                        foreach( $_SESSION["A_SHIFT_MENU"] as $key => $val )
                        {
                            if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                            {
                    ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                    <?php 
                            }
                        }
                    ?>
                </div><!-- /.drop_menu1 -->
                
                <div onclick="drop_menu2();">
                    <li class="sb-close_menu"><a>メンテナンス</a></li>
                </div><!-- /.drop_menu2() -->
                <div id="drop_menu2">
                    <?php 
                        foreach( $_SESSION["C_MANAGEMENT_MENU"] as $key => $val )
                        {
                            if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                            {
                    ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                    <?php 
                            }
                        }
                    ?>
                </div><!-- /.drop_menu2 -->
                
            </ul>
        </nav>
    </div><!-- /.sb-left -->
    <?php include("../FwCommon/FwRightMenuList.php"); ?>
