<?php
namespace controllers;

require_once dirname(__DIR__).'/vendor/autoload.php';
use DAO\PesquisaDAO as PesquisaDAO;
use DAO\PerguntaDAO as PerguntaDAO;
use DAO\OpcaoDAO as OpcaoDAO;
use DAO\PerguntaPesquisaDAO as PerguntaPesquisaDAO;
use DAO\PerguntaOpcaoDAO as PerguntaOpcaoDAO;
use DAO\UsuarioDAO as UsuarioDAO;
use \util\ValidacaoDados as ValidacaoDados;
use \util\GerenciarSenha as GerenciarSenha;
use \util\PercentualOpcoes as PercentualOpcoes;
use \models\Pesquisa as Pesquisa;
use \models\Pergunta as Pergunta;
use \models\Opcao as Opcao;
use \models\OpcaoPergunta as OpcaoPergunta;
use \exceptions\DadosCorrompidosException as DadosCorrompidosException;
use \exceptions\NomeInvalidoException as NomeInvalidoException;
use \exceptions\CampoInvalidoException as CampoInvalidoException;
use \exceptions\PerguntaInconsistenteException as PerguntaInconsistenteException;
use \exceptions\PesquisaJaExistenteException as PesquisaJaExistenteException;
use \exceptions\PesquisaInexistenteException as PesquisaInexistenteException;
use \exceptions\SenhaIncorretaException as SenhaIncorretaException;

class pesquisaController extends mainController{


  protected $dados = array();

  public function configurarAmbienteParaTeste($json = null, $senhaAdmin = null, $idPesquisa = null) {
    $_POST['json'] = $json;
    $_POST['senhaAdmin'] = $senhaAdmin;
    $_POST['idPesquisa'] = $idPesquisa;
  }

  public function index(){
  
    $this->carregarConteudo('homePesquisa',$this->dados);
  }
  
  /**
  * Este método carrega a página responsável pelo cadastro da pesquisa
  */
  public function cadastrar(){
    $this->carregarConteudo('cadastroPesquisa',$this->dados); 
  }


  /**
  * Este método, de fato, cria a pesquisa. É utilizado pela requisição AJAX.
  */
  public function criar(){
    
    try{
      $json = $_POST['json']; //Recebe os dados da view
      $dadosRecebidos = json_decode($json,true); //Decodifica o JSON
    
      if(ValidacaoDados::validarForm($dadosRecebidos,array("tituloPesquisa"))){// Verifica se os campos existem
          $tituloPesquisa = array_shift($dadosRecebidos);//Recebe o titulo da pesquisa
          $descricaoPesquisa = array_shift($dadosRecebidos);//Recebe a descrição da pesquisa

          $perguntas = array(); //Este array vai conter todas as perguntas a serem adicionadas;

          while(count($dadosRecebidos)> 0){ // Itera o array dos dados recebidos para organizar as informações
              $pergunta = array_shift($dadosRecebidos)[0];//Retira a pergunta do array recebido
              $perguntaFinal = array();

              if(strlen($tituloPesquisa)==0 || ValidacaoDados::campoVazio($tituloPesquisa)){ // Verifica se a string do titulo está vazia
                $tituloPesquisa = "Sem título";
              }

              if(!isset($descricaoPesquisa) || strlen($descricaoPesquisa)==0 || ValidacaoDados::campoVazio($descricaoPesquisa)){// Verifica se a string da descrição está vazia
                $descricaoPesquisa = "Sem descrição";
              }

              if(empty($pergunta['tipoPergunta']) || ValidacaoDados::campoVazio($pergunta['tipoPergunta'])){ //Verifica se o tipo da pergunta foi informado
                  throw new DadosCorrompidosException();  
              }

              if(strlen($pergunta['tituloPergunta'])==0 || ValidacaoDados::campoVazio($pergunta['tituloPergunta'])){//Verifica se a pergunta possui um título
                throw new PerguntaInconsistenteException();
              }else{
                $tituloPergunta = $pergunta['tituloPergunta'];
                $tipoPergunta = $pergunta['tipoPergunta'];
                $isOpcional = (strcmp($pergunta['obrigatorio'],"false")==0)? 1:0;
              }

              $opcoes;
              
              $perguntaFinal['pergunta']  = new Pergunta(null,$tituloPergunta,$tipoPergunta,$isOpcional); //Cria um objeto pergunta

              if(strcmp($tipoPergunta,"ABERTA")!=0){// Guarda as opções no array, caso existam
                if(ValidacaoDados::validarForm($pergunta,array("opcoes"))){
                  $opcoes = $pergunta['opcoes'];
                  $perguntaFinal['opcoes'] = !empty($opcoes)?$opcoes:null;
                }
              }
              
              array_push($perguntas,$perguntaFinal);//Insere a pergunta no array de perguntas
          }//Fim do while

          
          //Cria a pesquisa e guarda no banco
          $pesquisa = new Pesquisa(null,$tituloPesquisa,$descricaoPesquisa,false); // Inicializa a pesquisa, ao ser criada a pesquisa ainda não é ativada
          
          $pesquisaDAO = new PesquisaDAO();

          $buscarPesquisa = $pesquisaDAO->buscar(array("idPesquisa"),array("titulo"=>$tituloPesquisa));
          
          if(count($buscarPesquisa) == 0){
            $pesquisaDAO->inserir($pesquisa);
          }else{
              throw new PesquisaJaExistenteException();
          }
         

          $pesquisaDAO = new PesquisaDAO(); //Recupera o ID da Pesquisa
          $idPesquisa = $pesquisaDAO->buscar(array("idPesquisa"),array("titulo"=>$tituloPesquisa));
          $idPesquisa = $idPesquisa[0]->getIdPesquisa();

          $perguntaDAO = new PerguntaDAO();
          $opcaoDAO = new OpcaoDAO();
          foreach($perguntas as $pergunta){
            $perguntaDAO->inserir($pergunta['pergunta'],$idPesquisa);
            $idPergunta = $perguntaDAO->getIdUltimaPergunta();
            
            if(isset($pergunta['opcoes'])){// Se a pergunta tiver opções
              $opcoes = $pergunta['opcoes'];
              foreach($opcoes as $opcao){
                $opcaoDAO->inserir(new Opcao(null,$opcao),$idPergunta);
              }//Fim foreach
            }// Fim if
          }//Fim foreach
        }// Fim if

        echo json_encode(array("success"=>true));
    }catch(DadosCorrompidosException $e){
      echo json_encode(array("erro"=>"Por favor, escolha o tipo da pergunta.","success"=>"false"));
    }catch(PerguntaInconsistenteException $e){
      echo json_encode(array("erro"=>$e->getMessage(),"success"=>"false"));
    }catch(PesquisaJaExistenteException $e){
      echo json_encode(array("erro"=>$e->getMessage(),"success"=>"false"));
    }

       
  }// Fim método

