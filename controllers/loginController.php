<?php
namespace controllers;
require_once dirname(__DIR__).'/vendor/autoload.php';
use util\GerenciarSenha as GerenciarSenha;
use \DAO\usuarioDAO as usuarioDAO;
use \DAO\FuncionarioDAO as FuncionarioDAO;
use \models\Usuario as Usuario;
use \models\Funcionario as Funcionario;
use \models\Administrador as Administrador;
use \util\ValidacaoDados as ValidacaoDados;
use exceptions\UsuarioInexistenteException as UsuarioInexistenteException;
use exceptions\SenhaInvalidaException as SenhaInvalidaException;
use exceptions\EmailInvalidoException as EmailInvalidoException;
use exceptions\EmailExternoException as EmailExternoException;
use exceptions\AcessoExternoNegadoException as AcessoExternoNegadoException;
if(!isset($_SESSION)){
    session_start();
}

class loginController extends mainController{
    protected $dados = array();

    /**
    * Configura a classe para realização de teste.
     * @param String $email email do usuário
     * @param String $senha senha do usuário
     * @param String $novaSenha nova senha para o caso de redefinição
     */
    public function configuraAmbienteParaTeste($email, $senha, $novaSenha, $id) {
        $_POST = array("email" => $email,
        "senha" => isset($novaSenha)?$novaSenha:$senha,
        "confirmarSenha" => isset($novaSenha)?$novaSenha:$senha);

        $_GET = array("i" => $id,
        "e" => $email);

        $ds = DIRECTORY_SEPARATOR;
	    $pasta = explode($ds,getcwd());
	    $pasta = end($pasta);

        if(!defined('ABSPATH')) {
            define('ABSPATH', dirname(dirname( __FILE__ )));
        }

        if(!defined('ROOT_URL')) {
            define('ROOT_URL',"http://".'localhost'."/".$pasta."/");
        }

        if(!isset($_SERVER["SERVER_NAME"])) {
            $_SERVER["SERVER_NAME"] = "localhost";
        }

        if(!isset($_SERVER["HTTP_HOST"])) {
            $_SERVER["HTTP_HOST"] = 'HTTP_HOST';
        }

        if(!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER["REQUEST_URI"] = "REQUEST_URI";
        }

        if(!defined('URI_BASE')) {
            define('URI_BASE',"http://".$_SERVER['SERVER_NAME']."/".$pasta."/index.php");
        }

        if(!defined('VIEW_BASE')) {
            define("VIEW_BASE", "http://".$_SERVER['SERVER_NAME']."/".$pasta."/views/");
        }
    }

    public function getDados() {
        return $this->dados;
    }

    //Login do usuário
    public function index(){
        if(!isset($_SESSION['nome']) || empty($_SESSION['nome'])){
            if (ValidacaoDados::validarForm($_POST, array("email","senha"))) {
                try{
                    $email = addslashes($_POST["email"]);
                    $senha = $_POST["senha"];

                    if (!ValidacaoDados::validarSenha($senha)) {
                        throw new SenhaInvalidaException('A senha deve conter entre 8 e 32 caracteres.');
                    } 
                    if (!ValidacaoDados::validarEmail($email)) {
                        throw new EmailInvalidoException();
                    }

                    $senha = GerenciarSenha::criptografarSenha($_POST["senha"]);
                    $usuario = $this->login($email, $senha);
                    
                    if($usuario->confirmouCadastro()){
                        $this->redirecionarPagina('home');
                    }else{
                        $this->redirecionarPagina('login');//LEMBRAR DE MUDAR
                    }
                }catch(UsuarioInexistenteException $e){
                    $this->dados['exception'] = $e->getMessage();  
                }catch(SenhaInvalidaException $e){
                    $this->dados['exception'] = $e->getMessage();
                }catch(EmailInvalidoException $e){
                    $this->dados['exception'] = $e->getMessage();
                }
            }
            $this->carregarConteudo('login',$this->dados);
        }else{
            $this->redirecionarPagina('home');
        }
    }


    /**
    * Realiza o login do usuário
    * @param String $email, $senha
    */
    private function login($email, $senha){
        //Crio dois arrays para usar na busca do usuario. 
        $campos = array();
        $filtro = array(
            "email" => $email,
            "senha" => $senha,
        );

        $usuarioDAO = new UsuarioDAO();
        $usuario = $usuarioDAO->buscar($campos, $filtro);//Recebe o objeto do usuario que vai logar

        if(count($usuario) == 1){ //Verifica se existe usuario
        //Inicia uma sessão e guarda os dados para persistirem ao longo da execução do sistema            
            if($usuario[0]->confirmouCadastro()){
                if(!isset($_SESSION)){
                    session_start();
                }
                
                $this->setarSession($usuario[0]);
            }

           return $usuario[0];

        } else {
            throw new UsuarioInexistenteException();
        } 
    }


