<?php

class UsuarioDAO extends Database{

    public $isLogged = false;

    /**
    * Insere um usuário no banco de dados;
    * @param unknown $usuario - o usuário a ser inserido no banco;
    * */
    public function inserir($usuario){
        
        $nome = $usuario->getNome();
        $sobrenome = $usuario->getSobrenome();
        $email = $usuario->getEmail();
        $senha = $usuario->getSenha();
        $cadastroConfirmado = $usuario->confirmouCadastro();

        $query = "INSERT INTO `usuario`(`idUsuario`, `nome`, `sobrenome`, `email`, `senha`, `cadastroConfirmado`) 
                  VALUES (null,'$nome','$sobrenome','$email','$senha','$cadastroConfirmado')";
        try{
            $this->PDO->query($query);
        }catch(PDOException $e){

        }
    }

    /**
    * Altera informações de um usuário no banco de dados;
    * @param unknown $dados - um array contendo as colunas e valores para a alteração. Ex: array("nome"=>"João");
    * @param unknown $filtros - um array contendo os filtros usados na busca. Ex: array("idUsuario"=>5);
    * */
    public function alterar($dados,$filtros){
        $query = "UPDATE usuario SET ";

        foreach($dados as $chave=>$valor){
            $query .= $chave.'='."'$valor'";
        }

        if(count($filtros) > 0){
            $query .= " WHERE ";
            $aux = array();

            foreach($filtros as $chave=>$valor){
                $aux[] = $chave." = "."'$valor'";
            }

            $query .= implode(" AND ",$aux);
        }

        
        $this->PDO->query($query);

    }

    /**
    * Remove um usuário do banco de dados;
    * @param unknown $filtros - um array contendo os filtros usados na identificação do usuário. Ex: array("idUsuario"=>5);
    * */
    public function remover($filtros){
        $query = "DELETE FROM usuario WHERE ";

        if(count($filtros) > 0){
            $aux = array();

            foreach($filtros as $chave=>$valor){
                $aux[] = $chave." = "."'$valor'";
            }

            $query .= implode(" AND ",$aux);
        }

        $this->PDO->query($query);
    }

    /**
    * Busca um ou vários usuários no banco de dados;
    * @param unknown $campos - um array contendo os campos desejados
    * @param unknown $filtros - um array contendo os filtros usados na busca. Ex: array("idUsuario"=>5);
    * @return unknown $usuarios - um array contendo os usuários retornados na busca
    */
    public function buscar($campos,$filtros){
        $query = "SELECT ";

        if(count($campos) == 0){
            $campos = array("*");
        }

        $query .= implode(',',$campos)."FROM usuario";

        if(count($filtros) > 0){
            $query .= " WHERE ";
            $aux = array();

            foreach($filtros as $chave=>$valor){
                $aux[] = $chave."="."'$valor'";
            }
            
            $query .= implode(" AND ",$aux);
        }

        $result = $this->PDO->query($query);

        $usuarios = array();
        if(!empty($result) && $result->rowCount() > 0){
            foreach($result->fetchAll() as $item){
                $usuarios[] = new Usuario($item['idUsuario'],$item['email'],$item['nome'],$item['sobrenome'],$item['senha'],$item['cadastroConfirmado']);
            }    
        }
        
        return $usuarios;
    }

    public function login($email, $senha){
        $campos = array("email", "senha");
        $filtro = array(
            "email" => $email;
            "senha" => $senha;
        );
        $usuarios = array();
        $usuarios = buscar($campos, $filtro);
        if(count($usuarios) == 1){
            session_start();
            $_SESSION['email'] = $usuario[0]->email;
            $_SESSION['senha'] = $usuario[0]->senha;
            $this->isLogged = true;
            return true;
        }
        else{
            return false;
        }
    }

    public function logout(){
        session_start();
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
?>
