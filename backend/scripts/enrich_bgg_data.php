<?php

declare(strict_types=1);

(function (): void {
    $envPath = __DIR__ . '/../.env';
    if (!is_file($envPath)) {
        return;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\"'");
        if ($name !== '') {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
    if (getenv('DATABASE_URL') === false || getenv('DATABASE_URL') === '') {
        $host = getenv('DATABASE_HOST') ?: 'localhost';
        $port = getenv('DATABASE_PORT') ?: '3307';
        $name = getenv('DATABASE_NAME') ?: 'ludoplanet';
        $user = getenv('DATABASE_USER') ?: 'root';
        $pass = getenv('DATABASE_PASSWORD') ?: 'root';
        putenv("DATABASE_URL=mysql://$user:$pass@$host:$port/$name");
        $_ENV['DATABASE_URL'] = "mysql://$user:$pass@$host:$port/$name";
    }
})();

set_time_limit(0);
ob_implicit_flush(true);

function usage(): void
{
    $script = basename(__FILE__);
    fwrite(STDERR, "Usage: php {$script} [--limit=100] [--skip-price] [--debug]\n");
}


function parseDatabaseUrl(string $databaseUrl): array
{
    $parts = parse_url($databaseUrl);
    if ($parts === false) {
        throw new RuntimeException("DATABASE_URL invalide.");
    }

    $scheme = $parts['scheme'] ?? '';
    if ($scheme !== 'mysql') {
        throw new RuntimeException("DATABASE_URL doit utiliser mysql.");
    }

    $user = urldecode($parts['user'] ?? '');
    $pass = urldecode($parts['pass'] ?? '');
    $host = $parts['host'] ?? '127.0.0.1';
    $port = $parts['port'] ?? 3306;
    $dbname = ltrim($parts['path'] ?? '', '/');

    if ($user === '' || $dbname === '') {
        throw new RuntimeException("DATABASE_URL doit contenir user et database.");
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

    return [$dsn, $user, $pass];
}

function parseArgs(array $argv): array
{
    $limit = 100;
    $skipPrice = false;
    $debug = false;

    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--limit=')) {
            $limit = (int)substr($arg, 8);
        } elseif ($arg === '--skip-price') {
            $skipPrice = true;
        } elseif ($arg === '--debug') {
            $debug = true;
        }
    }

    if ($limit <= 0) {
        $limit = 100;
    }

    return [$limit, $skipPrice, $debug];
}


function generatePriceCents(int $bggId, ?float $complexity): int
{
    $weight = $complexity ?? 2.0;
    $base = 1500 + (int)round($weight * 1500);
    $jitter = (int)(crc32((string)$bggId) % 2001) - 1000;
    $price = $base + $jitter;
    return max(1500, min(8000, $price));
}


function generateFakeDescription(
    string $name,
    ?string $category,
    ?int $minPlayers,
    ?int $maxPlayers,
    ?int $playtime,
    ?float $weight
): string {
    $players = ($minPlayers !== null && $maxPlayers !== null)
        ? "{$minPlayers} à {$maxPlayers} joueurs"
        : "1+ joueurs";

    $duration = $playtime !== null ? "{$playtime} minutes" : "une durée variable";

    $level = "tout public";
    if ($weight !== null) {
        if ($weight < 2) {
            $level = "très accessible";
        } elseif ($weight < 3.5) {
            $level = "de difficulté intermédiaire";
        } else {
            $level = "pour joueurs experts";
        }
    }

    $catText = $category ?: "jeu de plateau";

    return "{$name} est un {$catText} {$level} pour {$players}, idéal pour des parties d’environ {$duration}. "
        . "Ajoutez-le à votre ludothèque pour vivre des moments conviviaux et renouveler vos soirées jeux.";
}

function defaultImageForCategory(?string $category): string
{
    $normalized = $category ? mb_strtolower($category) : '';

    if (str_contains($normalized, 'enfant')) {
        return '/images/categories/enfants.svg';
    }
    if (str_contains($normalized, 'ambiance')) {
        return '/images/categories/ambiance.svg';
    }
    if (str_contains($normalized, 'carte')) {
        return '/images/categories/cartes.svg';
    }
    if (str_contains($normalized, 'expert')) {
        return '/images/categories/expert.svg';
    }

    return '/images/categories/plateau.svg';
}

