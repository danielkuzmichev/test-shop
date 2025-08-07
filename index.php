<!DOCTYPE html>
<html>
<head>
    <title>Управление скриптами</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        #stats { 
            margin-top: 20px; 
            padding: 10px;
            border: 1px solid #ddd;
            background: #f5f5f5;
        }
        #results {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .request {
            margin-bottom: 5px;
            padding: 5px;
            background: #f0f0f0;
        }
        #loading {
            display: none;
            margin-top: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .meta-info {
            margin-top: 15px;
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>
    <h1>Управление скриптами</h1>
    
    <div>
        <label>Количество запусков (N):</label>
        <input type="number" id="N" value="10" min="1">
        <button id="runAlpha">Запустить Alpha</button>
        <span id="loading">Выполнение запросов...</span>
    </div>

    <div id="results"></div>

    <div>
        <h3>Статистика (Gamma):</h3>
        <div id="stats">Загрузка...</div>
    </div>

    <script>
        // Функция для запуска Alpha запросов
        document.getElementById('runAlpha').addEventListener('click', async () => {
            const N = document.getElementById('N').value;
            const resultsContainer = document.getElementById('results');
            const loadingIndicator = document.getElementById('loading');
            
            resultsContainer.innerHTML = '';
            loadingIndicator.style.display = 'inline';
            
            try {
                const promises = [];
                for (let i = 0; i < N; i++) {
                    promises.push(
                        fetch('public/alfa.php')
                            .then(response => response.text())
                            .then(data => ({ index: i, data: data.trim() }))
                    );
                }
                
                const results = await Promise.all(promises);
                
                resultsContainer.innerHTML = `
                    <h3>Выполнено ${results.length} запросов к Alpha:</h3>
                    ${results.map(r => `
                        <div class="request">
                            <strong>Запрос ${r.index}:</strong> ${r.data}
                        </div>
                    `).join('')}
                `;
            } catch (error) {
                resultsContainer.innerHTML = `Ошибка: ${error.message}`;
            } finally {
                loadingIndicator.style.display = 'none';
            }
        });

        // Функция для обновления статистики Gamma
        async function updateGammaStats() {
            try {
                const response = await fetch('public/gamma.php');
                const data = await response.json();
                
                // Проверяем структуру данных
                const statsData = data.data?.data || [];
                const meta = data.data?.meta || {};
                
                // Создаем HTML для таблицы
                let tableHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Категория</th>
                                <th>Количество заказов</th>
                                <th>Период</th>
                                <th>Первый заказ</th>
                                <th>Последний заказ</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                // Добавляем строки с данными
                statsData.forEach(item => {
                    tableHTML += `
                        <tr>
                            <td>${item.category}</td>
                            <td>${item.orders_count}</td>
                            <td>${item.time_period.human_readable}</td>
                            <td>${item.first_order}</td>
                            <td>${item.last_order}</td>
                        </tr>
                    `;
                });
                
                tableHTML += `
                        </tbody>
                    </table>
                    <div class="meta-info">
                        Всего категорий: ${meta.total_categories || 0}<br>
                        Всего заказов: ${meta.total_orders || 0}<br>
                        Данные обновлены: ${meta.generated_at || 'неизвестно'}
                    </div>
                `;
                
                document.getElementById('stats').innerHTML = tableHTML;
            } catch (error) {
                document.getElementById('stats').innerHTML = 
                    `<div style="color: red;">Ошибка загрузки статистики: ${error.message}</div>`;
            }
        }

        // Обновляем статистику Gamma каждую секунду
        setInterval(updateGammaStats, 1000);
        updateGammaStats(); // Первоначальная загрузка
    </script>
</body>
</html>