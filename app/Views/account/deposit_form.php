
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">

<link rel="stylesheet" href="<?= base_url('css/bank.css') ?>">

<div class="container">
    <?php if(session()->get('message')): ?><div class="notification success"><?= esc(session()->get('message')) ?></div><?php endif; ?>
    <?php if(session()->get('error')): ?><div class="notification error"><?= esc(session()->get('error')) ?></div><?php endif; ?>
    <div class="card">
        <div class="header">
            <h2 style="margin:0">Cuenta Bancaria</h2>
            <div>
                <form action="<?= site_url('/cerrar_sesion') ?>" method="get" style="display:inline-block;margin:0">
                        <button type="submit" class="secondary">Cerrar sesión</button>
                </form>
            </div>
        </div>

        <?php if(isset($user) && ! empty($user['nombre'])): ?>
                <div class="user-info"><p>Usuario: <?= esc($user['nombre']) . ' ' . esc($user['apellido']) ?> (<?= esc($user['username']) ?>)</p></div>
        <?php endif; ?>

        <div class="balance">Saldo actual: <?= number_format($balance, 2) ?></div>

        <form action="<?= site_url('bank/deposit') ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-row">
                        <label>Monto a depositar:</label>
                        <input type="number" step="0.01" name="amount" required>
                </div>
                <div class="form-row">
                        <button type="submit">Depositar</button>
                </div>
        </form>
    </div>
</div>
