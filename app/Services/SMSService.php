<?php
namespace App\Services;

use Kavenegar\KavenegarApi;
use Kavenegar\Exceptions\ApiException;

class SMSService
{
    protected $api;

    public function __construct()
    {
        $this->api = new KavenegarApi(env('KAVENEGAR_API_KEY'));
    }

    public function sendVerificationCode($phone, $code)
    {
        $message = "کد تأیید شما: " . $code;
        try {
            $this->api->Send("100090003", $phone, $message);
            return true; // اگر ارسال موفق بود
        } catch (ApiException $e) {
            // اگر ارسال ناموفق بود
            return false;
        }
    }
}



