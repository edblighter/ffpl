<?php

function encrypt($str, $key)
{
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = $block - (strlen($str) % $block);
    $str .= str_repeat(chr($pad), $pad);

    return mcrypt_encrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB);
}

function decrypt($str, $key)
{   
    $str = mcrypt_decrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB);

    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    return substr($str, 0, strlen($str) - $pad);
}

?>