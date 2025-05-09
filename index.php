<?php
    // ===== INÍCIO DO BLOCO PHP MOVIDO PARA O TOPO =====
    // Garante que este bloco seja executado ANTES de qualquer saída HTML.

    // Inicia a sessão para poder usar variáveis de sessão para mensagens de erro
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // AGORA ESTÁ NO LUGAR CORRETO!
    }

    $error_message = ''; // Inicializa a variável de erro

    // Verifica se há uma mensagem de erro na sessão (após um redirecionamento)
    if (isset($_SESSION['error_message'])) {
        $error_message = $_SESSION['error_message'];
        unset($_SESSION['error_message']); // Limpa a mensagem da sessão após lê-la
    }

    // Processa o download SE o formulário foi enviado E a URL da imagem está presente
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['button']) && isset($_POST['imgurl']) && !empty($_POST['imgurl'])) {
        $imgUrl = trim($_POST['imgurl']); // Remove espaços extras

        // Validação mais robusta da URL da imagem do YouTube
        // Verifica se começa com o domínio esperado e tem uma estrutura razoável
        if (preg_match('/^https?:\/\/i\.ytimg\.com\/vi\/[a-zA-Z0-9_-]+\/[a-zA-Z0-9_]+\.(?:jpe?g|png|webp)$/i', $imgUrl) ||
            preg_match('/^https?:\/\/img\.youtube\.com\/vi\/[a-zA-Z0-9_-]+\/[a-zA-Z0-9_]+\.(?:jpe?g|png|webp)$/i', $imgUrl)) // Adicionado png/webp na regex
        {
            // Inicializa o cURL
            $ch = curl_init();

            // Configura as opções do cURL
            curl_setopt($ch, CURLOPT_URL, $imgUrl); // Define a URL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Retorna o resultado como string
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Segue redirecionamentos
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verifica o certificado SSL (importante para segurança)
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);   // Verifica o nome do host no certificado
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'); // User agent comum
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Tempo limite para conectar (segundos)
            curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Tempo limite total da requisição (segundos)
            curl_setopt($ch, CURLOPT_FAILONERROR, true); // Falha explicitamente em erros HTTP >= 400

            // Executa a requisição cURL
            $downloadImg = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Pega o código de status HTTP
            $curlErrorNum = curl_errno($ch); // Pega o número do erro do cURL (0 se não houver erro)
            $curlError = curl_error($ch); // Pega a mensagem de erro do cURL
            curl_close($ch); // Fecha a sessão cURL

            // Verifica se houve erro no cURL ou se o código HTTP não foi 200
            if ($curlErrorNum !== 0 || ($httpCode !== 200 && $httpCode !== 0) || $downloadImg === false) { // $httpCode 0 pode ocorrer em alguns erros de cURL antes da conexão HTTP
                // Define a mensagem de erro para exibir na página
                $_SESSION['error_message'] = "Falha ao baixar a thumbnail. Código HTTP: {$httpCode}. Erro cURL: {$curlError} (Cod: {$curlErrorNum})";
                // Redireciona de volta para a página do formulário para exibir o erro
                header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
                exit; // Importante sair após o redirecionamento
            } else {
                // Se o download foi bem-sucedido
                // Extrai o nome do arquivo e a extensão da URL
                $pathInfo = pathinfo(parse_url($imgUrl, PHP_URL_PATH));
                $filename = $pathInfo['basename'] ?? 'thumbnail.jpg'; // Nome do arquivo original ou padrão
                $extension = strtolower($pathInfo['extension'] ?? 'jpg'); // Extensão em minúsculas ou padrão jpg

                // Define o tipo de conteúdo com base na extensão
                $contentType = 'image/jpeg'; // Padrão
                if ($extension === 'png') {
                    $contentType = 'image/png';
                } elseif ($extension === 'webp') {
                    $contentType = 'image/webp';
                }
                // Adicione outros tipos se necessário (gif, etc.)

                // Define os cabeçalhos para forçar o download
                header('Content-Description: File Transfer');
                header('Content-Type: ' . $contentType);
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($downloadImg)); // Informa o tamanho do arquivo
                ob_clean(); // Limpa o buffer de saída para evitar corrupção
                flush(); // Envia o conteúdo do buffer atual
                echo $downloadImg; // Envia os dados da imagem
                exit; // Termina o script para não enviar mais nada (HTML não será renderizado neste caso)
            }

        } else {
            // Se a URL da imagem fornecida for inválida ou não esperada
            $_SESSION['error_message'] = "A URL da imagem fornecida parece inválida ou não é suportada.";
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
            exit; // Importante sair após o redirecionamento
        }
    }
    // ===== FIM DO BLOCO PHP MOVIDO PARA O TOPO =====
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[ GΞΞK CΦDΞ ] - YouTube Thumbnail Downloader</title>
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="48x48" href="favicon-48x48.png">
    <link rel="icon" type="image/png" sizes="64x64" href="favicon-64x64.png">
	<link rel="icon" type="image/png" sizes="128x128" href="favicon-128x128.png">
    <link rel="icon" type="image/png" sizes="192x192" href="favicon-192x192.png">
	<link rel="icon" type="image/png" sizes="512x512" href="favicon-512x512.png">
    <link rel="manifest" href="manifest.json"> <!-- Se gerou um manifest -->
    <!-- Font Awesome para Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <!-- Google Fonts - Poppins e Fira Code (opcional para toque geek) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* Estilos CSS permanecem aqui... (sem alterações) */
        /*------------------------------------*\
          #VARIÁVEIS CSS (Paleta Geek/Dark)
        \*------------------------------------*/
        :root {
            --cor-fundo-primaria: #1a1a2e; /* Azul noite bem escuro */
            --cor-fundo-secundaria: #1f1f3a; /* Azul noite um pouco mais claro */
            --cor-container: #2a2a4a; /* Cor do container principal */
            --cor-texto-primaria: #e0e0e0; /* Cinza claro para texto */
            --cor-texto-secundaria: #a0a0c0; /* Roxo/Cinza claro para texto secundário */
            --cor-texto-titulo: #ffffff; /* Branco puro para títulos */
            --cor-neon-accent: #00f5c3; /* Verde/Ciano neon para destaque */
            --cor-neon-accent-hover: #00d1a7; /* Verde/Ciano mais escuro para hover */
            --cor-borda: #4a4a6a; /* Borda sutil */
            --cor-erro: #ff4d4d; /* Vermelho para erros */
            --sombra-neon: 0 0 5px var(--cor-neon-accent), 0 0 10px var(--cor-neon-accent), 0 0 15px var(--cor-neon-accent);
            --sombra-container: 0 10px 30px rgba(0, 0, 0, 0.4);
            --font-principal: 'Poppins', sans-serif;
            --font-codigo: 'Fira Code', monospace; /* Fonte opcional para elementos específicos */
        }

        /*------------------------------------*\
          #RESET BÁSICO E ESTILOS GLOBAIS
        \*------------------------------------*/
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-principal);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            display: flex;
            flex-direction: column; /* Permite que o footer fique abaixo */
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--cor-fundo-primaria), var(--cor-fundo-secundaria));
            color: var(--cor-texto-primaria);
            padding: 20px;
            overflow-x: hidden; /* Prevenir scroll horizontal */
        }

        ::selection {
            color: var(--cor-fundo-primaria);
            background: var(--cor-neon-accent);
        }

        /*------------------------------------*\
          #CONTAINER PRINCIPAL (FORMULÁRIO)
        \*------------------------------------*/
        .downloader-container {
            width: 100%;
            max-width: 550px; /* Aumentado um pouco */
            background: var(--cor-container);
            padding: 35px 40px; /* Mais padding */
            border-radius: 15px; /* Bordas mais arredondadas */
            box-shadow: var(--sombra-container);
            border: 1px solid var(--cor-borda);
            position: relative; /* Para pseudo-elementos se necessário */
            overflow: hidden; /* Para efeitos */
            transition: box-shadow 0.3s ease;
        }

        .downloader-container:hover {
             box-shadow: 0 0 25px rgba(0, 245, 195, 0.2); /* Sombra neon sutil no hover */
        }

        /*------------------------------------*\
          #CABEÇALHO
        \*------------------------------------*/
        .downloader-container header {
            text-align: center;
            font-size: 2rem; /* Tamanho maior */
            font-weight: 600;
            margin-bottom: 35px; /* Mais espaço abaixo */
            color: var(--cor-texto-titulo);
            letter-spacing: 1px;
             /* Efeito de texto neon */
            text-shadow: 0 0 3px rgba(0, 245, 195, 0.7);
            font-family: var(--font-codigo); /* Usando a fonte mono para o título */
        }
         .downloader-container header i {
            margin-right: 10px;
            color: var(--cor-neon-accent);
        }


        /*------------------------------------*\
          #INPUT DA URL
        \*------------------------------------*/
        .url-input {
            margin-bottom: 30px;
        }

        .url-input .title {
            display: block; /* Garante que fique em linha própria */
            font-size: 1.1rem;
            color: var(--cor-texto-secundaria);
            margin-bottom: 10px; /* Espaço entre título e campo */
            font-weight: 500;
        }

        .url-input .field {
            height: 55px; /* Altura ligeiramente maior */
            width: 100%;
            position: relative;
        }

        .url-input .field input[type="text"] {
            height: 100%;
            width: 100%;
            border: none;
            outline: none;
            padding: 0 15px;
            font-size: 1rem;
            background: var(--cor-fundo-secundaria); /* Fundo escuro para input */
            border: 1px solid var(--cor-borda); /* Borda sutil */
            border-radius: 8px; /* Bordas arredondadas */
            color: var(--cor-texto-primaria); /* Texto claro */
            caret-color: var(--cor-neon-accent); /* Cor do cursor */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .url-input .field input[type="text"]::placeholder {
            color: var(--cor-texto-secundaria);
            opacity: 0.7;
        }

        /* Linha inferior removida, foco agora na borda e sombra */
        .url-input .field input[type="text"]:focus {
            border-color: var(--cor-neon-accent);
            box-shadow: 0 0 8px rgba(0, 245, 195, 0.5); /* Sombra neon no foco */
        }

        /* Input oculto não precisa de estilo visível */
        .hidden-input {
            display: none;
        }

        /*------------------------------------*\
          #ÁREA DE PRÉ-VISUALIZAÇÃO
        \*------------------------------------*/
        .preview-area {
            border-radius: 10px;
            height: 250px; /* Altura maior */
            display: flex;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border: 3px dashed var(--cor-borda); /* Borda tracejada mais grossa */
            background-color: rgba(42, 42, 74, 0.3); /* Fundo semi-transparente */
            transition: border-color 0.4s ease, background-color 0.4s ease;
            position: relative; /* Para posicionar o spinner */
        }

        .preview-area.active {
    display: block; /* Sobrescreve o flex, torna um container bloco normal para a imagem */
    border-style: solid; /* Muda para sólido quando ativo */
    border-color: var(--cor-neon-accent);
    background-color: transparent; /* Remove fundo quando tem imagem */
}

        .preview-area .thumbnail {
    width: 100%;
    height: 100%; /* Faz a imagem preencher a área */
    object-fit: cover; /* Garante que a imagem cubra sem distorcer, cortando o excesso */
    object-position: center; /* Garante que o corte seja feito centralizado (padrão, mas bom explicitar) */
    display: none; /* Controlado via JS */
    border-radius: 7px; /* Borda interna leve */
    opacity: 0; /* Controlado via JS para transição */
    transition: opacity 0.5s ease-in-out; /* Transição suave de opacidade */
}

        .preview-area.active .thumbnail {
            display: block;
            opacity: 1; /* Fade-in da imagem */
        }

        .preview-area .icon,
        .preview-area .placeholder-text {
             /* Controla a visibilidade via JS */
             transition: opacity 0.3s ease;
        }

        .preview-area .icon {
            color: var(--cor-neon-accent);
            font-size: 6rem; /* Ícone maior */
            opacity: 0.6; /* Leve transparência */
        }

        .preview-area .placeholder-text {
            color: var(--cor-texto-secundaria);
            margin-top: 20px;
            font-size: 1rem;
            text-align: center;
            padding: 0 10px;
        }

        /* Classe para ocultar elementos da pré-visualização */
        .preview-hidden {
           opacity: 0 !important;
           pointer-events: none; /* Impede interação */
        }

        /* Spinner de carregamento (opcional, mas melhora UX) */
        .spinner {
            display: none; /* Escondido por padrão */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            border: 5px solid var(--cor-borda);
            border-top-color: var(--cor-neon-accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .preview-area.loading .spinner {
            display: block; /* Mostra o spinner */
        }
         .preview-area.loading .icon,
         .preview-area.loading .placeholder-text {
            opacity: 0.2; /* Diminui opacidade do placeholder durante loading */
         }


        /*------------------------------------*\
          #BOTÃO DE DOWNLOAD
        \*------------------------------------*/
        .download-btn {
            color: var(--cor-fundo-primaria); /* Texto escuro no botão neon */
            height: 55px;
            width: 100%;
            outline: none;
            border: none;
            font-size: 1.1rem;
            font-weight: 600; /* Texto mais forte */
            letter-spacing: 0.5px;
            cursor: pointer;
            margin: 35px 0 20px 0; /* Espaçamento ajustado */
            border-radius: 8px;
            background: var(--cor-neon-accent);
            transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.1s ease;
            display: flex; /* Para alinhar ícone e texto */
            align-items: center;
            justify-content: center;
        }
         .download-btn i {
            margin-right: 10px; /* Espaço entre ícone e texto */
         }

        .download-btn:hover:not(:disabled) {
            background: var(--cor-neon-accent-hover);
            box-shadow: var(--sombra-neon);
        }

        .download-btn:active:not(:disabled) {
            transform: scale(0.98); /* Efeito de clique */
        }

        /* Estilo do botão desabilitado */
        .download-btn:disabled {
            background: var(--cor-borda);
            color: var(--cor-texto-secundaria);
            cursor: not-allowed;
            opacity: 0.6;
        }

        /*------------------------------------*\
          #MENSAGEM DE ERRO (PHP)
        \*------------------------------------*/
        .error-message {
            background-color: rgba(255, 77, 77, 0.1); /* Fundo vermelho translúcido */
            color: var(--cor-erro);
            border: 1px solid var(--cor-erro);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 0.95rem;
            display: flex; /* Para alinhar ícone */
            align-items: center;
            justify-content: center;
            word-wrap: break-word; /* Garante que mensagens longas quebrem a linha */
        }
        .error-message i {
            margin-right: 10px; /* Espaço antes do texto */
            font-size: 1.2em; /* Ícone um pouco maior */
            flex-shrink: 0; /* Impede que o ícone encolha */
        }


        /*------------------------------------*\
          #RODAPÉ
        \*------------------------------------*/
        .tutorial-footer {
            text-align: center;
            padding: 2rem 1rem;
            margin-top: 3rem; /* Mais espaço acima do rodapé */
            color: var(--cor-texto-secundaria);
            font-size: 0.9rem;
            border-top: 1px solid var(--cor-borda); /* Borda sutil com cor do tema */
            width: 100%; /* Ocupa a largura */
            max-width: 800px; /* Limita a largura em telas grandes */
        }

        .tutorial-footer p b {
            color: var(--cor-texto-primaria); /* Texto principal mais claro */
            font-weight: 500;
        }

        .tutorial-footer a {
            color: var(--cor-texto-secundaria);
            text-decoration: none;
            margin: 0 8px; /* Mais espaço entre links */
            transition: color 0.3s ease, text-shadow 0.3s ease;
            position: relative; /* Para pseudo-elemento de underline */
        }

        .tutorial-footer a::after { /* Efeito de underline animado */
             content: '';
             position: absolute;
             width: 0;
             height: 1px;
             display: block;
             margin-top: 2px;
             right: 0;
             background: var(--cor-neon-accent);
             transition: width .3s ease;
             -webkit-transition: width .3s ease;
        }

        .tutorial-footer a:hover {
            color: var(--cor-neon-accent);
            text-shadow: 0 0 5px rgba(0, 245, 195, 0.5); /* Sombra neon suave no hover */
        }

         .tutorial-footer a:hover::after { /* Animação do underline */
             width: 100%;
             left: 0;
             background-color: var(--cor-neon-accent);
         }


        .tutorial-footer span {
            margin: 0 5px;
            opacity: 0.7;
        }

        /*------------------------------------*\
          #RESPONSIVIDADE
        \*------------------------------------*/
        @media screen and (max-width: 600px) {
            .downloader-container {
                padding: 25px 20px; /* Menos padding em telas menores */
                margin: 15px; /* Adiciona margem para não colar nas bordas */
            }

            .downloader-container header {
                font-size: 1.7rem; /* Título menor */
                margin-bottom: 25px;
            }

            .url-input .field,
            .download-btn {
                height: 50px; /* Altura ligeiramente menor */
            }
            .download-btn {
                font-size: 1rem;
            }

            .preview-area {
                height: 200px; /* Altura menor da pré-visualização */
            }

            .preview-area .icon {
                font-size: 4.5rem; /* Ícone menor */
            }

            .preview-area .placeholder-text {
                font-size: 0.9rem;
            }
             .tutorial-footer {
                margin-top: 2rem;
                padding: 1.5rem 0.5rem;
            }
            .tutorial-footer a, .tutorial-footer span {
                 margin: 0 4px; /* Menos espaço entre links */
             }
        }

         @media screen and (max-width: 400px) {
              .downloader-container header {
                font-size: 1.5rem;
             }
              .url-input .title {
                 font-size: 1rem;
             }
             .url-input .field input[type="text"] {
                 font-size: 0.9rem;
             }
              .download-btn {
                  height: 48px;
                  font-size: 0.95rem;
              }
              .preview-area {
                 height: 160px;
             }
              .preview-area .icon {
                font-size: 4rem;
             }
              .tutorial-footer div {
                 display: flex;
                 flex-direction: column; /* Empilha os links do rodapé */
                 gap: 8px; /* Espaço entre links empilhados */
             }
             .tutorial-footer span {
                 display: none; /* Esconde separadores no modo empilhado */
             }
             .error-message {
                 font-size: 0.9rem; /* Reduz fonte da msg de erro */
                 padding: 10px;
             }
         }
    </style>

    <!-- O Bloco PHP que estava aqui foi movido para o topo do arquivo -->
</head>
<body>

    <main class="downloader-container">
        <header><i class="fa-brands fa-youtube"></i>[ GΞΞK CΦDΞ ] Thumbnail Downloader</header>

        <?php
        // Exibe a mensagem de erro do PHP, se houver (Esta parte permanece aqui)
        // A variável $error_message foi definida no bloco PHP no topo do arquivo.
        if (!empty($error_message)):
        ?>
            <div class="error-message">
                 <i class="fas fa-exclamation-triangle"></i> <!-- Ícone de alerta -->
                 <span><?php echo htmlspecialchars($error_message); // Exibe a mensagem de erro com segurança ?></span>
            </div>
        <?php endif; ?>


        <!-- O action agora aponta para si mesmo de forma segura -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="downloadForm">
            <div class="url-input">
                <span class="title"><i class="fas fa-link"></i> Cole o link do vídeo do YouTube:</span>
                <div class="field">
                    <!-- Input visível para o usuário (NÃO tem 'name' para não ser enviado diretamente) -->
                    <input type="text" id="videoUrlField" placeholder="Ex: https://www.youtube.com/watch?v=SEkM3yT..." required autocomplete="off">
                    <!-- Input oculto que REALMENTE envia a URL da *imagem* para o PHP -->
                    <input class="hidden-input" type="hidden" name="imgurl" id="imageUrlInput">
                    <!-- Linha inferior foi removida, o estilo está na borda/sombra do input -->
                </div>
            </div>

            <div class="preview-area">
                <!-- Spinner de Carregamento -->
                <div class="spinner"></div>
                <!-- Imagem de pré-visualização -->
                <img class="thumbnail" src="" alt="Pré-visualização da Thumbnail">
                <!-- Ícone e texto do placeholder -->
                <i class="icon fas fa-cloud-download-alt"></i>
                <span class="placeholder-text">Cole a URL do vídeo para gerar a prévia da thumbnail</span>
            </div>

            <!-- O botão de submit agora tem um ícone -->
            <button class="download-btn" type="submit" name="button" disabled>
                <i class="fas fa-download"></i> Download Thumbnail
            </button>
        </form>
    </main>

    <!-- Rodapé Padrão -->
    <footer class="tutorial-footer">
        <p><b>Aviso:</b> Use esta ferramenta de forma ética e respeite os direitos autorais.</p>
        <div>
            <a href="../../politica.html" target="_blank" rel="noopener noreferrer">Política de Privacidade</a>
            <span>|</span>
            <a href="../../servicos.html" target="_blank" rel="noopener noreferrer">Nossos Serviços</a>
            <span>|</span>
            <a href="../../sobre.html" target="_blank" rel="noopener noreferrer">Sobre Nós</a>
            <span>|</span>
            <a href="../../termos.html" target="_blank" rel="noopener noreferrer">Termos de Uso</a>
            <span>|</span>
            <a href="../../index.html" target="_blank" rel="noopener noreferrer">Início</a>
        </div>
    </footer>
    <!-- Fim do Rodapé Padrão -->

    <script>
        // Script Javascript permanece aqui... (sem alterações)
        // Seleciona os elementos do DOM necessários uma vez para melhor performance
        const urlField = document.getElementById("videoUrlField"); // Input visível da URL do vídeo
        const previewArea = document.querySelector(".preview-area");
        const imgTag = previewArea.querySelector(".thumbnail");
        const hiddenImageUrlInput = document.getElementById("imageUrlInput"); // Input oculto com a URL da imagem
        const downloadButton = document.querySelector(".download-btn");
        const previewIcon = previewArea.querySelector(".icon");
        const previewPlaceholderText = previewArea.querySelector(".placeholder-text");
        const spinner = previewArea.querySelector(".spinner");

        // Função para atualizar a interface (UI) da pré-visualização
        const updatePreviewUI = (state, imageUrl = "") => {
            previewArea.classList.remove('active', 'loading', 'error'); // Limpa estados anteriores

            switch (state) {
                case 'loading':
                    previewArea.classList.add('loading');
                    previewIcon.classList.add('preview-hidden');
                    previewPlaceholderText.classList.add('preview-hidden');
                    imgTag.style.display = 'none'; // Garante que imagem anterior suma
                    imgTag.src = ""; // Limpa src anterior
                    hiddenImageUrlInput.value = "";
                    downloadButton.disabled = true;
                    break;
                case 'active':
                    previewArea.classList.add('active');
                    previewIcon.classList.add('preview-hidden');
                    previewPlaceholderText.classList.add('preview-hidden');
                    imgTag.src = imageUrl;
                    imgTag.style.display = 'block'; // Mostra a tag img
                    hiddenImageUrlInput.value = imageUrl; // Define a URL da imagem no input oculto
                    downloadButton.disabled = false; // Habilita o botão
                    break;
                case 'error': // Estado de erro (ex: imagem não carregou)
                     previewArea.classList.add('error'); // Pode usar para estilizar erro se quiser
                     previewIcon.classList.remove('preview-hidden'); // Mostra ícone de novo
                     previewPlaceholderText.classList.remove('preview-hidden'); // Mostra texto de novo
                     previewPlaceholderText.textContent = "Erro ao carregar prévia. Verifique a URL."; // Mensagem de erro
                     imgTag.style.display = 'none';
                     imgTag.src = "";
                     hiddenImageUrlInput.value = "";
                     downloadButton.disabled = true;
                    break;
                case 'idle': // Estado inicial ou URL inválida
                default:
                    previewIcon.classList.remove('preview-hidden');
                    previewPlaceholderText.classList.remove('preview-hidden');
                    previewPlaceholderText.textContent = "Cole a URL do vídeo para gerar a prévia da thumbnail"; // Texto padrão
                    imgTag.style.display = 'none';
                    imgTag.src = "";
                    hiddenImageUrlInput.value = "";
                    downloadButton.disabled = true;
                    break;
            }
        };

        // Event listener para o campo de input da URL (oninput é melhor para colar)
        urlField.addEventListener('input', () => {
            let videoUrl = urlField.value.trim(); // Pega o valor e remove espaços
            let videoID = null;

            // Reseta a UI para o estado inicial enquanto processa
            updatePreviewUI('idle');

            if (!videoUrl) {
                return; // Sai se o campo estiver vazio
            }

            // Expressão Regular mais robusta para extrair o ID do vídeo do YouTube
            // Cobre formatos como: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID, youtube.com/v/ID
            const youtubeRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
            const match = videoUrl.match(youtubeRegex);

            if (match && match[1]) {
                videoID = match[1]; // ID do vídeo encontrado
            } else {
                // Se não for uma URL do YouTube válida, tenta ver se é uma URL direta de imagem (menos comum)
                if (videoUrl.match(/\.(jpe?g|png|gif|bmp|webp)$/i)) {
                     // Neste caso, a URL da imagem é a própria URL inserida
                     updatePreviewUI('loading'); // Mostra carregando
                     // Tenta carregar a imagem diretamente
                     const tempImg = new Image();
                     tempImg.onload = () => updatePreviewUI('active', videoUrl);
                     tempImg.onerror = () => updatePreviewUI('error');
                     tempImg.src = videoUrl;
                     return; // Sai da função pois já está tratando
                } else {
                    // Se não for nem YouTube nem imagem direta, considera inválido
                    updatePreviewUI('idle'); // Volta ao estado inicial (ou pode mostrar um erro específico aqui)
                    previewPlaceholderText.textContent = "URL inválida ou não suportada.";
                    return; // Sai da função
                }
            }

            // Se chegou aqui, temos um videoID do YouTube
            if (videoID) {
                updatePreviewUI('loading'); // Mostra o estado de carregamento

                // Tenta carregar a melhor qualidade primeiro (maxresdefault)
                let thumbnailUrlMax = `https://i.ytimg.com/vi/${videoID}/maxresdefault.jpg`;
                 // Tenta a qualidade alta como fallback (hqdefault)
                let thumbnailUrlHq = `https://i.ytimg.com/vi/${videoID}/hqdefault.jpg`;
                 // Qualidade média (mqdefault) ou padrão (default/sddefault) como últimos recursos
                let thumbnailUrlMq = `https://i.ytimg.com/vi/${videoID}/mqdefault.jpg`;
                let thumbnailUrlSd = `https://i.ytimg.com/vi/${videoID}/sddefault.jpg`;


                // Usa um objeto Image para verificar se a URL da imagem é válida antes de mostrar
                const imageChecker = new Image();

                imageChecker.onload = () => {
                    // Se maxresdefault carregou com sucesso
                    updatePreviewUI('active', thumbnailUrlMax);
                };

                imageChecker.onerror = () => {
                    // Se maxresdefault falhou, tenta hqdefault
                    const imageCheckerHq = new Image();
                    imageCheckerHq.onload = () => {
                         updatePreviewUI('active', thumbnailUrlHq);
                    };
                    imageCheckerHq.onerror = () => {
                        // Se hqdefault falhou, tenta mqdefault
                        const imageCheckerMq = new Image();
                        imageCheckerMq.onload = () => {
                            updatePreviewUI('active', thumbnailUrlMq);
                        };
                        imageCheckerMq.onerror = () => {
                            // Se mqdefault falhou, tenta sddefault
                            const imageCheckerSd = new Image();
                            imageCheckerSd.onload = () => {
                                updatePreviewUI('active', thumbnailUrlSd);
                            };
                            imageCheckerSd.onerror = () => {
                                // Se todas falharem, mostra erro
                                updatePreviewUI('error');
                            };
                            imageCheckerSd.src = thumbnailUrlSd;
                        };
                        imageCheckerMq.src = thumbnailUrlMq;
                    };
                    imageCheckerHq.src = thumbnailUrlHq;
                };

                // Inicia a verificação com a imagem de maior resolução
                imageChecker.src = thumbnailUrlMax;
            }
        });

        // Limpa o campo e a pré-visualização ao carregar a página (útil se o navegador preencher automaticamente)
        window.addEventListener('load', () => {
            urlField.value = '';
            updatePreviewUI('idle');
        });

    </script>

</body>
</html>