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

$idReservation = $_GET["id"];

$requete = $connexion->prepare("SELECT u.email, u.firstname, u.lastname, date_debut, date_fin, accepte, m.nom, m.numero_de_serie
                                FROM reservation AS r
                                LEFT JOIN user AS u  ON r.id_loueur = u.id
                                LEFT JOIN materiel AS m ON r.id_materiel = m.id
                                WHERE r.id = ?");

$requete->execute([$idReservation]);

$reservation = $requete->fetch();

if (!$reservation) {
    echo json_encode(["message" => "utilisateur inexistant"]);
    http_response_code(404);
    exit;
}

// Formatage des dates et du booléen
$reservation['date_debut'] = date('c', strtotime($reservation['date_debut']));
$reservation['date_fin'] = date('c', strtotime($reservation['date_fin']));
$reservation['accepte'] = $reservation['accepte'] == 1;

echo json_encode($reservation);
