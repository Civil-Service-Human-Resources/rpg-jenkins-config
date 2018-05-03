<div class="info-setting owf-hidden" id="delete-history-div">
    <div class="dialog-title"><strong><?php echo __( "Delete History", "oasisworkflow" ); ?></strong></div>
    <div>
        <div id="delete_history_msg" class="ow-info-message select-info">
            <?php echo __( "Workflow History for posts/pages that are currently active in a workflow will NOT be deleted." ) ?>
        </div>
        <div class="select-info owf-text-info left full-width">
            <label class="half-width">
                <?php echo __( "Delete Workflow History for posts/pages which were last updated : ", "oasisworkflow" ); ?>
            </label>
            <select id="delete-history-range-select">
                <option value="one-month-ago"><?php echo __( "1 Month ago", "oasisworkflow" ); ?></option>
                <option value="three-month-ago"><?php echo __( "3 Months ago", "oasisworkflow" ); ?></option>
                <option value="six-month-ago"><?php echo __( "6 Months ago", "oasisworkflow" ); ?></option>
                <option value="twelve-month-ago"><?php echo __( "12 Months ago", "oasisworkflow" ); ?></option>
                <option value="everything"><?php echo __( "Since the beginning", "oasisworkflow" ); ?></option>
            </select>
            <br class="clear">
        </div>
        <br class="clear">
        <div class="select-info left changed-data-set full-width">
            <input type="button" id="deleteHistoryConfirm" class="button-primary" value="<?php echo __( "Delete Workflow History", "oasisworkflow" ); ?>" />
            <span>&nbsp;</span>
            <a href="#" id="deleteHistoryCancel"><?php echo __( "Cancel", "oasisworkflow" ); ?></a>
            <br class="clear">
        </div>
        <br class="clear">
    </div>
</div>