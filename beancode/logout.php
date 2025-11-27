<?php
// logout.php

// 1. Inicia a sessão. É essencial chamar session_start() antes de qualquer operação de sessão.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Limpa todas as variáveis de sessão
$_SESSION = array();

// 3. Se desejar, destrói o cookie de sessão.
// Isso irá limpar o cookie de sessão do navegador.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destrói a sessão
session_destroy();

// 5. Redireciona para a página inicial ou de login
header("Location: index.php");
exit();
?>