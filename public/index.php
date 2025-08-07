<!DOCTYPE html>
<html>
<head>
    <title>Тестовое задание</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        #stats { margin-top: 20px; white-space: pre; }
    </style>
</head>
<body>
    <h1>Управление скриптами</h1>
    
    <div>
        <label>Количество запусков (N):</label>
        <input type="number" id="N" value="10" min="1">
        <button id="runBeta">Запустить Beta</button>
    </div>

    <div>
        <h3>Статистика (Gamma):</h3>
        <div id="stats">Загрузка...</div>
    </div>

    <script>
        document.getElementById('runBeta').addEventListener('click', async () => {
            const N = document.getElementById('N').value;
            const response = await fetch(`beta.php?N=${N}`);
            alert(await response.text());
        });

        // Автообновление статистики
        async function updateStats() {
            const response = await fetch('gamma.php');
            const data = await response.json();
            document.getElementById('stats').textContent = 
                JSON.stringify(data, null, 2);
        }

        setInterval(updateStats, 1000);
        updateStats();
    </script>
</body>
</html>