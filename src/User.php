<?php

namespace Lain;

class User {
    protected $dates = [
        'registered_at',
        'created',
        'expired',
        'last_used'
    ];

    public function __construct($param_array=array())
    {
        foreach($this->dates as $date_property_name){
            if (property_exists($this, $date_property_name) and is_string($this->{$date_property_name}))
                $this->{$date_property_name} = new \DateTime($this->{$date_property_name});
        }

        foreach($param_array ?? [] as $param_name => $param_value)
            if (!is_null($param_value))
                $this->{$param_name} = $param_value;
    }
};