<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Nova Instância</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background:#f6f7fb; }
        .box { background:#fff; padding:20px; border-radius:10px; max-width:700px; margin:auto; }
        label { display:block; margin-top:12px; }
        input { width:100%; padding:10px; margin-top:5px; }
        .msg { padding:10px; border-radius:8px; margin-bottom:10px; background:#ffecec; }
        .actions { margin-top:15px; display:flex; gap:10px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Cadastrar nova instância</h2>

    <?php if(session('error')): ?>
        <div class="msg"><?= e(session('error')) ?></div>
    <?php endif; ?>

    <form method="POST" action="/dispara/public/instancias">
        @csrf

        <label>Label (apelido)</label>
        <input type="text" name="label" value="<?= e(old('label')) ?>" placeholder="Ex: WhatsApp Loja Centro">

        <label>Instance name (Evolution)</label>
        <input type="text" name="instance_name" value="<?= e(old('instance_name')) ?>" placeholder="Ex: loja-centro" required>

        <label>Limite por dia (máx 200)</label>
        <input type="number" name="daily_limit" value="<?= e(old('daily_limit', 200)) ?>" min="1" max="200" required>

        <div class="actions">
            <button type="submit">Salvar</button>
            <a href="/dispara/public/instancias">Voltar</a>
        </div>
    </form>
</div>
</body>
</html>
