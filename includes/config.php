<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli("localhost", "root", "", "webdog_db");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function app_url($caminho = "") {
    return "/webdog/" . ltrim($caminho, "/");
}

$usuarioLogadoNome = "";
if (isset($_SESSION["usuario"])) {
    $idUsuarioLogado = (int) $_SESSION["usuario"];
    $sqlUsuarioLogado = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $sqlUsuarioLogado->bind_param("i", $idUsuarioLogado);
    $sqlUsuarioLogado->execute();
    $resultadoUsuarioLogado = $sqlUsuarioLogado->get_result();

    if ($resultadoUsuarioLogado->num_rows > 0) {
        $usuarioLogadoNome = $resultadoUsuarioLogado->fetch_assoc()["nome"];
    }
}

