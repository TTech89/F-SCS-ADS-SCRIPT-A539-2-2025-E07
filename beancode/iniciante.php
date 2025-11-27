<?php
session_start();
$user_name = "Alex"; // Simulação de dependente logado
$track_title = "Primeiros Passos";
$track_emoji = "🌟";
$track_color = "text-primary";
$track_bg = "bg-purple-100";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BeanCode - Lição: O Bloco Mover</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Estilos base mantidos */
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
        <nav class="flex items-center space-x-4">
        <a href="course-track.php" class="px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">Voltar para Trilhas</a>
        <a href="logout.php" class="px-4 py-2 text-sm font-medium rounded-lg bg-secondary text-white hover:opacity-90 transition-opacity">Sair</a>
      </nav>
      </nav>
    </div>
  </header>

  <main class="flex-grow py-12 lg:py-20">
    <div class="container mx-auto px-4 max-w-4xl">
      <div class="bg-card p-8 rounded-xl shadow-2xl border-t-8 border-primary">
        
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full <?php echo $track_bg . ' ' . $track_color; ?>">
                <?php echo $track_emoji; ?> Trilha: <?php echo $track_title; ?>
            </span>
            <h1 class="text-4xl font-bold mt-4">Lição 1.1: O Bloco "Mover" e a Coordenada X</h1>
            <p class="text-gray-600 mt-2">Vamos fazer nosso primeiro personagem se movimentar!</p>
        </div>

        <hr class="mb-8 border-gray-200">

        <section class="space-y-8">
            <h2 class="text-2xl font-semibold text-primary">Conceito Básico: O Mundo como um Plano</h2>
            <div class="bg-gray-50 p-6 rounded-lg border">
                <p class="text-lg mb-4">No mundo da programação, a tela é como um **grande mapa**. O movimento para os lados é controlado pela **Coordenada X** (horizontal).</p>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                    <li>Se a Coordenada **X AUMENTA**, seu personagem vai para a **DIREITA**.</li>
                    <li>Se a Coordenada **X DIMINUI**, seu personagem vai para a **ESQUERDA**.</li>
                </ul>
                [Image of X and Y axis coordinate system]
            </div>

            <h2 class="text-2xl font-semibold text-primary">O Bloco "Mover" ➡️</h2>
            <div class="bg-blue-100 p-6 rounded-lg border border-blue-300 flex items-start gap-4">
                <div class="text-4xl">🧱</div>
                <div>
                    <p class="text-lg font-semibold text-blue-700">Este é o bloco mais importante para começar:</p>
                    <p class="font-mono bg-white inline-block px-3 py-1 rounded shadow mt-2">Mover (10) passos</p>
                    <p class="mt-4 text-gray-700">O número dentro do bloco (**10**) diz ao computador o **quanto** ele deve aumentar o valor de X. Se você usá-lo 3 vezes, ele move 3 vezes a distância!</p>
                </div>
            </div>

            <h2 class="text-2xl font-semibold text-primary">Seu Desafio de Hoje 🚀</h2>
            <div class="bg-yellow-100 p-6 rounded-lg border border-yellow-300 space-y-3">
                <p class="text-lg font-medium">Use 5 blocos **"Mover (10) passos"** para que o BeanCode (nosso mascote) chegue à sua casa!</p>
                <div class="text-center text-4xl">
                    <span>🧙‍♂️</span> <span>→</span> <span>→</span> <span>→</span> <span>🏠</span>
                </div>
                <a href="lesson_iniciante_exercise.php" class="w-full bg-secondary text-white py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity mt-4 block text-center">
                    Abrir Editor de Blocos
                </a>
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

</body>
</html>