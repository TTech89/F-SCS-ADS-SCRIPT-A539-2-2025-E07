<?php
// ==========================================================
// LÓGICA DE LOGIN, SESSÃO E BANCO DE DADOS (INTEGRADA DO login.php)
// ==========================================================

// 1. Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// 2. Inclui a conexão com o banco de dados (db.php deve existir)
include 'db.php'; 

$is_logged_in = isset($_SESSION['user_id']); 
$error_message = '';
$success_message = isset($_GET['status']) && $_GET['status'] == 'registered' ? 'Cadastro concluído com sucesso! Faça login abaixo. 🎉' : '';

// 3. Processa a submissão do formulário de Login do Modal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    
    // Conecta e sanitiza
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Tentar login como RESPONSÁVEL (usando a tabela 'responsaveis')
    // Nota: A coluna de senha deve ser a mesma usada no cadastro (ex: 'senha_hash' ou 'senha')
    $sql = "SELECT id, senha, nome_completo FROM responsaveis WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verifica a senha. Ajuste 'senha' para a coluna correta de hash no seu BD (ex: 'senha_hash')
            // Baseado na imagem e na sua lógica anterior, vou usar 'senha' (que deve conter o HASH)
            if (password_verify($password, $user['senha'])) { 
                // Login de RESPONSÁVEL bem-sucedido!
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = 'responsible'; 
                $_SESSION['nome_responsavel'] = $user['nome_completo'];
                
                // Redireciona para o Painel do Responsável
                header("Location: dashboard_responsavel.php");
                exit();
            } else {
                $error_message = "Email ou senha mágica incorretos. 🧙‍♂️";
            }
        } else {
            // Se o email não for encontrado na tabela de responsáveis
            $error_message = "Email ou senha mágica incorretos. 🧙‍♂️";
        }
        $stmt->close();
    } else {
         $error_message = "Erro de preparação da consulta: " . $conn->error;
    }
}

// 4. Fecha a conexão com o BD
$conn->close();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BeanCode - Programar é INCRÍVEL!</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --background: oklch(0.98 0.02 280);
      --foreground: oklch(0.15 0.05 260);
      --card: oklch(1 0 0);
      --card-foreground: oklch(0.15 0.05 260);
      --primary: oklch(0.55 0.15 280);
      --primary-foreground: oklch(0.98 0 0);
      --secondary: oklch(0.75 0.12 45);
      --secondary-foreground: oklch(0.98 0 0);
      --muted: oklch(0.95 0.02 280);
      --muted-foreground: oklch(0.45 0.05 260);
      --accent: oklch(0.65 0.18 160);
      --accent-foreground: oklch(0.98 0 0);
      --border: oklch(0.9 0.02 280);
    }

    body {
      background-color: var(--background);
      color: var(--foreground);
      font-family: system-ui, -apple-system, sans-serif;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    @keyframes bounce-gentle {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }

    .animate-float {
      animation: float 3s ease-in-out infinite;
    }

    .animate-bounce-gentle {
      animation: bounce-gentle 2s ease-in-out infinite;
    }

    .bg-primary { background-color: var(--primary); }
    .bg-secondary { background-color: var(--secondary); }
    .bg-accent { background-color: var(--accent); }
    .bg-card { background-color: var(--card); }
    .bg-muted { background-color: var(--muted); }
    .text-primary { color: var(--primary); }
    .text-secondary { color: var(--secondary); }
    .text-accent { color: var(--accent); }
    .text-foreground { color: var(--foreground); }
    .text-muted-foreground { color: var(--muted-foreground); }
    .text-card-foreground { color: var(--card-foreground); }
    .text-primary-foreground { color: var(--primary-foreground); }
    .text-secondary-foreground { color: var(--secondary-foreground); }
    .border-border { border-color: var(--border); }

    #mobile-menu { display: none; }
    #mobile-menu-toggle:checked ~ #mobile-menu { display: block; }
    
    /* Estilo para o Modal (Pop-up) */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none; /* Inicia oculto */
        justify-content: center;
        align-items: center;
        z-index: 60;
    }
    .modal-content {
        background-color: var(--card);
        border-radius: 0.75rem;
        padding: 1.5rem;
        width: 90%;
        max-width: 450px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: scale(0.95);
        opacity: 0;
        transition: all 0.3s ease-out;
    }
    .modal-overlay.open .modal-content {
        transform: scale(1);
        opacity: 1;
    }
    .modal-overlay.open {
        display: flex;
    }
  </style>