  public function listarTodasPesquisas(){

    $pesquisaDAO = new PesquisaDAO();

    if(ValidacaoDados::validarForm($_POST,array("titulo"))){
      $pesquisaDAO = $pesquisaDAO->buscarLike(array(),array("titulo"=>$_POST['titulo']));
    }else{
      $pesquisaDAO = $pesquisaDAO->buscar(array(),array());
    }
   
    $pesquisas = array();
    foreach($pesquisaDAO as $pesquisa){
      $pesquisaAtual;
      $pesquisaAtual['idPesquisa'] = $pesquisa->getIdPesquisa();
      $pesquisaAtual['tituloPesquisa'] = $pesquisa->getTitulo();
      $pesquisaAtual['descricaoPesquisa'] = $pesquisa->getDescricao();
      $pesquisaAtual['estaAtiva'] = $pesquisa->getEstaAtiva() ? true:false;

      $pesquisas[] = $pesquisaAtual;
    }
    echo json_encode($pesquisas);
  }
  
  /**
  * Este método carrega a página responsável pelo cadastro da pesquisa
  */
  public function gerenciar(){
    $this->carregarConteudo('gerenciarPesquisa',$this->dados); 
  }

  public function buscar($parametros){
     

     try{
      if(isset($parametros) && !empty($parametros)){
        $idPesquisa = array_shift($parametros);
        $pesquisaDAO = new PesquisaDAO();
        $pesquisa = $pesquisaDAO->buscar(array(),array('idPesquisa'=>$idPesquisa));
          if(count($pesquisa)>0){
              $tituloPesquisa = $pesquisa[0]->getTitulo();//Recebe o titulo da pesquisa
              $descricaoPesquisa = $pesquisa[0]->getDescricao();//Recebe a descrição da pesquisa
              
              $perguntaPesquisaDAO = new PerguntaPesquisaDAO();
              $perguntasPesquisa = $perguntaPesquisaDAO->buscarPergunta(array(),array('idPesquisa'=>$idPesquisa));

              $perguntaDAO = new PerguntaDAO();
              $perguntas = array();
              
              foreach($perguntasPesquisa as $perguntaPesquisa){
                $pergunta = $perguntaDAO->buscar(array(),array('idPergunta'=>$perguntaPesquisa->getIdPergunta())); //Este array vai conter todas as perguntas a serem adicionadas;
                array_push($perguntas,$pergunta[0]);
              }
              
              $perguntaOpcaoDAO = new PerguntaOpcaoDAO();
            
              $perguntasOpcao = array();


              foreach($perguntas as $pergunta){
                $opcoes = $perguntaOpcaoDAO->buscarOpcao(array(),array('idPergunta'=>$pergunta->getIdPergunta()));
                $opcoesRecuperadas = array();
                foreach($opcoes as $opcaoAtual){
                  $opcaoDAO = new OpcaoDAO();
                  $opcao = $opcaoDAO->buscar(array(),array('idOpcao'=>$opcaoAtual->getIdOpcao()));
                  $opcoesRecuperadas[] = $opcao[0];
                }
                $perguntaOpcao['Pergunta'] = $pergunta;
                $perguntaOpcao['Opcao'] = $opcoesRecuperadas;
                array_push($perguntasOpcao,$perguntaOpcao);
              }
              

              $pesquisaFinal = array();
              $pesquisaFinal['idPesquisa'] = $idPesquisa;
              $pesquisaFinal['tituloPesquisa'] = $tituloPesquisa;
              $pesquisaFinal['descricaoPesquisa'] = $descricaoPesquisa;
              
              foreach($perguntasOpcao as $perguntaOpcao){
                array_push($pesquisaFinal,$perguntaOpcao);
              }
              echo json_encode($pesquisaFinal);
              return $pesquisaFinal;
            }else{
              throw new PesquisaInexistenteException();
            }
      }
      
        
    }catch(DadosCorrompidosException $e){
      echo json_encode(array("erro"=>"Por favor, escolha o tipo da pergunta.","success"=>"false"));
    }catch(PerguntaInconsistenteException $e){
      echo json_encode(array("erro"=>$e->getMessage(),"success"=>"false"));
    }catch(PesquisaJaExistenteException $e){
      echo json_encode(array("erro"=>$e->getMessage(),"success"=>"false"));
    }catch(PesquisaInexistenteException $e){
      echo json_encode(array("erro"=>$e->getMessage(),"success"=>"false"));
    }
  }

