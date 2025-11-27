<?php
// login.php (CÓDIGO CORRIGIDO E ATUALIZADO)

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db.php'; 

$error_message = '';
$success_message = isset($_GET['status']) && $_GET['status'] == 'registered' ? 'Cadastro concluído com sucesso! Faça login abaixo. 🎉' : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // 1. Tentar login como RESPONSÁVEL (usando a tabela 'responsaveis')
    // Colunas usadas: id, senha, nome_completo
    $sql = "SELECT id, senha, nome_completo FROM responsaveis WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['senha'])) {
            // Login de RESPONSÁVEL bem-sucedido!
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = 'responsible'; // Novo campo para diferenciar
            $_SESSION['nome_responsavel'] = $user['nome_completo'];
            
            // Redireciona para o Painel do Responsável
            header("Location: dashboard_responsavel.php");
            exit();
        } else {
            $error_message = "Email ou senha mágica incorretos. 🧙‍♂️";
        }
    } else {
        // Se não for responsável, poderia ser um ALUNO (usando nome_user)
        // Se a sua tela de login é somente para responsáveis (usando email), este é o comportamento ideal.
        $error_message = "Email ou senha mágica incorretos. 🧙‍♂️";
    }
    $stmt->close();
}

$conn->close();

// ... O restante do HTML permanece o mesmo ...
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <title>BrinCode - Login</title>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-md bg-card border border-border p-8 rounded-xl shadow-2xl relative z-10">
    <?php if ($error_message): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo $error_message; ?></span>
      </div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
      <div class="bg-teal-100 border border-teal-400 text-teal-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo $success_message; ?></span>
      </div>
    <?php endif; ?>

    <form class="space-y-6" method="POST" action="login.php">
      <div>
        <label for="email" class="block text-sm font-medium mb-2 text-foreground">
          Email Mágico
        </label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          placeholder="seu@email.com" 
          required 
          class="w-full input-style"
        >
      </div>

      <div>
        <label for="password" class="block text-sm font-medium mb-2 text-foreground">
          Sua Senha Secreta
        </label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          placeholder="********" 
          required 
          class="w-full input-style"
        >
      </div>

      <button 
        type="submit" 
        name="login_submit"
        class="w-full bg-primary text-primary-foreground hover:opacity-90 px-4 py-3 text-lg font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
        Entrar na Aventura!
        </button>

      </form>
  </div>
</body>
</html>