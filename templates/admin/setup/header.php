<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php esc_html_e( 'Micropayment &rsaquo; Setup Wizard', BILLING_FOX_TRANSLATE ); ?></title>
    <?php do_action( 'admin_print_styles' ); ?>
    <?php do_action( 'admin_head' ); ?>
</head>
<body class="setup">
<h1 class="logo">
    <a href="https://micropayment.io/">
        <img src="<?php echo plugins_url('resources/img/logo.png', BILLING_FOX_PLUGIN_FILE); ?>" alt="Micropayment.io" />
    </a>
</h1>