  public function remover(){
    try{
      if(ValidacaoDados::validarForm($_POST,array("senhaAdmin","idPesquisa"))){

        $usuarioDAO = new UsuarioDAO();
        $senhaArmazenada = $usuarioDAO->buscar(array("senha"),array("tipoUsuario"=>"ADMINISTRADOR"));
        $senhaArmazenada = $senhaArmazenada[0]->getSenha();
        
        if(GerenciarSenha::checarSenha($_POST['senhaAdmin'],$senhaArmazenada)){
          
          $pesquisaDAO = new PesquisaDAO();  
          $perguntaPesquisaDAO = new PerguntaPesquisaDAO();
          
          $perguntas = $perguntaPesquisaDAO->buscarPergunta(array(),array());


          $opcoesTodasPerguntas;
          $perguntaOpcaoDAO = new PerguntaOpcaoDAO();
         
          foreach($perguntas as $pergunta){
             if(strcmp($pergunta->getTipo(),"ABERTA") !=0){
                $id = $pergunta->getIdPergunta();
                $opcoesTodasPerguntas[] = $perguntaOpcaoDAO->buscarOpcao(array(),array("idPergunta"=>$id));
             }
          }
          
          $perguntaDAO = new PerguntaDAO();
          
          $opcaoDAO = new OpcaoDAO();
          if(isset($opcoesTodasPerguntas) && !empty($opcoesTodasPerguntas)){          
            foreach($opcoesTodasPerguntas as $opcoes) {
              if(isset($opcoes) && !empty($opcoes)){
                foreach($opcoes as $opcao){
                  $opcaoDAO->remover(array("idOpcao"=>$opcao->getIdOpcao())); 
                }              
              }
            }
          }

          foreach($perguntas as $pergunta){
            $perguntaDAO->remover(array("idPergunta"=>$pergunta->getIdPergunta()));
          }

          $pesquisaDAO->remover(array("idPesquisa"=>$_POST['idPesquisa']));
          echo json_encode(array("success"=>true));
        }else{
          throw new SenhaIncorretaException();
        }

      }else{
        throw new DadosCorrompidosException();
      }
    }catch(DadosCorrompidosException $e){
      echo json_encode(array("erro"=>"Por favor, tente novamente.","success"=>"false"));
    }catch(SenhaIncorretaException $e){
      echo json_encode(array("erro"=>$e->getMessage(),"success"=>"false"));
    }
  }