$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl === false || trim($databaseUrl) === '') {
    fwrite(STDERR, "DATABASE_URL manquant. Definis-le avant d'executer le script.\n");
    usage();
    exit(1);
}

[$limit, $skipPrice, $debug] = parseArgs($argv);
[$dsn, $dbUser, $dbPass] = parseDatabaseUrl($databaseUrl);
$dsn .= ';connect_timeout=5';

$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

if ($debug) {
    echo "Debug: connexion DB OK\n";
}

if (!$skipPrice) {
    if ($debug) {
        echo "Debug: generation des prix\n";
    }

    $seedPrices = $pdo->prepare(
        'UPDATE games SET price_cents = :price_cents WHERE bgg_id = :bgg_id AND price_cents IS NULL'
    );

    $priceCandidates = $pdo->query(
        'SELECT bgg_id, complexity_average FROM games WHERE price_cents IS NULL'
    )->fetchAll();

    foreach ($priceCandidates as $game) {
        $price = generatePriceCents(
            (int)$game['bgg_id'],
            $game['complexity_average'] !== null ? (float)$game['complexity_average'] : null
        );
        $seedPrices->execute([
            ':price_cents' => $price,
            ':bgg_id' => (int)$game['bgg_id'],
        ]);
    }

    if ($debug) {
        echo "Debug: prix generes pour " . count($priceCandidates) . " jeux\n";
    }
}

if ($debug) {
    echo "Debug: selection des jeux a enrichir\n";
}

$selectGames = $pdo->prepare(
    'SELECT g.bgg_id, g.name, g.description,
            g.min_players, g.max_players, g.play_time, g.complexity_average,
            MIN(c.name) AS category_name
     FROM games g
     LEFT JOIN game_categories gc ON gc.game_id = g.bgg_id
     LEFT JOIN categories c ON c.id = gc.category_id
     WHERE g.description IS NULL
        OR NOT EXISTS (
            SELECT 1 FROM game_images gi
            WHERE gi.game_id = g.bgg_id AND gi.is_primary = 1
        )
     GROUP BY g.bgg_id, g.name, g.description,
              g.min_players, g.max_players, g.play_time, g.complexity_average
     ORDER BY g.users_rated DESC
     LIMIT :limit'
);
$selectGames->bindValue(':limit', $limit, PDO::PARAM_INT);
$selectGames->execute();
$games = $selectGames->fetchAll();

if ($debug) {
    echo "Debug: jeux selectionnes=" . count($games) . "\n";
}

$updateDescription = $pdo->prepare(
    'UPDATE games SET description = :description WHERE bgg_id = :bgg_id'
);

$deletePrimaryImage = $pdo->prepare(
    'DELETE FROM game_images WHERE game_id = :bgg_id AND is_primary = 1'
);

$insertPrimaryImage = $pdo->prepare(
    'INSERT INTO game_images (game_id, image_url, is_primary) VALUES (:bgg_id, :image_url, 1)'
);

$updated = 0;

foreach ($games as $game) {
    $bggId  = (int)$game['bgg_id'];

    if ($debug) {
        echo "Debug: enrich game bgg_id={$bggId}\n";
    }

    $pdo->beginTransaction();

    if ($game['description'] === null) {
        $desc = generateFakeDescription(
            $game['name'],
            $game['category_name'] ?? null,
            $game['min_players'] !== null ? (int)$game['min_players'] : null,
            $game['max_players'] !== null ? (int)$game['max_players'] : null,
            $game['play_time'] !== null ? (int)$game['play_time'] : null,
            $game['complexity_average'] !== null ? (float)$game['complexity_average'] : null
        );

        $updateDescription->execute([
            ':description' => $desc,
            ':bgg_id' => $bggId,
        ]);
    }

    $imageUrl = defaultImageForCategory($game['category_name'] ?? null);

    $deletePrimaryImage->execute([':bgg_id' => $bggId]);
    $insertPrimaryImage->execute([
        ':bgg_id'    => $bggId,
        ':image_url' => $imageUrl,
    ]);

    $pdo->commit();
    $updated++;
}

echo "Enrichissement termine: {$updated} jeux.\n";
