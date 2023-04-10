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
                                    <div id="jquery-replace-ProfitMenu01Name-ajax" style='display: inline-block; _display: inline;'>
                                        <select name="prodCd01" id="prodCd01" style="width: 100px">
                                            <?php foreach($profitMenuList as $profitMenu01) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($profitMenu01['prod_cd'] == $searchArray['prodCd01']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($profitMenu01['prod_cd']); ?>" <?php hsc($selected); ?>><?php hsc($profitMenu01['prod_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-ProfitMenu01Name-ajax -->
