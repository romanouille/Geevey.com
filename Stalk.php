<?php
include 'config.php';

$messagesPerPage = 20;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $messagesPerPage;

$auteur_id = isset($_GET['auteur_id']) ? (int)$_GET['auteur_id'] : null;

if (!$auteur_id) {
    echo "Aucun utilisateur spécifié.";
    exit;
}

$stmtAuthor = $conn->prepare("SELECT pseudo, avatar FROM public.auteurs WHERE id = :auteur_id");
$stmtAuthor->bindParam(':auteur_id', $auteur_id, PDO::PARAM_INT);
$stmtAuthor->execute();
$author = $stmtAuthor->fetch(PDO::FETCH_ASSOC);

if (!$author) {
    echo "Utilisateur introuvable.";
    exit;
}

$stmtTotalMessages = $conn->prepare("SELECT COUNT(*) FROM public.messages WHERE auteur_id = :auteur_id");
$stmtTotalMessages->bindParam(':auteur_id', $auteur_id, PDO::PARAM_INT);
$stmtTotalMessages->execute();
$totalMessages = $stmtTotalMessages->fetchColumn();

$totalPages = ceil($totalMessages / $messagesPerPage);

$stmtMessages = $conn->prepare("
    SELECT 
        m.id AS message_id,
        m.date_post,
        m.texte,
        m.topic_id,
        t.titre AS topic_titre
    FROM 
        public.messages m
    JOIN 
        public.topics t ON m.topic_id = t.id
    WHERE 
        m.auteur_id = :auteur_id
    ORDER BY 
        m.date_post DESC
    LIMIT :limit OFFSET :offset
");
$stmtMessages->bindParam(':auteur_id', $auteur_id, PDO::PARAM_INT);
$stmtMessages->bindParam(':limit', $messagesPerPage, PDO::PARAM_INT);
$stmtMessages->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmtMessages->execute();
$messages = $stmtMessages->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET["api"])) {
	header("Content-Type: application/json");
	echo json_encode($messages);
	exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Messages de <?= htmlspecialchars($author['pseudo']) ?> - Geevey</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        * {
            font-family: "Roboto Condensed";
        }
        .center {
            text-align: center;
        }
        .message {
            background-color: #2d3748;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message-body {
            margin-top: 8px;
        }
        .pagination a, .pagination span {
            margin: 0 4px;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
        }
        .pagination a {
            background-color: #4299e1;
            color: #fff;
        }
        .pagination span {
            background-color: #718096;
            color: #fff;
        }
    </style>
	<link rel="icon" href="/favicon.ico">
</head>
<body class="bg-gray-900 text-gray-200">
<?php
require "nav.php";
?>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl mb-4">Messages de <?= htmlspecialchars($author['pseudo']) ?></h1>

        <div class="pagination flex justify-center mb-4">
            <?php if ($page > 1): ?>
                <a href="?auteur_id=<?= $auteur_id ?>&page=1">Première page</a>
            <?php endif; ?>

            <span><?= $page ?></span>

            <?php
            for ($i = $page + 1; $i <= min($page + 5, $totalPages); $i++): ?>
                <a href="?auteur_id=<?= $auteur_id ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page + 5 < $totalPages): ?>
                <span>...</span>
                <a href="?auteur_id=<?= $auteur_id ?>&page=<?= $totalPages ?>">Dernière page (<?= $totalPages ?>)</a>
            <?php elseif ($page < $totalPages): ?>
                <a href="?auteur_id=<?= $auteur_id ?>&page=<?= $totalPages ?>">Dernière page</a>
            <?php endif; ?>
        </div>

        <?php foreach ($messages as $message): ?>
            <div class="message">
                <div class="message-header">
                    <div>
                        <a href="Liste_messages.php?topic_id=<?= $message['topic_id'] ?>" class="text-blue-400 hover:text-blue-600">
                            <?= htmlspecialchars($message['topic_titre']) ?>
                        </a>
                    </div>
                    <div class="text-gray-400">
                        <?= date('d/m/Y H:i', strtotime($message['date_post'])) ?>
                    </div>
                </div>
                <div class="message-body mt-2">
                    <?= richText($message['texte']) ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="pagination flex justify-center mt-4">
            <?php if ($page > 1): ?>
                <a href="?auteur_id=<?= $auteur_id ?>&page=1">Première page</a>
            <?php endif; ?>

            <span><?= $page ?></span>

            <?php
            for ($i = $page + 1; $i <= min($page + 5, $totalPages); $i++): ?>
                <a href="?auteur_id=<?= $auteur_id ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page + 5 < $totalPages): ?>
                <span>...</span>
                <a href="?auteur_id=<?= $auteur_id ?>&page=<?= $totalPages ?>">Dernière page (<?= $totalPages ?>)</a>
            <?php elseif ($page < $totalPages): ?>
                <a href="?auteur_id=<?= $auteur_id ?>&page=<?= $totalPages ?>">Dernière page</a>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-gray-800 p-4 mt-4 text-center border-t-4 border-blue-500">
        Geevey.com 2016-2021
    </footer>
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const nsElements = document.querySelectorAll("ns");

    nsElements.forEach(ns => {
        const img = document.createElement("img");

        img.src = ns.textContent.trim();

        img.width = 50;

        ns.replaceWith(img);
    });
});
    </script>
</body>
</html>