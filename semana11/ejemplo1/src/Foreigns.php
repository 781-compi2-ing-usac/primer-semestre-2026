<?php

class Foreign extends Invocable {
    public $ctx;
    public $closure;
    public $params;
    public function __construct($ctx, $closure, $params) {
        $this->ctx = $ctx;
        $this->closure = $closure;
        $this->params = $params;
    }
    public function get_arity() {
        return count($this->params);
    }
    public function invoke($visitor, $args){
        $newEnv = new Environment($this->closure);
        $i = 0;        
        foreach($this->params as $param) {
            $newEnv->set($param, $args[$i]);
            $i++;
        }
        $envBeforeCall = $visitor->env;
        $visitor->env = $newEnv;
        $result = $visitor->visit($this->ctx->block());
        if ($result instanceof ReturnType) {
            $visitor->env = $envBeforeCall;
            return $result->value;
        }
        $visitor->env = $envBeforeCall;
        return $result;
    }
}