<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<meta name="description" content="Setour" />
	<meta name=viewport content="width=device-width, initial-scale=1" />


	<link rel="stylesheet" href="assets/css/datepicker.css">
	<link rel="stylesheet" href="assets/css/bootstrap.css">
	<script src="assets/js/jquery-3.2.1.min.js"></script>
	<script src="assets/js/bootstrap-datepicker.js"></script>
	<script>
		$(function () {
			$('.datepicker').datepicker({
				format: 'dd/mm/yyyy',
			});
		});
	</script>

	<title>Registro</title>
	<link rel="stylesheet" href="assets/css/bootstrap-theme.css">
	<link rel="stylesheet" href="assets/css/estilo.css">
	<link rel="stylesheet" href="assets/css/bootstrap-social.css">
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<h2>Inscreva-se. É simples </h2>
				<form action="" method="">
					<div class="form-group">
						<label for="nome">Nome Completo</label>
						<input type="text" class="form-control" id="nome" required />
					</div>
					<div class="form-group">
						<label for="email">Email</label>
						<input type="email" class="form-control" id="email" required/>
					</div>
					<div class="form-group">
						<label for="senha">Senha</label>
						<input type="password" class="form-control" id="senha" required/>
					</div>
					<div class="form-group">
						<label for="date">Aniversário</label>
						<input class="datepicker" type="text" id="date" required>
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-default btn-md center">Registrar</button>
				</form>
			</div>
		</div>
	</div>

	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>	
</body>

</html>