<?php
    /**
     * @file      商品検索用ドロップメニュー(共通)
     * @author    K.Sakamoto
     * @date      2017/8/31
     * @version   1.00
     * @note      商品検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-ProfitMenu02Name-ajax" style='display: inline-block; _display: inline;'>
                                        <select name="prodCd02" id="prodCd02" style="width: 100px">
                                            <?php foreach($profitMenuList as $profitMenu02) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($profitMenu02['prod_cd'] == $searchArray['prodCd02']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($profitMenu02['prod_cd']); ?>" <?php hsc($selected); ?>><?php hsc($profitMenu02['prod_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-ProfitMenu02Name-ajax -->
