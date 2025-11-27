<?php
// process_dependents.php (AJUSTADO PARA O BD ANEXADO)
session_start();
include 'db.php';

// Verifica se o responsável está logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'responsible') {
    header("Location: index.php"); 
    exit();
}

// Verifica se a submissão é do novo formulário de aluno
if (!isset($_POST['submit_new_aluno'])) {
    header("Location: dashboard_responsavel.php");
    exit();
}

$responsible_id = $_SESSION['user_id'];
$alert_message = '';

// 1. Coleta e sanitiza os dados do POST
$nome_user = $conn->real_escape_string($_POST['nome_user'] ?? '');
$nome_completo = $conn->real_escape_string($_POST['nome_completo'] ?? '');
$email = $conn->real_escape_string($_POST['email'] ?? '');
$data_nasc = $conn->real_escape_string($_POST['data_nasc'] ?? ''); // Mudança: Usando data_nasc
$password = $_POST['password'] ?? '';
$repeat_password = $_POST['repeat_password'] ?? '';
// A trilha será ignorada no INSERT, mas coletada no formulário
$trilha_escolhida = $_POST['trilha_ativa'] ?? 'iniciante'; 

// 2. Validação básica
if (empty($nome_user) || empty($nome_completo) || empty($email) || empty($data_nasc) || empty($password)) {
    $_SESSION['alert_message'] = "Erro: Todos os campos são obrigatórios.";
} elseif ($password !== $repeat_password) {
    $_SESSION['alert_message'] = "Erro: As senhas não coincidem.";
} else {
    // 3. Hash da senha
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // 4. Prepara a consulta SQL para a tabela 'alunos'
    // ATENÇÃO: As colunas `trilha_ativa` foram removidas do INSERT para adequação ao BD
    $sql = "INSERT INTO alunos (responsavel_id, nome_user, nome_completo, email, data_nasc, senha) 
            VALUES (?, ?, ?, ?, ?, ?)"; 
    
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Binding: responsavel_id (i), nome_user (s), nome_completo (s), email (s), data_nasc (s), senha (s)
        $stmt->bind_param("isssss", $responsible_id, $nome_user, $nome_completo, $email, $data_nasc, $password_hash);

        if ($stmt->execute()) {
            $_SESSION['alert_message'] = "Programador **$nome_user** cadastrado com sucesso! 🎉";
        } else {
            if ($conn->errno === 1062) {
                $_SESSION['alert_message'] = "Erro: O nome de usuário '$nome_user' já está em uso.";
            } else {
                $_SESSION['alert_message'] = "Erro ao cadastrar: " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        $_SESSION['alert_message'] = "Erro de preparação da consulta: " . $conn->error;
    }
}

$conn->close();

// Redireciona de volta para o painel
header("Location: dashboard_responsavel.php");
exit();
?>