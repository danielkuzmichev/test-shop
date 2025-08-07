<?php
$N = (int)($_GET['N'] ?? 10);

// Создаем несколько дескрипторов cURL
$multiHandle = curl_multi_init();
$handles = [];

for ($i = 0; $i < $N; $i++) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://app/alfa.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_multi_add_handle($multiHandle, $ch);
    $handles[] = $ch;
}

// Выполняем все запросы одновременно
$running = null;
do {
    curl_multi_exec($multiHandle, $running);
    curl_multi_select($multiHandle);
} while ($running > 0);

// Собираем результаты
$results = [];
foreach ($handles as $ch) {
    $results[] = curl_multi_getcontent($ch);
    curl_multi_remove_handle($multiHandle, $ch);
    curl_close($ch);
}

curl_multi_close($multiHandle);

// Выводим результаты
echo "Выполнено ".count($results)." параллельных запросов:\n";
foreach ($results as $i => $result) {
    echo "Запрос $i: ".trim($result)."\n";
}
?>