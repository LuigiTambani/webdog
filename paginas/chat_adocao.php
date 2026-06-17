<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];
$solicitacaoId = (int) ($_GET["id"] ?? 0);

$conn->query("CREATE TABLE IF NOT EXISTS mensagens_adocao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL,
    remetente_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_adocao(id) ON DELETE CASCADE,
    FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE
)");

$stmt = $conn->prepare("SELECT s.*, p.nome AS pet_nome, p.imagem, p.tipo, p.raca,
        solicitante.nome AS solicitante_nome,
        doador.nome AS doador_nome
    FROM solicitacoes_adocao s
    INNER JOIN pets p ON p.id = s.pet_id
    INNER JOIN usuarios solicitante ON solicitante.id = s.solicitante_id
    INNER JOIN usuarios doador ON doador.id = s.doador_id
    WHERE s.id = ? AND (s.solicitante_id = ? OR s.doador_id = ?)");
$stmt->bind_param("iii", $solicitacaoId, $usuarioId, $usuarioId);
$stmt->execute();
$solicitacao = $stmt->get_result();

if ($solicitacao->num_rows === 0) {
    header("Location: adocoes.php");
    exit;
}

$solicitacao = $solicitacao->fetch_assoc();
$souDoador = (int) $solicitacao["doador_id"] === $usuarioId;

$stmt = $conn->prepare("SELECT m.*, u.nome AS remetente_nome
    FROM mensagens_adocao m
    INNER JOIN usuarios u ON u.id = m.remetente_id
    WHERE m.solicitacao_id = ?
    ORDER BY m.criado_em ASC, m.id ASC");
$stmt->bind_param("i", $solicitacaoId);
$stmt->execute();
$mensagens = $stmt->get_result();

function statusTextoChat($status) {
    $mapa = [
        "pendente" => "Pendente",
        "aprovada" => "Aprovada",
        "recusada" => "Recusada",
        "cancelada" => "Cancelada",
    ];

    return $mapa[$status] ?? ucfirst($status);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat da adoção - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/adocoes.css">
</head>
<body>

<header class="navbar">
    <div class="logo"><img src="../assets/img/logo.jpg" class="logo-img" alt="Logo WebDog"><span>WebDog</span></div>
    <nav>
        <span class="user-name">Olá, <?= e($usuarioLogadoNome) ?></span>
        <a href="listar_pets.php">Feed</a>
        <a href="cadastrar_pet.php">Cadastrar pet</a>
        <a href="adocoes.php">Minhas adoções</a>
        <a href="../acoes/logout.php">Sair</a>
    </nav>
</header>

<main class="chat-shell">
    <section class="chat-box">
        <div class="chat-topo">
            <a href="adocoes.php" class="chat-voltar">Voltar</a>
            <img src="<?= e(app_url($solicitacao['imagem'])) ?>" alt="Foto de <?= e($solicitacao['pet_nome']) ?>">
            <div>
                <h1><?= e($solicitacao["pet_nome"]) ?></h1>
                <p><?= e($solicitacao["tipo"]) ?> · <?= e($solicitacao["raca"]) ?> · <?= e(statusTextoChat($solicitacao["status"])) ?></p>
                <span><?= e($solicitacao["solicitante_nome"]) ?> e <?= e($solicitacao["doador_nome"]) ?></span>
            </div>
        </div>

        <?php if ($souDoador && $solicitacao["status"] === "pendente"): ?>
            <div class="chat-acoes">
                <form action="../acoes/responder_adocao.php" method="POST">
                    <input type="hidden" name="solicitacao_id" value="<?= (int) $solicitacao["id"] ?>">
                    <input type="hidden" name="acao" value="aceitar">
                    <button type="submit">Aceitar adoção</button>
                </form>
                <form action="../acoes/responder_adocao.php" method="POST">
                    <input type="hidden" name="solicitacao_id" value="<?= (int) $solicitacao["id"] ?>">
                    <input type="hidden" name="acao" value="recusar">
                    <button class="btn excluir" type="submit">Recusar</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="chat-mensagens" id="chatMensagens" data-solicitacao="<?= (int) $solicitacao["id"] ?>">
            <?php if ($mensagens->num_rows === 0): ?>
                <div class="chat-vazio">Nenhuma mensagem ainda. Comece a conversa sobre a adoção.</div>
            <?php else: ?>
                <?php while ($mensagem = $mensagens->fetch_assoc()): ?>
                    <?php $minhaMensagem = (int) $mensagem["remetente_id"] === $usuarioId; ?>
                    <div class="mensagem-linha <?= $minhaMensagem ? "minha" : "outra" ?>">
                        <div class="mensagem-balao">
                            <strong><?= $minhaMensagem ? "Você" : e($mensagem["remetente_nome"]) ?></strong>
                            <p><?= nl2br(e($mensagem["mensagem"])) ?></p>
                            <small><?= e(date("H:i", strtotime($mensagem["criado_em"]))) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <form class="chat-form" action="../acoes/enviar_mensagem_adocao.php" method="POST">
            <input type="hidden" name="solicitacao_id" value="<?= (int) $solicitacao["id"] ?>">
            <textarea name="mensagem" rows="1" placeholder="Mensagem" required></textarea>
            <button type="submit">Enviar</button>
        </form>
    </section>
</main>

<script>
var chat = document.getElementById("chatMensagens");
if (chat) {
    chat.scrollTop = chat.scrollHeight;
}

var form = document.querySelector(".chat-form");
var campoMensagem = document.querySelector(".chat-form textarea");

if (form && campoMensagem) {
    campoMensagem.addEventListener("keydown", function(evento) {
        if ((evento.key === "Enter" || evento.keyCode === 13) && !evento.shiftKey) {
            evento.preventDefault();

            if (campoMensagem.value.trim() !== "") {
                if (form.requestSubmit) {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        }
    });
}

function escaparHtml(texto) {
    var div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

function carregarMensagens() {
    if (!chat) {
        return;
    }

    var id = chat.getAttribute("data-solicitacao");
    var estavaNoFinal = chat.scrollTop + chat.clientHeight >= chat.scrollHeight - 40;

    fetch("../acoes/buscar_mensagens_adocao.php?id=" + id)
        .then(function(resposta) {
            return resposta.json();
        })
        .then(function(dados) {
            if (!dados.mensagens) {
                return;
            }

            var html = "";
            for (var i = 0; i < dados.mensagens.length; i++) {
                var mensagem = dados.mensagens[i];
                html += '<div class="mensagem-linha ' + (mensagem.minha ? "minha" : "outra") + '">';
                html += '<div class="mensagem-balao">';
                html += '<strong>' + escaparHtml(mensagem.remetente) + '</strong>';
                html += '<p>' + escaparHtml(mensagem.texto).replace(/\n/g, "<br>") + '</p>';
                html += '<small>' + escaparHtml(mensagem.hora) + '</small>';
                html += '</div></div>';
            }

            if (html === "") {
                html = '<div class="chat-vazio">Nenhuma mensagem ainda. Comece a conversa sobre a adoção.</div>';
            }

            if (chat.innerHTML !== html) {
                chat.innerHTML = html;
                if (estavaNoFinal) {
                    chat.scrollTop = chat.scrollHeight;
                }
            }
        });
}

setInterval(carregarMensagens, 3000);
</script>

</body>
</html>
