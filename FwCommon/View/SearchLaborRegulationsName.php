<?php
    /**
     * @file      就業規則検索用ドロップメニュー(共通)
     * @author    USE S.kasai
     * @date      2016/06/13
     * @version   1.00
     * @note      就業規則検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchLaborRegulationsName-ajax">
                                        <select name="laborRegulationsName" id="laborRegulationsName" style="width: 100px">
                                            <?php foreach($laborRegNameList as $laborRegulations) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($laborRegulations['labor_regulations_id'] == $searchArray['laborRegulationsID']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($laborRegulations['labor_regulations_id']); ?>" <?php hsc($selected); ?>><?php hsc($laborRegulations['labor_regulations_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchLaborRegulationsName-ajax -->
