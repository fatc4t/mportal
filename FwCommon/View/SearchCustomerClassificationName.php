<?php
    /**
     * @file      顧客分類検索用ドロップメニュー(共通)
     * @author    K.Sakamoto
     * @date      2017/07/31
     * @version   1.00
     * @note      顧客分類検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchCustomerTypeName-ajax">
                                        <select name="customerClassificationName" id="customerClassificationName" style="width: 100px">
                                            <?php foreach($custTypeNmList as $custTypeNm) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($custTypeNm['cust_type_nm'] == $searchArray['custTypeNm']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($custTypeNm['cust_type_nm']); ?>" <?php hsc($selected); ?>><?php hsc($custTypeNm['cust_type_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchCustomerTypeName-ajax -->
