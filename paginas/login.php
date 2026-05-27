<?php
include("../includes/config.php");

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $senha = trim($_POST["senha"] ?? "");

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($senha === $user["senha"]) {
            $_SESSION["usuario"] = $user["id"];
            header("Location: menu.php");
            exit;
        }

        $erro = "Senha incorreta.";
    } else {
        $erro = "Usuário não encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<header class="navbar">
    <div class="logo"><img src="../assets/img/logo.jpg" class="logo-img" alt="Logo WebDog"><span>WebDog</span></div>
    <nav>
        <?php if ($usuarioLogadoNome !== ""): ?>
            <span class="user-name">Olá, <?= e($usuarioLogadoNome) ?></span>
        <?php endif; ?>
        <a href="../index.php">Início</a>
        <a href="listar_pets.php">Feed de pets</a>
    </nav>
</header>

<main class="login-page">
    <img src="../assets/img/banner.jpg" class="login-bg" alt="Cães felizes">

    <section class="login-content">
        <div class="login-copy">
<h1>Entre para gerenciar adoções com mais segurança.</h1>
            <p>Acesse o feed, cadastre pets para adoção, edite informações e acompanhe tudo em um ambiente organizado.</p>

            <div class="login-benefits">
                <div>
                    <strong>Feed completo</strong>
                    <span>Pesquise por tipo, raça, idade e doador.</span>
                </div>
                <div>
                    <strong>Cadastro rápido</strong>
                    <span>Publique fotos e descrições dos pets disponíveis.</span>
                </div>
                <div>
                    <strong>Adoção responsável</strong>
                    <span>Fluxo claro para acompanhar cada etapa da adoção.</span>
                </div>
            </div>
        </div>

        <div class="login-box">
            <div class="login-box-header">
                <h2>Entrar</h2>
                <p>Use seu email e senha cadastrados.</p>
            </div>

            <?php if ($erro !== ""): ?>
                <div class="alerta"><?= e($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" placeholder="seuemail@exemplo.com" required>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <input id="senha" type="password" name="senha" placeholder="Digite sua senha" required>
                </div>

                <button type="submit" class="btn">Entrar</button>
            </form>

            <div class="login-footer">
                <span>Ainda não tem conta?</span>
                <a class="form-link" href="cadastro_usuario.php">Criar conta</a>
                <a class="form-link muted-link" href="termos.php">Termos e condições</a>
            </div>
        </div>
    </section>
</main>

</body>
</html>













