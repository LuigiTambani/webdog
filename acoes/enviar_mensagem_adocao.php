<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: ../paginas/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../paginas/adocoes.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];
$solicitacaoId = (int) ($_POST["solicitacao_id"] ?? 0);
$mensagem = trim($_POST["mensagem"] ?? "");

if ($solicitacaoId <= 0 || $mensagem === "") {
    header("Location: ../paginas/adocoes.php");
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS mensagens_adocao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL,
    remetente_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_adocao(id) ON DELETE CASCADE,
    FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE
)");

$stmt = $conn->prepare("SELECT id FROM solicitacoes_adocao WHERE id = ? AND (solicitante_id = ? OR doador_id = ?)");
$stmt->bind_param("iii", $solicitacaoId, $usuarioId, $usuarioId);
$stmt->execute();
$solicitacao = $stmt->get_result();

if ($solicitacao->num_rows === 0) {
    header("Location: ../paginas/adocoes.php");
    exit;
}

$stmt = $conn->prepare("INSERT INTO mensagens_adocao (solicitacao_id, remetente_id, mensagem) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $solicitacaoId, $usuarioId, $mensagem);
$stmt->execute();

header("Location: ../paginas/chat_adocao.php?id=" . $solicitacaoId);
exit;
