<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $banner_registration = $row['banner_registration'];
}

$selected_province_id = isset($_POST['cust_province_id']) ? (int)$_POST['cust_province_id'] : 0;
$selected_district_id = isset($_POST['cust_district_id']) ? (int)$_POST['cust_district_id'] : 0;
$selected_ward_id = isset($_POST['cust_ward_id']) ? (int)$_POST['cust_ward_id'] : 0;
$redirect_target = (isset($_GET['redirect']) && $_GET['redirect'] === 'checkout') ? 'checkout' : 'home';
$redirect_after_register = BASE_URL . (($redirect_target === 'checkout') ? 'checkout.php' : 'index.php');
?>

<?php
if (isset($_POST['form1'])) {

    if(isset($_POST['redirect']) && $_POST['redirect'] === 'checkout') {
        $redirect_target = 'checkout';
        $redirect_after_register = BASE_URL . 'checkout.php';
    }

    $valid = 1;
    $province_name = isset($_POST['cust_province_name']) ? trim(strip_tags($_POST['cust_province_name'])) : '';
    $district_name = isset($_POST['cust_district_name']) ? trim(strip_tags($_POST['cust_district_name'])) : '';
    $ward_name = isset($_POST['cust_ward_name']) ? trim(strip_tags($_POST['cust_ward_name'])) : '';
    $cust_name_input = isset($_POST['cust_name']) ? trim(strip_tags($_POST['cust_name'])) : '';
    $cust_phone_input = isset($_POST['cust_phone']) ? trim(strip_tags($_POST['cust_phone'])) : '';
    $cust_address_input = isset($_POST['cust_address']) ? trim(strip_tags($_POST['cust_address'])) : '';
    $cust_password_input = isset($_POST['cust_password']) ? $_POST['cust_password'] : '';
    $cust_re_password_input = isset($_POST['cust_re_password']) ? $_POST['cust_re_password'] : '';

    $cust_province_id = isset($_POST['cust_province_id']) ? (int)$_POST['cust_province_id'] : 0;
    $cust_district_id = isset($_POST['cust_district_id']) ? (int)$_POST['cust_district_id'] : 0;
    $cust_ward_id = isset($_POST['cust_ward_id']) ? (int)$_POST['cust_ward_id'] : 0;

    if($cust_name_input === '') {
        $valid = 0;
        $error_message .= "Vui lòng nhập họ và tên.<br>";
    } elseif(!preg_match('/^(?=.*\s)[\p{L}\s]+$/u', $cust_name_input)) {
        $valid = 0;
        $error_message .= "Họ và tên phải có ít nhất 1 khoảng trắng và không chứa số hoặc ký tự đặc biệt.<br>";
    }

    if(empty($_POST['cust_email'])) {
        $valid = 0;
        $error_message .= "Vui lòng nhập địa chỉ email.<br>";
    } else {
        if (filter_var($_POST['cust_email'], FILTER_VALIDATE_EMAIL) === false) {
            $valid = 0;
            $error_message .= "Địa chỉ email không hợp lệ.<br>";
        } else {
            $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
            $statement->execute(array($_POST['cust_email']));
            $total = $statement->rowCount();
            if($total) {
                $valid = 0;
                $error_message .= "Địa chỉ email này đã được đăng ký trước đó.<br>";
            }
        }
    }

    if($cust_phone_input === '') {
        $valid = 0;
        $error_message .= "Vui lòng nhập số điện thoại.<br>";
    } elseif(!preg_match('/^0\d{9}$/', $cust_phone_input)) {
        $valid = 0;
        $error_message .= "Số điện thoại phải gồm đúng 10 số và bắt đầu bằng số 0.<br>";
    }

    if($cust_province_id <= 0) {
        $valid = 0;
        $error_message .= "Vui lòng chọn tỉnh/thành phố.<br>";
    }

    if($cust_district_id <= 0) {
        $valid = 0;
        $error_message .= "Vui lòng chọn quận/huyện.<br>";
    }

    if($cust_ward_id <= 0) {
        $valid = 0;
        $error_message .= "Vui lòng chọn phường/xã.<br>";
    }

    if($province_name === '') {
        $valid = 0;
        $error_message .= "Tên tỉnh/thành phố chưa hợp lệ. Vui lòng chọn lại.<br>";
    }

    if($district_name === '') {
        $valid = 0;
        $error_message .= "Tên quận/huyện chưa hợp lệ. Vui lòng chọn lại.<br>";
    }

    if($ward_name === '') {
        $valid = 0;
        $error_message .= "Tên phường/xã chưa hợp lệ. Vui lòng chọn lại.<br>";
    }

    if($cust_address_input === '') {
        $valid = 0;
        $error_message .= "Vui lòng nhập địa chỉ chi tiết.<br>";
    } elseif(!preg_match('/^(?=.*\s)[\p{L}0-9\-\/\s]+$/u', $cust_address_input)) {
        $valid = 0;
        $error_message .= "Số nhà, tên đường phải có ít nhất 1 khoảng trắng và chỉ được chứa chữ, số, dấu '/' hoặc '-'.<br>";
    }

    if($cust_password_input === '' || $cust_re_password_input === '') {
        $valid = 0;
        $error_message .= "Vui lòng nhập mật khẩu và xác nhận mật khẩu.<br>";
    } else {
        if(strlen($cust_password_input) < 8 || strlen($cust_password_input) > 16) {
            $valid = 0;
            $error_message .= "Mật khẩu phải có từ 8 đến 16 ký tự.<br>";
        }

        if(!preg_match('/[A-Z]/', $cust_password_input)) {
            $valid = 0;
            $error_message .= "Mật khẩu phải có ít nhất 1 chữ in hoa (A-Z).<br>";
        }

        if(!preg_match('/[a-z]/', $cust_password_input)) {
            $valid = 0;
            $error_message .= "Mật khẩu phải có ít nhất 1 chữ thường (a-z).<br>";
        }

        if(!preg_match('/\d/', $cust_password_input)) {
            $valid = 0;
            $error_message .= "Mật khẩu phải có ít nhất 1 chữ số (0-9).<br>";
        }

        if(!preg_match('/[!@#$%^&*]/', $cust_password_input)) {
            $valid = 0;
            $error_message .= "Mật khẩu phải có ít nhất 1 ký tự đặc biệt (!@#$%^&*).<br>";
        }
    }

    if($cust_password_input !== '' && $cust_re_password_input !== '') {
        if($cust_password_input != $cust_re_password_input) {
            $valid = 0;
            $error_message .= "Mật khẩu xác nhận không khớp.<br>";
        }
    }

    if($valid == 1) {

        $token = '';
        $cust_datetime = date('Y-m-d h:i:s');
        $cust_timestamp = time();

        // saving into the database
        $statement = $pdo->prepare("INSERT INTO tbl_customer (
                                        cust_name,
                                        cust_email,
                                        cust_phone,
                                        cust_password,
                                        cust_token,
                                        cust_datetime,
                                        cust_timestamp,
                                        cust_status
                                    ) VALUES (?,?,?,?,?,?,?,?)");
        $statement->execute(array(
                                        $cust_name_input,
                                        strip_tags($_POST['cust_email']),
                                        $cust_phone_input,
                                        md5($cust_password_input),
                                        $token,
                                        $cust_datetime,
                                        $cust_timestamp,
                                        1
                                    ));

        $new_customer_id = (int)$pdo->lastInsertId();

        $statement = $pdo->prepare("INSERT INTO tbl_customer_address (cust_id, receiver_name, receiver_phone, address_line, city, district, ward, is_default) VALUES (?,?,?,?,?,?,?,1)");
        $statement->execute(array(
                                        $new_customer_id,
                                        $cust_name_input,
                                        $cust_phone_input,
                                        $cust_address_input,
                                        $province_name,
                                        $district_name,
                                        $ward_name
                                    ));

        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? LIMIT 1");
        $statement->execute(array($new_customer_id));
        $new_customer = $statement->fetch(PDO::FETCH_ASSOC);
        if($new_customer) {
            $_SESSION['customer'] = $new_customer;
        }

        unset($_POST['cust_name']);
        unset($_POST['cust_email']);
        unset($_POST['cust_phone']);
        unset($_POST['cust_address']);
        unset($_POST['cust_province_id']);
        unset($_POST['cust_district_id']);
        unset($_POST['cust_ward_id']);
        unset($_POST['cust_province_name']);
        unset($_POST['cust_district_name']);
        unset($_POST['cust_ward_name']);

        $success_message = 'Đăng ký thành công!';
    }
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(assets/uploads/<?php echo $banner_registration; ?>);">
    <div class="inner">
        <h1>Đăng ký tài khoản</h1>
    </div>
</div>

<style>
    .register-shell {
        max-width: 920px;
        margin: 0 auto;
    }

    .register-card {
        background: #fff;
        border: 1px solid #e7eaf0;
        border-radius: 0;
        box-shadow: 0 12px 30px rgba(15, 35, 95, 0.08);
        overflow: hidden;
    }

    .register-card-body {
        padding: 24px 26px 26px;
    }

    .register-section-title {
        margin: 12px 0 14px;
        font-size: 14px;
        font-weight: 700;
        color: #22457a;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .register-divider {
        border: 0;
        border-top: 1px dashed #d8e2f0;
        margin: 18px 0 8px;
    }

    .register-card .form-group {
        margin-bottom: 16px;
    }

    .register-card label {
        font-weight: 600;
        color: #23344f;
        margin-bottom: 7px;
    }

    .register-card .form-control {
        border-radius: 0;
        border-color: #d8e0ec;
        min-height: 42px;
    }

    .register-card textarea.form-control {
        min-height: 64px;
        resize: vertical;
    }

    .register-card .password-field {
        position: relative;
    }

    .register-card .password-field .form-control {
        padding-right: 46px;
    }

    .register-card .password-field .toggle-password {
        position: absolute;
        top: 1px;
        right: 1px;
        width: 40px;
        height: 40px;
        border: 0;
        border-left: 1px solid #d8e0ec;
        background: #f8fafc;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        color: #355579;
        padding: 0;
    }

    .register-card .password-field .toggle-password:hover,
    .register-card .password-field .toggle-password:focus {
        background: #edf3fb;
        outline: none;
    }

    .register-card .register-submit-wrap {
        margin-top: 6px;
    }

    .register-card .btn-register {
        min-width: 150px;
        border-radius: 0;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .register-card .help-block {
        color: #5d6f89;
    }

    @media (max-width: 767px) {
        .register-card-body {
            padding: 18px;
        }
    }
</style>

<div class="page">
    <div class="container">
        <div class="register-shell">
            <div class="register-card">
                <div class="register-card-body">
                    <div class="user-content" style="padding:0;">
                        <form action="" method="post">
                            <?php $csrf->echoInputField(); ?>
                            <input type="hidden" name="redirect" value="<?php echo $redirect_target; ?>">

                            <?php
                            if($error_message != '') {
                                echo "<div class='error' style='padding: 12px;background:#fff4f4;border:1px solid #f3c9c9;margin-bottom:18px;'>".$error_message."</div>";
                            }
                            if($success_message != '') {
                                echo "<div class='success' style='padding: 12px;background:#f2fbf5;border:1px solid #b9e6c6;margin-bottom:18px;'>".$success_message."</div>";
                            }
                            ?>

                            <div class="register-section-title">Thông tin cá nhân</div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="">Họ và tên *</label>
                                    <input type="text" class="form-control" name="cust_name" placeholder="Nhập họ và tên" value="<?php if(isset($_POST['cust_name'])){echo $_POST['cust_name'];} ?>" title="Họ và tên phải có ít nhất 1 khoảng trắng và không chứa số, ký tự đặc biệt.">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="">Địa chỉ email *</label>
                                    <input type="email" class="form-control" name="cust_email" placeholder="Nhập địa chỉ email" value="<?php if(isset($_POST['cust_email'])){echo $_POST['cust_email'];} ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="">Số điện thoại *</label>
                                    <input type="text" class="form-control" name="cust_phone" placeholder="Nhập số điện thoại" value="<?php if(isset($_POST['cust_phone'])){echo $_POST['cust_phone'];} ?>" inputmode="numeric" maxlength="10" pattern="^0\d{9}$" title="Số điện thoại phải gồm đúng 10 số và bắt đầu bằng số 0.">
                                </div>
                            </div>

                            <hr class="register-divider">
                            <div class="register-section-title">Địa chỉ liên hệ</div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label for="">Tỉnh/Thành phố *</label>
                                    <select name="cust_province_id" class="form-control select2 vn-province">
                                        <option value="">Chọn tỉnh/thành phố</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="">Quận/Huyện *</label>
                                    <select name="cust_district_id" class="form-control select2 vn-district">
                                        <option value="">Chọn quận/huyện</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="">Phường/Xã *</label>
                                    <select name="cust_ward_id" class="form-control select2 vn-ward">
                                        <option value="">Chọn phường/xã</option>
                                    </select>
                                </div>

                                <input type="hidden" name="cust_province_name" class="vn-province-name" value="<?php echo isset($_POST['cust_province_name']) ? htmlspecialchars($_POST['cust_province_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                <input type="hidden" name="cust_district_name" class="vn-district-name" value="<?php echo isset($_POST['cust_district_name']) ? htmlspecialchars($_POST['cust_district_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                <input type="hidden" name="cust_ward_name" class="vn-ward-name" value="<?php echo isset($_POST['cust_ward_name']) ? htmlspecialchars($_POST['cust_ward_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                                <div class="col-md-12 form-group">
                                    <label for="">Địa chỉ chi tiết *</label>
                                    <textarea name="cust_address" class="form-control" cols="30" rows="3" placeholder="Ví dụ: 12A Nguyen Trai" title="Số nhà, tên đường phải có ít nhất 1 khoảng trắng; chỉ chứa chữ, số, dấu '/' hoặc '-'."><?php if(isset($_POST['cust_address'])){echo $_POST['cust_address'];} ?></textarea>
                                </div>
                            </div>

                            <hr class="register-divider">
                            <div class="register-section-title">Bảo mật tài khoản</div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="">Mật khẩu *</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="cust_password" name="cust_password" placeholder="Nhập mật khẩu" minlength="8" maxlength="16" autocomplete="new-password" required>
                                        <button class="toggle-password" type="button" data-target="#cust_password" aria-label="Hiện hoặc ẩn mật khẩu">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="help-block" style="margin-top:6px;">
                                        Tối thiểu 8 ký tự (khuyên: 8-16), có ít nhất 1 chữ in hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt (!@#$%^&*).
                                    </small>
                                    <ul class="list-unstyled" id="password-rule-list" style="margin:6px 0 0 0;font-size:12px;line-height:1.6;">
                                        <li data-rule="length" style="color:#a94442;">8-16 ký tự</li>
                                        <li data-rule="upper" style="color:#a94442;">Ít nhất 1 chữ in hoa (A-Z)</li>
                                        <li data-rule="lower" style="color:#a94442;">Ít nhất 1 chữ thường (a-z)</li>
                                        <li data-rule="digit" style="color:#a94442;">Ít nhất 1 số (0-9)</li>
                                        <li data-rule="special" style="color:#a94442;">Ít nhất 1 ký tự đặc biệt (!@#$%^&*)</li>
                                    </ul>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="">Xác nhận mật khẩu *</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="cust_re_password" name="cust_re_password" placeholder="Nhập lại mật khẩu" minlength="8" maxlength="16" autocomplete="new-password" required>
                                        <button class="toggle-password" type="button" data-target="#cust_re_password" aria-label="Hiện hoặc ẩn mật khẩu xác nhận">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="help-block" id="password-match-hint" style="margin-top:6px;"></small>
                                </div>
                            </div>

                            <div class="register-submit-wrap">
                                <input type="submit" class="btn btn-danger btn-register" value="Đăng ký" name="form1">
                            </div>
                            <p class="login-register-hint" style="margin-top:14px;">
                                Đã có tài khoản?
                                <a href="login.php">Đăng nhập ngay</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($success_message != ''): ?>
<script>
    setTimeout(function() {
        window.location.href = '<?php echo $redirect_after_register; ?>';
    }, 1200);
</script>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var provinceSelect = document.querySelector('.vn-province');
        var districtSelect = document.querySelector('.vn-district');
        var wardSelect = document.querySelector('.vn-ward');

        var provinceNameInput = document.querySelector('.vn-province-name');
        var districtNameInput = document.querySelector('.vn-district-name');
        var wardNameInput = document.querySelector('.vn-ward-name');

        var selectedProvinceId = '<?php echo $selected_province_id; ?>';
        var selectedDistrictId = '<?php echo $selected_district_id; ?>';
        var selectedWardId = '<?php echo $selected_ward_id; ?>';
        var vnTree = [];

        function refreshSelect2(selectEl) {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery(selectEl).trigger('change.select2');
            }
        }

        function resetSelect(selectEl, placeholder) {
            selectEl.innerHTML = '';
            var option = document.createElement('option');
            option.value = '';
            option.textContent = placeholder;
            selectEl.appendChild(option);
            selectEl.value = '';
            refreshSelect2(selectEl);
        }

        function fillSelect(selectEl, items, placeholder, valueKey, textKey, selectedValue) {
            resetSelect(selectEl, placeholder);
            items.forEach(function(item) {
                var option = document.createElement('option');
                option.value = item[valueKey];
                option.textContent = item[textKey];
                if (String(item[valueKey]) === String(selectedValue)) {
                    option.selected = true;
                }
                selectEl.appendChild(option);
            });
            refreshSelect2(selectEl);
        }

        function updateSelectedNames() {
            provinceNameInput.value = provinceSelect.selectedIndex > 0 ? provinceSelect.options[provinceSelect.selectedIndex].text : '';
            districtNameInput.value = districtSelect.selectedIndex > 0 ? districtSelect.options[districtSelect.selectedIndex].text : '';
            wardNameInput.value = wardSelect.selectedIndex > 0 ? wardSelect.options[wardSelect.selectedIndex].text : '';
        }

        function togglePasswordVisibility(button) {
            var targetSelector = button.getAttribute('data-target');
            var targetInput = document.querySelector(targetSelector);
            if (!targetInput) {
                return;
            }

            var icon = button.querySelector('i');
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                targetInput.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }

        function setRuleState(ruleElement, isValid) {
            ruleElement.style.color = isValid ? '#3c763d' : '#a94442';
        }

        function updatePasswordRulesAndMatch() {
            var passwordInput = document.getElementById('cust_password');
            var confirmInput = document.getElementById('cust_re_password');
            var matchHint = document.getElementById('password-match-hint');

            if (!passwordInput || !confirmInput || !matchHint) {
                return;
            }

            var value = passwordInput.value || '';
            var rules = {
                length: value.length >= 8 && value.length <= 16,
                upper: /[A-Z]/.test(value),
                lower: /[a-z]/.test(value),
                digit: /\d/.test(value),
                special: /[!@#$%^&*]/.test(value)
            };

            Object.keys(rules).forEach(function(ruleKey) {
                var el = document.querySelector('#password-rule-list li[data-rule="' + ruleKey + '"]');
                if (el) {
                    setRuleState(el, rules[ruleKey]);
                }
            });

            if (confirmInput.value === '') {
                matchHint.textContent = '';
                return;
            }

            if (value === confirmInput.value) {
                matchHint.textContent = 'Mật khẩu xác nhận đã khớp.';
                matchHint.style.color = '#3c763d';
            } else {
                matchHint.textContent = 'Mật khẩu xác nhận chưa khớp.';
                matchHint.style.color = '#a94442';
            }
        }

        function fetchWithTimeout(url, timeoutMs) {
            return Promise.race([
                fetch(url, { cache: 'no-store' }),
                new Promise(function(_, reject) {
                    setTimeout(function() { reject(new Error('timeout')); }, timeoutMs);
                })
            ]);
        }

        function fetchVnTree() {
            var endpoints = [
                'https://provinces.open-api.vn/api/?depth=3',
                'https://provinces.open-api.vn/api/p/?depth=3'
            ];

            var chain = Promise.reject();
            endpoints.forEach(function(endpoint) {
                chain = chain.catch(function() {
                    return fetchWithTimeout(endpoint, 12000).then(function(response) {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    });
                });
            });

            return chain;
        }

        function getSelectedProvince() {
            return vnTree.find(function(item) {
                return String(item.code) === String(provinceSelect.value);
            });
        }

        function getSelectedDistrict() {
            var province = getSelectedProvince();
            if (!province || !Array.isArray(province.districts)) {
                return null;
            }

            return province.districts.find(function(item) {
                return String(item.code) === String(districtSelect.value);
            }) || null;
        }

        function populateDistricts(selectedValue) {
            var province = getSelectedProvince();
            var districts = province && Array.isArray(province.districts) ? province.districts : [];
            fillSelect(districtSelect, districts, 'Chọn quận/huyện', 'code', 'name', selectedValue || '');
        }

        function populateWards(selectedValue) {
            var district = getSelectedDistrict();
            var wards = district && Array.isArray(district.wards) ? district.wards : [];
            fillSelect(wardSelect, wards, 'Chọn phường/xã', 'code', 'name', selectedValue || '');
        }

        provinceSelect.addEventListener('change', function() {
            selectedProvinceId = provinceSelect.value;
            selectedDistrictId = '';
            selectedWardId = '';
            populateDistricts('');
            populateWards('');
            updateSelectedNames();
        });

        districtSelect.addEventListener('change', function() {
            selectedDistrictId = districtSelect.value;
            selectedWardId = '';
            populateWards('');
            updateSelectedNames();
        });

        wardSelect.addEventListener('change', updateSelectedNames);

        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                togglePasswordVisibility(button);
            });
        });

        var passwordInputEl = document.getElementById('cust_password');
        var confirmInputEl = document.getElementById('cust_re_password');
        if (passwordInputEl && confirmInputEl) {
            passwordInputEl.addEventListener('input', updatePasswordRulesAndMatch);
            confirmInputEl.addEventListener('input', updatePasswordRulesAndMatch);
            updatePasswordRulesAndMatch();
        }

        fetchVnTree()
            .then(function(data) {
                vnTree = Array.isArray(data) ? data : [];
                fillSelect(provinceSelect, vnTree, 'Chọn tỉnh/thành phố', 'code', 'name', selectedProvinceId);
                populateDistricts(selectedDistrictId);
                populateWards(selectedWardId);
                updateSelectedNames();
            })
            .catch(function() {
                resetSelect(provinceSelect, 'Không tải được dữ liệu tỉnh/thành');
                resetSelect(districtSelect, 'Không tải được dữ liệu quận/huyện');
                resetSelect(wardSelect, 'Không tải được dữ liệu phường/xã');
                updateSelectedNames();
            });
    });
</script>

<?php require_once('footer.php'); ?>