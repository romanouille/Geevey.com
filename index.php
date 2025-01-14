<?php
include 'config.php';

$topicsPerPage = 20;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $topicsPerPage;

$searchQuery = isset($_GET['search_in_forum']) ? $_GET['search_in_forum'] : '';
$searchType = isset($_GET['type_search_in_forum']) ? $_GET['type_search_in_forum'] : '';

$whereClause = "WHERE t.date_dernier_message IS NOT NULL AND EXISTS (SELECT 1 FROM public.messages m WHERE m.topic_id = t.id)";

if ($searchQuery) {
    $wildcardQuery = '%' . $searchQuery . '%';  // Wildcard for SQL LIKE
    switch ($searchType) {
        case 'titre_topic':
            $whereClause .= " AND t.titre ILIKE :searchQuery";
            break;
        case 'auteur_topic':
            $whereClause .= " AND a.pseudo ILIKE :searchQuery";
            break;
        case 'texte_message':
            // Todo: comprendre comment gère postgres les dates dans une condition de comparaison de 2 dates str
            $whereClause .= " AND EXISTS (
                SELECT 1 FROM public.messages m2 
                WHERE m2.topic_id = t.id 
                AND m2.texte ILIKE :searchQuery
                AND t.date_dernier_message >= '2024-10-01'
            )";
            break;
    }
}

$topicsPerPage = (int)$topicsPerPage;
$offset = (int)$offset;

$query = "
    SELECT DISTINCT 
        t.id AS topic_id,
        t.titre AS topic_titre,
        a.id AS auteur_id,
        a.pseudo AS auteur,
        t.nb_messages_enregistre,
        t.date_dernier_message
    FROM 
        public.topics t
    JOIN 
        public.auteurs a ON t.auteur_id = a.id
    $whereClause
    ORDER BY 
        t.date_dernier_message DESC
    LIMIT $topicsPerPage OFFSET $offset
";
$stmt = $conn->prepare($query);

if ($searchQuery) {
    $stmt->bindValue(':searchQuery', $wildcardQuery, PDO::PARAM_STR);
}

try {
    $stmt->execute();
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de l'exécution de la requête principale : " . $e->getMessage();
}

$nextOffset = $offset + $topicsPerPage;
$hasNextPageQuery = "
    SELECT 1 FROM public.topics t 
    JOIN public.auteurs a ON t.auteur_id = a.id
    $whereClause
    LIMIT 1 OFFSET $nextOffset
";
$hasNextPageStmt = $conn->prepare($hasNextPageQuery);

if ($searchQuery) {
    $hasNextPageStmt->bindValue(':searchQuery', $wildcardQuery, PDO::PARAM_STR);
}

try {
    $hasNextPageStmt->execute();
    $hasNextPage = $hasNextPageStmt->fetchColumn() !== false;
} catch (PDOException $e) {
    echo "Erreur lors de la vérification de la page suivante : " . $e->getMessage();
}

$excerpts = [];
if ($searchType === 'texte_message' && $searchQuery) {
    $topicIds = array_column($topics, 'topic_id');
    if (!empty($topicIds)) {
        $topicIds = array_map('intval', $topicIds);
        $inClause = implode(',', $topicIds);

        $excerptQuery = "
            SELECT 
                m.topic_id,
                substring(m.texte, GREATEST(1, strpos(lower(m.texte), lower(:searchQuery))), 150) AS excerpt
            FROM 
                public.messages m
            WHERE 
                m.texte ILIKE :searchQuery
                AND m.topic_id IN ($inClause)
        ";
        $excerptStmt = $conn->prepare($excerptQuery);
        $excerptStmt->bindValue(':searchQuery', $wildcardQuery, PDO::PARAM_STR);

        try {
            $excerptStmt->execute();
            $excerpts = $excerptStmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur lors de la récupération des extraits : " . $e->getMessage();
        }
    }
}

