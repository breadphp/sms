<?php
namespace Bread\SMS\Vendor;

use Bread\SMS\Interfaces\SMS;
use Bread\Configuration\Manager as CM;
use DateTime;
use Exception;

/**
 * http://www.smsfoxbox.it/it/
 *
 * @author tomaselloa
 *
 */
class ArduinoFoxBox implements SMS
{

    private $account;

    public function __construct($domain)
    {
        $this->account = array(
            'username' => CM::get(__CLASS__, 'login', $domain),
            'pwd' => CM::get(__CLASS__, 'password', $domain),
            'proxy' => CM::get(__CLASS__, 'proxy', $domain),
            'url' => CM::get(__CLASS__, 'url', $domain)
        );
    }

    public function credits()
    {
        throw new Exception("Not implemented");
    }

    public function history($from, $to)
    {
        throw new Exception("Not implemented");
    }

    public function send($sms)
    {
        if (!$sms->recipient | !$sms->message) {
            throw new Exception("Recipient and message are mandatory");
        }
        $arrayurl = array(
            'username' => $this->account['username'],
            'pwd' => $this->account['pwd'],
            'nphone' => trim(trim($sms->recipient, '+')),
            'testo' => $sms->message,
            'from' => $sms->sender
        );
        $url = "{$this->account['url']}?" . http_build_query($arrayurl);
        $sendresult = $this->curlRequest($url, $this->account['proxy']);
        return array("{$sendresult}" => $sms->order_id);
    }

    public function status($order_id)
    {
        throw new Exception("Not implemented");
    }

    protected function curlRequest($url, $proxy = null)
    {
        $ch = curl_init($url);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        $sendresult = curl_exec($ch);
        curl_close($ch);
        return $sendresult;
    }
}
