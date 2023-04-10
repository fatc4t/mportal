<?php
    /**
     * @file      上位組織名検索用ドロップメニュー(共通)
     * @author    USE R.dendo
     * @date      2016/06/28
     * @version   1.00
     * @note      上位組織名検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchUpOrganizationName-ajax">
                                        <select id="upOrganizationName" name="upOrganizationName" style="width: 200px">
                                        <?php foreach($upOrganizationList as $upOrganizationName) { ?>
                                         <?php $selected = ""; ?>
                                         <?php if($upOrganizationName['organization_id'] == $searchArray['upOrganizationName']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                            <?php $selected = ""; ?>
                                            <option value="<?php hsc($upOrganizationName['organization_id']);?>" <?php hsc($selected); ?>><?php hsc($upOrganizationName['abbreviated_name']);?></option>
                                        <?php } ?>
                                    </select>
                                    </div><!-- /.jquery-replace-SearchUpOrganizationName-ajax -->
