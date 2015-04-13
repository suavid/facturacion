<?php

if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    exit('This file can not be accessed directly...');
}

/**
 * check if $email is a valid email 
 * @param string $email email address
 * @return boolean
 *
 */
function check_email($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

/**
 * check if $image is a valid image file
 * @param string $image file name
 * @return boolean 
 *
 */
function check_image($image) {
    $patron = "%\.(gif|jpe?g|png)$%i";
    return preg_match($patron, $image);
}

/**
 * encrypt text usgin RSA RC4 algorithm
 * @param string $plaintext some plain text
 * @return RSA encrypted text 
 *
 */
function cifrar_RSA_RC4($plaintext) {
    $publicKey = openssl_pkey_get_public('file://' . $_SESSION['APP_PATH'] . 'backend/public.key'); // obtiene la clave publica 
    $encrypted = '';
    $a_envelope = array(); // array para almacenar clave de descifrado
    $a_key = array($publicKey); // obtiene la clave
    if (openssl_seal($plaintext, $encrypted, $a_envelope, $a_key) === FALSE)
        die('Failed to encrypt data'); // trata de descifrar
    openssl_free_key($publicKey);
    // devuelve un array con el dato cifrado y una clave de descifrado en base64
    $data = array('data' => base64_encode($encrypted), 'envelope' => base64_encode($a_envelope[0]));
    return $data;
}

/**
 * decrypt text usgin RSA RC4 algorithm
 * @param string $encrypted encrypted text
 * @param array $envelope key data  
 * @return RSA decrypted text 
 *
 */
function descifrar_RSA_RC4($encrypted, $envelope) {
    if (!$privateKey = openssl_pkey_get_private('file://' . $_SESSION['APP_PATH'] . 'backend/private.key')) // descifra con la clave privada
        die('Private Key failed');
    $decrypted = '';
    if (openssl_open(base64_decode($encrypted), $decrypted, base64_decode($envelope), $privateKey) === FALSE)
        die('Failed to decrypt data'); // trata de descirar el texto
    openssl_free_key($privateKey);
    return $decrypted;
}

/**
 * encrypt text usgin RIJNDAEL 256 bits algorithm
 * @param string $plaintext some plain text
 * @return RIJNDAEL encrypted text 
 *
 */
function cifrar_RIJNDAEL_256($plaintext) {
    $llave = "";
    if (extension_loaded('mcrypt') === FALSE) {
        exit("Mcrypt module can not be loaded"); // verifica si se ha cargado el modulo
    }
    $td = mcrypt_module_open('rijndael_256', '', 'ecb', ''); // establece rijndael_256 en modo ecb para cifrar 
    $semilla_aleatoria = strstr(PHP_OS, "WIN") ? MCRYPT_RAND : MCRYPT_DEV_RANDOM; // genera numeros aleatorios
    $longitud_llave = mcrypt_enc_get_key_size($td); // obtiene la longitud esperada de la llave
    $llave = substr(md5($llave), 0, $longitud_llave); // crea la llave usando un hash md5
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), $semilla_aleatoria); // genera un vector de inicializacion
    mcrypt_generic_init($td, $llave, $iv); // inicializa el modulo
    return base64_encode(mcrypt_generic($td, $plaintext)); // retorna el valor cifrado codificado en base64
}

/**
 * decrypt text usgin RIJNDAEL 256 bits algorithm
 * @param string $encrypted encrypted text
 * @return RIJNDAEL decrypted text 
 *
 */
function descifrar_RIJNDAEL_256($encrypted) {
    $llave = "";
    if (extension_loaded('mcrypt') === FALSE) {
        exit("Mcrypt module can not be loaded");
    }
    $td = mcrypt_module_open('rijndael_256', '', 'ecb', '');
    $semilla_aleatoria = strstr(PHP_OS, "WIN") ? MCRYPT_RAND : MCRYPT_DEV_RANDOM;
    $longitud_llave = mcrypt_enc_get_key_size($td);
    $llave = substr(md5($llave), 0, $longitud_llave);
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), $semilla_aleatoria);
    mcrypt_generic_init($td, $llave, $iv);
    return trim(mdecrypt_generic($td, base64_decode($encrypted))); // retorna la cadena descifrada
}

?>