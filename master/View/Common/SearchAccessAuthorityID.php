<?php
    /**
     * @file      アクセス権限ID検索用ドロップメニュー(共通)
     * @author    USE Y.Sakata
     * @date      2016/07/02
     * @version   1.00
     * @note      アクセス権限ID検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchAccessAuthorityID-ajax">
                                        <select name="accessAuthorityID" id="accessAuthorityID">
                                            <?php foreach($accessIdList as $accessAuthorityId) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($accessAuthorityId['access_authority_id'] == $searchArray['accessAuthorityId']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                <option value="<?php hsc($accessAuthorityId['access_authority_id']); ?>" <?php hsc($selected); ?>><?php hsc($accessAuthorityId['access_authority_id']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchAccessAuthorityID-ajax -->
