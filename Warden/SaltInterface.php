<?php
/**
* SaltInterface.php
* 
* Warden user authentication
* @author Barry O'Mahony <the.ewok@gmail.com>
* @version 1.0
* @package Warden
*/

/**
* Interface for Salt class
* 
* @author Barry O'Mahony <the.ewok@gmail.com>
* @version 1.0
* @package Warden
* @subpackage classes
*/
namespace Warden;

interface SaltInterface
{
    public function getSalt();
}
