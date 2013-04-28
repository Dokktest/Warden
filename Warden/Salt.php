<?php
/**
* Salt.php
* 
* Warden user authentication
* @author Barry O'Mahony <the.ewok@gmail.com>
* @version 1.0
* @package Warden
*/

/**
* Class responsible for generating salt
* 
* @author Barry O'Mahony <the.ewok@gmail.com>
* @version 1.0
* @package Warden
* @subpackage classes
*/

namespace Warden;

class Salt implements SaltInterface
{
    public function getSalt() {
        return substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22);
    }
}
