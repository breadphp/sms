<?php
namespace Bread\SMS\Interfaces;

interface SMS
{

    public function send($sms);

    public function status($sms);

    public function history($from, $to);

    public function credits();
}
