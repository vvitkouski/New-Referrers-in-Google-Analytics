<?php
    header('Content-type: application/javascript');
    if (isset($_GET['request'])) {
        if (file_exists($_GET['request'])) {
            include($_GET['request']);
        }
    }
?>