  public function toggle(){

    try{
      if(ValidacaoDados::validarForm($_POST,array('idPesquisa','estadoAtual'))){
        $pesquisaDAO = new PesquisaDAO();
        $pesquisaDAO->alterar(array("estaAtiva"=>"0"),array()); //Seta false em todas as pesquisas;

        
        if(strcmp($_POST['estadoAtual'],'false')==0){
          $pesquisaDAO->alterar(array("estaAtiva"=>1),array("idPesquisa"=>$_POST["idPesquisa"]));
        }

        echo json_encode(array("success"=>true));
      }
    }catch(Exception $e){
      echo json_encode(array("erro"=>"Ocorreu um erro,tente novamente","success"=>false));
    }

  }

public function alterar(){
    try{
      if(ValidacaoDados::validarForm($_POST,array('idPesquisa','json'))){
        $idPesquisa = $_POST['idPesquisa'];
        $json = $_POST['json'];

        $dadosRecebidos = json_decode($json,true);

        $tituloPesquisa = array_shift($dadosRecebidos);
        $descricaoPesquisa = array_shift($dadosRecebidos);

        $pesquisaDAO = new PesquisaDAO();
        $pesquisaEncontrada = $pesquisaDAO->buscar(array(),array('titulo'=>$tituloPesquisa));
        
        if(count($pesquisaEncontrada)>0 ){
          foreach($pesquisaEncontrada as $pesquisaConflitante){
            if($pesquisaConflitante->getIdPesquisa() != $idPesquisa){
              throw new PesquisaJaExistenteException();
            } else {
              $pesquisaDAO->alterar(array('descricao' => $descricaoPesquisa),array('idPesquisa'=>$idPesquisa));              
            }
          }
        }else{
          $pesquisaDAO->alterar(array('titulo' => $tituloPesquisa,'descricao' => $descricaoPesquisa),array('idPesquisa'=>$idPesquisa));
        }

        $idPerguntasExistentes = $this->perguntasPesquisa($idPesquisa); //Recupera as perguntas existentes de uma pesquisa
        
        $perguntaDAO = new PerguntaDAO();
        while(count($dadosRecebidos)>0){
          $pergunta = array_shift($dadosRecebidos)[0];
          $idPergunta = $pergunta['idPergunta'];
          $tituloPergunta = $pergunta['tituloPergunta'];
          $tituloPergunta = (ValidacaoDados::campoVazio($tituloPergunta))? 'Sem título':$tituloPergunta;
          $tipoPergunta = $pergunta['tipoPergunta'];
          $opcional = (strcmp($pergunta['obrigatorio'],'false')==0)? 1:0;
          $opcoes = isset($pergunta['opcoes'])?$pergunta['opcoes']:null;

          if(in_array($idPergunta,$idPerguntasExistentes)){ //Caso a pergunta já exista

            $key = array_search($idPergunta, $idPerguntasExistentes);
            unset($idPerguntasExistentes[$key]); // Remove o id do array para futura verificação

            $perguntaDAO->alterar(array("titulo"=>$tituloPergunta,"opcional"=>$opcional),array("idPergunta"=>$idPergunta));
            if(strcmp($tipoPergunta,"ABERTA")!=0){
              $idOpcoesPerguntaExistentes = $this->getOpcoesPergunta($idPergunta);
              $opcaoDAO = new OpcaoDAO();
              foreach($opcoes as $opcao){

                if(in_array($opcao['idOpcao'],$idOpcoesPerguntaExistentes)){//Caso a opção já exista
                  
                  $key = array_search($opcao['idOpcao'], $idOpcoesPerguntaExistentes);
                  unset($idOpcoesPerguntaExistentes[$key]); // Remove o id do array para futura verificação
                  
                  if(!ValidacaoDados::campoVazio($opcao['titulo'])){ //Se a descrição for vazia não altera a opção
                    $opcaoDAO->alterar(array('descricao'=>$opcao['titulo']),array('idOpcao'=>$opcao['idOpcao']));
                  }
                }else{
                  if(!ValidacaoDados::campoVazio($opcao['titulo'])){ //Se a descrição for vazia não cadastra a opção
                    $opcaoDAO->inserir(new Opcao(null,$opcao['titulo']),$idPergunta);
                  }
                }
              } 
              //Se existiam opções que não foram verificadas, significa que as opções não foram recebidas no JSON, logo foram excluídas
              if(count($idOpcoesPerguntaExistentes)>0){
                foreach($idOpcoesPerguntaExistentes as $idOpcao){
                  $opcaoDAO->remover(array('idOpcao'=>$idOpcao));
                }
              }
            }

          }else if($idPergunta == 'nulo'){//Caso a pergunta não exista, ela é criada
            $perguntaDAO->inserir(new Pergunta(null,$tituloPergunta,$tipoPergunta,$opcional),$idPesquisa);//Insere a pergunta
            $idPergunta = $perguntaDAO->getIdUltimaPergunta(); //Recupera o último id inserido;
            $opcaoDAO = new OpcaoDAO();
            if($opcoes != null) {
              foreach($opcoes as $opcao){
                if(!ValidacaoDados::campoVazio($opcao['titulo'])){ //Se a descrição for vazia não cadastra a opção
                  $opcaoDAO->inserir(new Opcao(null,$opcao['titulo']),$idPergunta);
                }
              }
            }

          }
        }
        //Se existiam perguntas que não foram verificadas, significa que as opções não foram recebidas no JSON, logo foram excluídas
        if(count($idPerguntasExistentes)>0){
          foreach($idPerguntasExistentes as $idPergunta){
              $opcoes;
              $perguntaOpcaoDAO = new PerguntaOpcaoDAO();
              
              $perguntaDAO = new PerguntaDAO();
              $pergunta = $perguntaDAO->buscar(array(),array('idPergunta'=>$idPergunta))[0];//Recupera as informações da pergunta atual

              if(strcmp($pergunta->getTipo(),"ABERTA") !=0){//Recupera as opções associadas à pergunta
                $id = $pergunta->getIdPergunta();
                $opcoes = $perguntaOpcaoDAO->buscarOpcao(array(),array("idPergunta"=>$idPergunta));
              }
              
              $opcaoDAO = new OpcaoDAO();//Remove as opções da pergunta atual, caso exista
              if(isset($opcoes) && !empty($opcoes)){
                foreach($opcoes as $opcao){
                  $opcaoDAO->remover(array("idOpcao"=>$opcao->getIdOpcao())); 
                }
              }
              $perguntaDAO->remover(array("idPergunta"=>$idPergunta));   
          }
        }
        echo json_encode(array("success"=>true));
      }else{
        throw new DadosCorrompidosException();
      }
    }catch(DadosCorrompidosException $e){
      echo json_encode(array("erro"=>"Ocorreu um erro,atualize a página e tente novamente","success"=>false));
    }catch(PesquisaJaExistenteException $e){
      echo json_encode(array("erro"=>"Já existe uma pesquisa com este título. Tente outro","success"=>false));
    }

  }

