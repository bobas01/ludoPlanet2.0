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
    fwrite(STDERR, "Usage: php {$script} /path/to/bgg_dataset.csv [--limit-per-category=20]\n");
}

/** @return array{0: string, 1: int} [csvPath, limitPerCategory] */
function parseArgs(array $argv): array
{
    $limitPerCategory = 0;
    $csvPath = '';
    for ($i = 1; $i < count($argv); $i++) {
        $arg = $argv[$i];
        if (str_starts_with($arg, '--limit-per-category=')) {
            $limitPerCategory = (int) substr($arg, 21);
            $limitPerCategory = $limitPerCategory > 0 ? $limitPerCategory : 0;
        } elseif ($arg !== '' && $csvPath === '') {
            $csvPath = $arg;
        }
    }
    return [$csvPath, $limitPerCategory];
}

/** Catégories attendues (ordre pour savoir quand arrêter) */
function getExpectedCategories(): array
{
    return ['enfants', "jeux d'ambiance", 'jeux de cartes', "jeux d'expert", 'jeux de plateau'];
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

function parseInt(?string $value): ?int
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    return (int)$value;
}

function parseDecimal(?string $value): ?float
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $value = str_replace(',', '.', $value);
    return (float)$value;
}

function splitList(?string $value): array
{
    $value = trim((string)$value);
    if ($value === '') {
        return [];
    }
    $items = array_map('trim', explode(',', $value));
    return array_values(array_filter($items, static fn(string $item): bool => $item !== ''));
}

function deriveCategories(array $domains, ?float $complexity): array
{
    $categories = [];
    $domainSet = array_flip($domains);

    if (isset($domainSet["Children's Games"])) {
        $categories[] = 'enfants';
    }
    if (isset($domainSet['Party Games'])) {
        $categories[] = "jeux d'ambiance";
    }
    if (isset($domainSet['Customizable Games'])) {
        $categories[] = 'jeux de cartes';
    }
    if (isset($domainSet['Wargames']) || ($complexity !== null && $complexity >= 3.5)) {
        $categories[] = "jeux d'expert";
    }

    $plateauDomains = [
        'Family Games',
        'Strategy Games',
        'Thematic Games',
        'Abstract Games',
    ];
    foreach ($plateauDomains as $domain) {
        if (isset($domainSet[$domain])) {
            $categories[] = 'jeux de plateau';
            break;
        }
    }

    if ($categories === []) {
        $categories[] = 'jeux de plateau';
    }

    return array_values(array_unique($categories));
}

if ($argc < 2) {
    usage();
    exit(1);
}

[$csvPath, $limitPerCategory] = parseArgs($argv);
if ($csvPath === '' || !is_file($csvPath)) {
    fwrite(STDERR, "Fichier CSV introuvable ou non fourni: " . ($csvPath ?: '(vide)') . "\n");
    usage();
    exit(1);
}

if ($limitPerCategory > 0) {
    echo "Limite: {$limitPerCategory} jeux par catégorie.\n";
}

$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl === false || trim($databaseUrl) === '') {
    fwrite(STDERR, "DATABASE_URL manquant. Definis-le avant d'executer le script.\n");
    exit(1);
}

[$dsn, $dbUser, $dbPass] = parseDatabaseUrl($databaseUrl);

