<?php
session_start();
$user_name = "Alex"; // Simulação de dependente logado
$track_title = "Primeiros Passos";
$track_emoji = "🌟";
$track_color_class = "text-primary";
$track_bg_class = "bg-purple-100";

// Variáveis de estado
$feedback_message = '';
$show_success_animation = false;
$expected_answer = 5;

// Lógica de Validação da Resposta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['answer_submit'])) {
    $student_answer = filter_input(INPUT_POST, 'num_blocks', FILTER_VALIDATE_INT);
    
    if ($student_answer === $expected_answer) {
        $feedback_message = "Resposta CORRETA! O Broto de Feijão está crescendo! 🎉";
        $show_success_animation = true;
        // **[AÇÃO REAL DO BD]**: Aqui você faria o UPDATE na tabela progresso_licoes
        // e possivelmente adicionaria XP/conquistas ao aluno.
    } else {
        $feedback_message = "Quase lá! Tente novamente. Lembre-se, o mascote precisa de 5 blocos para chegar em casa. 🧐";
        $show_success_animation = false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BeanCode - Exercício: Bloco Mover</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Estilos base mantidos do iniciante.php */
    :root {
      --background: oklch(0.98 0.02 280);
      --foreground: oklch(0.15 0.05 260);
      --card: oklch(1 0 0);
      --primary: oklch(0.55 0.15 280);
      --secondary: oklch(0.75 0.12 45);
    }
    body { background-color: var(--background); color: var(--foreground); font-family: system-ui, -apple-system, sans-serif; }
    .bg-primary { background-color: var(--primary); }
    .bg-secondary { background-color: var(--secondary); }
    .text-primary { color: var(--primary); }
    .border-border { border-color: var(--border); }

    /* ANIMAÇÃO DO BROTO DE FEIJÃO */
    @keyframes grow {
      0% { transform: scaleY(0.1) translateY(100px); opacity: 0; }
      50% { transform: scaleY(1.1) translateY(-10px); opacity: 1; }
      100% { transform: scaleY(1) translateY(0); opacity: 1; }
    }

    .seedling {
        font-size: 80px;
        transform-origin: bottom;
        animation: none; /* Inicia sem animação */
        opacity: 0;
    }
    .animate-grow {
        animation: grow 1.5s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards; /* Animação de crescimento com bounce */
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

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

      <nav class="flex items-center space-x-4">
        <a href="course-track.php" class="px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">Voltar para Trilhas</a>
        <a href="logout.php" class="px-4 py-2 text-sm font-medium rounded-lg bg-secondary text-white hover:opacity-90 transition-opacity">Sair</a>
      </nav>
    </div>
  </header>

  <main class="flex-grow py-12 lg:py-20">
    <div class="container mx-auto px-4 max-w-4xl">
      <div class="bg-card p-8 rounded-xl shadow-2xl border-t-8 border-primary">
        
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full <?php echo $track_bg_class . ' ' . $track_color_class; ?>">
                <?php echo $track_emoji; ?> Trilha: <?php echo $track_title; ?>
            </span>
            <h1 class="text-4xl font-bold mt-4">Exercício: O Bloco "Mover"</h1>
            <p class="text-gray-600 mt-2">Valide seu conhecimento!</p>
        </div>

        <hr class="mb-8 border-gray-200">

        <section class="space-y-8">
            <div class="bg-gray-50 p-6 rounded-lg border text-center">
                <p class="text-lg font-semibold mb-4">Lembre-se do desafio:</p>
                <p class="text-xl font-medium mb-6">Quantos blocos **"Mover (10) passos"** você precisa para que o BeanCode chegue à sua casa?</p>
                <div class="text-center text-4xl mb-4">
                    <span>🧙‍♂️</span> <span>→</span> <span>→</span> <span>→</span> <span>🏠</span>
                </div>

                <form method="POST" action="lesson_iniciante_exercise.php" class="max-w-xs mx-auto space-y-4">
                    <div>
                        <label for="num_blocks" class="block text-sm font-medium text-foreground mb-1">Número de Blocos (1-10):</label>
                        <input 
                            type="number" 
                            id="num_blocks" 
                            name="num_blocks" 
                            min="1" 
                            max="10" 
                            required 
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg text-center text-xl focus:outline-none focus:border-primary"
                        >
                    </div>
                    <button 
                        type="submit" 
                        name="answer_submit"
                        class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity"
                    >
                        Verificar Resposta
                    </button>
                </form>
            </div>
            
            <div id="feedback-area" class="text-center p-6 rounded-xl border border-dashed border-gray-400 min-h-[150px] flex flex-col justify-center items-center">
                <?php if ($feedback_message): ?>
                    <p class="text-xl font-bold mb-4 <?php echo $show_success_animation ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $feedback_message; ?>
                    </p>
                    
                    <?php if ($show_success_animation): ?>
                        <div id="seedling-container" class="mt-4">
                            <div id="seedling" class="seedling">🌱</div>
                        </div>
                        <a href="lesson_iniciante_2.php" class="bg-secondary text-white py-2 px-4 rounded-lg font-semibold hover:opacity-90 transition-opacity mt-4">
                            Próxima Lição
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                     <p class="text-gray-500">Aguardando sua resposta...</p>
                <?php endif; ?>
            </div>

        </section>

      </div>
    </div>
  </main>

  <footer class="border-t border-gray-200 bg-white">
    <div class="container mx-auto px-4 py-6 text-center">
      <p class="text-sm text-gray-600">© 2025 BeanCode. Programar é incrível!</p>
    </div>
  </footer>

  <script>
    // Se a resposta for correta, dispara a animação
    document.addEventListener('DOMContentLoaded', () => {
        const seedling = document.getElementById('seedling');
        const showAnimation = <?php echo $show_success_animation ? 'true' : 'false'; ?>;

        if (showAnimation && seedling) {
            // Remove a opacidade 0 inicial e adiciona a classe de animação
            seedling.style.opacity = 1;
            seedling.classList.add('animate-grow');
            
            // Opcional: Substituir o emoji após a animação para um efeito final
            setTimeout(() => {
                seedling.innerHTML = '🌳'; // De broto para árvore
                seedling.classList.remove('animate-grow');
            }, 1500); // Duração da animação
        }
    });
  </script>
</body>
</html>