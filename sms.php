<?php
    require 'vendor/autoload.php';
    use AfricasTalking\SDK\AfricasTalking;

    include_once 'util.php';
    include_once 'db.php';


    class Sms {
        protected $AT;

        function __construct(){            
            $this->AT = new AfricasTalking(Util::$API_USERNAME, Util::$API_KEY);
        }

        public function sendSMS($message, $recipients){
            //get the sms service
            $sms = $this->AT->sms();
            //use the service 
            $result = $sms->send([
                'to'      => $recipients,
                'message' => $message,
                'from'    => Util::$SMS_SHORTCODE,
            ]);
            return $result;
        }
    }

?>