<?php
namespace App\Libraries;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * MessageLib Class
 *
 * @package                Safe Health
 * @subpackage             MessageLib
 * @category               Library
 * @DateOfCreation         28 Apr 2018
 * @ShortDescription       This Library is responsible for sending message
 */
class MessageLib
{
    
    /* @var String $messageConfig
     * This public member stores message config data
     * Pass data in predefine keys
     * [
     *     'viewData' => 'Pass view data in this key'
     *     'messageTemplate' => 'Pass message template view file name in this key'
     *     'subject'   => 'Pass subject in this key'
     * ]
     */    
    public $messageConfig = [
        'viewData' => ['firstName' => '', 'lastName' => ''],
    ];

    /**
     * Build the message.
     *
     * @return $this
     */
    public function messageData($data)
    {
        if(isset($data['firstName']) and !empty($data['firstName'])){
            $messageConfig['firstName'] = $data['firstName'];
        }
        if(isset($data['lastName']) and !empty($data['lastName'])){
            $messageConfig['lastName'] = $data['lastName'];
        }
        $message = 'Hi '.$messageConfig['firstName'].' '.$messageConfig['lastName'].',<br>';
        $message .= 'Your booking has been Successful.<br>';
        $message .= 'Please visit the clinic 10 minutes before the scheduled appointment.<br>';
        $message .= 'Thanks<br>';
        $message .= 'Safehealth Team';
        
        return $message;
    }
}
