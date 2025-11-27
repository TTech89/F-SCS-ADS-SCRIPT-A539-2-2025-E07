<?php
session_start();
include 'db.php'; 

// Redireciona se o usuário não for um responsável logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'responsible') {
    header("Location: index.php");
    exit();
}

$responsible_id = $_SESSION['user_id'];
$responsible_name = $_SESSION['nome_responsavel'] ?? "Responsável"; 

// 1. Lógica para buscar os ALUNOS (Dependentes) cadastrados
$alunos = [];
// Mudança: Colunas ajustadas para o BD, removendo 'trilha_ativa' (que não existe)
$sql_alunos = "SELECT id, nome_user, nome_completo, email, data_nasc FROM alunos WHERE responsavel_id = ?";
$stmt_alu = $conn->prepare($sql_alunos);

if ($stmt_alu) {
    $stmt_alu->bind_param("i", $responsible_id);
    $stmt_alu->execute();
    $result_alu = $stmt_alu->get_result();

    while ($row = $result_alu->fetch_assoc()) {
        $alunos[] = $row;
    }
    $stmt_alu->close();
}

// 2. Lógica para buscar as últimas notificações/conquistas (SIMULAÇÃO)
$notifications = [
    ['child_name' => 'Joãozinho', 'message' => 'Desbloqueou o emblema "Mago do Loop" na trilha Iniciante!', 'time' => '10 minutos atrás'],
    ['child_name' => 'Mariazinha', 'message' => 'Concluiu o Módulo 1 da trilha Criador de Jogos.', 'time' => 'Ontem'],
];

$conn->close();

