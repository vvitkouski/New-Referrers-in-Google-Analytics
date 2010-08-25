<?php
    header('Content-type: text/css');
    if (isset($_GET['request'])) {
        if (file_exists($_GET['request'])) {
            include($_GET['request']);
        }
    }
?>