  private function perguntasPesquisa($idPesquisa){
    $perguntaPesquisaDAO = new PerguntaPesquisaDAO();
    $perguntaPesquisa = $perguntaPesquisaDAO->buscarPergunta(array("pergunta.idPergunta"),array("idPesquisa"=>$idPesquisa));

    $ids = array();
    foreach($perguntaPesquisa as $pergunta){
      $ids[] = $pergunta->getIdPergunta();
    }
    return $ids;
  }

  private function getOpcoesPergunta($idPergunta){
    $perguntaOpcaoDAO = new PerguntaOpcaoDAO();
    $perguntaOpcao = $perguntaOpcaoDAO->buscarOpcao(array("opcao.idOpcao"),array("idPergunta"=>$idPergunta));
    
    $ids = array();
    foreach($perguntaOpcao as $opcao){
      
      $ids[] = $opcao->getIdOpcao();
    }
    return $ids;
  }


  public function buscarAtiva(){
      $pesquisaDAO = new PesquisaDAO();
      $pesquisaDAO = $pesquisaDAO->buscar(array(),array("estaAtiva"=>1));//Busca uma pesquisa ativa
      $pesquisa;
      if(count($pesquisaDAO)>0){
        $pesquisa = $this->buscar(array($pesquisaDAO[0]->getIdPesquisa()));
      }
  }

