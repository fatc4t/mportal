<?php
    /**
     * @file      顧客分類検索用ドロップメニュー(共通)
     * @author    K.Sakamoto
     * @date      2017/8/31
     * @version   1.00
     * @note      顧客分類検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-CustomerClassificationName-ajax" style='display: inline-block; _display: inline;'>
                                        <select name="custTypeCd" id="custTypeCd" style="width: 100px">
                                            <?php foreach($customerClassificationList as $customerClassification) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($customerClassification['cust_type_cd'] == $searchArray['custTypeCd']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($customerClassification['cust_type_cd']); ?>" <?php hsc($selected); ?>><?php hsc($customerClassification['cust_type_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-CustomerClassificationName-ajax -->