if (isset($_GET["api"])) {
	header("Content-Type: application/json");
	echo json_encode($topics);
	exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Liste des sujets - Geevey</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
		.alert {
			padding:20px;
			margin:10px 0 10px 0;
			background:#1C262F;
			color:#E45C34;
			border-left:5px solid #E45C34;
		}
        * {
            font-family: "Roboto Condensed";
        }
        .center {
            text-align: center;
        }
        .topics-list tbody tr:nth-child(even) {
            background: #2d3748;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 4px;
            color: white;
            background-color: #3b82f6;
            margin-left: 8px;
            text-decoration: none;
        }
        .pagination a:hover {
            background-color: #2563eb;
        }
        .pagination .current-page {
            background-color: #2d3748;
            cursor: default;
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
            <!-- Colonne principale avec la liste des topics et la pagination -->
            <div class="w-full lg:w-2/3 pr-4 mb-4 lg:mb-0">
                <!-- Formulaire de recherche et outils de forum en haut de la liste des topics -->
                <div class="bloc-pre-pagi-forum bloc-outils-top mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <!--<a href="#bloc-formulaire-forum"><span class="btn bg-blue-500 text-white p-2 rounded">Nouveau sujet</span></a>
                        <button class="btn bg-blue-500 text-white p-2 rounded">Actualiser</button>-->
                    </div>
                    <div class="bloc-rech-forum w-full">
                        <form class="form-rech-forum flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-2 w-full" method="get" action="#">
                            <input class="txt-search form-control w-full sm:flex-1 p-2 rounded bg-gray-700 text-gray-200" type="text" placeholder="Rechercher dans le forum" name="search_in_forum" value="<?= htmlspecialchars($searchQuery) ?>" autocomplete="off">
                            <select class="select-search-input form-control w-full sm:w-auto p-2 rounded bg-gray-700 text-gray-200" name="type_search_in_forum" id="type_search_in_forum">
                                <option value="titre_topic" <?= $searchType === 'titre_topic' ? 'selected' : '' ?>>Sujet</option>
                                <option value="auteur_topic" <?= $searchType === 'auteur_topic' ? 'selected' : '' ?>>Auteur</option>
                                <option value="texte_message" <?= $searchType === 'texte_message' ? 'selected' : '' ?>>Message (bêta)</option>
                            </select>
                            <button class="btn btn-lancer-rech bg-blue-500 text-white p-2 rounded" type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>

                <!-- Pagination en haut de la liste des topics, alignée à gauche et droite -->
                <div class="flex justify-between mb-2">
                    <div class="flex">
                        <?php if ($page > 1): ?>
                            <a href="?page=1" title="Retour à la première page" class="btn bg-blue-500 text-white p-2 rounded">Première page</a>
                            <a href="?page=<?= $page - 1 ?><?=isset($searchType) && !empty($searchType) ? "&search_in_forum=".urlencode($searchQuery)."&type_search_in_forum=$searchType" : ""?>" title="Page précédente" class="btn bg-blue-500 text-white p-2 rounded ml-2"><i class="fas fa-arrow-left"></i> Précédent</a>
                        <?php endif; ?>
                    </div>
                    <?php if ($hasNextPage): ?>
                        <a href="?page=<?= $page + 1 ?><?=isset($searchType) && !empty($searchType) ? "&search_in_forum=".urlencode($searchQuery)."&type_search_in_forum=$searchType" : ""?>" class="btn bg-blue-500 text-white p-2 rounded">Suivant <i class="fas fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
<?php
if (isset($excerptQuery)) {
?>
				<div class="alert">
					La recherche est en bêta et est actuellement bridée à 1 mois pour des raisons techniques.
				</div>
<?php
}
?>

                <!-- Liste des topics -->
                <table class="table-auto w-full topics-list bg-gray-800 rounded">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="p-2">Icon</th>
                            <th class="p-2">Sujet</th>
                            <th class="p-2">Msg.</th>
                            <th class="p-2">Dernier msg.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topics as $topic): ?>
                        <tr>
                            <?php 
                            // Sélection de l'icône en fonction du nombre de messages
                            $icon = $topic['nb_messages_enregistre'] > 20 ? "topic-hot.png" : "topic.png";
                            ?>
                            <td class="p-2"><img src="/img/forums/<?= $icon ?>" alt="" title="Topic icon"></td>
                            <td class="p-2">
                                <a href="Liste_messages.php?topic_id=<?= $topic['topic_id'] ?>" class="text-blue-400 hover:text-blue-600" title="<?= htmlspecialchars($topic['topic_titre']) ?>">
                                    <?= htmlspecialchars($topic['topic_titre']) ?>
                                </a>
                                <a href="/Stalk.php?auteur_id=<?= $topic['auteur_id'] ?>" class="text-gray-400 font-bold">
                                    <?= htmlspecialchars($topic['auteur']) ?>
                                </a>
                                <?php if ($searchType === 'texte_message' && isset($excerpts[$topic['topic_id']])): ?>
                                    <div class="excerpt">
                                        Extrait : <?= htmlspecialchars(strip_tags($excerpts[$topic['topic_id']][0]['excerpt'])) ?>...
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-2"><?= htmlspecialchars($topic['nb_messages_enregistre']) ?></td>
                            <td class="p-2 center">
                                <a href="/Liste_messages.php?topic_id=<?=$topic["topic_id"]?>&page=<?=ceil($topic["nb_messages_enregistre"]/20)?>" class="text-blue-400 hover:text-blue-600" title="Accéder au dernier message posté"><?= htmlspecialchars($topic['date_dernier_message']) ?></a>
                                <!--<a href="/Stalk.php?id=<?= $topic['auteur_id'] ?>" class="text-gray-400 font-bold">
                                    <?= htmlspecialchars($topic['auteur']) ?>
                                </a>-->
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination en bas de la liste des topics, alignée à gauche et droite -->
                <div class="flex justify-between mt-4">
                    <div class="flex">
                        <?php if ($page > 1): ?>
                            <a href="?page=1" title="Retour à la première page" class="btn bg-blue-500 text-white p-2 rounded">Première page</a>
                            <a href="?page=<?= $page - 1 ?><?=isset($searchType) && !empty($searchType) ? "&search_in_forum=".urlencode($searchQuery)."&type_search_in_forum=$searchType" : ""?>" title="Page précédente" class="btn bg-blue-500 text-white p-2 rounded ml-2"><i class="fas fa-arrow-left"></i> Précédent</a>
                        <?php endif; ?>
                    </div>
                    <?php if ($hasNextPage): ?>
                        <a href="?page=<?= $page + 1 ?><?=isset($searchType) && !empty($searchType) ? "&search_in_forum=".urlencode($searchQuery)."&type_search_in_forum=$searchType" : ""?>" class="btn bg-blue-500 text-white p-2 rounded">Suivant <i class="fas fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Colonne de droite avec les blocs d'informations -->
<?php
require "droite.php";
?>
        </div>
    </div>

    <footer class="bg-gray-800 p-4 mt-4 text-center border-t-4 border-blue-500">
        Geevey.com 2024
    </footer>
</body>
</html>