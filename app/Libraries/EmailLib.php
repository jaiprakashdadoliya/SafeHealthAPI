<?php
namespace App\Libraries;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * EmailLib Class
 *
 * @package                Safe Health
 * @subpackage             EmailLib
 * @category               Library
 * @DateOfCreation         28 Apr 2018
 * @ShortDescription       This Library is responsible for sending email
 */
class EmailLib extends Mailable
{
    use Queueable, SerializesModels;
    
    /* @var String $emailConfig
     * This public member stores email config data
     * Pass data in predefine keys
     * [
     *     'viewData' => 'Pass view data in this key'
     *     'emailTemplate' => 'Pass email template view file name in this key'
     *     'subject'   => 'Pass subject in this key'
     * ]
     */    
    public $emailConfig = [
        'viewData' => [],
        'emailTemplate' => '',
        'subject' => ''
    ];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailConfig = [])
    {
        // Initialize view data
        if(isset($emailConfig['viewData']) and !empty($emailConfig['viewData'])){
            $this->emailConfig['viewData'] = $emailConfig['viewData'];
        }
        
        // Initialize email template
        if(isset($emailConfig['emailTemplate']) and !empty($emailConfig['emailTemplate'])){
            $this->emailConfig['emailTemplate'] = $emailConfig['emailTemplate'];
        }
        
        // Initialize subject
        if(isset($emailConfig['subject']) and !empty($emailConfig['subject'])){
            $this->emailConfig['subject'] = $emailConfig['subject'];
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->emailConfig['subject'])
                    ->view($this->emailConfig['emailTemplate'])
                    ->with($this->emailConfig['viewData']);
    }
}
