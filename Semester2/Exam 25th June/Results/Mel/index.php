<?php
session_start();
$connection = new mysqli("localhost", "root", "", "ppl");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

//Task 1

if (isset($_POST['country'])) {
    $_SESSION['selected_country'] = $_POST['country'];
    unset($_SESSION['selected_city']);
}
$sqlCountries = $connection->prepare("SELECT CountryId, CountryName FROM Countries ORDER BY CountryName");
$sqlCountries->execute();
$resultCountries = $sqlCountries->get_result();
?>
<form method="POST" action="" onchange="this.submit()">
    <select name="country">
        <option value="">Please select a country</option>
        <?php
        while ($row = $resultCountries->fetch_assoc()) {
            $id = $row['CountryId'];
            $name = $row['CountryName'];

            $selected = (isset($_SESSION['selected_country']) && $_SESSION['selected_country'] == $id)
                        ? 'selected' : '';
            echo "<option value='$id' $selected>$name</option>";
        }
        ?>
    </select>
</form>
<?php

//Task 2

if (!empty($_SESSION['selected_country'])) {
    if (isset($_POST['city'])) {
        $_SESSION['selected_city'] = $_POST['city'];
    }
    $countryId = $_SESSION['selected_country'];
    $sqlCities = $connection->prepare("SELECT CityId, CityName FROM Cities WHERE CountryId = ? ORDER BY CityName");
    $sqlCities->bind_param("i", $countryId);
    $sqlCities->execute();
    $resultCities = $sqlCities->get_result();
    echo '<form method="POST" action="" onchange="this.submit()">';
    echo '<select name="city">';
    echo '<option value="">Please select a city</option>';
    while ($row = $resultCities->fetch_assoc()) {
        $cityId = $row['CityId'];
        $cityName = $row['CityName'];
        $selectedCity = (isset($_SESSION['selected_city']) && $_SESSION['selected_city'] == $cityId)
                        ? 'selected' : '';
        echo "<option value='$cityId' $selectedCity>$cityName</option>";
    }
    echo '</select>';
    echo '</form>';
}
?>
