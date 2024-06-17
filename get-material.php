<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if ($user->role != "Administrateur") {
    http_response_code(403);
    echo '{"message" : "Vous n\avez pas les droits nécessaires"}';
    exit();
}

if (!isset($_GET['id'])) {
    echo '{"message" : "il n\'y a pas d\'identiant dans l\'URL"}';
    http_response_code(400);
    exit;
}

$idMateriel = $_GET["id"];

$requete = $connexion->prepare("SELECT m.nom, m.id, m.date_achat, m.numero_de_serie, r.date_debut, r.date_fin, r.id_loueur
                                FROM materiel AS m
                                JOIN reservation AS r ON m.id = r.id_materiel
                                WHERE m.id = ?");
$requete->execute([$idMateriel]);

$materiel = $requete->fetch();

if (!$materiel) {
    echo json_encode(["message" => "utilisateur inexistant"]);
    http_response_code(404);
    exit;
}


// Formatage des dates et du booléen
$materiel['date_achat'] = date('c', strtotime($materiel['date_achat']));

echo json_encode($materiel);
