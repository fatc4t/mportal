<?php
    /**
     * @file      賃金形態検索用ドロップメニュー(共通)
     * @author    USE M.Higashihara
     * @date      2016/07/07
     * @version   1.00
     * @note      賃金形態検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchWageForm-ajax">
                                        <select name="wageForm" id="wageForm" style="width: 100px">
                                            <?php foreach($wageFormList as $wageForm) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($wageForm['wage_form_id'] == $searchArray['wageForm']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php }?>
                                                <option value="<?php hsc($wageForm['wage_form_id']); ?>" <?php hsc($selected); ?>><?php hsc($wageForm['wage_form_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchWageForm-ajax -->

