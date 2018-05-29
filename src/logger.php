<?php
class Logger {

    function __construct($enabled=true) {
        $this->enabled = $enabled;
        $this->start = microtime(true);
    }

    public function log($msg) {
        if(!$this->enabled) {
            return;
        }
        $now = microtime(true);
        $ellapsed = ($now - $this->start) * 1000;
        print "$ellapsed:$msg\n";
    }

    public static function null() {
        return new Logger(false);
    }
}
