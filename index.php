<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.03.16
 * Time: 17:40
 */

function __autoload($class) {
    require_once 'classes/'.$class.'.php';
}
try {
    new App();
} catch(Exception $error) {
    echo 'Sorry your get error:'.$error->getMessage();
}