$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$schemaPath = __DIR__ . '/../sql/bgg_schema.sql';
if (is_file($schemaPath)) {
    $schemaSql = file_get_contents($schemaPath);
    $statements = preg_split('/;\s*\n/', (string)$schemaSql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

$insertGame = $pdo->prepare(
    'INSERT INTO games (
        bgg_id, name, year_published, min_players, max_players, play_time,
        min_age, description, price_cents, users_rated, rating_average, bgg_rank,
        complexity_average, owned_users
    ) VALUES (
        :bgg_id, :name, :year_published, :min_players, :max_players, :play_time,
        :min_age, :description, :price_cents, :users_rated, :rating_average, :bgg_rank,
        :complexity_average, :owned_users
    )
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        year_published = VALUES(year_published),
        min_players = VALUES(min_players),
        max_players = VALUES(max_players),
        play_time = VALUES(play_time),
        min_age = VALUES(min_age),
        description = VALUES(description),
        price_cents = VALUES(price_cents),
        users_rated = VALUES(users_rated),
        rating_average = VALUES(rating_average),
        bgg_rank = VALUES(bgg_rank),
        complexity_average = VALUES(complexity_average),
        owned_users = VALUES(owned_users)'
);

$upsertMechanic = $pdo->prepare(
    'INSERT INTO mechanics (name) VALUES (:name)
     ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
);

$upsertDomain = $pdo->prepare(
    'INSERT INTO domains (name) VALUES (:name)
     ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
);

$insertGameMechanic = $pdo->prepare(
    'INSERT IGNORE INTO game_mechanics (game_id, mechanic_id) VALUES (:game_id, :mechanic_id)'
);

$insertGameDomain = $pdo->prepare(
    'INSERT IGNORE INTO game_domains (game_id, domain_id) VALUES (:game_id, :domain_id)'
);

$upsertCategory = $pdo->prepare(
    'INSERT INTO categories (name) VALUES (:name)
     ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
);

$insertGameCategory = $pdo->prepare(
    'INSERT IGNORE INTO game_categories (game_id, category_id) VALUES (:game_id, :category_id)'
);

$handle = fopen($csvPath, 'r');
if ($handle === false) {
    fwrite(STDERR, "Impossible d'ouvrir le CSV.\n");
    exit(1);
}

$header = fgetcsv($handle, 0, ';', '"', '\\');
if ($header === false) {
    fwrite(STDERR, "CSV vide.\n");
    exit(1);
}

$header = array_map('trim', $header);
if (isset($header[0])) {
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
}

$index = array_flip($header);
$required = [
    'ID',
    'Name',
    'Year Published',
    'Min Players',
    'Max Players',
    'Play Time',
    'Min Age',
    'Users Rated',
    'Rating Average',
    'BGG Rank',
    'Complexity Average',
    'Owned Users',
    'Mechanics',
    'Domains',
];

foreach ($required as $column) {
    if (!array_key_exists($column, $index)) {
        fwrite(STDERR, "Colonne manquante: {$column}\n");
        exit(1);
    }
}

$rowCount = 0;
$categoryCounts = [];
$expectedCategories = getExpectedCategories();
$pdo->beginTransaction();

while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
    $bggId = parseInt($row[$index['ID']] ?? null);
    if ($bggId === null) {
        continue;
    }

    $complexity = parseDecimal($row[$index['Complexity Average']] ?? null);
    $domains = splitList($row[$index['Domains']] ?? null);
    $categories = deriveCategories($domains, $complexity);

    if ($limitPerCategory > 0 && $categories !== []) {
        $allCategoriesFull = true;
        foreach ($categories as $cat) {
            if (($categoryCounts[$cat] ?? 0) < $limitPerCategory) {
                $allCategoriesFull = false;
                break;
            }
        }
        if ($allCategoriesFull) {
            continue;
        }
    }

    $insertGame->execute([
        ':bgg_id' => $bggId,
        ':name' => trim((string)$row[$index['Name']]),
        ':year_published' => parseInt($row[$index['Year Published']] ?? null),
        ':min_players' => parseInt($row[$index['Min Players']] ?? null),
        ':max_players' => parseInt($row[$index['Max Players']] ?? null),
        ':play_time' => parseInt($row[$index['Play Time']] ?? null),
        ':min_age' => parseInt($row[$index['Min Age']] ?? null),
        ':description' => null,
        ':price_cents' => null,
        ':users_rated' => parseInt($row[$index['Users Rated']] ?? null),
        ':rating_average' => parseDecimal($row[$index['Rating Average']] ?? null),
        ':bgg_rank' => parseInt($row[$index['BGG Rank']] ?? null),
        ':complexity_average' => $complexity,
        ':owned_users' => parseInt($row[$index['Owned Users']] ?? null),
    ]);

    $mechanics = splitList($row[$index['Mechanics']] ?? null);
    foreach ($mechanics as $name) {
        $upsertMechanic->execute([':name' => $name]);
        $mechanicId = (int)$pdo->lastInsertId();
        $insertGameMechanic->execute([
            ':game_id' => $bggId,
            ':mechanic_id' => $mechanicId,
        ]);
    }

    foreach ($domains as $name) {
        $upsertDomain->execute([':name' => $name]);
        $domainId = (int)$pdo->lastInsertId();
        $insertGameDomain->execute([
            ':game_id' => $bggId,
            ':domain_id' => $domainId,
        ]);
    }

    foreach ($categories as $name) {
        $upsertCategory->execute([':name' => $name]);
        $categoryId = (int)$pdo->lastInsertId();
        $insertGameCategory->execute([
            ':game_id' => $bggId,
            ':category_id' => $categoryId,
        ]);
        $categoryCounts[$name] = ($categoryCounts[$name] ?? 0) + 1;
    }

    $rowCount++;
    if ($rowCount % 500 === 0) {
        echo "Importe {$rowCount} lignes...\n";
    }

    if ($limitPerCategory > 0) {
        $allFull = true;
        foreach ($expectedCategories as $cat) {
            if (($categoryCounts[$cat] ?? 0) < $limitPerCategory) {
                $allFull = false;
                break;
            }
        }
        if ($allFull) {
            echo "Limite atteinte ({$limitPerCategory} par catégorie). Arrêt.\n";
            break;
        }
    }
}

$pdo->commit();
fclose($handle);

echo "Import termine: {$rowCount} jeux.\n";
