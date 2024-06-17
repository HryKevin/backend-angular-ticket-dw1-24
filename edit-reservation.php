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
    echo '{"message" : "Il n\'y a pas d\'identifiant dans l\'URL"}';
    http_response_code(400);
    exit;
}

$idReservation = $_GET["id"];

// Prend les données brutes de la requête
$json = file_get_contents('php://input');

// Le convertit en objet PHP
$reservation = json_decode($json);

if (!isset($reservation->date_debut) || !isset($reservation->date_fin) || !isset($reservation->accepte)) {
    http_response_code(400);
    echo '{"message" : "Données invalides"}';
    exit();
}

// On récupère la réservation dans la bdd
$requete = $connexion->prepare("SELECT * FROM reservation WHERE id = ?");
$requete->execute([$idReservation]);
$reservationDb = $requete->fetch();

// Si la réservation n'existe pas, on retourne une erreur 404
if (!$reservationDb) {
    http_response_code(404);
    echo '{"message" : "Cette réservation n\'existe pas"}';
    exit();
}

// Vérifier la disponibilité du matériel pour les nouvelles dates
$requete = $connexion->prepare("SELECT * FROM reservation 
                                WHERE id_materiel = ? 
                                AND id != ? 
                                AND ((date_debut BETWEEN ? AND ?) 
                                OR (date_fin BETWEEN ? AND ?) 
                                OR (? BETWEEN date_debut AND date_fin) 
                                OR (? BETWEEN date_debut AND date_fin))");

$requete->execute([
    $reservationDb['id_materiel'],
    $idReservation,
    $reservation->date_debut,
    $reservation->date_fin,
    $reservation->date_debut,
    $reservation->date_fin,
    $reservation->date_debut,
    $reservation->date_fin
]);

$conflictingReservations = $requete->fetchAll();

if (count($conflictingReservations) > 0) {
    http_response_code(409);
    echo '{"message" : "Les dates choisies ne sont pas disponibles pour ce matériel"}';
    exit();
}

// Mettre à jour la réservation
$requete = $connexion->prepare("UPDATE reservation SET 
                                    date_debut = :date_debut, 
                                    date_fin = :date_fin, 
                                    accepte = :accepte 
                                WHERE id = :id");

$requete->execute([
    "date_debut" => $reservation->date_debut,
    "date_fin" => $reservation->date_fin,
    "accepte" => $reservation->accepte,
    "id" => $idReservation
]);

echo '{"message" : "La réservation a bien été modifiée"}';
