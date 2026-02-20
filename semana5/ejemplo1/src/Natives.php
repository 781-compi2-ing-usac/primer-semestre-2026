<?php 

class Time extends Invocable {
    public function get_arity() {
        return 0;
    }
    public function invoke($visitor, $args) {
        date_default_timezone_set("UTC");
        return date("d-M-Y H:i:s");
    }
}

return $embeded = array(
    "time" => new Time()
);