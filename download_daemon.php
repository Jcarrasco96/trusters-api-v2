<?php

use app\core\Database;

require_once 'app.php';

$config = require_once 'config/config.php';

$app = new app\core\App($config);

$db = new Database();

$downloadDir = __DIR__ . '/downloads';
$downloadMethod = 'curl';

const COLOR_RESET = "\033[0m";
const COLOR_RED = "\033[31m";
const COLOR_GREEN = "\033[32m";
const COLOR_YELLOW = "\033[33m";
const COLOR_BLUE = "\033[34m";
const COLOR_MAGENTA = "\033[35m";
const COLOR_CYAN = "\033[36m";
const COLOR_WHITE = "\033[37m";

echo "Download daemon started.\n";

function fisrtDownload(): false|array|null
{
    global $db;

    $sql = "SELECT d.*, u.username as 'u_username', u.name as 'u_name', u.email as 'u_email' FROM download as d JOIN user as u ON d.user_id = u.id WHERE d.status = 'PENDING' ORDER BY d.created_at DESC LIMIT 1";

    return $db->unique_query($sql);
}

function fetchUrlLocation($url): ?string
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);

    $response = curl_exec($ch);

    $info = curl_getinfo($ch);
    $headers = substr($response, 0, $info['header_size']);
    $body = substr($response, $info['header_size']);

    curl_close($ch);

    if (preg_match('/href="([^"]*download[^"]*mediafire\.com[^"]*)"/', $body, $matches)) {
        return trim($matches[1]);
    }

    if (preg_match('/^Location:\s*(.*)$/mi', $headers, $matches)) {
//        return trim($matches[1]);
        $location = trim($matches[1]);

        if (stripos($location, 'error') !== false) {
            return 'contains_error => SI';
        }

        return 'contains_error => NO';
    }

    return null;
}

while (true) {

    $d = fisrtDownload();

    if ($d) {
        $url = $d['url'];

        if (str_contains($url, "www.mediafire.com")) {
            $ee = fetchUrlLocation($url); // https://www.mediafire.com/file/vzpe0lxfpmr607l/Cor1alIsl7and-1.1.1198-elamigos.part1.rar/file
            var_dump($ee);
            die();
            $html = shell_exec("curl -v --ssl-no-revoke -Lqs \"$url\"");
            var_dump($html);
            die();
            if (preg_match('/href="([^"]*download[^"]*mediafire\.com[^"]*)"/', $html, $matches)) {
                $url = $matches[1];
            }
        }

        $filename = basename(parse_url($url, PHP_URL_PATH));
        $outputPath = $downloadDir . '/' . $filename;

        echo "Downloading $url...\n";

        if ($downloadMethod === 'wget') {
            $command = "wget -O " . escapeshellarg($outputPath) . " " . escapeshellarg($url);
        } elseif ($downloadMethod === 'axel') {
            $command = "axel -o " . escapeshellarg($outputPath) . " " . escapeshellarg($url);
        } elseif ($downloadMethod === 'curl') {
            $command = "curl -v --ssl-no-revoke -o " . escapeshellarg($outputPath) . " " . escapeshellarg($url);
        } else {
            echo "Unsupported download method: $downloadMethod\n";
            continue;
        }

        $status = exec($command);

        echo "Downloaded $filename with status $status\n";

        $dateExpires = date('Y-m-d H:i:s', strtotime('+1 month'));

        $sql = sprintf("UPDATE download SET status = 'DOWNLOADED', path = '%s', expires_at = '%s' WHERE id = %u", $filename, $d['id'], $dateExpires);
        $db->query($sql);
    }

    // Archivar descargas
    $archives = $db->fetch_query("SELECT * FROM download WHERE status = 'DOWNLOADED' AND expires_at < NOW()");

    foreach ($archives as $archive) {
        @unlink($downloadDir . '/' . $archive['path']);

        $sql = sprintf("UPDATE download SET status = 'ARCHIVED', path = null WHERE id = %u", $archive['id']);
        $db->query($sql);

        echo "Archiving " . COLOR_GREEN . $archive['name'] . COLOR_RESET . " and deleting " . COLOR_RED . $archive['path'] . COLOR_RESET . "...\n";
    }

    echo "Sleeping 10 seconds...\n";
    sleep(10);
}
