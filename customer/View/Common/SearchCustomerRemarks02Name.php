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
                                    <div id="jquery-replace-CustomerRenarks02Name-ajax" style='display: inline-block; _display: inline;'>
                                        <select name="custBCd02" id="custBCd02" style="width: 250px">
                                            <?php foreach($customerRemarks02List as $customerRemarks02) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($customerRemarks02['cust_b_cd'] == $searchArray['custBCd02']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($customerRemarks02['cust_b_cd']); ?>" <?php hsc($selected); ?>><?php hsc($customerRemarks02['cust_b_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-CustomerRenarks02Name-ajax -->
