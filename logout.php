<?php
/* Template Name: Logout */
session_start();
session_destroy();
wp_redirect(home_url('/page-login'));
exit;