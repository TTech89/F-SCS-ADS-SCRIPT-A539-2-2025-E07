<?php
session_start();
$user_name = "Alex"; // Simulação de dependente logado
$track_title = "Criador de Jogos";
$track_emoji = "🎮";
$track_color = "text-secondary";
$track_bg = "bg-orange-100";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BeanCode - Lição: Game Loop</title>
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
    .text-secondary { color: var(--secondary); }
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
        <a href="course-track.php" class="px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">Voltar para Trilhas</a>
        <a href="logout.php" class="px-4 py-2 text-sm font-medium rounded-lg bg-secondary text-white hover:opacity-90 transition-opacity">Sair</a>
      </nav>
    </div>
  </header>

  <main class="flex-grow py-12 lg:py-20">
    <div class="container mx-auto px-4 max-w-4xl">
      <div class="bg-card p-8 rounded-xl shadow-2xl border-t-8 border-secondary">
        
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full <?php echo $track_bg . ' ' . $track_color; ?>">
                <?php echo $track_emoji; ?> Trilha: <?php echo $track_title; ?>
            </span>
            <h1 class="text-4xl font-bold mt-4">Lição 1.1: O Coração de Todo Jogo (Game Loop)</h1>
            <p class="text-gray-600 mt-2">Como os jogos fazem as coisas se moverem o tempo todo?</p>
        </div>

        <hr class="mb-8 border-gray-200">

        <section class="space-y-8">
            <h2 class="text-2xl font-semibold text-secondary">O Que é o Game Loop? 🔄</h2>
            <div class="bg-gray-50 p-6 rounded-lg border">
                <p class="text-lg mb-4">O **Game Loop** (ou Loop de Jogo) é o coração invisível que bate dentro de cada jogo. Ele é um código que fica repetindo **milhares de vezes por segundo** e garante que o mundo do jogo esteja sempre atualizado.</p>
                
                <p class="font-semibold text-secondary mt-4">O ciclo é sempre o mesmo:</p>
                <ol class="list-decimal list-inside text-gray-700 space-y-2 ml-4">
                    <li>**Input (Entrada):** O jogo verifica se você apertou alguma tecla ou clicou em algo.</li>
                    <li>**Update (Atualização):** O jogo move inimigos, calcula a gravidade e vê se houve colisões.</li>
                    <li>**Render (Desenho):** O jogo desenha tudo na tela com a nova posição.</li>
                </ol>
                
            </div>

            <h2 class="text-2xl font-semibold text-secondary">A Palavra Mágica: `while (running)`</h2>
            <div class="bg-yellow-100 p-6 rounded-lg border border-yellow-300">
                <p class="text-lg font-semibold text-yellow-800">Em código real, o Game Loop se parece com isso (em pseudocódigo):</p>
                <pre class="bg-gray-800 text-white p-3 rounded-lg text-sm font-mono mt-3">
while (Jogo está rodando) {
    Verificar_Entradas();
    Atualizar_Posicoes_e_Logica();
    Desenhar_Tela();
}
                </pre>
                <p class="mt-4 text-gray-700">Enquanto a condição de que o jogo está rodando for verdadeira, o loop continua infinitamente! Se o personagem morrer ou você vencer, a condição muda, e o loop para.</p>
            </div>

            <h2 class="text-2xl font-semibold text-secondary">Seu Desafio de Hoje 🏃</h2>
            <div class="bg-orange-100 p-6 rounded-lg border border-orange-300 space-y-3">
                <p class="text-lg font-medium">Crie um loop que faz um objeto se mover e, ao mesmo tempo, verifica se ele tocou em um obstáculo. Se tocar, use o comando **`break`** para sair do loop!</p>
                <button class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity mt-4">
                    Abrir Editor de Código
                </button>
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