<?php
/**
* Hash.php
* 
* Warden user authentication
* @author Barry O'Mahony <the.ewok@gmail.com>
* @version 1.0
* @package Warden
*/

/**
* Class responsible for hasing passwords
* 
* @author Barry O'Mahony <the.ewok@gmail.com>
* @version 1.0
* @package Warden
* @subpackage classes
*/

namespace Warden;

class Hash implements HashInterface
{
    /**
     * Hash the string with the salt
     * @param string $string
     * @param string $salt
     */
    public function hashString($string,$salt) {
        return crypt($string,'$2a$07$'.$salt.'$');
    }
}
