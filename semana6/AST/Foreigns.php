<?php
class Foreign extends Invocable {
    public $node;
    public $closure;
    public function __construct($node, $closure) {
        $this->node = $node;
        $this->closure = $closure;
    }
    public function get_arity() {        
        return count($this->node->params);
    }
    public function invoke($interpreter, $args) {
        $newEnv = new Environment($this->closure);
        $i = 0;
        foreach ($this->node->params as $param) {
            $newEnv->set($param, $args[$i]);
            $i++;
        }
        $envBeforeCall = $interpreter->env;
        $interpreter->env = $newEnv;
        $retVal = $this->node->block->accept($interpreter);
        if ($retVal instanceof ReturnType) {
            return $retVal->retVal;
        }
        $interpreter->env = $envBeforeCall;
        return $retVal;
    }

    public function link($instance) {
        $hiddenEnv = new Environment($this->closure);
        $hiddenEnv->set("this", $instance);
        return new Foreign($this->node, $hiddenEnv);
    }
}