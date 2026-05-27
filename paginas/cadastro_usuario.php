<?php
include("../includes/config.php");

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = trim($_POST["senha"] ?? "");

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $erro = "Email já cadastrado.";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $senha);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        }

        $erro = "Erro ao cadastrar.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/cadastro.css">
</head>
<body>

<header class="navbar">
    <div class="logo"><img src="../assets/img/logo.jpg" class="logo-img" alt="Logo WebDog"><span>WebDog</span></div>
    <nav>
        <?php if ($usuarioLogadoNome !== ""): ?>
            <span class="user-name">Olá, <?= e($usuarioLogadoNome) ?></span>
        <?php endif; ?>
        <a href="../index.php">Início</a>
        <a href="login.php">Login</a>
    </nav>
</header>

<main class="form-container">
    <div class="form-box">
        <h2>Criar conta</h2>

        <?php if ($erro): ?>
            <div class="alerta"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label for="nome">Nome</label>
                <input id="nome" type="text" name="nome" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required>
            </div>

            <div class="field">
                <label for="senha">Senha</label>
                <input id="senha" type="password" name="senha" required>
            </div>

            <button type="submit" class="btn">Cadastrar</button>
        </form>

        <a class="form-link" href="login.php">Já tenho conta</a>
        <a class="form-link muted-link" href="termos.php">Termos e condições</a>
    </div>
</main>

</body>
</html>