</head>
<body class="min-h-screen">
   
  <header class="sticky top-0 z-50 w-full border-b backdrop-blur bg-white/95 border-border">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
      <div class="flex items-center space-x-2">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
          <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
          </svg>
        </div>
        <span class="text-xl font-bold text-foreground">BeanCode</span>
      </div>

       
      <nav class="hidden md:flex items-center space-x-8">
        <a href="#features" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Recursos</a>
        <a href="#courses" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Cursos</a>
        <a href="#testimonials" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Avaliações</a>
      </nav>

      <div class="hidden md:flex items-center space-x-4">
        <?php if ($is_logged_in): ?>
             <a href="dashboard_responsavel.php" class="px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">Meu Painel</a>
            <a href="logout.php" class="px-4 py-2 text-sm font-medium rounded-lg bg-secondary text-white hover:opacity-90 transition-opacity">Sair</a>
        <?php else: ?>
             <button onclick="openModal('loginModal')" class="px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">Entrar</button>
            <button onclick="openModal('registerModal')" class="px-4 py-2 text-sm font-medium rounded-lg bg-secondary text-white hover:opacity-90 transition-opacity">Começar</button>
        <?php endif; ?>
      </div>

       
      <label for="mobile-menu-toggle" class="md:hidden cursor-pointer">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </label>
      <input type="checkbox" id="mobile-menu-toggle" class="hidden">
    </div>

    
    <div id="mobile-menu" class="md:hidden border-t border-border bg-white">
      <nav class="container mx-auto px-4 py-4 space-y-4">
        <a href="#features" class="block text-sm font-medium text-muted-foreground hover:text-foreground">Recursos</a>
        <a href="#courses" class="block text-sm font-medium text-muted-foreground hover:text-foreground">Cursos</a>
        <a href="#testimonials" class="block text-sm font-medium text-muted-foreground hover:text-foreground">Avaliações</a>
        <div class="pt-4 space-y-2">
            <?php if ($is_logged_in): ?>
                <a href="dashboard_responsavel.php" class="w-full px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-left">Meu Painel</a>
                <a href="logout.php" class="w-full px-4 py-2 text-sm font-medium rounded-lg bg-secondary text-white hover:opacity-90 transition-opacity text-left">Sair</a>
            <?php else: ?>
                <button onclick="openModal('loginModal')" class="w-full px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-left">Entrar</button>
                <button onclick="openModal('registerModal')" class="w-full px-4 py-2 text-sm font-medium rounded-lg bg-secondary text-white hover:opacity-90 transition-opacity text-left">Começar</button>
            <?php endif; ?>
        </div>
      </nav>
    </div>
  </header>
  
  <main>
    
    <section class="relative overflow-hidden bg-gradient-to-br from-white via-purple-50 to-teal-50 py-20 lg:py-32">
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-purple-200 opacity-30 blur-3xl animate-float"></div>
        <div class="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-teal-200 opacity-30 blur-3xl animate-bounce-gentle"></div>
        <div class="absolute top-1/2 left-1/2 h-60 w-60 rounded-full bg-orange-200 opacity-20 blur-3xl animate-float" style="animation-delay: 1s;"></div>
      </div>

      <div class="container mx-auto relative px-4">
        <div class="mx-auto max-w-4xl text-center">
          <div class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-4 py-2 text-sm font-medium mb-8">
            <svg class="h-4 w-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            Lógica de Programação floresce tão rápido quanto um broto de feijão!
          </div>

          <h1 class="text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl mb-6">
            Programar é 
            <span class="bg-gradient-to-r from-purple-600 via-teal-500 to-orange-500 bg-clip-text text-transparent">
              INCRÍVEL!
            </span>
          </h1>

          <p class="mx-auto max-w-2xl text-lg text-gray-600 mb-8 leading-relaxed">
            Crie jogos, sites e animações incríveis! É grátis e super divertido.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <button onclick="openModal('registerModal')" class="bg-primary text-white hover:opacity-90 px-8 py-4 text-lg font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2">
              Quero Começar!
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
              </svg>
            </button>
            <button class="px-8 py-4 text-lg font-semibold rounded-xl border-2 border-gray-300 hover:bg-teal-50 hover:border-teal-400 transition-all duration-300 bg-transparent flex items-center gap-2">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Ver Como Funciona
            </button>
          </div>

          <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
            <div class="flex">
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <span class="ml-2 font-medium">Crianças adoram!</span>
          </div>

          <div class="mt-16 relative">
            <div class="mx-auto w-80 h-80 bg-gradient-to-br from-purple-200 via-teal-200 to-orange-200 rounded-3xl flex items-center justify-center animate-float">
              <div class="text-6xl animate-bounce-gentle">🧙‍♂️</div>
            </div>
            <div class="absolute top-10 left-10 bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono animate-float" style="animation-delay: 0.5s;">
              &lt;hello /&gt;
            </div>
            <div class="absolute top-20 right-10 bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono animate-bounce-gentle" style="animation-delay: 1.5s;">
              if (fun) { code() }
            </div>
            <div class="absolute bottom-10 left-20 bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono animate-float" style="animation-delay: 2s;">
              print("Amazing!")
            </div>
          </div>
        </div>
      </div>
    </section>


    <section id="features" class="py-20 lg:py-32 bg-purple-50">
      <div class="container mx-auto px-4">
        <div class="mx-auto max-w-2xl text-center mb-16">
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4">
            Por Que Você Vai Amar Programar
          </h2>
          <p class="text-lg text-gray-600 leading-relaxed">
            Tudo foi feito para ser super divertido!
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="mb-4">
              <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-primary">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Jogos Viciantes</h3>
            <p class="text-gray-600 leading-relaxed">Cada lição é um jogo novo! Você vai querer programar mais e mais.</p>
          </div>

            
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="mb-4">
              <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 text-secondary">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Conquistas Épicas</h3>
            <p class="text-gray-600 leading-relaxed">Ganhe medalhas e troféus a cada vitória!</p>
          </div>

         
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="mb-4">
              <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-accent">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Amigos Programadores</h3>
            <p class="text-gray-600 leading-relaxed">Conheça outros jovens programadores e compartilhe suas criações.</p>
          </div>

           
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="mb-4">
              <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-primary">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Magia Instantânea</h3>
            <p class="text-gray-600 leading-relaxed">Veja seu código ganhar vida na hora!</p>
          </div>

   
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="mb-4">
              <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 text-secondary">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Ambiente Seguro</h3>
            <p class="text-gray-600 leading-relaxed">Um lugar seguro para aprender e se divertir.</p>
          </div>

       
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="mb-4">
              <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-accent">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Cresce Contigo</h3>
            <p class="text-gray-600 leading-relaxed">Do básico até jogos complexos - sempre no seu ritmo.</p>
          </div>
        </div>
      </div>
    </section>

    
    <section id="courses" class="py-20 lg:py-32">
      <div class="container mx-auto px-4">
        <div class="mx-auto max-w-2xl text-center mb-16">
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4">
            Escolha Sua Aventura
          </h2>
          <p class="text-lg text-gray-600 leading-relaxed">Qual você quer começar?</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
           
          <div class="group hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-gray-200 rounded-xl overflow-hidden bg-white">
            <div class="p-6 pb-4">
              <div class="flex items-center justify-between mb-2">
                <span class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-primary border border-purple-200">Iniciante</span>
                <span class="text-2xl animate-bounce-gentle">🌟</span>
              </div>
              <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Primeiros Passos</h3>
              <p class="text-gray-600">Comece aqui! Aprenda com blocos coloridos e personagens divertidos.</p>
            </div>
            <div class="px-6 pt-0 pb-6">
              <div class="space-y-3 mb-6">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                  <span>Idades 6-8 anos</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span>4 semanas</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                  <span>4.9 • 12.000+ crianças</span>
                </div>
              </div>
              <button onclick="openModal('registerModal')" class="w-full text-center block bg-primary text-white hover:opacity-90 py-3 rounded-lg font-semibold transition-all duration-300 group-hover:shadow-lg">
                Quero Começar!
              </button>
            </div>
          </div>

       
          <div class="group hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-gray-200 rounded-xl overflow-hidden bg-white">
            <div class="p-6 pb-4">
              <div class="flex items-center justify-between mb-2">
                <span class="px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-secondary border border-orange-200">Intermediário</span>
                <span class="text-2xl animate-bounce-gentle" style="animation-delay: 0.5s;">🎮</span>
              </div>
              <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Criador de Jogos</h3>
              <p class="text-gray-600">Faça seus próprios jogos! É mais fácil do que parece.</p>
            </div>
            <div class="px-6 pt-0 pb-6">
              <div class="space-y-3 mb-6">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                  <span>Idades 9-12 anos</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span>8 semanas</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                  <span>4.8 • 8.500+ crianças</span>
                </div>
              </div>
              <button onclick="openModal('registerModal')" class="w-full text-center block bg-primary text-white hover:opacity-90 py-3 rounded-lg font-semibold transition-all duration-300 group-hover:shadow-lg">
                Quero Começar!
              </button>
            </div>
          </div>

         
          <div class="group hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-gray-200 rounded-xl overflow-hidden bg-white">
            <div class="p-6 pb-4">
              <div class="flex items-center justify-between mb-2">
                <span class="px-3 py-1 rounded-full text-sm font-medium bg-teal-100 text-accent border border-teal-200">Avançado</span>
                <span class="text-2xl animate-bounce-gentle" style="animation-delay: 1s;">🧙‍♂️</span>
              </div>
              <h3 class="text-xl font-semibold mb-2 group-hover:text-primary transition-colors">Mago da Web</h3>
              <p class="text-gray-600">Crie sites incríveis que todo mundo vai querer ver!</p>
            </div>
            <div class="px-6 pt-0 pb-6">
              <div class="space-y-3 mb-6">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                  <span>Idades 11-14 anos</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span>12 semanas</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                  <span>4.9 • 6.200+ crianças</span>
                </div>
              </div>
              <button onclick="openModal('registerModal')" class="w-full text-center block bg-primary text-white hover:opacity-90 py-3 rounded-lg font-semibold transition-all duration-300 group-hover:shadow-lg">
                Quero Começar!
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    
    <section id="testimonials" class="py-20 lg:py-32 bg-purple-50">
      <div class="container mx-auto px-4">
        <div class="mx-auto max-w-2xl text-center mb-16">
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4">
            O Que os Pais Estão Dizendo
          </h2>
          <p class="text-lg text-gray-600 leading-relaxed">
            Junte-se a milhares de famílias que descobriram a alegria de programar juntas.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
           
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="flex items-center gap-1 mb-4">
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <p class="text-gray-700 leading-relaxed mb-4">"Emma passou de não saber nada sobre programação para construir seus próprios jogos em apenas 2 meses! Ela fica muito animada com cada lição."</p>
            <div class="flex items-center gap-3">
              <div class="text-2xl">👩‍💼</div>
              <div>
                <p class="font-semibold">Maria Silva</p>
                <p class="text-sm text-gray-600">Mãe da Emma (9 anos)</p>
              </div>
            </div>
          </div>

           
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="flex items-center gap-1 mb-4">
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <p class="text-gray-700 leading-relaxed mb-4">"O acompanhamento de progresso me ajuda a ver exatamente o que Alex está aprendendo. O currículo tem o ritmo perfeito para sua faixa etária."</p>
            <div class="flex items-center gap-3">
              <div class="text-2xl">👨‍💻</div>
              <div>
                <p class="font-semibold">João Santos</p>
                <p class="text-sm text-gray-600">Pai do Alex (12 anos)</p>
              </div>
            </div>
          </div>

            
          <div class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-200 bg-white rounded-xl p-6">
            <div class="flex items-center gap-1 mb-4">
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-4 w-4 fill-orange-400 text-orange-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <p class="text-gray-700 leading-relaxed mb-4">"Sofia pede para fazer suas lições de programação todos os dias! É incrível como eles fazem a programação parecer brincadeira."</p>
            <div class="flex items-center gap-3">
              <div class="text-2xl">👩‍🏫</div>
              <div>
                <p class="font-semibold">Ana Costa</p>
                <p class="text-sm text-gray-600">Mãe da Sofia (7 anos)</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    
    <section class="py-20 lg:py-32 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-purple-100 via-teal-100 to-orange-100"></div>

      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-10 left-10 h-20 w-20 rounded-full bg-purple-300 opacity-40 blur-xl animate-float"></div>
        <div class="absolute bottom-10 right-10 h-32 w-32 rounded-full bg-teal-300 opacity-40 blur-xl animate-bounce-gentle"></div>
        <div class="absolute top-1/2 left-1/3 h-16 w-16 rounded-full bg-orange-300 opacity-30 blur-xl animate-float" style="animation-delay: 1s;"></div>
      </div>

      <div class="container mx-auto relative px-4">
        <div class="mx-auto max-w-3xl text-center">
          <h2 class="text-3xl font-bold tracking-tight sm:text-5xl mb-6">
            Pronto para 
            <span class="bg-gradient-to-r from-purple-600 via-teal-500 to-orange-500 bg-clip-text text-transparent">
              Criar Coisas Incríveis?
            </span>
          </h2>

          <p class="text-lg text-gray-600 leading-relaxed mb-8 max-w-2xl mx-auto">
            Mais de 50.000 crianças já estão criando jogos, apps e projetos incríveis. Você vai adorar!
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <button onclick="openModal('registerModal')" class="bg-primary text-white hover:opacity-90 px-8 py-4 text-lg font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 group">
              Começar a Diversão Agora
              <svg class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
              </svg>
            </button>
            <button class="px-8 py-4 text-lg font-semibold rounded-xl border-2 border-gray-300 hover:bg-teal-50 hover:border-teal-400 transition-all duration-300 bg-transparent flex items-center gap-2">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
              </svg>
              Ver Projetos das Crianças
            </button>
          </div>
        </div>
      </div>
    </section>
  </main>

 
  <footer class="border-t border-gray-200 bg-purple-50">
    <div class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        
        <div class="space-y-4">
          <div class="flex items-center space-x-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
              <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
              </svg>
            </div>
            <span class="text-xl font-bold">BeanCode</span>
          </div>
          <p class="text-sm text-gray-600">
            Capacitando a próxima geração de programadores através de experiências de aprendizado divertidas e interativas.
          </p>
        </div>

          
        <div class="space-y-4">
          <h3 class="font-semibold">Cursos</h3>
          <ul class="space-y-2 text-sm text-gray-600">
            <li><a href="#" class="hover:text-gray-900 transition-colors">Fundamentos da Programação</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Desenvolvimento de Jogos</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Desenvolvimento Web</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Apps Mobile</a></li>
          </ul>
        </div>

          
        <div class="space-y-4">
          <h3 class="font-semibold">Suporte</h3>
          <ul class="space-y-2 text-sm text-gray-600">
            <li><a href="#" class="hover:text-gray-900 transition-colors">Central de Ajuda</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Guia dos Pais</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Segurança</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Fale Conosco</a></li>
          </ul>
        </div>

          
        <div class="space-y-4">
          <h3 class="font-semibold">Empresa</h3>
          <ul class="space-y-2 text-sm text-gray-600">
            <li><a href="#" class="hover:text-gray-900 transition-colors">Sobre Nós</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Carreiras</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Política de Privacidade</a></li>
            <li><a href="#" class="hover:text-gray-900 transition-colors">Termos de Serviço</a></li>
          </ul>
        </div>
      </div>

      <div class="mt-12 pt-8 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
        <p class="text-sm text-gray-600">© 2025 BeanCode. Todos os direitos reservados.</p>
        <div class="flex items-center gap-1 text-sm text-gray-600">
          Feito com 
          <svg class="h-4 w-4 text-red-500 fill-red-500" viewBox="0 0 24 24">
            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
          para jovens programadores
        </div>
      </div>
    </div>
  </footer>


  <div id="loginModal" class="modal-overlay">
    <div class="modal-content">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Login</h2>
        <button onclick="closeModal('loginModal')" class="text-gray-400 hover:text-gray-600">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <form action="index.php" method="POST" class="space-y-4">
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="bg-teal-100 border border-teal-400 text-teal-700 px-4 py-3 rounded-lg text-sm" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <div>
          <label for="login-email" class="block text-sm font-medium text-foreground mb-1">E-mail</label>
          <input type="email" id="login-email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        </div>
        <div>
          <label for="login-password" class="block text-sm font-medium text-foreground mb-1">Senha</label>
          <div class="relative">
            <input type="password" id="login-password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
            <button type="button" onclick="togglePasswordVisibility('login-password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
          </div>
        </div>
        <button type="submit" name="login_submit" class="w-full bg-primary text-white py-2 rounded-lg font-semibold hover:opacity-90 transition-opacity">Entrar</button>
      </form>

      <p class="mt-4 text-center text-sm text-gray-600">
        Não tem conta? 
        <button onclick="closeModal('loginModal'); openModal('registerModal')" class="text-primary hover:underline font-medium">Cadastre-se aqui</button>
      </p>
    </div>
  </div>

  <div id="registerModal" class="modal-overlay">
    <div class="modal-content">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Cadastro do Responsável</h2>
        <button onclick="closeModal('registerModal')" class="text-gray-400 hover:text-gray-600">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <form action="process_register.php" method="POST" class="space-y-4">
        <div>
          <label for="register-name" class="block text-sm font-medium text-foreground mb-1">Nome Completo</label>
          <input type="text" id="register-name" name="full_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        </div>
        <div>
          <label for="register-email" class="block text-sm font-medium text-foreground mb-1">E-mail</label>
          <input type="email" id="register-email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
        </div>
        <div>
          <label for="register-password" class="block text-sm font-medium text-foreground mb-1">Senha</label>
          <div class="relative">
            <input type="password" id="register-password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
            <button type="button" onclick="togglePasswordVisibility('register-password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
          </div>
        </div>
        <div>
          <label for="register-repeat-password" class="block text-sm font-medium text-foreground mb-1">Repetir Senha</label>
          <div class="relative">
            <input type="password" id="register-repeat-password" name="repeat_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
             <button type="button" onclick="togglePasswordVisibility('register-repeat-password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
          </div>
        </div>
        <button type="submit" class="w-full bg-secondary text-white py-2 rounded-lg font-semibold hover:opacity-90 transition-opacity">Criar Conta</button>
      </form>

      <p class="mt-4 text-center text-sm text-gray-600">
        Já é cadastrado? 
        <button onclick="closeModal('registerModal'); openModal('loginModal')" class="text-secondary hover:underline font-medium">Fazer Login</button>
      </p>
    </div>
  </div>


  <script>
    /**
     * Abre o modal (pop-up) especificado pelo ID.
     * @param {string} modalId - O ID do elemento modal.
     */
    function openModal(modalId) {
      document.getElementById(modalId).classList.add('open');
      document.body.style.overflow = 'hidden'; // Evita rolagem do fundo
    }

    /**
     * Fecha o modal (pop-up) especificado pelo ID.
     * @param {string} modalId - O ID do elemento modal.
     */
    function closeModal(modalId) {
      document.getElementById(modalId).classList.remove('open');
      document.body.style.overflow = ''; // Restaura a rolagem
    }

    /**
     * Alterna a visibilidade do campo de senha (de password para text e vice-versa).
     * @param {string} inputId - O ID do campo de input de senha.
     * @param {HTMLElement} buttonElement - O botão de alternância que foi clicado.
     */
    function togglePasswordVisibility(inputId, buttonElement) {
      const passwordInput = document.getElementById(inputId);
      const isPassword = passwordInput.type === 'password';

      // 1. Altera o tipo do input
      passwordInput.type = isPassword ? 'text' : 'password';

      // 2. Altera o ícone do botão (opcional, mas bom para UX)
      const svg = buttonElement.querySelector('svg');
      if (isPassword) {
        // Mostra Senha: Ícone do Olho Aberto
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.666-3.235m1.52-1.52c.24-.24.512-.45.81-.628M15 12a3 3 0 11-6 0 3 3 0 016 0zm-3 3a3 3 0 100-6 3 3 0 000 6z"/>';
      } else {
        // Esconde Senha: Ícone do Olho Cortado
         svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
      }
    }

    // Fecha o modal ao clicar fora (no overlay)
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeModal(this.id);
            }
        });
    });
    
    // Abrir o modal de login automaticamente se houver mensagem de erro ou sucesso
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const hasError = "<?php echo $error_message; ?>";
        const isRegistered = urlParams.get('status') === 'registered';
        
        if (hasError || isRegistered) {
            openModal('loginModal');
        }
    }
  </script>

</body>
</html>