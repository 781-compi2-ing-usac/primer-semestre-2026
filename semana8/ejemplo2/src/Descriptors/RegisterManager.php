<?php

namespace App\Descriptors;

use App\Env\Result;

class RegisterManager
{
    public $registers = [];
    public $temporaryRegisters = [];
    public $specialRegisters = [];
    public $variableLocations = [];
    public $stackLocations = [];
    private $stackCounter = 0;
    private $usedRegisters = [];
    private $code;

    public function __construct($codeGenerator) {
        $this->code = $codeGenerator;
        $this->initRegisters();
    }

    private function initRegisters() {
        $this->specialRegisters['xzr'] = new RegisterDescriptor('xzr');
        $this->specialRegisters['sp'] = new RegisterDescriptor('sp');
        $this->specialRegisters['fp'] = new RegisterDescriptor('fp');
        $this->specialRegisters['lr'] = new RegisterDescriptor('lr');
        $this->specialRegisters['x30'] = new RegisterDescriptor('x30');
        $this->specialRegisters['x29'] = new RegisterDescriptor('x29');

        for ($i = 0; $i <= 7; $i++) {
            $this->registers['x' . $i] = new RegisterDescriptor('x' . $i);
        }

        for ($i = 9; $i <= 15; $i++) {
            $this->temporaryRegisters['x' . $i] = new RegisterDescriptor('x' . $i);
        }

        // x19-x28 son registros callee-saved, pero los incluimos como temporales para poder usarlos si es necesario
        for ($i = 19; $i <= 28; $i++) {
            $this->registers['x' . $i] = new RegisterDescriptor('x' . $i);
        }

        $this->registers['x16'] = new RegisterDescriptor('x16');
        $this->registers['x17'] = new RegisterDescriptor('x17');
        $this->registers['x18'] = new RegisterDescriptor('x18');
    }

    public function getFreeRegister() {
        foreach ($this->temporaryRegisters as $name => $reg) {
            if (substr($name, 0, 1) === 'x' && !$reg->isBusy) {
                $this->usedRegisters[] = $name;
                return $reg;
            }
        }

        foreach ($this->registers as $name => $reg) {
            if (substr($name, 0, 1) === 'x' && !$reg->isBusy && !in_array($name, ['x0', 'x1', 'x2', 'x3', 'x4', 'x5', 'x6', 'x7'])) {
                $this->usedRegisters[] = $name;
                return $reg;
            }
        }

        return $this->spillOneRegister();
    }

    public function spillOneRegister() {
        foreach ($this->temporaryRegisters as $name => $reg) {
            if (substr($name, 0, 1) === 'x' && !empty($reg->variables)) {
                return $this->spillRegister($reg);
            }
        }

        foreach ($this->registers as $name => $reg) {
            if (substr($name, 0, 1) === 'x' && !empty($reg->variables) && !in_array($name, ['x0', 'x1', 'x2', 'x3', 'x4', 'x5', 'x6', 'x7'])) {
                return $this->spillRegister($reg);
            }
        }

        throw new \Exception("No hay registros disponibles para spilling");
    }

    public function spillRegister($reg) {
        $offset = ($this->stackCounter + 1) * 16;
        $this->stackCounter++;
        $fp = $this->getFramePointer();
        $this->code->str($reg->name, $fp->name, -$offset);

        foreach ($reg->variables as $varName) {
            $this->stackLocations[$varName] = [
                'register' => $reg->name,
                'offset' => $offset
            ];
        }

        $reg->clear();
        $reg->onStack = true;
        $reg->stackOffset = $offset;

        return $reg;
    }

    public function allocateRegister($varName = null) {
        $reg = $this->getFreeRegister();
        $reg->isBusy = true;
        
        if ($varName !== null) {
            $reg->addVariable($varName);
            $this->variableLocations[$varName] = $reg->name;
        }
        
        return Result::temp(Result::INT, $reg->name);
    }

    public function allocateRegisterWithType($tipo, $varName = null) {
        $reg = $this->getFreeRegister();
        $reg->isBusy = true;
        
        if ($varName !== null) {
            $reg->addVariable($varName);
            $this->variableLocations[$varName] = $reg->name;
        }
        
        return Result::temp($tipo, $reg->name);
    }

    public function freeRegister($register) {
        if ($register instanceof Result) {
            $name = $register->valor;
        } elseif (is_string($register)) {
            $name = $register;
        } else {
            $name = $register->name;
        }

        if ($name === null) return;

        if (isset($this->temporaryRegisters[$name])) {
            $this->temporaryRegisters[$name]->clear();
        } elseif (isset($this->registers[$name])) {
            $this->registers[$name]->clear();
        }

        foreach ($this->variableLocations as $var => $regName) {
            if ($regName === $name) {
                unset($this->variableLocations[$var]);
            }
        }
    }

    public function getVariableLocation($varName) {
        if (isset($this->variableLocations[$varName])) {
            return $this->variableLocations[$varName];
        }
        return null;
    }

    public function storeVariable($varName, $register) {
        if ($register instanceof Result) {
            $registerName = $register->valor;
        } else {
            $registerName = $register->name;
        }
        
        if (isset($this->temporaryRegisters[$registerName])) {
            $this->temporaryRegisters[$registerName]->addVariable($varName);
        } elseif (isset($this->registers[$registerName])) {
            $this->registers[$registerName]->addVariable($varName);
        }
        
        $this->variableLocations[$varName] = $registerName;
    }

    public function loadFromStack($varName) {
        if (isset($this->stackLocations[$varName])) {
            $location = $this->stackLocations[$varName];
            $reg = $this->getFreeRegister();
            
            $fp = $this->getFramePointer();
            $this->code->ldr($reg->name, $fp->name, -$location['offset']);
            
            unset($this->stackLocations[$varName]);
            
            return Result::temp(Result::INT, $reg->name);
        }
        return null;
    }

    public function getValueFromResult($result) {
        if ($result->onStack) {
            $loaded = $this->loadFromStack($result->valor);
            if ($loaded) {
                return $loaded->valor;
            }
        }
        return $result->valor;
    }

    public function getZeroRegister() {
        return $this->specialRegisters['xzr'];
    }

    public function getStackPointer() {
        return $this->specialRegisters['sp'];
    }

    public function getFramePointer() {
        return $this->specialRegisters['fp'];
    }

    public function getLinkRegister() {
        return $this->specialRegisters['lr'];
    }

    public function getArgumentRegisters($count = 8) {
        $args = [];
        for ($i = 0; $i < $count && $i < 8; $i++) {
            $args[] = $this->registers['x' . $i];
        }
        return $args;
    }

    public function getReturnRegister() {
        return $this->registers['x0'];
    }

    public function getSyscallRegister() {
        return $this->registers['x8'];
    }

    public function spillToStack($register) {
        $sp = $this->getStackPointer();
        $this->code->subi($sp->name, $sp->name, 16);
        $this->code->str($register->name, $sp->name);
        return $sp->name;
    }

    public function loadFromStackByOffset($register, $spOffset = 0) {
        $sp = $this->getStackPointer();
        $this->code->ldr($register->name, $sp->name, $spOffset);
        $this->code->addi($sp->name, $sp->name, 16);
    }
}
