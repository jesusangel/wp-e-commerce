<?php
global $wpdb;
?>
<div class="wrap">
  <?php
  if($_GET['debug'] == 'true')
    {
    exit(ABSPATH);
    }
    else
      {
      ?>
      <h2><?php echo TXT_WPSC_HELPINSTALLATION;?></h2>
      <p>
        <?php echo TXT_WPSC_INSTRUCTIONS;?>
      </p>
      <?php
      }
  ?>
</div>