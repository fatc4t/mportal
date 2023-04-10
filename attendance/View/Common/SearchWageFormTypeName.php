<?php
    /**
     * @file      支給単位検索用ドロップメニュー(共通)
     * @author    USE S.Kasai
     * @date      2016/06/16
     * @version   1.00
     * @note      支給単位検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchWageFormTypeName-ajax">
                                        <select name="wageFormTypeName" id="wageFormTypeName" style="width: 100px">
                                            <?php foreach($wageTypeNameList as $wageFormType) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($wageFormType['payment_unit_id'] == $searchArray['wageFormTypeID']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($wageFormType['payment_unit_id']); ?>" <?php hsc($selected); ?>><?php hsc($wageFormType['payment_unit_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchWageFormTypeName-ajax -->
