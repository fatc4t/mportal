<?php
    /**
     * @file      従業員検索用ドロップメニュー(共通)
     * @author    USE Y.Sakata
     * @date      2016/08/19
     * @version   1.00
     * @note      従業員検索用ドロップメニュー(共通)
     */
?>
        <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
        <!-- ajax差し替えエリア -->
        <div id="jquery-replace-SearchUserName-ajax">
            <select name="searchUser" id="searchUser" style="width: 100px">
                <?php
                    foreach($searchUserNameList as $userName)
                    {
                        $selected = "";
                        if($userName['user_id'] == $searchArray['userID'])
                        {
                            $selected = "selected";
                        }
                ?>
                        <option value="<?php hsc($userName['user_id']); ?>" <?php hsc($selected); ?>><?php hsc($userName['user_name']); ?></option>
                <?php } ?>
            </select>
        </div><!-- /.jquery-replace-SearchUserName-ajax -->
