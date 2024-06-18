<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if ($user->role != "Administrateur") {
    http_response_code(403);
    echo '{"message" : "Vous n\avez pas les droits nécessaires"}';
    exit();
}

$request = $connexion->query("  SELECT   u.email, u.firstname, u.lastname, date_debut, date_fin, accepte, m.nom, m.numero_de_serie, r.id
                                FROM reservation AS r
                                LEFT JOIN user AS u  ON r.id_loueur = u.id
                                LEFT JOIN materiel AS m ON r.id_materiel = m.id");

$reservationList = $request->fetchAll();


foreach ($reservationList as &$reservation) {
    // Formatage des dates
    $date_debut = new DateTime($reservation['date_debut']);
    $reservation['date_debut'] = $date_debut->format('d-m-Y');

    $date_fin = new DateTime($reservation['date_fin']);
    $reservation['date_fin'] = $date_fin->format('d-m-Y');

    // Convertir accepte en booléen
    $reservation['accepte'] = $reservation['accepte'] == 1 ? 'Oui' : 'Non';
}

echo json_encode($reservationList);
