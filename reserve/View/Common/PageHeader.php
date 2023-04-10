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
                <?php
                    if( $_SESSION["SCHEMA"] != "honbu" ) {
                ?>
                <a href="../home/index.php?param=Home/show&home=1"><img src="../img/top-menu-logo.png" alt="HOME" height="40"></a>
                <?php
                   }else{
                ?>
                <a href="../home/index.php?param=Home/show2&home=1"><img src="../img/top-menu-logom.png" alt="HOME" height="40"></a>
                <?php } ?>
            </div><!-- /.logo -->

            <!-- Menu -->
            <ul class="nav navbar-nav navbar-right">
<!--                <li><a href="#">ﾌﾞｯｸﾏｰｸ登録</a></li> -->
                <?php
                    if( $_SESSION["SCHEMA"] != "honbu" ) {
                ?>
                <li><a href="../home/index.php?param=Home/show&home=1">ホーム</a></li>
                <li><a href="#"><?php hsc( $_SESSION["SECURITY_NAME"] ); ?></a></li>
                <li><a href="#"><?php hsc( $_SESSION["USER_NAME"] ); ?></a></li>
                <li><a href="<?php hsc( SystemParameters::$LOGOUT_PATH ); ?>">ログアウト</a></li>
                <li><a id="top-arrow" href="#top">＾</a></li>
                <?php
                   }else{
                ?>
                <li><a href="../home/index.php?param=Home/show2&home=1">ホーム</a></li>
                <li><a href="#"><?php hsc( $_SESSION["SECURITY_NAME"] ); ?></a></li>
                <li><a href="<?php hsc( SystemParameters::$LOGOUT_PATH ); ?>">ログアウト</a></li>
                <li><a id="top-arrow" href="#top">＾</a></li>
                <?php } ?>
            </ul><!-- /.Menu -->
        </div>
    </nav>

    <!-- Slidebars -->
    <!-- sb-left -->
    <div class="sb-slidebar sb-left">
        <nav>
            <ul class="sb-menu">
                <li><img src="../img/right-menu-logo.png" alt="Slidebars" width="118" height="40"></li>
                <?php
                    if( $_SESSION["SCHEMA"] != "honbu" ) {
                ?>
                <li class="sb-close"><a href="/home/index.php?param=Home/show&home=1">トップページ</a></li>
                <?php
                   }else{
                ?>
                <li class="sb-close"><a href="/home/index.php?param=Home/show2&home=1">トップページ</a></li>
                <?php } ?>

    <!--Mショップメニュー START----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                <?php
                    if( $_SESSION["SCHEMA"] == "honbu" )
                    {
                ?>

                <?php
                //薬局がログインした場合のメニュー表示
                if($_SESSION["SECURITY_ID"]=="9") {
                ?>
                        <div onclick="drop_menu();">
                            <li class="sb-close_menu"><a>薬剤発注</a></li>
                        </div><!-- /.drop_menu() -->
                                <div id="drop_menu">
                                    <li class="sb-close"><a href="/order/index.php?param=Order/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;発注画面</a></li>
                                    <li class="sb-close"><a href="/order/index.php?param=History/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;発注履歴画面</a></li>
                                </div>
                <?php
                //薬剤メーカーログイン
                } else if($_SESSION["SECURITY_ID"]=="8") {
                ?>
                        <div onclick="drop_menu1();">
                            <li class="sb-close_menu"><a>受注管理</a></li>
                        </div><!-- /.drop_menu() -->
                                <div id="drop_menu1">
                                    <li class="sb-close"><a href="/order/index.php?param=MakerAdmin/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;受注一覧</a></li>
                                    <li class="sb-close"><a href="/order/index.php?param=MakerSale/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;受注処理</a></li>
                                                        <!--
                                    <li class="sb-close"><a href="/order/index.php?param=MakerShipment/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;出荷処理</a></li>
                                                        -->
                </div>
                <?php
                } else {
                //管理者ログイン
                ?>
                        <div onclick="drop_menu2();">
                            <li class="sb-close_menu"><a>薬剤発注管理</a></li>
                        </div><!-- /.drop_menu() -->
                        <div id="drop_menu2">
                            <li class="sb-close"><a href="/order/index.php?param=Pharmacy/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;薬局マスタ画面</a></li>
                            <li class="sb-close"><a href="/order/index.php?param=Maker/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;発注先マスタ画面</a></li>
                            <li class="sb-close"><a href="/order/index.php?param=Recipient/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;発注先設定画面</a></li>
                        </div>
                
                    <div onclick="drop_menu3();">
                        <li class="sb-close_menu"><a>管理者メンテナンス</a></li>
                    </div><!-- /.drop_menu() -->
                    <div id="drop_menu3">
                        <?php
                            foreach( $_SESSION["M_PORTAL_SYS_MENU"] as $key => $val )
                            {
                                if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                                {
                        ?>
                                    <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                        <?php
                                }
                            }
                        ?>
                        <?php 
                            foreach( $_SESSION["M_MANAGEMENT_MENU"] as $key => $val )
                            {
                                if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                                {
                        ?>
                                    <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                        <?php
                                }
                            }
                        ?>
                    </div><!-- /.drop_menu -->
                <?php
                }
                ?>
    <!--Mショップメニュー END----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                <?php
                }else if( $_SESSION["SCHEMA"] == "akinai" || $_SESSION["SCHEMA"] == "twrestaurant" || $_SESSION["SCHEMA"] == "acrossring" || $_SESSION["SCHEMA"] == "mandr"  || $_SESSION["SCHEMA"] == "questionnaire" || $_SESSION["SCHEMA"] == "millionet" ){
                ?>
    <!--旧 M-PORTAL メニュー START----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                
                <?php
                    if( $_SESSION["SCHEMA"] == "acrossring" || $_SESSION["SCHEMA"] == "mandr" || $_SESSION["SCHEMA"] == "millionet")
                    {
                ?>
                <div onclick="drop_menu();">
                    <li class="sb-close_menu"><a>売上管理</a></li>
                </div>
                <div id="drop_menu">
                    <div onclick="drop_menu1);">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;売上報告書</a></li>
                    </div>
                    <div id="drop_menu1">
                                                        <li class="sb-close"><a href="/profit/index.php?param=SalesReportListDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日次報告書 - 売上報告書</a></li>
                                                        <li class="sb-close"><a href="/profit/index.php?param=OrderReportListDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日次報告書 - 仕入れ</a></li>
                                                        <li class="sb-close"><a href="/profit/index.php?param=CostReportListMonth/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;月次報告書 - コスト</a></li>
                                                        <li class="sb-close"><a href="/profit/index.php?param=TergetReportListDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;目標       - 日次</a></li>
                                                        <li class="sb-close"><a href="/profit/index.php?param=TergetReportListMonth/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;目標       - 月次</a></li>
                    </div>
                        <li class="sb-close_menu"><a href="/profit/index.php?param=LedgerSheetDay/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;売上帳票</a></li>
                </div>
                <?php
                    }
                ?>

                <?php
                    if( $_SESSION["SCHEMA"] != "akinai")
                    {
                ?>
                <div onclick="drop_menu2();">
                    <li class="sb-close_menu"><a>勤怠管理</a></li>
                </div>
                <div id="drop_menu2">
                    <div onclick="drop_menu3();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;勤怠機能</a></li>
                    </div>
                    <div id="drop_menu3">
                                                    <li class="sb-close"><a href="/attendance/index.php?param=AttendanceRecord/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;勤怠一覧画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=AttendanceCorrection/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;勤怠修正画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=AttendanceApproval/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;勤怠承認画面</a></li>

                    </div>
                    <div onclick="drop_menu4();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;シフト作成機能</a></li>
                    </div>
                    <div id="drop_menu4">

                                                    <li class="sb-close"><a href="/attendance/index.php?param=ShiftList/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;シフト一覧画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=ShiftDlUp/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;シフトインポート画面</a></li>

                    </div>
                    <div onclick="drop_menu5();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;勤怠メンテナンス</a></li>
                    </div>
                    <div id="drop_menu5">

                                                    <li class="sb-close"><a href="/attendance/index.php?param=Section/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;セクションマスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=HolidayName/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;休日名称マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=Holiday/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;休日マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=Allowance/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;手当マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=LaborRegulations/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;就業規則マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=OrganizationCalendar/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;組織カレンダーマスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=Alert/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;アラートマスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=DisplayItem/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示項目設定マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=PayrollSystem/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;給与システム連携マスタ</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=PayrollDataOut/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;給与データ出力画面</a></li>

                    </div>
                </div>
                <?php
                    }
                ?>

                <?php
                    if( $_SESSION["SCHEMA"] == "akinai" or $_SESSION["SCHEMA"] == "questionnaire" or $_SESSION["SCHEMA"] == "millionet") 
                    {
                ?>
                <div onclick="drop_menu6();">
                    <li class="sb-close_menu"><a>グループウェア</a></li>
                </div>
	        <div id="drop_menu6">
                            <!-- テロップ -->
                            <?php
                                foreach( $_SESSION["TELOP_TOP_MENU"] as $key => $val )
                                {
                                    if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                                    {
                            ?>
                                        <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                            <?php
                                    }
                                }
                            ?>
                            <!-- トップメッセージ -->
                            <?php
                                foreach( $_SESSION["TM_TOP_MENU"] as $key => $val )
                                {
                                    if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                                    {
                            ?>
                                        <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                            <?php
                                    }
                                }
                            ?>
                            <!-- 日報 -->
                            <?php
                                foreach( $_SESSION["DRM_TOP_MENU"] as $key => $val )
                                {
                                    if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                                    {
                            ?>
                                        <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                            <?php
                                    }
                                }
                            ?>
                            <!-- 通達連絡 -->
                            <?php
                                foreach( $_SESSION["NC_TOP_MENU"] as $key => $val )
                                {
                                    if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                                    {
                            ?>
                                        <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                            <?php
                                    }
                                }
                            ?>

	                <div onclick="drop_menu7();">
	                    <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;ファイルボックス</a></li>
	                </div>
	                <div id="drop_menu7">
                <?php
                    if( $_SESSION["SECURITY_ID"] == 1)
                    {
                ?>
	                    <li class="sb-close"><a href="/filebox/index.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;編集</a></li>
                <?php
                    }
                ?>
	                    <li class="sb-close"><a href="/filebox/library.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;閲覧</a></li>
	                </div>

	                <div onclick="drop_menu8();">
	                    <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー</a></li>
	                </div>
	                <div id="drop_menu8">
	                    <li class="sb-close"><a href="/workflow/apply.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー申請</a></li>
	                    <li class="sb-close"><a href="/workflow/apply_agree.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー承認</a></li>
	                    <li class="sb-close"><a href="/workflow/apply_reference.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー参照</a></li>
	                    <li class="sb-close"><a href="/workflow/apply_view.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー一覧</a></li>
	                    <li class="sb-close"><a href="/workflow/apply_check.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー確認</a></li>
                <?php
                    if( $_SESSION["WRITE_MENU_LIST"]['/workflow/apply_view.php'] == 1)
                    {
                ?>
	                    <li class="sb-close"><a href="/workflow/inport_workflow.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ルートマスタ</a></li>
	                    <li class="sb-close"><a href="/workflow/inport_workflow_doc_format.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;申請書マスタ</a></li>
                <?php
                    }
                ?>
	                </div>
                </div>
                <?php
                    }
                ?>

                <div onclick="drop_menu9();">
                    <li class="sb-close_menu"><a>管理者メンテナンス</a></li>
                </div><!-- /.drop_menu() -->
                <div id="drop_menu9">
                    <?php
                        foreach( $_SESSION["M_PORTAL_SYS_MENU"] as $key => $val )
                        {
                            if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                            {
                    ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                    <?php
                            }
                        }
                    ?>
                    <?php 
                        foreach( $_SESSION["M_MANAGEMENT_MENU"] as $key => $val )
                        {
                            if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                            {
                    ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                    <?php
                            }
                        }
                    ?>
                </div><!-- /.drop_menu -->

    <!--旧 M-PORTAL メニュー END----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
              <?php
              }else{
              ?>
    <!--新 M-PORTAL メニュー START----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                 
                <?php
                    if( $_SESSION["SCHEMA"] != "juso" && $_SESSION["SCHEMA"] != "juso_test")
                    {
                ?>
                <div onclick="drop_menu();">
                    <li class="sb-close_menu"><a>発注管理</a></li>
                </div>
                <div id="drop_menu">
                    <div onclick="drop_menu1();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;発注関連帳票</a></li>
                    </div>
                    <div id="drop_menu1">
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetOrder/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;注文書</a></li>
                    </div>
                </div>
                    <?php
                        }
                    ?>

                <div onclick="drop_menu2();">
                    <li class="sb-close_menu"><a>仕入管理</a></li>
                </div>
                <div id="drop_menu2">
                    <div onclick="drop_menu3();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;仕入関連帳票</a></li>
                    </div>
                    <div id="drop_menu3">
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetPurchaseDay/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;仕入単品分析</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetSuppliersPayment/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;仕入先・店舗別支払一覧</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetPurchaseLedger/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;仕入先・店舗別仕入元帳</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetSupplierTotal/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;仕入実績</a></li>
                        <!--<li class="sb-close"><a href="/profit/index.php?param=LedgerSheetSalesByProvider/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;仕入先別実績</a></li>-->
                    </div>
                </div>
    
                <div onclick="drop_menu4();">
                    <li class="sb-close_menu"><a>在庫管理</a></li>
                </div>
                <div id="drop_menu4">
                    <div onclick="drop_menu5();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;在庫関連帳票</a></li>
                    </div>
                    <div id="drop_menu5">
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetStockpile/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;在庫調査表</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetStocklist/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;在庫一覧表(明細一覧表)</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetStockTotal/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;在庫一覧表(合計表)</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetStockTransition/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;在庫推移表</a></li>
                    </div>
                </div>
    
                <div onclick="drop_menu6();">
                    <li class="sb-close_menu"><a>売上管理</a></li>
                </div>
                <div id="drop_menu6">
                    <div onclick="drop_menu7();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;売上帳票</a></li>
                    </div>
                    <div id="drop_menu7">
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetShopMonth/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;店舗別売上月報</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetShopDay/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;店舗日別売上動向表</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetDepartmentalGrossProfit/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;部門別粗利速報</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetBySalesStaffSelectedItem/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;販売員別選定品(税込)</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetBySalesStaffSpreadSheet/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;担当者別売上集計表</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetHourlySales/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;時間帯別実績</a></li>
                        <!--<li class="sb-close"><a href="/profit/index.php?param=LedgerSheetTotalSalesRecord/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;売上実績</a></li>-->
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetYearlyProgress/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;推移実績</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetCreditSalesRecord/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;信販実績</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetUnpaidTransactions/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;掛売明細一覧</a></li>
                        <!--<li class="sb-close"><a href="/profit/index.php?param=LedgerSheetDepositsAndWithdrawals/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;33.入金一覧</a></li>-->
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetSalesResults/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;売上実績 </a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetDailySalesResults/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;売上日別実績</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetProductResults/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品別実績</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetDepartmentResults/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;部門別実績</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetDailySalesResultsByStore/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;店別売上日報</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetSalesSummary/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;単品売上集計表</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetSalesByProvider/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;仕入先別実績</a></li>
                    </div>
                </div>

                <div onclick="drop_menu8();">
                    <li class="sb-close_menu"><a>商品管理</a></li>
                </div>
                <div id="drop_menu8">
                    <div onclick="drop_menu9();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;商品関連帳票</a></li>
                    </div>
                    <div id="drop_menu9">
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetAbnormality/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;売上原価異常明細</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetSaled/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;当日販売商品一覧</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetUnpopular/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;死に筋商品リスト</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetProduct/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品検索一覧</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetRank/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品実績順位表</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetTimedSale/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;特売一覧</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetTimedSaleRecord/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;特売実績</a></li>
                        <!--<li class="sb-close"><a href="/profit/index.php?param=LedgerSheetProductSales/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品実績</a></li>-->
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetTemporaryCost/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;期間原価一覧</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetProductUpdateRecord/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品更新記録</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetProductDelayedUpdate/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;予約商品登録一覧</a></li>
                    </div>
                    <div onclick="drop_menu10();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;商品メンテナンス</a></li>
                    </div>
                    <div id="drop_menu10">
<!--                                                    <li class="sb-close"><a href="/profit/index.php?param=Mst0201/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;POS商品マスタ</a></li>
-->
                                                    <li class="sb-close"><a href="/profit/index.php?param=Mst0211/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品マスタ</a></li>
                <?php
                    if( $_SESSION["SCHEMA"] == "honbu_test" )
                    {
                ?>
<!--                                                    <li class="sb-close"><a href="/product/index.php?param=Mst1201/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品部門マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst1101/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;仕入先マスタ</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=Mst5101/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品セット構成マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst1205/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品部門分類マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst5501/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品自社分類マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst5401/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品JICFS分類マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst1001/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品メーカーマスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst0901/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品区分マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst0801/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品分類マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst1301/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品特売マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst5102/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品入数構成マスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst5301/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;バンドルマスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst5201/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ミックスマッチマスタ</a></li>
                                                    <li class="sb-close"><a href="/product/index.php?param=Mst1401/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品期間原価マスタ</a></li>-->
                                                    <li class="sb-close"><a href="http://153.127.193.168:10080/admin/mst-categories-product">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品カテゴリ</a></li>
                                                    <li class="sb-close"><a href="http://153.127.193.168:10080/admin/mst-product">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品一覧</a></li>
                                                    <li class="sb-close"><a href="http://153.127.193.168:10080/admin/master-option">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品オプション一覧</a></li>
                                                    <li class="sb-close"><a href="http://153.127.193.168:10080/admin/master-coupon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;クーポン一覧</a></li>
                <?php
                    }
                ?>
                    </div>
                </div>
    
                <div onclick="drop_menu11();">
                    <li class="sb-close_menu"><a>顧客管理</a></li>
                </div>
                <div id="drop_menu11">
                    <div onclick="drop_menu12();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;顧客帳票</a></li>
                    </div>
                    <div id="drop_menu12">
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetCustomer/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客台帳</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetDetails/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客別売上明細</a></li>
                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetClient/initial&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客検索一覧</a></li>
                    </div>
                <?php
                    if( $_SESSION["SCHEMA"] == "honbu_test" ) {
                ?>
                    <div onclick="drop_menu13();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;顧客メンテナンス</a></li>
                    </div>
                    <div id="drop_menu13">
                        <!--<li class="sb-close_menu"><a href="/customer/index.php?param=Customer/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客マスタ</a></li>-->
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-customer/index">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客一覧</a></li>
                        <li class="sb-close"><a href="/customer/index.php?param=ProdCustomer/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客別商品売価マスタ</a></li>
                        <li class="sb-close"><a href="/customer/index.php?param=CustClass/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客分類マスタ</a></li>
                        <li class="sb-close"><a href="/customer/index.php?param=Area/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;地区マスタ</a></li>
                        <li class="sb-close"><a href="/customer/index.php?param=Rank/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客ランクマスタ</a></li>
                        <li class="sb-close"><a href="/customer/index.php?param=CustRemarks/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;顧客備考マスタ</a></li>
                        <li class="sb-close"><a href="/customer/index.php?param=Relationship/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;続柄マスタ</a></li>
                        <li class="sb-close"><a href="http://153.127.193.168:10080/admin/master-customer/index-history-point">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ポイント履歴</a></li>
                        <li class="sb-close"><a href="http://153.127.193.168:10080/admin/master-customer/index-booking-history">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;予約履歴</a></li>
                        <li class="sb-close"><a href="http://153.127.193.168:10080/admin/master-customer/index-visit-history">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;来店履歴</a></li>
                        <li class="sb-close"><a href="http://153.127.193.168:10080/admin/master-customer/index-history-point">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ポイント履歴</a></li>
                        <li class="sb-close"><a href="http://153.127.193.168:10080/admin/master-customer/index-history-point">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;フリー項目設定</a></li>
                    </div>

                <?php
                    }
                ?>

                </div>
                <?php
                    if( $_SESSION["SCHEMA"] == "honbu_test" ) {
                ?>
                <div onclick="drop_menu14();">
                    <li class="sb-close_menu"><a>システム管理</a></li>
                </div>
                <div id="drop_menu14">
                        <li class="sb-close_menu"><a href="/system/index.php?param=System/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;システムマスタ</a></li>
                        <li class="sb-close_menu"><a href="/system/index.php?param=ScaledScore/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;得点倍率マスタ</a></li>
                        <li class="sb-close_menu"><a href="/system/index.php?param=Credit/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;クレジットマスタ</a></li>
                        <li class="sb-close_menu"><a href="/system/index.php?param=Gift/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;商品券マスタ</a></li>
                        <li class="sb-close_menu"><a href="/system/index.php?param=Staff/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;担当者マスタ</a></li>
                        <li class="sb-close_menu"><a href="/system/index.php?param=DepWithdraw/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;入出金マスタ</a></li>
                        <li class="sb-close_menu"><a href="/system/index.php?param=Memo/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;レシートメモマスタ</a></li>
                        <li class="sb-close_menu"><a href="/system/index.php?param=TimeZone/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;時間帯マスタ</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/store">&nbsp;&nbsp;&nbsp;&nbsp;店舗一覧</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-staff">&nbsp;&nbsp;&nbsp;&nbsp;スタッフ一覧</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-type-seat">&nbsp;&nbsp;&nbsp;&nbsp;席タイプ一覧</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-seat">&nbsp;&nbsp;&nbsp;&nbsp;席一覧</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-rank">&nbsp;&nbsp;&nbsp;&nbsp;ランク設定</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/point-setting">&nbsp;&nbsp;&nbsp;&nbsp;ポイント設定</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/company">&nbsp;&nbsp;&nbsp;&nbsp;企業設定</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/point-setting">&nbsp;&nbsp;&nbsp;&nbsp;QRコード生成</a></li>
                </div>                

                <?php
                    }
                ?>
    
                <div onclick="drop_menu15();">
                    <li class="sb-close_menu"><a>管理者メンテナンス</a></li>
                </div>
                <div id="drop_menu15">
                    <?php
                        foreach( $_SESSION["M_PORTAL_SYS_MENU"] as $key => $val )
                        {
                            if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                            {
                    ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                    <?php
                            }
                        }
                    ?>
                    <?php 
                        foreach( $_SESSION["M_MANAGEMENT_MENU"] as $key => $val )
                        {
                            if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                            {
                    ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                    <?php
                            }
                        }
                    ?>
                </div>
               
                <div onclick="drop_menu16();">
                    <li class="sb-close_menu"><a>予約管理</a></li>
                </div>
                <div id="drop_menu16">
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/booking/schedule">&nbsp;&nbsp;&nbsp;&nbsp;予約スケジュール</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/booking/index?date=2019%2F06%2F05">&nbsp;&nbsp;&nbsp;&nbsp;予約一覧</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/booking/receptionsetting">&nbsp;&nbsp;&nbsp;&nbsp;店舗予約受付設定</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/booking/staff-booking-setting">&nbsp;&nbsp;&nbsp;&nbsp;スタッフ予約受付設定</a></li>
                </div>
                <div onclick="drop_menu17();">
                    <li class="sb-close_menu"><a>お知らせ管理</a></li>
                </div>
                <div id="drop_menu17">
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-plan">&nbsp;&nbsp;&nbsp;&nbsp;指定配信一覧</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-auto-send">&nbsp;&nbsp;&nbsp;&nbsp;自動配信一覧</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-history">&nbsp;&nbsp;&nbsp;&nbsp;配信履歴</a></li>
                </div>
                <div onclick="drop_menu18();">
                    <li class="sb-close_menu"><a>アンケート管理</a></li>
                </div>
                <div id="drop_menu18">
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-plan">&nbsp;&nbsp;&nbsp;&nbsp;アンケート作成</a></li>
                </div>
                <div onclick="drop_menu19();">
                    <li class="sb-close_menu"><a>メール管理</a></li>
                </div>
                <div id="drop_menu19">
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-plan">&nbsp;&nbsp;&nbsp;&nbsp;ステップアップメール</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-auto-send">&nbsp;&nbsp;&nbsp;&nbsp;サイクルメール</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-history">&nbsp;&nbsp;&nbsp;&nbsp;メール設定</a></li>
                </div>
                <div onclick="drop_menu20();">
                    <li class="sb-close_menu"><a>セルフメニュー管理</a></li>
                </div>
                <div id="drop_menu20">
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-plan">&nbsp;&nbsp;&nbsp;&nbsp;メニュー一覧</a></li>
                </div>
                <div onclick="drop_menu21();">
                    <li class="sb-close_menu"><a>アプリ管理</a></li>
                </div>
                <div id="drop_menu21">
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-plan">&nbsp;&nbsp;&nbsp;&nbsp;画面配置設定</a></li>
                        <li class="sb-close_menu"><a href="http://153.127.193.168:10080/admin/master-notice-member/list-plan">&nbsp;&nbsp;&nbsp;&nbsp;おすすめ設定</a></li>
                </div>
              <?php
              }
              ?>
    <!--新 M-PORTAL メニュー END----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
            </ul>
        </nav>
    </div>
    <!-- /.sb-left -->


