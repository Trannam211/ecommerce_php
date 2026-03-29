<?php

declare(strict_types=1);

$apiUrl = 'https://provinces.open-api.vn/api/?depth=3';

$dsn = 'mysql:host=127.0.0.1;dbname=ecommerceweb;charset=utf8mb4';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $json = @file_get_contents($apiUrl);
    if ($json === false) {
        throw new RuntimeException('Khong the tai du lieu dia gioi tu API.');
    }

    $provinces = json_decode($json, true);
    if (!is_array($provinces) || empty($provinces)) {
        throw new RuntimeException('Du lieu API khong hop le hoac rong.');
    }

    $pdo->exec("ALTER TABLE tbl_vn_province CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("ALTER TABLE tbl_vn_district CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("ALTER TABLE tbl_vn_ward CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    $pdo->exec('TRUNCATE TABLE tbl_vn_ward');
    $pdo->exec('TRUNCATE TABLE tbl_vn_district');
    $pdo->exec('TRUNCATE TABLE tbl_vn_province');

    $insertProvince = $pdo->prepare('INSERT INTO tbl_vn_province (province_id, province_name) VALUES (?, ?)');
    $insertDistrict = $pdo->prepare('INSERT INTO tbl_vn_district (district_id, province_id, district_name) VALUES (?, ?, ?)');
    $insertWard = $pdo->prepare('INSERT INTO tbl_vn_ward (ward_id, district_id, ward_name) VALUES (?, ?, ?)');

    $districtCount = 0;
    $wardCount = 0;

    foreach ($provinces as $province) {
        $provinceId = isset($province['code']) ? (int)$province['code'] : 0;
        $provinceName = isset($province['name']) ? trim((string)$province['name']) : '';
        if ($provinceId <= 0 || $provinceName === '') {
            continue;
        }

        $insertProvince->execute([$provinceId, $provinceName]);

        $districts = isset($province['districts']) && is_array($province['districts']) ? $province['districts'] : [];
        foreach ($districts as $district) {
            $districtId = isset($district['code']) ? (int)$district['code'] : 0;
            $districtName = isset($district['name']) ? trim((string)$district['name']) : '';
            if ($districtId <= 0 || $districtName === '') {
                continue;
            }

            $insertDistrict->execute([$districtId, $provinceId, $districtName]);
            $districtCount++;

            $wards = isset($district['wards']) && is_array($district['wards']) ? $district['wards'] : [];
            foreach ($wards as $ward) {
                $wardId = isset($ward['code']) ? (int)$ward['code'] : 0;
                $wardName = isset($ward['name']) ? trim((string)$ward['name']) : '';
                if ($wardId <= 0 || $wardName === '') {
                    continue;
                }

                $insertWard->execute([$wardId, $districtId, $wardName]);
                $wardCount++;
            }
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    echo 'Da dong bo thanh cong du lieu dia gioi Viet Nam.' . PHP_EOL;
    echo 'Tinh/Thanh: ' . count($provinces) . PHP_EOL;
    echo 'Quan/Huyen: ' . $districtCount . PHP_EOL;
    echo 'Phuong/Xa: ' . $wardCount . PHP_EOL;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO) {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    fwrite(STDERR, 'Loi: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
