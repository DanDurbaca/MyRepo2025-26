<!DOCTYPE html>
<html>

<head>
    <title>Measurements</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">
    <script src="../Libraries/JS/jquery-3.7.1.min.js"></script>
    <?php
    include("../Libraries/navbar.php");
    include("../Libraries/conndb.php");
    include("../Libraries/createmeasurement.php");
    ?>
    <?php createnavbar("Measurements"); ?>
    <h1>Measurements</h1>
    <?php
    $selectstationsstmt = $conn->prepare("SELECT * FROM stations WHERE fk_user_owns = ?");
    $selectstationsstmt->bind_param("i", $_SESSION['username']);
    $selectstationsstmt->execute();
    $result = $selectstationsstmt->get_result();
    ?>
    <div class="controls-grid">

        <!-- CREATE MEASUREMENT -->
        <div class="form-card control-card">
            <div class="card-title">
                <h2>Create Measurement</h2>
                <p>Select a station and generate a new measurement.</p>
            </div>

            <div class="form-group">
                <label for="stationslist">Station</label>

                <select name="stationslist" id="stationslist">
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['pk_serialNumber'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" id="createMeasurementButton">
                Create Measurement
            </button>
        </div>

        <!-- FILTER -->
        <div class="form-card control-card">
            <div class="card-title">
                <h2>Filter Measurements</h2>
                <p>Show measurements within a selected time range.</p>
            </div>

            <div class="filter-grid">

                <div class="form-group">
                    <label for="fromTime">From</label>
                    <input type="datetime-local" id="fromTime">
                </div>

                <div class="form-group">
                    <label for="toTime">To</label>
                    <input type="datetime-local" id="toTime">
                </div>

            </div>

            <div class="filter-actions">
                <button id="filterTime">
                    Apply Filter
                </button>

                <button id="clearFilter" class="secondary-button">
                    Clear
                </button>
            </div>
        </div>

    </div>

    <div class="table-card">
        <div class="table-header">
            <h2>Measurement Data</h2>
            <span id="measurementCount"></span>
        </div>

        <div class="table-wrapper">
            <table id="tablemeasurements" class="measurement-table">
                <tr>
                    <th data-col="0">Time <span class="arrow"></span></th>
                    <th data-col="1">Temperature <span class="arrow"></span></th>
                    <th data-col="2">Humidity <span class="arrow"></span></th>
                    <th data-col="3">Pressure <span class="arrow"></span></th>
                    <th data-col="4">Light <span class="arrow"></span></th>
                    <th data-col="5">Gas <span class="arrow"></span></th>
                </tr>
            </table>
        </div>
    </div>

    <script>
        let sortDirections = {};
        let activeColumn = null;

        function start() {
            $.post("../Libraries/createmeasurement.php", {
                stationslist: $("#stationslist").val()
            }, function(data) {
                $("#tablemeasurements tr:first").after(data);
                applyCurrentSort();
            });
        }
        $("#createMeasurementButton").click(start);

        function loadMeasurements() {
            $.post("../Libraries/showmeasurements.php", {
                station: $("#stationslist").val()
            }, function(data) {
                $("#tablemeasurements").append(data);
            });
        }
        loadMeasurements();
        $("#tablemeasurements th").each(function(index) {
            sortDirections[index] = 1;
            $(this).css("cursor", "pointer").click(function() {
                if (activeColumn === index) {
                    sortDirections[index] *= -1;
                } else {
                    activeColumn = index;
                }
                updateArrows(index);
                sortTable(index);
            });
        });

        function updateArrows(col) {
            $(".arrow").text("");
            let arrow = sortDirections[col] === 1 ? "▲" : "▼";
            $("#tablemeasurements th").eq(col).find(".arrow").text(arrow);
        }

        function sortTable(col) {
            let table = $("#tablemeasurements");
            let rows = table.find("tr:gt(0)").toArray();
            let dir = sortDirections[col];

            rows.sort(function(a, b) {
                let A = $(a).children("td").eq(col).text().trim();
                let B = $(b).children("td").eq(col).text().trim();

                // timestamp sort (column 0)
                if (col === 0) {
                    return (new Date(A) - new Date(B)) * dir;
                }

                let numA = parseFloat(A.replace(",", "."));
                let numB = parseFloat(B.replace(",", "."));

                if (!isNaN(numA) && !isNaN(numB)) {
                    return (numA - numB) * dir;
                }

                return A.localeCompare(B) * dir;
            });

            $.each(rows, function(_, row) {
                table.append(row);
            });
        }

        function applyCurrentSort() {
            if (activeColumn !== null) {
                sortTable(activeColumn);
            }
        }
        $("#filterTime").click(function() {
            let from = $("#fromTime").val() ? new Date($("#fromTime").val()) : null;
            let to = $("#toTime").val() ? new Date($("#toTime").val()) : null;

            $("#tablemeasurements tr:gt(0)").each(function() {
                let timeText = $(this).children("td").eq(0).text().trim();
                let rowTime = new Date(timeText);

                let show = true;

                if (from && rowTime < from) show = false;
                if (to && rowTime > to) show = false;

                $(this).toggle(show);
            });
        });

        $("#clearFilter").click(function() {
            $("#fromTime, #toTime").val("");
            $("#tablemeasurements tr").show();
        });
    </script>


</body>

</html>