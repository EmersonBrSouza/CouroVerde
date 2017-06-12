<?php
/**
 * Classe responsável por representar uma exceção lançada quando o sobrenome é preenchido de forma incorreta durante o cadastro de usuário.
 * @author MItologhic Software
 *
 */

class NomeInvalidoException extends Exception
{
    
    /**
	* Construtor da classe.
	*/
    public function __construct()
    {
        parent::__construct("O sobrenome não foi informado..");
    }
}
