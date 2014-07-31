<?php
namespace Bread\SMS;

use Bread\Configuration\Manager as CM;
use Bread\REST;

class Model extends REST\Model
{

    protected $order_id;

    protected $recipient;

    protected $message;

    protected $sender;

    protected $scheduled_delivery_time;
}

CM::defaults('Bread\SMS\Model', array(
    'keys' => array(
        'order_id'
    ),
    'properties' => array(
        'order_id' => array(
            'type' => 'string'
        ),
        'recipient' => array(
            'type' => 'string'
        ),
        'message' => array(
            'type' => 'string'
        ),
        'sender' => array(
            'type' => 'string'
        ),
        'scheduled_delivery_time' => array(
            'type' => 'datetime'
        )
    )
));
