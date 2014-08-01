<?php
namespace Bread\SMS\Vendor;

use Bread\SMS\Interfaces\SMS;
use Bread\Configuration\Manager as CM;
use DateTime;

class SMSFarm implements SMS
{

    private static $response = array(
        'credits' => array(
            'type',
            'country',
            'count'
        ),
        'error' => array(
            'status',
            'errorcode',
            'errormessage'
        ),
        'history' => array(
            'order_id',
            'date',
            'type',
            'sender',
            'recipients',
            'delay'
        ),
        'send' => array(
            'status',
            'order_id',
            'count'
        ),
        'status' => array(
            'recipient_number',
            'status',
            'delivery_date'
        )
    );

    private static $parse_status = array(
        'SCHEDULED' => 'Posticipato, non ancora inviato',
        'SENT' => 'Inviato, non attende delivery',
        'DLVRD' => "L'SMS è stato correttamente ricevuto",
        'ERROR' => "Errore nella consegna dell'SMS",
        'TIMEOUT' => "L'operatore non ha fornito informazioni sull'SMS entro 48 ore",
        'TOOM4NUM' => 'Troppi SMS per lo stesso destinatario nelle ultime 24 ore',
        'TOOM4USER' => "Troppi SMS inviati dall'utente nelle ultime 24 ore",
        'UNKNPFX' => 'Prefisso SMS non valido o sconosciuto',
        'UNKNRCPT' => 'Numero di telefono del destinatario non valido o sconosciuto',
        'WAIT4DLVR' => 'Messaggio inviato, in attesa di delivery',
        'WAITING' => 'In attesa, non ancora inviato',
        'UNKNOWN' => 'Stato sconosciuto'
    );

    private $account;

    public function __construct($domain)
    {
        $this->account = array(
            'login' => CM::get(__CLASS__, 'login', $domain),
            'password' => CM::get(__CLASS__, 'password', $domain),
            'proxy' => CM::get(__CLASS__, 'proxy', $domain),
            'url' => CM::get(__CLASS__, 'url', $domain),
            'type' => CM::get(__CLASS__, 'type', $domain)
        );
    }

    public function credits()
    {
        $arrayurl = array(
            'login' => $this->account['login'],
            'password' => $this->account['password']
        );
        $url = "{$this->account['url']}/CREDITS?" . http_build_query($arrayurl);
        $sendresult = $this->curlRequest($url, $this->account['proxy']);
        $credits = $this->parseResponse($sendresult, self::$response['credits']);

        return $credits;
    }

    public function history($from, $to)
    {
        $arrayurl = array(
            'login' => $this->account['login'],
            'password' => $this->account['password'],
            'from' => $from,
            'to' => $to
        );
        $url = "{$this->account['url']}/SMSHISTORY?" . http_build_query($arrayurl);
        $sendresult = $this->curlRequest($url, $this->account['proxy']);
        $history = $this->parseResponse($sendresult, self::$response['history']);

        return $history;
    }

    public function send($sms)
    {
        $arrayurl = array(
            'login' => $this->account['login'],
            'password' => $this->account['password'],
            'message_type' => $this->account['type'],
            'recipient' => $sms->recipient,
            'message' => $sms->message,
            'sender' => $sms->sender,
            'order_id' => $sms->order_id
        );
        $url = "{$this->account['url']}/SENDSMS?" . http_build_query($arrayurl);
        $sendresult = $this->curlRequest($url, $this->account['proxy']);
        // $sendresult = "OK|SMS51a35d85e50a4|1";
        return $this->parseResponse($sendresult);
    }

    public function status($order_id)
    {
        $arrayurl = array(
            'login' => $this->account['login'],
            'password' => $this->account['password'],
            'order_id' => $order_id
        );
        $url = "{$this->account['url']}/SMSSTATUS?" . http_build_query($arrayurl);
        $sendresult = $this->curlRequest($url, $this->account['proxy']);
        $status = $this->parseResponse($sendresult, self::$response['status']);
        $result = array();
        if (empty($status)) {
            $result['status'] = "SMS non più disponibile";
        } else
            if (! isset($status['false'])) {
                $result = array_shift($status);
                foreach ($result as $key => $value) {
                    switch ($key) {
                        case 'status':
                            $result[$key] = self::$parse_status[$value];
                            break;
                        case 'delivery_date':
                            $result[$key] = new DateTime($value);
                            break;
                    }
                }
            }
        return $result;
    }

    protected function parseResponse($response, $keys = null)
    {
        $response = urldecode(trim($response, ";"));
        $response = explode(';', $response);
        $status = array_shift($response);
        $result = array();
        if ($status == 'OK') {
            foreach ($response as $elem) {
                $sms = explode('|', $elem);
                $result[] = array_combine($keys, $sms);
            }
        } else {
            $split = explode('|', $status);
            switch ($split[0]) {
                case 'KO':
                    $result['false'] = array_combine(self::$response['error'], $split);
                    break;
                case 'OK':
                    $result['true'] = array_combine(self::$response['send'], $split);
                    break;
            }
        }
        return $result;
    }

    protected function curlRequest($url, $proxy = null)
    {
        $ch = curl_init($url);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $sendresult = curl_exec($ch);
        curl_close($ch);
        return $sendresult;
    }
}
