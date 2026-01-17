<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body>
<div class="container">
  <div class="card" style="max-width:400px;margin:0 auto">
    <h2 style="margin-top:0">Iniciar sesión</h2>
    <form action="/login/autenticar" method="post">
        <div class="form-row">
            <label for="username">Usuario</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div class="form-row">
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-row">
            <button type="submit">Entrar</button>
        </div>
    </form>
  </div>
</div>
</body>
</html>