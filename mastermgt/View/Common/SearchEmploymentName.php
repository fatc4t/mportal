<?php
    /**
     * @file      雇用形態検索用ドロップメニュー(共通)
     * @author    USE K.Narita
     * @date      2016/06/14
     * @version   1.00
     * @note      雇用形態検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchEmploymentName-ajax">
                                        <select name="employmentName" id="employmentName" style="width: 100px">
                                            <?php foreach($employmentNameList as $employment) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($employment['employment_name'] == $searchArray['employmentName']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($employment['employment_name']); ?>" <?php hsc($selected); ?>><?php hsc($employment['employment_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchEmploymentName-ajax -->
