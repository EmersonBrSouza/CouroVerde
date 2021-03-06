<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<meta name="description" content="Setour" />
	<meta name=viewport content="width=device-width, initial-scale=1, user-scalable=no" />
	<title>SERTOUR - Cadastro</title>
    <link rel="icon" href="../sertour/views/assets/images/icone.ico" type="image/x-icon"/>    

	<?php $this->carregarDependencias()?>

	<script type="text/javascript" src=<?php $this->path("assets/js/validator.js");?>></script>
	<script type="text/javascript" src=<?php $this->path('assets/js/login-script.js');?>></script>

</head>

<body style="background-color: #ffcc80;">
	<a href =<?php echo ROOT_URL?>>
	<div class="text-center"><img src=<?php $this->path('../views/assets/images/logo-header.png')?>></div></a>
	<div id="container-reg" style="background-color: #ffcc80;"> 
		<section id="caixa-registro" style="background-color: #FFF;">
			<h4 class="text-center" style="margin-bottom:16%;">Inscreva-se. É grátis!</h4>
			<!-- Form do cadastro -->
			<form method="POST" data-toggle="validator">
					<div class="row">
						<div class="form-group col-xs-6">
							<input type="text" class="form-control" id="nome" name="nome"required placeholder="Nome"/>
						</div>
						<div class="form-group col-xs-6">
							<input type="text" class="form-control" id="sobrenome" name="sobrenome"required placeholder="Sobrenome"/>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-xs-12">
							<input type="email" class="form-control" id="email" name="email" required placeholder="Email"/>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-xs-12">
							<!-- <input type="password" class="form-control" id="senha" name="senha" required placeholder="Senha"/> -->
							<input id="senha" type="password" name="senha" class="form-control" pattern=".{8,32}" placeholder="Senha" required title="Insira uma senha de 8 a 32 caracteres.">	
						</div>
					</div>
				
					<div class="row">
						<div class="form-group">
							<div class="col-md-4"></div>
							<div class="col-md-4">
								<button type="submit" class="btn btn-primary">Cadastrar</button>
								<!--ESSE TRECHO AQUI VAI MOSTRAR UMA EXCEPTION DO BACK-END TU VÊ ONDE COLOCA AÍ-->
							</div>
							<div class="col-md-4"></div>
						</div> 
					</div>
					<?php if(isset($this->dados['exception']) && !empty($this->dados['exception'])){
									echo '<br><center><span class="text-danger">'.$this->dados['exception'].'</span></center><br>';
								}
							?>
					<center><span style="margin-top: 16%;">Ou entre com:</span></center>

					<!-- Botões sociais -->
					<div class="center" style="margin: 0 auto; width:43%">

						<a class="btn btn-social-icon btn-lg btn-facebook " id='btn-facebook'>
							<span class = "fa fa-facebook" style="background-color:#3b5998;"> </span>
						</a>

						<img src=<?php $this->path('assets/images/divisor.jpg')?>>

						<a class="btn btn-social-icon btn-lg btn-google-plus " href=<?php echo "http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'].'/acessoGoogle';?>>
							<span class = "fa fa-google-plus"  style="background-color:rgb(223,75,55);"> </span>
						</a>

              		</div>


					
					
				</form>
		</section>
	</div>
	<?php $this->carregarRodape()?>
</body>

</html>