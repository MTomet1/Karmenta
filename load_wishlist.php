<?php
include 'dbconn.php';

$data = json_decode(file_get_contents("php://input"), true);
$wishlist = $data['wishlist'] ?? [];

if (!is_array($wishlist) || count($wishlist) === 0) {
    echo '
    <div class="wl-empty">
        <span class="wl-empty-icon">♡</span>
        <p>Nema spremljenih automobila</p>
    </div>';
    exit;
}

$ids = implode(',', array_map('intval', $wishlist));
$query = "SELECT * FROM cars WHERE id IN ($ids)";
$result = mysqli_query($MySQL, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $main_picture = $row['picture'] ?: "no-car-img.png";
    $brand_model  = htmlspecialchars($row['brand'] . " " . $row['model']);
    $year         = htmlspecialchars($row['year']);
    $price        = number_format($row['price'], 2, ',', '.');
    $img          = htmlspecialchars($main_picture);
    $id           = $row['id'];

    echo "
    <div class=\"car-card\">
        <div class=\"car-img-wrap\">
            <img src=\"Gallery/cars/{$img}\" alt=\"{$brand_model}\">
        </div>
        <div class=\"car-card-details\">
            <div class=\"car-title\">{$brand_model}</div>
            <div class=\"car-info\"><b>Godina:</b> {$year}</div>
            <div class=\"car-price\">{$price} EUR</div>
            <button class=\"remove-from-wishlist\" data-id=\"{$id}\">Ukloni</button>
        </div>
    </div>
    ";
}
