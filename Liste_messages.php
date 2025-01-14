<?php
// Importation de la configuration de la base de données
include 'config.php';

// Nombre de messages par page
$messagesPerPage = 20;

// Récupération de l'ID du topic et de la page actuelle
$topicId = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $messagesPerPage;

// Récupération des informations du topic
$topicStmt = $conn->prepare("SELECT titre FROM public.topics WHERE id = :topicId");
$topicStmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
$topicStmt->execute();
$topic = $topicStmt->fetch(PDO::FETCH_ASSOC);

if (!$topic) {
    echo "Le topic n'existe pas.";
    exit;
}

// Récupération des messages du topic avec pagination
$messageStmt = $conn->prepare("
    SELECT 
        m.texte AS message_texte,
        m.date_post AS date_post,
        a.id AS auteur_id,
        a.pseudo AS auteur
    FROM 
        public.messages m
    JOIN 
        public.auteurs a ON m.auteur_id = a.id
    WHERE 
        m.topic_id = :topicId
    ORDER BY 
        m.date_post ASC
    LIMIT :limit OFFSET :offset
");
$messageStmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
$messageStmt->bindParam(':limit', $messagesPerPage, PDO::PARAM_INT);
$messageStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$messageStmt->execute();
$messages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul du nombre total de pages
$countStmt = $conn->prepare("SELECT COUNT(*) FROM public.messages WHERE topic_id = :topicId");
$countStmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
$countStmt->execute();
$totalMessages = $countStmt->fetchColumn();
$totalPages = ceil($totalMessages / $messagesPerPage);

// Limite de numéros de pages affichés dans la pagination
$pageRange = 5; // Nombre de pages à afficher autour de la page actuelle
$startPage = max(1, $page - $pageRange);
$endPage = min($totalPages, $page + $pageRange);

if (isset($_GET["api"])) {
	header("Content-Type: application/json");
	echo json_encode($messages);
	exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title><?= htmlspecialchars($topic['titre']) ?> - Geevey</title>
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
        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 4px;
            color: white;
            background-color: #3b82f6;
            margin-left: 4px;
            text-decoration: none;
        }
        .pagination a:hover {
            background-color: #2563eb;
        }
        .pagination .current-page {
            background-color: #2d3748;
            cursor: default;
        }
        .message-item {
            background: #2d3748;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .message-author {
            font-weight: bold;
            color: #9f7aea;
        }
        .message-date {
            font-size: 0.875rem;
            color: #a0aec0;
        }
    </style>
	<link rel="icon" href="/favicon.ico">
</head>
<body class="bg-gray-900 text-gray-200">
<?php
require "nav.php";
?>

    <div class="container mx-auto p-4">
        <div class="flex flex-col lg:flex-row">
            <!-- Colonne principale avec la liste des messages -->
            <div class="w-full lg:w-2/3 pr-4 mb-4 lg:mb-0">
				<div class="flex justify-end mb-4">
					<a href="/" class="btn bg-blue-500 text-white p-2 rounded">
						<i class="fas fa-arrow-left mr-1"></i> Retour à la liste des sujets
					</a>
				</div>
                <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($topic['titre']) ?></h1>

                <!-- Pagination en haut de la liste des messages -->
                <div class="flex justify-center mb-4">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?topic_id=<?= $topicId ?>&page=1">1</a>
                            <?php if ($page > 2): ?>
                                <a href="?topic_id=<?= $topicId ?>&page=<?= $page - 1 ?>">&laquo; Précédent</a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current-page"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?topic_id=<?= $topicId ?>&page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?topic_id=<?= $topicId ?>&page=<?= $page + 1 ?>">Suivant &raquo;</a>
                            <a href="?topic_id=<?= $topicId ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Liste des messages -->
                <?php foreach ($messages as $message): ?>
                    <div class="message-item">
                            <a href="/Stalk.php?auteur_id=<?= $message['auteur_id'] ?>" class="text-gray-400 font-bold">
                                <?= htmlspecialchars($message['auteur']) ?>
                            </a>
                        <div class="message-date"><?= htmlspecialchars($message['date_post']) ?></div>
                        <p><?= richText($message['message_texte']) ?></p>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination en bas de la liste des messages -->
                <div class="flex justify-center mt-4">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?topic_id=<?= $topicId ?>&page=1">1</a>
                            <?php if ($page > 2): ?>
                                <a href="?topic_id=<?= $topicId ?>&page=<?= $page - 1 ?>">&laquo; Précédent</a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current-page"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?topic_id=<?= $topicId ?>&page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?topic_id=<?= $topicId ?>&page=<?= $page + 1 ?>">Suivant &raquo;</a>
                            <a href="?topic_id=<?= $topicId ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Colonne de droite avec les blocs d'informations -->
<?php
require "droite.php";
?>
        </div>
    </div>

    <footer class="bg-gray-800 p-4 mt-4 text-center border-t-4 border-blue-500">
        Geevey.com
    </footer>
</body>
</html>