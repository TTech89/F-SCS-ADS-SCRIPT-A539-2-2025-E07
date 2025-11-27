<?php
session_start();
$user_name = "Alex"; // Simulação de dependente logado
$track_title = "Mago da Web";
$track_emoji = "🧙‍♂️";
$track_color = "text-accent";
$track_bg = "bg-teal-100";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BeanCode - Lição: Estrutura HTML</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Estilos base mantidos */
    :root {
      --background: oklch(0.98 0.02 280);
      --foreground: oklch(0.15 0.05 260);
      --card: oklch(1 0 0);
      --primary: oklch(0.55 0.15 280);
      --secondary: oklch(0.75 0.12 45);
      --accent: oklch(0.65 0.18 160);
    }
    body { background-color: var(--background); color: var(--foreground); font-family: system-ui, -apple-system, sans-serif; }
    .bg-primary { background-color: var(--primary); }
    .bg-secondary { background-color: var(--secondary); }
    .text-primary { color: var(--primary); }
    .text-accent { color: var(--accent); }
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
      <div class="bg-card p-8 rounded-xl shadow-2xl border-t-8 border-accent">
        
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full <?php echo $track_bg . ' ' . $track_color; ?>">
                <?php echo $track_emoji; ?> Trilha: <?php echo $track_title; ?>
            </span>
            <h1 class="text-4xl font-bold mt-4">Lição 1.1: A Esqueletagem Mágica do HTML</h1>
            <p class="text-gray-600 mt-2">Construindo a fundação de qualquer site: o HTML.</p>
        </div>

        <hr class="mb-8 border-gray-200">

        <section class="space-y-8">
            <h2 class="text-2xl font-semibold text-accent">Conceito Básico: O HTML é o Esqueleto 🦴</h2>
            <div class="bg-gray-50 p-6 rounded-lg border">
                <p class="text-lg mb-4">HTML (**H**yper**T**ext **M**arkup **L**anguage) não é uma linguagem de programação, mas sim uma **linguagem de marcação**. Ele define a **estrutura** do seu site, como onde fica o título, os parágrafos e as imagens.</p>
                
                <p class="font-semibold text-accent mt-4">Todo documento HTML tem uma estrutura essencial:</p>
                <pre class="bg-gray-800 text-white p-3 rounded-lg text-sm font-mono mt-3">
&lt;!DOCTYPE html&gt;
&lt;html lang="pt-BR"&gt;
    &lt;head&gt;
        &lt;title&gt;Meu Primeiro Site&lt;/title&gt;
    &lt;/head&gt;
    &lt;body&gt;
        &lt;h1&gt;Bem-vindo!&lt;/h1&gt;
        &lt;p&gt;Este é o conteúdo principal.&lt;/p&gt;
    &lt;/body&gt;
&lt;/html&gt;
                </pre>
            </div>

            <h2 class="text-2xl font-semibold text-accent">As Tags Mágicas e o Navegador</h2>
            <div class="bg-teal-100 p-6 rounded-lg border border-teal-300">
                <p class="text-lg font-semibold text-teal-800">Tags são como caixas que dizem ao navegador o que é o quê:</p>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4 mt-3">
                    <li>A tag **`<head>`** contém informações invisíveis ao usuário (ex: título da aba).</li>
                    <li>A tag **`<body>`** contém tudo que o usuário vê (textos, imagens, botões).</li>
                    <li>A tag **`<h1>`** é para o título mais importante.</li>
                    <li>A tag **`<p>`** é para parágrafos de texto normal.</li>
                </ul>
            </div>

            <h2 class="text-2xl font-semibold text-accent">Seu Desafio de Hoje 🏗️</h2>
            <div class="bg-yellow-100 p-6 rounded-lg border border-yellow-300 space-y-3">
                <p class="text-lg font-medium">No editor, crie a estrutura básica do HTML e adicione o título `<h1>` e um parágrafo `<p>` com informações sobre seu filme favorito!</p>
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