    /**
    * Realiza a autenticação via Google+
    **/
    public function acessoGoogle(){
        require_once (ABSPATH.'/vendor/credentialsConfig.php');

        // $service implements the client interface, has to be set before auth call
        $service = new \Google_Service_Plus($client);

        if (isset($_GET['code'])) { // we received the positive auth callback, get the token and store it in session
            $client->authenticate($_GET['code']);
            $_SESSION['token'] = $client->getAccessToken();
        }

        if (isset($_SESSION['token'])) { // extract token from session and configure client
            $token = $_SESSION['token'];
            $client->setAccessToken($token);
        }

        if (!$client->getAccessToken()) { // auth call to google
            $authUrl = $client->createAuthUrl();
            header("Location: ".$authUrl);
            die;
        }
        $me = $service->people->get('me');
        $filtros = array("idUsuarioGoogle"=>$me['id']);

        $usuarioDao = new UsuarioDAO();
        $usuario = $usuarioDao->buscarUsuarioContaExterna(array("idUsuarioGoogle"),array("idUsuario","nome","sobrenome","email","cadastroConfirmado","tipoUsuario"),$filtros,"google");
        
        if(count($usuario)>0){//Se o usuário estiver cadastrado...
            $_SESSION = array();//Limpa os dados de token
            $this->setarSession($usuario[0], 'google');

            //Redireciona para a home configurada como de usuário
        }else{
            $cadastro = new cadastroController();
            $cadastro->cadastrarUsuarioGoogle($me);
            //Redireciona para página confirmando cadastro
            $_SESSION = array();//Limpa os dados de token
            $_SESSION['id'] = $me['id'];
            $_SESSION['nome'] = $me['modelData']['name']['givenName'];
            $_SESSION['sobrenome'] = $me['modelData']['name']['familyName'];
            $_SESSION['email'] = $me['modelData']['emails'][0]['value'];
            $_SESSION['redeSocial'] = 'google';
        }
        $this->redirecionarPagina('home');
    }

    /**
    * Realiza a autenticação do login via Facebook.
    **/
    public function acessoFacebook() {
        require_once ABSPATH. '/php-graph-sdk-5.4/src/Facebook/autoload.php';  
        $fb = new \Facebook\Facebook([
        'app_id' => '1435160229855766',
        'app_secret' => 'fa696e39b476a2c926ff6f2fa080532d',
        'default_graph_version' => 'v2.9',
        ]);

        $helper = $fb->getRedirectLoginHelper();
        if (isset($_GET['state'])) {
            $helper->getPersistentDataHandler()->set('state', $_GET['state']);
        }

        try {
            $accessToken = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            throw new AcessoExternoErroException($e->getSubErrorCode());
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            throw new AcessoExternoErroException('SDK');
            exit;
        }

        if (! isset($accessToken)) {
            throw new AcessoExternoNegadoException();
            exit;
        }

        // Logged in
        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId($tokenMetadata->getAppId());
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();


        if (! $accessToken->isLongLived()) {
        
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                throw new AcessoExternoErroException('SDK_LONG_LIVED_ACESS_TOKEN');
                exit;
            }
        }

        $_SESSION['fb_access_token'] = (string) $accessToken;

        $response = $fb->get('/me?fields=first_name,last_name,email', $accessToken);
        $graph = $response->getGraphUser();
        $filtros = array("idUsuarioFacebook"=>$graph->getId());

        $usuarioDao = new UsuarioDAO();
        $usuario = $usuarioDao->buscarUsuarioContaExterna(array("idUsuarioFacebook"),array("idUsuario","nome","sobrenome","email","cadastroConfirmado","tipoUsuario"),$filtros,"facebook");
        $usuarioInt = $usuarioDao->buscar(array(), array("email" => $graph->getEmail()));

