<?php
    /**
     * @file      顧客備考検索用ドロップメニュー(共通)
     * @author    K.Sakamoto
     * @date      2017/8/31
     * @version   1.00
     * @note      顧客備考検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-CustomerRenarks09Name-ajax" style='display: inline-block; _display: inline;'>
                                        <select name="custBCd09" id="custBCd09" style="width: 250px">
                                            <?php foreach($customerRemarks09List as $customerRemarks09) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($customerRemarks09['cust_b_cd'] == $searchArray['custBCd09']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($customerRemarks09['cust_b_cd']); ?>" <?php hsc($selected); ?>><?php hsc($customerRemarks09['cust_b_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-CustomerRenarks09Name-ajax -->
