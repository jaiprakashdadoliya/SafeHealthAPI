<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use Config;
/**
 * SecurityLib Class
 *
 * @package                Safe Health
 * @subpackage             SecurityLib
 * @category               Library
 * @DateOfCreation         05 Apr 2018
 * @ShortDescription       This Library is responsible for all security functions
 */
class SecurityLib {
    
    // @var String $secret_key1
    // This protected member contains first secret key for encryption
    protected $secret_key1 = '';
    
    // @var String $secret_key2
    // This protected member contains second secret key for encryption
    protected $secret_key2 = '';
    
    // @var String $encrypt_method
    // This protected member contains encrypted method
    protected $encrypt_method = "AES-256-CBC";

    // @var Array $blacklist_ip
    // This protected member contains array of blacklist ip's
    protected $blackListIps = [];
    
    // @var Array $whitelist_ip
    // This protected member contains array of whitelist ip's
    protected $whiteListIps = [];
    /**
     * Create a new library instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->secret_key1  = Config::get('constants.ENCRYPTION_KEY1');
        $this->secret_key2  = Config::get('constants.ENCRYPTION_KEY2');
        $this->blackListIps = Config::get('iplist.blacklist_ip');
        $this->whiteListIps = Config::get('iplist.whitelist_ip');
    }
   
    
    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible for encrypting ids
    * @param                 String $stringToEncrypt any string to encrypt 
    * @return                Encrypted string
    */
    public function encrypt($stringToEncrypt) {
        $output = '';
        if(!is_null($stringToEncrypt)){
            $key1 = hash( 'sha256', $this->secret_key1);
            $key2 = substr( hash( 'sha256', $this->secret_key2 ), 0, 16 );
            $output = base64_encode( openssl_encrypt( $stringToEncrypt, $this->encrypt_method, $key1, 0, $key2 ) );
        }
       return $output;
    }
    
    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible for decrypting an encrypted id
    * @param                 String $stringToDecrypt any string to decrypt
    * @return                Decrypted string
    */
    public function decrypt($stringToDecrypt) {
       $key1 = hash( 'sha256', $this->secret_key1);
       $key2 = substr( hash( 'sha256', $this->secret_key2 ), 0, 16 );
       $output = openssl_decrypt( base64_decode( $stringToDecrypt ), $this->encrypt_method, $key1, 0, $key2 );
       return $output;
    }

    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to check blacklist ip
    * @param                 String $ip
    * @return                Boolean (true/false) 
    */
    public function isIpBlackListed($ip)
    {
        $output = false;
        $blackList = $this->blackListIps;
        $output = in_array($ip,$blackList);
        return $output;
    }

     /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to check whitelist ip
    * @param                 String $ip
    * @return                Boolean (true/false) 
    */
    public function isIpWhiteListed($ip)
    {
        $output = false;
        $whiteList = $this->whiteListIps;
        $output = in_array($ip,$whiteList);
        return $output;
    }

    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to generate random otp
    * @param                 Integer $lenghtOfOtp
                             Integer $typeOfOtp (1 for numeric, 2 for alpha, 3 alphanumeric)
    * @return                Generated OTP
    */
    public function genrateRandomOTP($lenghtOfOtp,$typeOfOtp)
    {
        $utilitylibObj = new UtilityLib();
        $otp = '';
        switch ($lenghtOfOtp) {
            case 1:
                $otp = $utilitylibObj->randomNumericInteger($lenghtOfOtp);
            break;
            case 2:
                $otp = $utilitylibObj->alphabeticString($lenghtOfOtp);
            break;
            case 3:
                $otp = $utilitylibObj->alphanumericString($lenghtOfOtp);
            break;
        }
        return $otp;
            
    }
}