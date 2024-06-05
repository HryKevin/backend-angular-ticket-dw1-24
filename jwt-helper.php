<?php

//La clé secrète permettant de générer les signatures des JWT
define("CLE_SECRETE", "votre_cle_secrete");

/**
 * Extrait le JWT de l'entete Authorization
 * Vérifie si il est valide
 * Retourne un objet PHP avec le corp du JWT
 */
function extractJwtBody()
{

    $enTetes = apache_request_headers();

    //si il n'y a pas de JWT on affiche une erreur 401
    if (!isset($enTetes['Authorization'])) {
        http_response_code(401);
        echo '{"message" : "Vous n\'êtes pas connecté"}';
        exit();
    }

    // verifier sa validité, si il est invalide : refuser la requete
    $partiesJwt = explode(".", $enTetes['Authorization']);
    $base64UrlHeader = $partiesJwt[0];
    $base64UrlPayload = $partiesJwt[1];
    $base64UrlSignature = $partiesJwt[2];

    // Regénérer la signature
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, CLE_SECRETE, true);
    $verificationBase64UrlSignature = base64UrlEncode($signature);

    //si la signature envoyée est différente de celle regénérée, il y a eu certainement une tentative de modification
    if ($base64UrlSignature != $verificationBase64UrlSignature) {
        http_response_code(403);
        echo '{"message" : "Ce JWT est invalide"}';
        exit();
    }

    //on decode la parties payload et on la retourne sous forme d'objet PHP 
    $paylod = base64UrlDecode($base64UrlPayload);

    return json_decode($paylod);
}


/**
 * Prend en paramètre l'utilisateur à stocker dans la partie payload
 * Retourne le JWT 
 */
function generateJwt($utilisateur)
{
    // générer le JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

    $payload = json_encode([
        'id' => $utilisateur['id'],
        'role' => $utilisateur['role'],
        'email' => $utilisateur['email'],
        'firstname' => $utilisateur['firstname'],
        'lastname' => $utilisateur['lastname'],
    ]);

    // Encoder en Base64 URL-safe
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // Créer la signature
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, CLE_SECRETE, true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Assembler le token
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    return $jwt;
}


function base64UrlDecode($data)
{
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
}

function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
