<?php
    function getPostArg($name){
        return isset($_POST[$name]) ? $_POST[$name] : die(0);
    }

    function isSessionLoggedIn(){
        if (!isset($_SESSION['auth']) || !$_SESSION['auth'])
            return false;
        return true;
    }
?>