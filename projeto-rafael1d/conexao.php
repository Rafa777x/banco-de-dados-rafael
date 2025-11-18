<?php

// Configurações do banco
$host    = "localhost";   // normalmente não precisa alterar
$usuario = "root";        // substituir se seu usuário não for root
$senha   = "";            // substituir se você tiver senha no MySQL
$banco   = "mural1";       // substituir pelo nome do seu banco criado no phpMyAdmin

// Conexão MySQLi
$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro ao conectar: " . mysqli_connect_error());
}

// SENSITIVE CASE suportar acentos e Ç
mysqli_set_charset($conexao, "utf8");

$cloud_name = "dgjvkardk";  // exemplo: "meucloud123"
$api_key    = "431931954895513";     // exemplo: "123456789012345"
$api_secret = "_FSVkWjXW4W2OmvWuG_riPzg-Vk";  // exemplo: "abcdeFGHijkLMNopqrstu"

?>