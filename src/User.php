<?php

namespace Lain;

class User {
    protected $dates = [
        'created_at',
        'created',
        'expired',
        'last_used'
    ];

    public function __construct() {
        foreach($this->dates as $date_property_name) {
            if (property_exists($this, $date_property_name)) 
                $this->{$date_property_name} = new \DateTime($this->{$date_property_name});
        }
        #unset($this->password);
        #unset($this->token);
    }
};