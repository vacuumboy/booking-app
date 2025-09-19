<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет по аналитике</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .stats-cell.label {
            font-weight: bold;
            width: 40%;
            background-color: #f0f0f0;
        }
        .stats-cell.value {
            width: 60%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table th, .table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        .number {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Отчет по аналитике</div>
        <div class="subtitle">
            {{ $userType === 'salon' ? 'Салон' : 'Мастер' }}: {{ $user->name }}<br>
            Период: {{ $periodTitle }}
        </div>
    </div>

    <!-- Основная статистика -->
    @if(!empty($stats))
    <div class="section">
        <div class="section-title">Основная статистика</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell label">Всего записей:</div>
                <div class="stats-cell value number">{{ number_format($stats['total_appointments'] ?? 0) }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell label">Общий доход:</div>
                <div class="stats-cell value number">{{ number_format($stats['total_revenue'] ?? 0, 2) }} €</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell label">Активные клиенты:</div>
                <div class="stats-cell value number">{{ number_format($stats['active_clients'] ?? 0) }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell label">Средний чек:</div>
                <div class="stats-cell value number">
                    {{ ($stats['total_appointments'] ?? 0) > 0 ? number_format(($stats['total_revenue'] ?? 0) / ($stats['total_appointments'] ?? 1), 2) : 0 }} €
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Статистика по услугам -->
    @if(!empty($serviceStats))
    <div class="section">
        <div class="section-title">Статистика по услугам</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Название услуги</th>
                    <th>Количество записей</th>
                    <th>Доход</th>
                    <th>Средняя цена</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceStats as $serviceStat)
                <tr>
                    <td>{{ $serviceStat['service']->name ?? 'Неизвестная услуга' }}</td>
                    <td class="number">{{ $serviceStat['appointments_count'] ?? 0 }}</td>
                    <td class="number">{{ number_format($serviceStat['revenue'] ?? 0, 2) }} €</td>
                    <td class="number">{{ number_format($serviceStat['avg_price'] ?? 0, 2) }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Статистика по мастерам (только для салонов) -->
    @if($userType === 'salon' && !empty($masterStats))
    <div class="section">
        <div class="section-title">Статистика по мастерам</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Имя мастера</th>
                    <th>Количество записей</th>
                    <th>Доход</th>
                </tr>
            </thead>
            <tbody>
                @foreach($masterStats as $masterStat)
                <tr>
                    <td>{{ $masterStat['master']->name ?? 'Неизвестный мастер' }}</td>
                    <td class="number">{{ $masterStat['appointments_count'] ?? 0 }}</td>
                    <td class="number">{{ number_format($masterStat['revenue'] ?? 0, 2) }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Топ клиенты (только для мастеров) -->
    @if($userType === 'master' && !empty($topClients))
    <div class="section">
        <div class="section-title">Топ клиенты</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Имя клиента</th>
                    <th>Количество записей</th>
                    <th>Потрачено</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topClients as $clientStat)
                <tr>
                    <td>{{ $clientStat['client']->name ?? 'Неизвестный клиент' }}</td>
                    <td class="number">{{ $clientStat['appointments_count'] ?? 0 }}</td>
                    <td class="number">{{ number_format($clientStat['total_spent'] ?? 0, 2) }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Клиентская статистика -->
    @if(!empty($clientStats))
    <div class="section">
        <div class="section-title">Клиентская статистика</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell label">Всего клиентов:</div>
                <div class="stats-cell value number">{{ $clientStats['total_clients'] ?? 0 }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell label">Новые клиенты:</div>
                <div class="stats-cell value number">{{ $clientStats['new_clients'] ?? 0 }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell label">Постоянные клиенты:</div>
                <div class="stats-cell value number">{{ $clientStats['returning_clients'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Производительность (только для мастеров) -->
    @if($userType === 'master' && !empty($performanceStats))
    <div class="section">
        <div class="section-title">Производительность</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell label">Записей в день (среднее):</div>
                <div class="stats-cell value number">{{ $performanceStats['avg_appointments_per_day'] ?? 0 }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell label">Загруженность:</div>
                <div class="stats-cell value number">{{ $performanceStats['efficiency_percentage'] ?? 0 }}%</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell label">Средняя длительность:</div>
                <div class="stats-cell value number">{{ $performanceStats['avg_duration_minutes'] ?? 0 }} мин</div>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        Система аналитики booking-app
    </div>
</body>
</html> 