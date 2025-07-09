<?php
script('nextcloud_chat', 'admin-settings');
style('nextcloud_chat', 'admin-settings');
?>

<div id="nextcloud_chat_admin_settings">
    <div class="section">
        <h2><?php p($l->t('Nextcloud Chat Configuration')); ?></h2>
        <p class="settings-hint">
            <?php p($l->t('Configure the Matrix server and Element services for Nextcloud Chat.')); ?>
        </p>

        <div id="admin-settings-content">
            <!-- Vue.js component will be mounted here -->
        </div>
    </div>
</div>
