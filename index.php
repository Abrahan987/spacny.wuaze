<?php
/**
 * Página simple para Vercel
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Página Simple en Vercel</title>
    <style>
        :root {
            --bg-color: #0a0a0a;
            --text-color: #ededed;
            --accent-color: #0070f3;
            --secondary-color: #888;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            animation: fadeIn 1s ease-out;
        }

        h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -0.05rem;
        }

        p {
            font-size: 1.25rem;
            color: var(--secondary-color);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .badge {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
        }

        .links {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            text-decoration: none;
            color: var(--text-color);
            border: 1px solid var(--text-color);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn:hover {
            background-color: var(--text-color);
            color: var(--bg-color);
        }

        .btn-primary {
            background-color: var(--text-color);
            color: var(--bg-color);
        }

        .btn-primary:hover {
            background-color: transparent;
            color: var(--text-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Glitch effect for the title */
        .glitch {
            position: relative;
        }

        @media (max-width: 480px) {
            h1 { font-size: 2rem; }
            p { font-size: 1rem; }
            .links { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="badge">Vercel PHP</div>
        <h1 class="glitch">Página Simple</h1>
        <p>Esta es una página minimalista ejecutándose en Vercel con PHP. Rápida, limpia y moderna.</p>
        
        <div class="links">
            <a href="#" class="btn btn-primary">Empezar</a>
            <a href="index_backup.php" class="btn">Ver Proyecto Anterior</a>
        </div>
    </div>
</body>
</html>