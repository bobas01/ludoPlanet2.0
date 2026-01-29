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

function usage(): void
{
    $script = basename(__FILE__);
    fwrite(STDERR, "Usage: php {$script} [--limit=20] [--dry-run]\n");
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
    $limit = 20;
    $dryRun = false;

    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--limit=')) {
            $limit = (int)substr($arg, 8);
        } elseif ($arg === '--dry-run') {
            $dryRun = true;
        }
    }

    if ($limit <= 0) {
        $limit = 20;
    }

    return [$limit, $dryRun];
}

$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl === false || trim($databaseUrl) === '') {
    fwrite(STDERR, "DATABASE_URL manquant. Definis-le avant d'executer le script.\n");
    usage();
    exit(1);
}

[$limit, $dryRun] = parseArgs($argv);
[$dsn, $dbUser, $dbPass] = parseDatabaseUrl($databaseUrl);

$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$selectKeep = $pdo->prepare(
    'SELECT DISTINCT t.game_id
     FROM (
        SELECT gc.game_id,
               ROW_NUMBER() OVER (
                   PARTITION BY gc.category_id
                   ORDER BY g.users_rated DESC, g.bgg_id DESC
               ) AS rn
        FROM game_categories gc
        INNER JOIN games g ON g.bgg_id = gc.game_id
     ) AS t
     WHERE t.rn <= :limit'
);
$selectKeep->bindValue(':limit', $limit, PDO::PARAM_INT);
$selectKeep->execute();
$keepIds = array_map(static fn(array $row): int => (int)$row['game_id'], $selectKeep->fetchAll());

$totalGames = (int)$pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
$keepCount = count($keepIds);
$deleteCount = max(0, $totalGames - $keepCount);

echo "Jeux totaux: {$totalGames}\n";
echo "Jeux a conserver (union categories): {$keepCount}\n";
echo "Jeux a supprimer: {$deleteCount}\n";

if ($dryRun) {
    echo "Dry-run termine.\n";
    exit(0);
}

if ($keepCount === 0) {
    fwrite(STDERR, "Aucun jeu a conserver. Annulation pour eviter une suppression totale.\n");
    exit(1);
}

$pdo->beginTransaction();

$pdo->exec('CREATE TEMPORARY TABLE keep_games (bgg_id INT PRIMARY KEY)');

$insertKeep = $pdo->prepare('INSERT INTO keep_games (bgg_id) VALUES (:bgg_id)');
foreach ($keepIds as $id) {
    $insertKeep->execute([':bgg_id' => $id]);
}

$pdo->exec('DELETE FROM game_images WHERE game_id NOT IN (SELECT bgg_id FROM keep_games)');
$pdo->exec('DELETE FROM game_mechanics WHERE game_id NOT IN (SELECT bgg_id FROM keep_games)');
$pdo->exec('DELETE FROM game_domains WHERE game_id NOT IN (SELECT bgg_id FROM keep_games)');
$pdo->exec('DELETE FROM game_categories WHERE game_id NOT IN (SELECT bgg_id FROM keep_games)');
$pdo->exec('DELETE FROM games WHERE bgg_id NOT IN (SELECT bgg_id FROM keep_games)');

$pdo->commit();

echo "Suppression terminee.\n";
