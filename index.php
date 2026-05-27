<?php
include("includes/config.php");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebDog</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <div class="logo"><img src="assets/img/logo.jpg" class="logo-img" alt="Logo WebDog"><span>WebDog</span></div>
    <nav>
        <?php if ($usuarioLogadoNome !== ""): ?>
            <span class="user-name">Olá, <?= e($usuarioLogadoNome) ?></span>
        <?php endif; ?>
        <a href="#sobre">Sobre</a>
        <a href="paginas/listar_pets.php">Feed de pets</a>
        <?php if ($usuarioLogadoNome !== ""): ?>
            <a href="paginas/adocoes.php">Minhas adoções</a>
            <a href="acoes/logout.php">Sair</a>
        <?php else: ?>
            <a href="paginas/login.php" class="btn">Entrar</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <img src="assets/img/banner.jpg" class="hero-img" alt="Pessoa com cachorro em ambiente claro">

    <div class="hero-text">
        <h1>Adote com clareza, cuidado e confiança</h1>
        <p>Uma plataforma organizada para encontrar pets disponíveis, comparar informações importantes e apoiar uma adoção responsável.</p>
        <div class="hero-actions">
            <a href="paginas/listar_pets.php" class="btn accent">Explorar pets</a>
            <a href="paginas/cadastro_usuario.php" class="btn secondary">Criar conta</a>
        </div>
    </div>
</section>

<section id="sobre" class="section">
    <div class="section-inner">
        <h2>Uma experiência feita para decidir melhor</h2>
        <p>
            O WebDog conecta doadores e adotantes com um fluxo direto: cadastre, filtre, veja detalhes e avance com responsabilidade.
        </p>

        <div class="split">
            <div class="info-card">
                <strong>Feed pesquisável</strong>
                Encontre pets por nome, tipo, raça, idade, doador ou descrição.
            </div>
            <div class="info-card">
                <strong>Informação organizada</strong>
                Cards claros, detalhes completos e fotos em destaque para comparar melhor.
            </div>
            <div class="info-card">
                <strong>Adoção responsável</strong>
                Informações claras ajudam adotantes e doadores a tomarem decisões melhores.
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <h2>Pronto para encontrar um novo companheiro?</h2>
    <a href="paginas/cadastro_usuario.php" class="btn accent">Começar agora</a>
</section>

<footer id="contato">
    <p>© 2026 WebDog - Todos os direitos reservados · <a href="paginas/termos.php">Termos e condições</a></p>
</footer>

</body>
</html>


