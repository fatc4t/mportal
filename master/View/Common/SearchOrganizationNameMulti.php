<?php
    /**
     * @file      組織検索用ドロップメニュー(共通)
     * @author    USE Y.Sakata
     * @date      2016/06/13
     * @version   1.00
     * @note      組織検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchOrganizationName-ajax">
                                            <?php $selectOrgSize = empty( $selectOrgSize ) ? 100 : $selectOrgSize  ?>
                                            <select name="organizationNameM" id="organizationNameM" style="width: 200px" multiple>
                                                <?php foreach($abbreviatedNameList as $abbreviated) { ?>
                                                    <?php $selected = ""; ?>
                                                    <?php if($abbreviated['organization_id'] == $searchArray['organizationID']) { ?>
                                                        <?php $selected = "selected"; ?>
                                                    <?php } ?>
                                                    <option value="<?php hsc($abbreviated['organization_id']); ?>" <?php hsc($selected); ?>><?php hsc($abbreviated['abbreviated_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                    </div><!-- /.jquery-replace-SearchOrganizationName-ajax -->
