<?php
/**
 * Profile Tab for Clipboard Module
 *
 * @package Redegeld
 */

// Haal de ingelogde gebruiker op
$user = ossn_loggedin_user();

// URL naar de Clipboard-pagina van de gebruiker
$clipboard_url = ossn_site_url("clipboard/{$user->username}");

?>
<div class="ossn-profile-module clipboard-overview">
    <!-- Titel van de sectie -->
    <div class="module-title">
        <h3><?php echo ossn_print('clipboard:title'); ?></h3>
    </div>

    <!-- Beschrijving van de sectie -->
    <div class="module-contents">
        <p>
            <?php echo ossn_print('clipboard:description'); ?>
        </p>

        <!-- Actieknop: Bekijk persoonlijke data -->
        <div class="clipboard-download">
            <a href="<?php echo $clipboard_url; ?>" class="btn btn-primary">
                <?php echo ossn_print('clipboard:view_data'); ?>
            </a>
        </div>

        <!-- Extra knop: Download data -->
        <div class="clipboard-download" style="margin-top: 10px;">
            <a href="<?php echo $clipboard_url . '/download'; ?>" class="btn btn-secondary">
                <?php echo ossn_print('clipboard:download_data'); ?>
            </a>
        </div>
    </div>
</div>
