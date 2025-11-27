<?php
// course-track.php (CORRIGIDO E UNIFICADO PARA TROCA DE ABAS E PERSISTÊNCIA DE CONTEXTO)

session_start();
include 'db.php'; 

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Inicialização de variáveis
$responsible_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;
$user_id_to_view = 0; // ID do aluno/dependente sendo visualizado
$user_name = "Aluno";
$monitoring_mode = false;
$view_title = "Minha Trilha de Cursos";

// IDs que podem vir da URL (GET) ou da submissão do formulário (POST)
$aluno_id_to_pass = (int)($_GET['aluno_id'] ?? ($_POST['aluno_id'] ?? 0));


// =========================================================
// ESTRUTURA DE DADOS DAS TRILHAS (Conteúdo estático)
// =========================================================

$tracks = [
    'iniciante' => [
        'title' => 'Primeiros Passos', 'subtitle' => 'Lógica com Blocos Coloridos', 'emoji' => '🌟', 'level' => 'Iniciante',
        'bg' => 'bg-purple-100', 'border' => 'border-purple-200', 'color' => 'text-primary',
        'modules' => [
            ['title' => 'Módulo Básico 1: Descobrindo os Blocos Mágicos', 'slug' => 'iniciante', 'licoes' => [['id' => 101, 'title' => 'Lição 1.1: O Bloco "Mover" e a Coordenada X'], ['id' => 102, 'title' => 'Lição 1.2: Bloco "Repetir": Criando Loops Simples']]]
        ]
    ],
    'intermediario' => [
        'title' => 'Criador de Jogos', 'subtitle' => 'Desenvolvimento de Games Simples', 'emoji' => '🎮', 'level' => 'Intermediário',
        'bg' => 'bg-orange-100', 'border' => 'border-orange-200', 'color' => 'text-secondary',
        'modules' => [
            ['title' => 'Módulo Básico 1: Fundamentos de Movimento e Colisão', 'slug' => 'intermediario', 'licoes' => [['id' => 201, 'title' => 'Lição 1.1: Introdução ao Loop de Jogo (Game Loop)'], ['id' => 202, 'title' => 'Lição 1.2: Detectando Colisões Simples']]]
        ]
    ],
    'avancado' => [
        'title' => 'Mago da Web', 'subtitle' => 'Criação de Sites e Apps', 'emoji' => '🧙‍♂️', 'level' => 'Avançado',
        'bg' => 'bg-teal-100', 'border' => 'border-teal-200', 'color' => 'text-accent',
        'modules' => [
            ['title' => 'Módulo Básico 1: Criando Sua Primeira Página (HTML e CSS)', 'slug' => 'avancado', 'licoes' => [['id' => 301, 'title' => 'Lição 1.1: Estrutura Básica do HTML5'], ['id' => 302, 'title' => 'Lição 1.2: Estilizando com Classes e IDs (CSS)']]]
        ]
    ]
];

// =========================================================
// LÓGICA DE DEFINIÇÃO DE USUÁRIO E VERIFICAÇÃO DE SESSÃO
// =========================================================

// CORREÇÃO DE REDIRECIONAMENTO: A lógica garante que o aluno_id_to_pass seja validado.
if ($user_type === 'responsible' && $aluno_id_to_pass > 0) {
    
    // VERIFICAÇÃO DE SEGURANÇA: Garante que este aluno pertence ao responsável logado
    $sql_check = "SELECT id, nome_user FROM alunos WHERE id = ? AND responsavel_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $aluno_id_to_pass, $responsible_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        // Acesso negado
        header("Location: dashboard_responsavel.php?error=acesso_negado");
        exit();
    }
    
    $aluno_data = $result_check->fetch_assoc();
    $user_id_to_view = $aluno_data['id']; // ID DO ALUNO MONITORADO
    $user_name = $aluno_data['nome_user'];
    $monitoring_mode = true;
    $view_title = "Monitorando: " . htmlspecialchars($user_name);
    $stmt_check->close();
    
} elseif ($user_type === 'child') {
    // Se o próprio aluno estiver logado
    $user_id_to_view = $_SESSION['user_id']; // ID DO ALUNO LOGADO
    $user_name = $_SESSION['nome_aluno'] ?? 'Meu Perfil'; 
    $monitoring_mode = false;
    $view_title = "Minha Trilha de Cursos";
    $aluno_id_to_pass = $user_id_to_view; // O aluno logado é o alvo
    
} else {
    // Redireciona para o index se o contexto for inválido (e.g., responsável sem aluno_id)
    header("Location: index.php");
    exit();
}


