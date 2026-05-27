<?php
include("../includes/config.php");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos e condições - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/termos.css">
</head>
<body>

<header class="navbar">
    <div class="logo"><img src="../assets/img/logo.jpg" class="logo-img" alt="Logo WebDog"><span>WebDog</span></div>
    <nav>
        <?php if ($usuarioLogadoNome !== ""): ?>
            <span class="user-name">Olá, <?= e($usuarioLogadoNome) ?></span>
        <?php endif; ?>
        <a href="../index.php">Início</a>
        <a href="login.php">Entrar</a>
    </nav>
</header>

<main class="page-shell terms-page">
    <div class="page-title">
        <div>
            <h1>Termos e condições</h1>
            <p>Regras básicas para manter o WebDog seguro, transparente e focado em adoção responsável.</p>
        </div>
    </div>

    <section class="details-panel terms-content">
        <h2>1. Uso da plataforma</h2>
        <p>O WebDog aproxima pessoas interessadas em adoção de usuários que cadastram pets disponíveis. As informações publicadas devem ser verdadeiras, claras e atualizadas.</p>

        <h2>2. Responsabilidade do doador</h2>
        <p>O usuário que cadastra um pet confirma que possui autorização para divulgar o animal e se compromete a informar idade, raça, descrição e imagem de forma honesta.</p>

        <h2>3. Responsabilidade do adotante</h2>
        <p>Antes de adotar, o interessado deve avaliar se possui condições de oferecer cuidado, alimentação, segurança, vacinação e acompanhamento veterinário.</p>

        <h2>4. Conteúdo proibido</h2>
        <p>Não é permitido publicar informações falsas, imagens inadequadas, anúncios de venda de animais ou dados de terceiros sem autorização.</p>

        <h2>5. Privacidade</h2>
        <p>Os dados cadastrados são usados para identificação de usuários e organização dos anúncios de adoção. Não compartilhe senhas ou informações sensíveis em descrições públicas.</p>

        <h2>6. Uso responsável</h2>
        <p>Ao criar uma conta, o usuário declara que compreende as regras de uso. O WebDog pode remover cadastros que descumpram essas condições.</p>
    </section>
</main>

</body>
</html>

