<?php session_start()?>
<div class="container">
      <div class="row">
          <div class="col-md-1 col-sm-1"></div>
          <div class="col-xs-12 col-md-11">
               <h2><i class="fa fa-bars"></i> Sertour</h2>
          </div>

      </div>

      <div class="row">
          <div class="col-xs-12 visible-xs-block" >
              
                <?php
                    if(isset($_SESSION['nome']) && !empty($_SESSION['nome'])){
                        echo '<span style="float:right" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <b>Olá, '.$_SESSION['nome'].'! <span class="caret"></span></b>
                              </span>
                                <br>
                                <br>';
                        echo '<ul class="dropdown-menu pull-right">
                                <li><a href="#">Gerenciar Conta</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="'.ROOT_URL.'login/logout">Sair</a></li>
                              </ul>';
                    }else{
                       echo '<center><span><a href="'.ROOT_URL.'login"><strong>Fazer Login</strong></a></span> ou <span><a href="'.ROOT_URL.'cadastro"><strong> Cadastre-se!</strong></a></span></center><br>';     
                    }
                ?>   
          </div>
      </div>

      <div class="row">
          
          <div class="col-xs-12 visible-xs-block">
                <div class="flex">
                  <span class="item-cabecalho">
                      <i class="fa fa-home" aria-hidden="true"></i>
                      <a href="#"> Home</a>
                  </span>

                  <span class="item-cabecalho">
                      <i class="fa fa-sign-in" aria-hidden="true"></i>
                      <a href="#"> Explorar </a>
                  </span>
                  
                  <span class="item-cabecalho">
                      <i class="fa fa-info-circle" aria-hidden="true"></i>
                      <a href="#"> Sobre </a>
                  </span>
                    
                    <?php
                        if(isset($_SESSION['tipo_usuario']) && !empty($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario']){
                            if($_SESSION['tipo_usuario'] == 'Administrador' || $_SESSION['tipo_usuario'] == 'Funcionario'){
                                echo '<span class="item-cabecalho">
                                      <i class="fa fa-cogs" aria-hidden="true"></i>
                                      <a href="#"> Admin </a>
                                      </span>';
                            }
                        }                             
                    ?>
                </div>
                
          </div>
          

          <div class="col-md-1 hidden-xs"></div>
          <div class="col-md-6 col-sm-8 hidden-xs">
              <div class="flex">
                  <span class="item-cabecalho">
                      <i class="fa fa-home" aria-hidden="true"></i>
                      <a href="#"> Home</a>
                  </span>

                  <span class="item-cabecalho">
                      <i class="fa fa-sign-in" aria-hidden="true"></i>
                      <a href="#"> Explorar </a>
                  </span>
                  
                  <span class="item-cabecalho">
                      <i class="fa fa-info-circle" aria-hidden="true"></i>
                      <a href="#"> Sobre </a>
                  </span>

                  <?php
                        if(isset($_SESSION['tipo_usuario']) && !empty($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario']){
                            if($_SESSION['tipo_usuario'] == 'Administrador' || $_SESSION['tipo_usuario'] == 'Funcionario'){
                                echo '<span class="item-cabecalho">
                                      <i class="fa fa-cogs" aria-hidden="true"></i>
                                      <a href="#">Painel Administrativo </a>
                                      </span>';
                            }
                        }
                  ?>

                </div>
          </div>
          <div class="col-sm-0 col-md-2 hidden-xs"></div>
          <div class="col-sm-4 col-md-3 hidden-xs">
                <?php
                    if(isset($_SESSION['nome']) && !empty($_SESSION['nome'])){
                        echo '<span style="float:right" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <b>Olá, '.$_SESSION['nome'].'! <span class="caret"></span></b>
                              </span>
                                <br>
                                <br>';
                        echo '<ul class="dropdown-menu pull-right">
                                <li><a href="#">Gerenciar Conta</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="'.ROOT_URL.'login/logout">Sair</a></li>
                              </ul>';
                    }else{
                       echo '<center><span><a href="'.ROOT_URL.'login"><strong>Fazer Login</strong></a></span> ou <span><a href="'.ROOT_URL.'cadastro"><strong> Cadastre-se!</strong></a></span></center><br>';     
                    }
                ?> 
                
          </div>
      </div>
      <hr>
  </div>
  