// =========================================================
// LÓGICA DE SELEÇÃO E SALVAMENTO DA TRILHA (Integrado e Corrigido)
// =========================================================

$user_track = isset($_SESSION['user_track']) ? $_SESSION['user_track'] : null;

// Processa a submissão do formulário de seleção de trilha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_track'])) {
    $new_track = filter_var($_POST['selected_track'], FILTER_SANITIZE_STRING);
    
    if (isset($tracks[$new_track])) {
        $_SESSION['user_track'] = $new_track;
        $user_track = $new_track;
        
        // Redireciona, garantindo que o parâmetro aluno_id seja mantido.
        $redirect_url = "course-track.php";
        if ($aluno_id_to_pass > 0) {
            $redirect_url .= "?aluno_id=$aluno_id_to_pass";
        }
        header("Location: " . $redirect_url);
        exit;
    }
}

// Determina a trilha ativa e se o modal deve ser exibido
$has_selected_track = !empty($user_track);
$course_slug = $has_selected_track ? $user_track : 'iniciante'; 
$current_track_data = $tracks[$course_slug];


// =========================================================
// BUSCA DE PROGRESSO (Utiliza o BD anexo: progresso_licoes)
// =========================================================

$progresso_licoes = [];
// Simulação: Usamos o ID 1 como curso padrão de base (Iniciante)
$current_course_id = 1; 

$sql_progresso = "
    SELECT 
        l.id AS licao_id, 
        pl.concluida
    FROM progresso_licoes pl
    JOIN licoes l ON pl.licao_id = l.id
    JOIN modulos m ON l.modulo_id = m.id
    JOIN cursos c ON m.curso_id = c.id
    WHERE pl.aluno_id = ? AND c.id = ?
";
$stmt_prog = $conn->prepare($sql_progresso);

