<!DOCTYPE html>
<html>

<head>
    <title>Welcome</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">

    <?php
    include("../Libraries/conndb.php");
    include("../Libraries/navbar.php");
    include("../Libraries/loginlib.php");

    createnavbar("Dashboard");

    // Fetch data
    $stmt = $conn->prepare("
    SELECT * 
    FROM measurements m 
    JOIN stations s ON m.fk_station_records = s.pk_serialNumber 
    WHERE s.fk_user_owns = ? 
    ORDER BY timestamp DESC 
    LIMIT 10
");

    $stmt->bind_param("s", $_SESSION["username"]);
    $stmt->execute();
    $result = $stmt->get_result();

    // Store results in array (IMPORTANT)
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    ?>

    <h1>Welcome!</h1>
    <h2>Dashboard</h2>

    <!-- TABLE -->
    <table>
        <tr>
            <th>Station</th>
            <th>Timestamp</th>
            <th>Temperature</th>
            <th>Humidity</th>
            <th>Pressure</th>
            <th>Light</th>
            <th>Gas</th>
        </tr>
        <tbody>
            <?php foreach ($data as $row) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['fk_station_records']) ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                    <td><?= htmlspecialchars($row['temperature']) ?>°C</td>
                    <td><?= htmlspecialchars($row['humidity']) ?>%</td>
                    <td><?= htmlspecialchars($row['pressure']) ?></td>
                    <td><?= htmlspecialchars($row['light']) ?></td>
                    <td><?= htmlspecialchars($row['gas']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- CHARTS -->
<div class="charts">
    <div class="chart-box"><canvas id="chart_temperature"></canvas></div>
    <div class="chart-box"><canvas id="chart_humidity"></canvas></div>
    <div class="chart-box"><canvas id="chart_pressure"></canvas></div>
    <div class="chart-box"><canvas id="chart_light"></canvas></div>
    <div class="chart-box"><canvas id="chart_gas"></canvas></div>
</div>

    <!-- Chart.js (FIXED) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const rawData = <?php echo json_encode($data); ?>;
        const labels = [];
        const temperatures = [];
        const humidities = [];
        const pressures = [];
        const lights = [];
        const gases = [];
        
        for (let i = rawData.length - 1; i >= 0; i--) {
            labels.push(rawData[i].timestamp);
            temperatures.push(Number(rawData[i].temperature));
            humidities.push(Number(rawData[i].humidity));
            pressures.push(Number(rawData[i].pressure));
            lights.push(Number(rawData[i].light));
            gases.push(Number(rawData[i].gas));
        }
        
        function getData(key) {
            if (key === 'temperature') return temperatures;
            if (key === 'humidity') return humidities;
            if (key === 'pressure') return pressures;
            if (key === 'light') return lights;
            if (key === 'gas') return gases;
            return [];
        }
        function createChart(canvasId, label, data, color) {
            new Chart(document.getElementById(canvasId), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: color,
                        borderWidth: 2,
                        fill: false,
                        tension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // IMPORTANT
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Create charts
        createChart('chart_temperature', 'Temperature (°C)', getData('temperature'), 'red');
        createChart('chart_humidity', 'Humidity (%)', getData('humidity'), 'blue');
        createChart('chart_pressure', 'Pressure', getData('pressure'), 'orange');
        createChart('chart_light', 'Light', getData('light'), 'purple');
        createChart('chart_gas', 'Gas', getData('gas'), 'green');
    </script>

</body>

</html>