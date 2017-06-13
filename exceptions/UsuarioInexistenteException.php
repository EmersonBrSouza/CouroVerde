<?php
/**
 * Classe responsável por representar uma exceção lançada quando ao tentar verificar ou alterar um usuário inexistente.
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
        parent::__construct("O usuário especificado não existe.");
    }
}