if ($stmt_prog) {
    // Usamos $user_id_to_view que é o ID do aluno/dependente
    $stmt_prog->bind_param("ii", $user_id_to_view, $current_course_id); 
    $stmt_prog->execute();
    $result_prog = $stmt_prog->get_result();

    while ($row = $result_prog->fetch_assoc()) {
        // Mapeia o progresso: $progresso_licoes[licao_id] = 0 (pendente) ou 1 (concluida)
        $progresso_licoes[$row['licao_id']] = (int)$row['concluida'];
    }
    $stmt_prog->close();
} 

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeanCode - Trilha de Cursos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* (Estilos base do projeto) */
        :root {
            --background: oklch(0.98 0.02 280); --foreground: oklch(0.15 0.05 260);
            --card: oklch(1 0 0); --primary: oklch(0.55 0.15 280);
            --secondary: oklch(0.75 0.12 45); --border: oklch(0.9 0.02 280);
        }
        body { background-color: var(--background); color: var(--foreground); }
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .bg-secondary { background-color: var(--secondary); }
        .text-secondary { color: var(--secondary); }
        .text-accent { color: oklch(0.65 0.18 160); }
        
        /* Estilo para o Modal */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5); display: none; justify-content: center;
            align-items: center; z-index: 60;
        }
        .modal-content {
            background-color: var(--card); border-radius: 0.75rem; padding: 1.5rem;
            width: 90%; max-width: 600px; box-shadow: 0 20px 25px rgba(0, 0, 0, 0.2);
            transform: scale(0.95); opacity: 0; transition: all 0.3s ease-out;
        }
        .modal-overlay.open .modal-content { transform: scale(1); opacity: 1; }
        .modal-overlay.open { display: flex; }
        .track-card { cursor: pointer; transition: all 0.2s ease-in-out; }
        .track-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .track-card.selected {
            border-width: 3px; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(88, 51, 153, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <header class="sticky top-0 z-50 w-full border-b backdrop-blur bg-white/95 border-border">
        <div class="container mx-auto flex h-16 items-center justify-between px-4 max-w-6xl">
            <div class="flex items-center space-x-2">
                <span class="text-xl font-bold text-foreground">BeanCode | Trilha Ativa: <?php echo htmlspecialchars($current_track_data['title']); ?></span>
            </div>
            <div class="flex items-center space-x-4">
                <?php if ($monitoring_mode): ?>
                    <a href="dashboard_responsavel.php" class="text-sm font-medium text-gray-600 hover:text-primary">Voltar ao Painel</a>
                <?php endif; ?>
                <span class="text-sm font-medium text-gray-600">Olá, <?php echo htmlspecialchars($user_name); ?>!</span>
                <a href="logout.php" class="px-4 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">Sair</a>
            </div>
        </div>
    </header>

    <main class="flex-grow py-12 lg:py-20 bg-purple-50">
        <div class="container mx-auto px-4 max-w-4xl">
            <h1 class="text-4xl font-extrabold text-center mb-4 text-foreground">
                <?php echo htmlspecialchars($view_title); ?>
            </h1>
            <?php if (!$monitoring_mode): ?>
                <p class="text-xl text-center text-gray-600 mb-10">Escolha ou continue sua aventura!</p>
            <?php endif; ?>

            <div class="border-b border-gray-200 mb-8 sticky top-16 bg-white/95 z-40 p-2 rounded-lg shadow-sm">
                <nav class="-mb-px flex space-x-4 md:space-x-8 justify-center" aria-label="Tabs">
                    <?php foreach ($tracks as $track_key => $track):
                        $is_active = $track_key === $course_slug;
                        $tab_class = $is_active
                            ? 'border-primary text-primary bg-purple-50'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                    ?>
                    <button
                        id="tab-<?php echo $track_key; ?>"
                        onclick="switchTrack('<?php echo $track_key; ?>')"
                        class="track-tab whitespace-nowrap py-3 px-1 border-b-2 font-medium text-lg transition-colors duration-200 flex items-center gap-2 <?php echo $tab_class; ?>"
                        aria-current="<?php echo $is_active ? 'page' : 'false'; ?>"
                    >
                        <?php echo $track['emoji']; ?> <?php echo htmlspecialchars($track['title']); ?>
                    </button>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div id="track-content-area" class="space-y-12">

                <?php foreach ($tracks as $track_key => $track):
                    $is_active = $track_key === $course_slug;
                ?>
                <div id="content-<?php echo $track_key; ?>" class="track-content-panel space-y-8" style="<?php echo $is_active ? '' : 'display: none;'; ?>">

                    <header class="text-center p-6 rounded-xl border-t-4 border-b-4 <?php echo $track['border']; ?> <?php echo $track['bg']; ?>">
                        <h2 class="text-3xl font-extrabold <?php echo $track['color']; ?>"><?php echo $track['emoji']; ?> Trilha: <?php echo htmlspecialchars($track['title']); ?></h2>
                        <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($track['subtitle']); ?> | Nível: <span class="font-semibold"><?php echo $track['level']; ?></span></p>
                    </header>

                    <?php foreach ($track['modules'] as $module): ?>
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl font-bold text-white bg-primary shadow-md">
                                ★
                            </div>
                            <h3 class="text-2xl font-bold text-primary"><?php echo htmlspecialchars($module['title']); ?></h3>
                        </div>
                        <p class="text-gray-600 mb-6 border-l-4 border-gray-300 pl-4">Aprenda os fundamentos desta trilha.</p>

                        <div class="space-y-3">
                            <h4 class="text-lg font-semibold text-foreground">Lições:</h4>
                            <?php foreach ($module['licoes'] as $licao):
                                $licao_id = $licao['id'];
                                
                                // CORREÇÃO: Usa a validação real do BD
                                // Verifica se o licao_id existe na matriz de progresso E se o valor é 1 (concluído)
                                $is_completed = isset($progresso_licoes[$licao_id]) && $progresso_licoes[$licao_id] === 1;

                                $status_class = $is_completed ? 'bg-green-100 border-green-400' : 'bg-gray-50 border-gray-300';
                                $status_text = $is_completed ? 'Concluída' : 'Começar';
                                $link_dest = $is_completed ? '#' : $module['slug'] . ".php";
                                $button_class = $is_completed ? 'bg-green-600 cursor-default' : 'bg-secondary hover:bg-secondary/90';
                            ?>
                            <div class="flex justify-between items-center p-3 rounded-lg border <?php echo $status_class; ?>">
                                <span class="text-gray-800"><?php echo htmlspecialchars($licao['title']); ?></span>
                                <a href="<?php echo $link_dest; ?>" class="text-white px-3 py-1 text-sm font-semibold rounded-lg transition-all <?php echo $button_class; ?>">
                                    <?php echo $status_text; ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="container mx-auto px-4 py-6 text-center max-w-6xl">
            <p class="text-sm text-gray-600">© 2025 BeanCode. Programar é Mágico! ✨</p>
        </div>
    </footer>


    <div id="trackSelectionModal" class="modal-overlay">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-primary">Escolha Sua Aventura!</h2>
                <?php if ($has_selected_track): ?>
                    <button onclick="closeModal('trackSelectionModal')" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                <?php endif; ?>
            </div>

            <p class="text-gray-700 mb-6">Selecione a trilha que mais combina com seu nível para começar.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($tracks as $key => $t): ?>
                    <div 
                        id="track-card-<?php echo $key; ?>"
                        class="track-card border-2 border-transparent rounded-xl p-4 text-center <?php echo $t['bg'] . ($key === $course_slug ? ' selected' : ''); ?>"
                        onclick="selectTrack('<?php echo $key; ?>')"
                    >
                        <div class="text-4xl mb-2"><?php echo $t['emoji']; ?></div>
                        <h4 class="text-lg font-semibold <?php echo $t['color']; ?>"><?php echo $t['title']; ?></h4>
                        <p class="text-xs text-gray-600 mt-1"><?php echo $t['level']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-8">
                <button id="selectTrackButton" disabled class="w-full bg-primary text-white py-3 rounded-lg font-semibold opacity-50 cursor-not-allowed transition-opacity hover:opacity-100" onclick="confirmTrackSelection()">
                    Confirmar Seleção de Trilha
                </button>
            </div>
        </div>
    </div>
    
    <form id="trackForm" method="POST" action="course-track.php" class="hidden"> 
        <input type="hidden" name="selected_track" id="selectedTrackInput">
        <input type="hidden" name="aluno_id" value="<?php echo $aluno_id_to_pass; ?>">
    </form>


    <script>
        const courseSlug = '<?php echo $course_slug; ?>';
        const hasSelectedTrack = <?php echo $has_selected_track ? 'true' : 'false'; ?>;
        let selectedTrackModal = courseSlug;

        function switchTrack(trackKey) {
            // 1. Atualização Visual Imediata (Tabs)
            document.querySelectorAll('.track-tab').forEach(tab => {
                tab.classList.remove('border-primary', 'text-primary', 'bg-purple-50');
                tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            const newActiveTab = document.getElementById(`tab-${trackKey}`);
            newActiveTab.classList.add('border-primary', 'text-primary', 'bg-purple-50');
            newActiveTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');

            // 2. Atualização Visual Imediata (Conteúdo)
            document.querySelectorAll('.track-content-panel').forEach(panel => {
                panel.style.display = 'none';
            });
            document.getElementById(`content-${trackKey}`).style.display = 'block';

            // 3. Persistência (Dispara POST se a trilha for diferente da ativa atual)
            if (trackKey !== courseSlug) {
                document.getElementById('selectedTrackInput').value = trackKey;
                document.getElementById('trackForm').submit();
            }
        }

        // --- Lógica do Modal ---
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('open');
            document.body.style.overflow = 'hidden'; 
            selectTrack(courseSlug); // Garante a seleção inicial
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('open');
            document.body.style.overflow = ''; 
        }
        
        function selectTrack(trackKey) {
            selectedTrackModal = trackKey;
            const cards = document.querySelectorAll('.track-card');
            const selectButton = document.getElementById('selectTrackButton');

            cards.forEach(card => card.classList.remove('selected'));
            document.getElementById(`track-card-${trackKey}`).classList.add('selected');

            if (selectedTrackModal !== courseSlug || !hasSelectedTrack) {
                selectButton.disabled = false;
                selectButton.classList.remove('opacity-50', 'cursor-not-allowed');
                selectButton.classList.add('hover:opacity-100');
            } else {
                selectButton.disabled = true;
                selectButton.classList.add('opacity-50', 'cursor-not-allowed');
                selectButton.classList.remove('hover:opacity-100');
            }
        }

        function confirmTrackSelection() {
            if (selectedTrackModal) {
                // Chama a função principal que submete o POST e recarrega
                switchTrack(selectedTrackModal); 
                closeModal('trackSelectionModal');
            }
        }

        // --- Abertura Automática do Modal ---
        window.onload = function() {
            if (!hasSelectedTrack) {
                openModal('trackSelectionModal');
            }
        };
    </script>

</body>
</html>