  public function guardarResposta(){
    try{
      $json = $_POST['json'];
      $idPesquisa = $_POST['idPesquisa'];
      $dadosRecebidos = json_decode($json,true);
    
      $arrayAux = array();
      while(count($dadosRecebidos)>0){//Coloca todas as informações relevantes em um array só
        array_push($arrayAux,array_shift($dadosRecebidos)[0]);
      }

      $path = ABSPATH.'/media/pesquisas/';

      if(file_exists($path.'Respostas-IdPesquisa['.$idPesquisa.'].xml')){// Verifica se o XML já existe,caso exista adiciona as respostas ao arquivo
        $file = simplexml_load_file($path.'Respostas-IdPesquisa['.$idPesquisa.'].xml');
        $this->respostasParaXML($arrayAux,$file);
        $result = $file->asXML();
        file_put_contents($path.'Respostas-IdPesquisa['.$idPesquisa.'].xml',$result);
      }else{//Caso não exista, cria o arquivo
        $xml_data = new \SimpleXMLElement('<Pesquisa/>');
        $this->respostasParaXML($arrayAux,$xml_data);
        $result = $xml_data->asXML();
        file_put_contents($path.'Respostas-IdPesquisa['.$idPesquisa.'].xml',$result);
      }

      echo json_encode(array("success"=>true));
    }catch(Exception $e){
      echo json_encode(array("erro"=>"Ocorreu um erro,atualize a página e tente novamente","success"=>false));
    }
    
  }

  private function respostasParaXML($data,&$xml_data){
    foreach ($data as $key => $value) {
      if(is_numeric($key)){ 
          $key= 'pergunta'; 
      }
      if(is_array($value)){
        $subnode= $xml_data->addChild($key);
        $this->respostasParaXML($value, $subnode);
      }else{
          $xml_data->addChild($key, htmlspecialchars($value));
      }
    }
  }

  public function respostaPesquisa(){
    // $idPesquisa = $_POST['idPesquisa'];
    $idPesquisa = 2;
    $path = ABSPATH.'/media/pesquisas/';
    $nomeArquivo = $path.'Respostas-IdPesquisa['.$idPesquisa.'].xml';
    if(file_exists($nomeArquivo)){
      $file = simplexml_load_file($path.'Respostas-IdPesquisa['.$idPesquisa.'].xml');
      $c = $file->pergunta;
      
      $perguntasAbertas = array();
      $perguntasMultiplaEscolha = array();
      $perguntasUnicaEscolha = array();
      
      for($i=0;$i<count($c);$i++){ // Converte o XML para arrays
        if(strcmp($c[$i]->tipoPergunta,"ABERTA")==0){
          $perguntasAbertas[] = get_object_vars($c[$i]);
        }else if(strcmp($c[$i]->tipoPergunta ,"MULTIPLA ESCOLHA")==0){
          $opcoes = get_object_vars($c[$i]->opcoesSelecionadas)['pergunta']; //Guarda as opções do xml em forma de array
          $pergunta = get_object_vars($c[$i]);// Guarda os dados do xml
          unset($pergunta['opcoesSelecionadas']); //Remove as opções no formato xml
          $pergunta['opcoesSelecionadas'] = $opcoes; //Adiciona ao array as informações das opções
          $perguntasMultiplaEscolha[]=$pergunta;//Adiciona ao array com todas as perguntas de múltipla escolha
        }else if(strcmp($c[$i]->tipoPergunta,"UNICA ESCOLHA")==0){
          $perguntasUnicaEscolha[] = get_object_vars($c[$i]);
        }
      }

      
      $UnicaEscolhaPorId = array();
      while(count($perguntasUnicaEscolha)>0){
        $atual = array_shift($perguntasUnicaEscolha);
        $chave = 'idPergunta='.$atual['idPergunta'];
        if(array_key_exists($chave,$UnicaEscolhaPorId)){
          array_push($UnicaEscolhaPorId[$chave],$atual);
        }else{
          $UnicaEscolhaPorId[$chave][0] = $atual;
        }
      }

      $UnicaEscolhaPercentual = array();
      foreach($UnicaEscolhaPorId as $pergunta){
        foreach($pergunta as $resposta){
          $chave = 'idPergunta='.$resposta['idPergunta'].'&opcao='.$resposta['opcaoSelecionada'];
          if(array_key_exists($chave,$UnicaEscolhaPercentual)){
            $UnicaEscolhaPercentual[$chave]->addOcorrencias();
          }else{
            $UnicaEscolhaPercentual[$chave] = new PercentualOpcoes($resposta['idPergunta'],$resposta['tituloPergunta'],
                                                                   $resposta['tipoPergunta'],$resposta['opcaoSelecionada']);
          }
        }
      }
      var_dump($UnicaEscolhaPercentual);
    }
  }
}

?>