// Mensagem de sucesso/erro após a submissão do cadastro
$alert_message = '';
if (isset($_SESSION['alert_message'])) {
    $alert_message = $_SESSION['alert_message'];
    unset($_SESSION['alert_message']); 
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BeanCode - Painel do Responsável</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --background: oklch(0.98 0.02 280); --foreground: oklch(0.15 0.05 260);
      --card: oklch(1 0 0); --primary: oklch(0.55 0.15 280);
      --secondary: oklch(0.75 0.12 45); --border: oklch(0.9 0.02 280);
    }
    body { background-color: var(--background); color: var(--foreground); }
    .bg-primary { background-color: var(--primary); }
    .bg-secondary { background-color: var(--secondary); }
    .bg-card { background-color: var(--card); }
    /* Estilo para o Modal */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.5); display: none; justify-content: center;
        align-items: center; z-index: 60;
    }
    .modal-overlay.open { display: flex; }
    .modal-content {
        background-color: var(--card); border-radius: 0.75rem; padding: 1.5rem;
        width: 90%; max-width: 500px; box-shadow: 0 20px 25px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <header class="sticky top-0 z-50 w-full border-b backdrop-blur bg-white/95 border-border">
    <div class="container mx-auto flex h-16 items-center justify-between px-4 max-w-6xl">
      <div class="flex items-center space-x-2">
        <span class="text-xl font-bold text-foreground">BeanCode | Responsável</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="text-sm font-medium text-gray-600">Bem-vindo(a), <?php echo htmlspecialchars($responsible_name); ?>!</span>
        <a href="logout.php" class="px-4 py-2 text-sm font-medium rounded-lg bg-secondary text-white hover:opacity-90 transition-opacity">Sair</a>
      </div>
    </div>
  </header>

  <main class="flex-grow py-12 lg:py-20">
    <div class="container mx-auto px-4 max-w-6xl">
      <h1 class="text-4xl font-bold mb-10 text-center">Painel de Controle Familiar 👨‍👩‍👧‍👦</h1>
      
      <?php if ($alert_message): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
              <span class="block sm:inline"><?php echo $alert_message; ?></span>
          </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 bg-card p-6 rounded-xl shadow-lg border border-border h-fit">
          <h2 class="text-2xl font-bold text-primary mb-4 flex items-center gap-2">
            <span class="text-3xl">🔔</span> Últimas Conquistas
          </h2>
          <div class="space-y-4">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="bg-purple-50 p-3 rounded-lg border-l-4 border-primary">
                        <p class="text-sm font-semibold text-foreground"><?php echo htmlspecialchars($n['child_name']); ?>:</p>
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($n['message']); ?></p>
                        <p class="text-xs text-muted-foreground mt-1"><?php echo htmlspecialchars($n['time']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">Nenhuma conquista nova ainda. ✨</p>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-card p-8 rounded-xl shadow-lg border border-border">
                <h2 class="text-2xl font-bold text-foreground mb-4 flex items-center justify-between">
                    Programadores Cadastrados (<?php echo count($alunos); ?>)
                    <button onclick="openModal('registerAlunoModal')" class="bg-primary text-white px-4 py-2 text-sm rounded-lg hover:opacity-90 transition-opacity">
                        + Novo Programador
                    </button>
                </h2>
                
                <div class="space-y-4">
                    <?php if (!empty($alunos)): ?>
                        <?php foreach ($alunos as $aluno): ?>
                            <div class="flex justify-between items-center p-4 bg-muted rounded-lg border">
                                <div>
                                    <p class="text-lg font-semibold text-primary"><?php echo htmlspecialchars($aluno['nome_user']); ?></p>
                                    <p class="text-sm text-gray-600">Nome Completo: <?php echo htmlspecialchars($aluno['nome_completo']); ?></p>
                                    <p class="text-sm text-gray-600">Nascimento: <?php echo date('d/m/Y', strtotime($aluno['data_nasc'])); ?></p>
                                    </div>
                                <a href="course-track.php?aluno_id=<?php echo $aluno['id']; ?>" class="text-sm font-medium text-secondary hover:underline">
                                    Monitorar Progresso
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center p-6 bg-yellow-50 rounded-lg border border-yellow-200">
                            <p class="text-lg font-medium text-yellow-800">Nenhum aluno cadastrado ainda.</p>
                            <button onclick="openModal('registerAlunoModal')" class="text-primary hover:underline mt-2 inline-block">Clique aqui para começar o cadastro!</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            </div>
      </div>

    </div>
  </main>

  <footer class="border-t border-gray-200 bg-white">
    <div class="container mx-auto px-4 py-6 text-center max-w-6xl">
      <p class="text-sm text-gray-600">© 2025 BeanCode. Painel de Responsável.</p>
    </div>
  </footer>

  <div id="registerAlunoModal" class="modal-overlay">
    <div class="modal-content">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-primary">Cadastrar Novo Programador</h2>
        <button onclick="closeModal('registerAlunoModal')" class="text-gray-400 hover:text-gray-600">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <form action="process_dependents.php" method="POST" class="space-y-4">
        
        <div>
          <label for="aluno-user-name" class="block text-sm font-medium text-foreground mb-1">Nome do Usuário</label>
          <input type="text" id="aluno-user-name" name="nome_user" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        </div>
        <div>
          <label for="aluno-full-name" class="block text-sm font-medium text-foreground mb-1">Nome Completo</label>
          <input type="text" id="aluno-full-name" name="nome_completo" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        </div>
        <div>
          <label for="aluno-email" class="block text-sm font-medium text-foreground mb-1">E-mail</label>
          <input type="email" id="aluno-email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        </div>
        <div>
          <label for="aluno-dob" class="block text-sm font-medium text-foreground mb-1">Data de Nascimento</label>
          <input type="date" id="aluno-dob" name="data_nasc" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        </div>
        
        <div>
          <label for="aluno-password" class="block text-sm font-medium text-foreground mb-1">Senha</label>
          <div class="relative">
            <input type="password" id="aluno-password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
            <button type="button" onclick="togglePasswordVisibility('aluno-password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
          </div>
        </div>
        <div>
          <label for="aluno-repeat-password" class="block text-sm font-medium text-foreground mb-1">Repetir Senha</label>
          <div class="relative">
            <input type="password" id="aluno-repeat-password" name="repeat_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
             <button type="button" onclick="togglePasswordVisibility('aluno-repeat-password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
          </div>
        </div>
        
        <input type="hidden" name="submit_new_aluno" value="1"> 
        <input type="hidden" name="trilha_ativa" value="iniciante">

        <button type="submit" class="w-full bg-secondary text-white py-2 rounded-lg font-semibold hover:opacity-90 transition-opacity">
            Cadastrar Programador
        </button>
      </form>

    </div>
  </div>


  <script>
    function openModal(modalId) {
      document.getElementById(modalId).classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
      document.getElementById(modalId).classList.remove('open');
      document.body.style.overflow = '';
    }
    
    // Função de visibilidade de senha (replicada do index.php)
    function togglePasswordVisibility(inputId, buttonElement) {
      const passwordInput = document.getElementById(inputId);
      const isPassword = passwordInput.type === 'password';

      passwordInput.type = isPassword ? 'text' : 'password';

      const svg = buttonElement.querySelector('svg');
      if (isPassword) {
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.666-3.235m1.52-1.52c.24-.24.512-.45.81-.628M15 12a3 3 0 11-6 0 3 3 0 016 0zm-3 3a3 3 0 100-6 3 3 0 000 6z"/>';
      } else {
         svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
      }
    }
  </script>

</body>
</html>