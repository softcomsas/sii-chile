<?php
namespace app\components\validators;

use yii\validators\Validator;

class MovilValidator extends Validator
{
    public $codigoPais = '53'; 
    public $prefijos   = [ 51, 52, 53, 54, 55, 56, 58, 59];
    public $pattern;

    public function init()
    {
        parent::init();
        $this->pattern = '/^'.$this->codigoPais.'{1}('.implode('|',$this->prefijos).'){1}([0-9]{6})$/';
    }

    protected function validateValue($telefono)
    {
        $telefono = trim($telefono);
        $long = strlen($telefono);
        if ($long < 8) {
            return ['La longitud debe ser igual o mayor que 8.', []];
        }
        if (substr_compare($telefono, '+', 0, 1) == 0) {
            $telefono = substr($telefono, 1);
        }
        //'/^(53){0,1}(51|52|53|54|55|56|58|59){1}([0-9]{6})$/'
        if (!preg_match($this->pattern, $telefono, $coincidencia)) {
            return ['Teléfono inválido.', []];
        } 
        return null;
    }
}