        if(isset($usuarioInt[0]) && $usuarioInt[0]->getSenha() != null) {
            $this->dados['exception'] = 'O email da sua conta já está cadastrado no sistema.';
            $this->carregarConteudo("login",$this->dados);
        } else {
            if(count($usuario)>0){//Se o usuário estiver cadastrado...
                $usuario = $usuario[0];
                $_SESSION = array();//Limpa os dados de token
                $this->setarSession($usuario, 'facebook');
                //Falta redirecionar usuário
            }else{
                $cadastro = new cadastroController();
                $cadastro->cadastrarUsuarioFacebook([
                    'fb_id' => $graph->getId(),
                    'nome' => $graph->getFirstName(),
                    'sobrenome' => $graph->getLastName(),
                    'email' => $graph->getEmail()
                ]);

                $_SESSION = array();//Limpa os dados de token
                $_SESSION['id'] = $graph->getId();
                $_SESSION['nome'] = $graph->getFirstName();
                $_SESSION['sobrenome'] = $graph->getLastName();
                $_SESSION['email'] = $graph->getEmail();
                $_SESSION['redeSocial'] = 'facebook';
            }
            $this->redirecionarPagina('home');              
        }
    }

    /**
    * Realiza o logout do usuário
    */
    public function logout(){
        // Inicializa a sessão.
       
        // Apaga todas as variáveis da sessão
        $_SESSION = array();
        // Se é preciso matar a sessão, então os cookies de sessão também devem ser apagados.
        // Isto destruirá a sessão, e não apenas os dados!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        // Por último, destrói a sessão
        session_destroy();
        $this->redirecionarPagina('home');
    }


    /**
    *Envia o email para a redefinição de senha.
    */
    public function recuperar() {
        try{
            if(!isset($_SESSION['nome']) || empty($_SESSION['nome'])){
                if (ValidacaoDados::validarForm($_POST,array("email"))) {
                
                    $email = addslashes($_POST["email"]); //Recebe o endereço de e-mail digitado pelo usuário.

                    $usuarioDao = new UsuarioDAO();
                    $usuario = $usuarioDao->buscar(array(), array("email"=>$email)); //Busca o usuário desejado.

                    if ($usuario == null) { //Verifica se o usuário existe.
                        throw new UsuarioInexistenteException(); //Caso não exista, lança uma exceção.
                    }
                    if($usuario[0]->getSenha()==="" || empty($usuario[0]->getSenha())){
                        throw new RecuperacaoInvalidaException();
                    }
                    $nome = $usuario[0]->getNome();
                    require(ABSPATH.'/plugins/PHPMailer/PHPMailerAutoload.php');

                    $id = $usuario[0]->getId(); //Recebe o ID do usuário encontrado.
                    
                    $linkRedefinir = ROOT_URL."login/redefinir/?e=".md5($email)."&i=".$id; //Gera um link composto pelas informações do usuário.

                    $mail = new \PHPMailer();

                    $mail->isSMTP();                                      // Set mailer to use SMTP
                    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true;                               // Enable SMTP authentication
                    $mail->Username = 'websertour@gmail.com';                 // SMTP username
                    $mail->Password = 'sertourweb';                           // SMTP password
                    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = 587;                                    // TCP port to connect to
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('websertour@websertour.com', 'Sertour');
                    $mail->addAddress($email, $nome);     // Add a recipient
                    $mail->addReplyTo('noreply@gmail.com', 'Não responda');

                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->Subject = 'Sertour - Redefinição de Senha';
                    $mail->Body    = "Olá, ".$nome.". Você solicitou uma redefinição de senha.<br/><br/>"
                                    ."Por favor, clique no link abaixo e insira sua nova senha: <br/><br/>".
                                    "Link de Redefinição: ".$linkRedefinir;

                    if (!$mail->send()) {
                        throw new EmailNaoEnviadoException();
                    }

                $this->dados['redefinido'] = "Um email de recuperação foi enviado para ".$email.' !';
                
                }
            }else{
                $this->redirecionarPagina('home');
            }
        }catch(EmailNaoEnviadoException $e){
            $this->dados['exception'] = $e->getMessage();
        }catch(DadosCorrompidosException $e){
            $this->dados['exception'] = $e->getMessage();
        }catch(UsuarioInexistenteException $e){
            $this->dados['exception'] = "Não existe usuário cadastrado com esse email";
        }catch(RecuperacaoInvalidaException $e){
            $this->dados['exception'] = $e->getMessage();
        }
        $this->carregarConteudo('pedidoRedefinicao',$this->dados);
    }

    /**
    *Redefine a senha.
    */
    public function redefinir() {
        if(!isset($_SESSION['nome']) || empty($_SESSION['nome'])){
            if(ValidacaoDados::validarForm($_POST,array("senha","confirmarSenha"))) {
                try{
                    $novaSenha = GerenciarSenha::criptografarSenha($_POST["senha"]); //Recebe a nova senha do usuário.
                    $confirmarSenha = GerenciarSenha::criptografarSenha($_POST["confirmarSenha"]); //Recebe a confirmação de senha.

                    if (!ValidacaoDados::validarSenha($novaSenha) || !ValidacaoDados::validarSenha($confirmarSenha) ) { //Verifica se a nova senha digitada é válida.
                        throw new SenhaInvalidaException(); //Caso não seja, lança uma exceção.
                    } else if($novaSenha != $confirmarSenha) { //Verifica se a senha e a confirmação são iguais.
                        throw new SenhaInconsistenteException(); //Caso não seja, lança uma exceção.
                    }
            
                    $id = $_GET['i']; //Obtém o ID a partir da URL.
                    $email = $_GET['e']; //Obtém o e-mail a partir da URL.

                    $usuarioDao = new UsuarioDAO();
                    $usuarioDao->alterar(array("senha"=>$novaSenha), array("idUsuario"=>$id)); //Altera a senha do usuário desejado. 
                    $this->dados['redefinido'] = "Sua senha foi redefinida, agora pode efetuar seu login";
                }catch(SenhaInvalidaException $e){
                    $this->dados['exception'] = $e->getMessage();
                }catch(SenhaInconsistenteException $e){
                    $this->dados['exception'] = $e->getMessage();
                }
            }

            $this->carregarConteudo("redefinicaoSenha",$this->dados);
        }
    }

    /**
    * Configura a variável $_SESSION com os valores adequados referentes ao usuário que logou.
    * @param Usuario $usuario - objeto usuário que logou
    * @param String $redeSocial - nome da rede social utilizada para cadastrado, caso tenha sido utilizada alguma
    */
    private function setarSession($usuario, $redeSocial = null) {
        
        if($redeSocial == 'facebook'){ //verifica se o usuário está associado a alguma conta externa
            $_SESSION['redeSocial'] = $redeSocial;
        }else if($redeSocial == 'google') {
            $_SESSION['redeSocial'] = $redeSocial;
        }

        //define os valores básicos de SESSION
        $_SESSION['id'] = $usuario->getId();
        $_SESSION['nome'] = $usuario->getNome();
        $_SESSION['sobrenome'] = $usuario->getSobrenome();
        $_SESSION['email'] = $usuario->getEmail();
        $_SESSION['tipoUsuario'] = $usuario->getTipo();
        $_SESSION['confirmouCadastro'] = $usuario->confirmouCadastro();

        if($usuario->getTipo() == "FUNCIONARIO") { //se o usuário for funcionário
            $funcionarioDAO = new FuncionarioDAO();
            $funcionario = $funcionarioDAO->buscar(array(), array('idUsuario'=>$usuario->getId()));

            if(count($funcionario) > 0) {
                $_SESSION['podeCadastrarObra'] = $funcionario[0]->isPodeCadastrarObra();
                $_SESSION['podeGerenciarObra'] = $funcionario[0]->isPodeGerenciarObra();
                $_SESSION['podeRemoverObra'] = $funcionario[0]->isPodeRemoverObra();
                $_SESSION['podeCadastrarNoticia'] = $funcionario[0]->isPodeCadastrarNoticia();
                $_SESSION['podeGerenciarNoticia'] = $funcionario[0]->isPodeGerenciarNoticia();
                $_SESSION['podeRemoverNoticia'] = $funcionario[0]->isPodeRemoverNoticia();
                $_SESSION['podeRealizarBackup'] = $funcionario[0]->isPodeRealizarBackup(); 
            } else {
                throw new UsuarioInexistenteException();
            }
            var_dump($_SESSION);
        } else if($usuario->getTipo() == "ADMINISTRADOR") { //se o usuário for o admininistrador
                $_SESSION['podeCadastrarObra'] = true;
                $_SESSION['podeGerenciarObra'] = true;
                $_SESSION['podeRemoverObra'] = true;
                $_SESSION['podeCadastrarNoticia'] = true;
                $_SESSION['podeGerenciarNoticia'] = true;
                $_SESSION['podeRemoverNoticia'] = true;
                $_SESSION['podeRealizarBackup'] = true;
                $_SESSION['administrador'] = true;
        }
    }       
}
?>