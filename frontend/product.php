<?php require_once('header.php'); ?>

<?php
$product_flash_success = '';
if(isset($_SESSION['product_flash_success'])) {
    $product_flash_success = trim((string)$_SESSION['product_flash_success']);
    unset($_SESSION['product_flash_success']);
}
?>

<?php
if(!isset($_REQUEST['id'])) {
    safe_redirect('index.php');
} else {
    // Check the id is valid or not
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    if( $total == 0 ) {
        safe_redirect('index.php');
    }
}

foreach($result as $row) {
    $p_name = $row['p_name'];
    $p_old_price = $row['p_old_price'];
    $p_current_price = $row['p_current_price'];
    $p_qty = $row['p_qty'];
    $p_featured_photo = $row['p_featured_photo'];
    $p_description = $row['p_description'];
    $p_short_description = $row['p_short_description'];
    $p_feature = $row['p_feature'];
    $p_condition = $row['p_condition'];
    $p_return_policy = $row['p_return_policy'];
    $p_total_view = $row['p_total_view'];
    $p_is_featured = $row['p_is_featured'];
    $p_is_active = $row['p_is_active'];
    $ecat_id = $row['ecat_id'];
}

// Getting all categories name for breadcrumb
$statement = $pdo->prepare("SELECT
                        t1.ecat_id,
                        t1.ecat_name,
                        t1.mcat_id,

                        t2.mcat_id,
                        t2.mcat_name,
                        t2.tcat_id,

                        t3.tcat_id,
                        t3.tcat_name

                        FROM tbl_end_category t1
                        JOIN tbl_mid_category t2
                        ON t1.mcat_id = t2.mcat_id
                        JOIN tbl_top_category t3
                        ON t2.tcat_id = t3.tcat_id
                        WHERE t1.ecat_id=?");
$statement->execute(array($ecat_id));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $ecat_name = $row['ecat_name'];
    $mcat_id = $row['mcat_id'];
    $mcat_name = $row['mcat_name'];
    $tcat_id = $row['tcat_id'];
    $tcat_name = $row['tcat_name'];
}


$p_total_view = $p_total_view + 1;

$statement = $pdo->prepare("UPDATE tbl_product SET p_total_view=? WHERE p_id=?");
$statement->execute(array($p_total_view,$_REQUEST['id']));


$variant_map = array();
$variant_size_ids = array();
$variant_color_ids = array();

$statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
$statement->execute();
$variant_table_exists = $statement->rowCount() > 0;
if($variant_table_exists) {
    $statement = $pdo->prepare("SELECT * FROM tbl_product_variant WHERE p_id=?");
    $statement->execute(array($_REQUEST['id']));
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach($result as $row) {
        $key = ((int)$row['size_id']).'_'.((int)$row['color_id']);
        $variant_map[$key] = array(
            'qty' => (int)$row['pv_qty']
        );
        $variant_size_ids[(int)$row['size_id']] = (int)$row['size_id'];
        $variant_color_ids[(int)$row['color_id']] = (int)$row['color_id'];
    }
}

$preview_color_id = isset($_GET['color_preview']) ? (int)$_GET['color_preview'] : 0;
$preview_size_id = isset($_GET['size_preview']) ? (int)$_GET['size_preview'] : 0;
$photo_rows_default = array();
$photo_rows_by_color = array();
$featured_color_id = 0;
$featured_matched_pp_id = 0;

$statement = $pdo->prepare("SHOW COLUMNS FROM tbl_product_photo LIKE 'color_id'");
$statement->execute();
$photo_has_color_column = $statement->rowCount() > 0;

$statement = $pdo->prepare("SELECT * FROM tbl_product_photo WHERE p_id=? ORDER BY pp_id ASC");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $row) {
    $photo_color_id = $photo_has_color_column ? (int)$row['color_id'] : 0;

    if($p_featured_photo !== '' && isset($row['photo']) && (string)$row['photo'] === (string)$p_featured_photo && $photo_color_id > 0) {
        $featured_color_id = $photo_color_id;
        $featured_matched_pp_id = isset($row['pp_id']) ? (int)$row['pp_id'] : 0;
    }

    if($photo_color_id > 0) {
        if(!isset($photo_rows_by_color[$photo_color_id])) {
            $photo_rows_by_color[$photo_color_id] = array();
        }
        $photo_rows_by_color[$photo_color_id][] = $row;
    } else {
        $photo_rows_default[] = $row;
    }
}

$all_color_photo_rows = array();
foreach($photo_rows_by_color as $rows_by_color) {
    foreach($rows_by_color as $color_photo_row) {
        $all_color_photo_rows[] = $color_photo_row;
    }
}

// Build a full gallery once; client-side JS will dim/highlight thumbnails by selected color.
$gallery_items = array();
$featured_image_src = '';
if($p_featured_photo !== '') {
    $featured_image_src = '../assets/uploads/'.$p_featured_photo;
}

if($featured_image_src === '' && count($photo_rows_by_color) > 0) {
    foreach($photo_rows_by_color as $cid => $rows_by_color) {
        if(!empty($rows_by_color)) {
            $first_color_row = $rows_by_color[0];
            if(isset($first_color_row['photo']) && trim((string)$first_color_row['photo']) !== '') {
                $featured_image_src = '../assets/uploads/product_photos/'.$first_color_row['photo'];
                $featured_color_id = (int)$cid;
                $featured_matched_pp_id = isset($first_color_row['pp_id']) ? (int)$first_color_row['pp_id'] : 0;
            }
            break;
        }
    }
}

if($featured_image_src === '' && count($photo_rows_default) > 0) {
    $first_default_row = $photo_rows_default[0];
    if(isset($first_default_row['photo']) && trim((string)$first_default_row['photo']) !== '') {
        $featured_image_src = '../assets/uploads/product_photos/'.$first_default_row['photo'];
    }
}

if($featured_image_src !== '') {
    $gallery_items[] = array(
        'src' => $featured_image_src,
        'color_id' => $featured_color_id,
        'kind' => 'featured'
    );
}

foreach($photo_rows_default as $photo_row) {
    $gallery_items[] = array(
        'src' => '../assets/uploads/product_photos/'.$photo_row['photo'],
        'color_id' => 0,
        'kind' => 'default'
    );
}
// Keep stable ordering by pp_id ASC as fetched above.
foreach($photo_rows_by_color as $color_id_key => $rows_by_color) {
    foreach($rows_by_color as $photo_row) {
        $pp_id = isset($photo_row['pp_id']) ? (int)$photo_row['pp_id'] : 0;
        if($featured_matched_pp_id > 0 && $pp_id === $featured_matched_pp_id) {
            continue;
        }
        $gallery_items[] = array(
            'src' => '../assets/uploads/product_photos/'.$photo_row['photo'],
            'color_id' => (int)$color_id_key,
            'kind' => 'color'
        );
    }
}

if($preview_color_id <= 0 && $featured_color_id > 0) {
    $preview_color_id = $featured_color_id;
}

if(count($gallery_items) === 0 && $p_featured_photo !== '') {
    $gallery_items[] = array('src' => '../assets/uploads/'.$p_featured_photo, 'color_id' => 0, 'kind' => 'featured');
}


if(isset($_POST['form_review'])) {
    
    $statement = $pdo->prepare("SELECT * FROM tbl_rating WHERE p_id=? AND cust_id=?");
    $statement->execute(array($_REQUEST['id'],$_SESSION['customer']['cust_id']));
    $total = $statement->rowCount();
    
    if($total) {
        $error_message = 'Bạn đã đánh giá sản phẩm này rồi!'; 
    } else {
        $statement = $pdo->prepare("INSERT INTO tbl_rating (p_id,cust_id,comment,rating) VALUES (?,?,?,?)");
        $statement->execute(array($_REQUEST['id'],$_SESSION['customer']['cust_id'],$_POST['comment'],$_POST['rating']));
        $success_message = 'Gửi đánh giá thành công!';    
    }
    
}

// Getting the average rating for this product
$t_rating = 0;
$statement = $pdo->prepare("SELECT * FROM tbl_rating WHERE p_id=?");
$statement->execute(array($_REQUEST['id']));
$tot_rating = $statement->rowCount();
if($tot_rating == 0) {
    $avg_rating = 0;
} else {
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
    foreach ($result as $row) {
        $t_rating = $t_rating + $row['rating'];
    }
    $avg_rating = $t_rating / $tot_rating;
}

if(isset($_POST['form_add_to_cart'])) {
    if(!isset($_SESSION['customer'])) {
        $redirect_product_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        safe_redirect('login.php?redirect=product&id='.$redirect_product_id);
    }

    $selected_size_id = isset($_POST['size_id']) ? (int)$_POST['size_id'] : 0;
    $selected_color_id = isset($_POST['color_id']) ? (int)$_POST['color_id'] : 0;
    $requested_qty = isset($_POST['p_qty']) ? (int)$_POST['p_qty'] : 1;
    if($requested_qty < 1) {
        $requested_qty = 1;
    }

    $stock_limit = (int)$p_qty;
    $cart_price_value = (int)$p_current_price;
    $variant_key = $selected_size_id.'_'.$selected_color_id;

    if(count($variant_map) > 0) {
        if($selected_size_id <= 0 || $selected_color_id <= 0) {
            $error_message1 = 'Vui lòng chọn đủ kích thước và màu sắc trước khi thêm vào giỏ.';
        }
        if($error_message1 === '' && !isset($variant_map[$variant_key])) {
            $error_message1 = 'Biến thể size-màu bạn chọn hiện không khả dụng.';
        }
        if($error_message1 === '') {
            $stock_limit = (int)$variant_map[$variant_key]['qty'];
        }
    }

    if($error_message1 === '' && $requested_qty > $stock_limit) {
        $error_message1 = 'Xin lỗi! Chỉ còn '.$stock_limit.' sản phẩm trong kho cho biến thể đã chọn.';
    }

    if($error_message1 === ''):
    if(isset($_SESSION['cart_p_id']))
    {
        $arr_cart_p_id = array();
        $arr_cart_size_id = array();
        $arr_cart_color_id = array();
        $arr_cart_p_qty = array();
        $arr_cart_p_current_price = array();

        $i=0;
        foreach($_SESSION['cart_p_id'] as $key => $value) 
        {
            $i++;
            $arr_cart_p_id[$i] = $value;
        }

        $i=0;
        foreach($_SESSION['cart_size_id'] as $key => $value) 
        {
            $i++;
            $arr_cart_size_id[$i] = $value;
        }

        $i=0;
        foreach($_SESSION['cart_color_id'] as $key => $value) 
        {
            $i++;
            $arr_cart_color_id[$i] = $value;
        }


        $added = 0;
        $size_id = $selected_size_id;
        $color_id = $selected_color_id;
        for($i=1;$i<=count($arr_cart_p_id);$i++) {
            if( ($arr_cart_p_id[$i]==$_REQUEST['id']) && ($arr_cart_size_id[$i]==$size_id) && ($arr_cart_color_id[$i]==$color_id) ) {
                $added = 1;
                break;
            }
        }
        if($added == 1) {
           $error_message1 = 'Sản phẩm này đã có trong giỏ hàng.';
        } else {

            $i=0;
            foreach($_SESSION['cart_p_id'] as $key => $res) 
            {
                $i++;
            }
            $new_key = $i+1;

            if($size_id > 0) {
                $statement = $pdo->prepare("SELECT * FROM tbl_size WHERE size_id=?");
                $statement->execute(array($size_id));
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
                foreach ($result as $row) {
                    $size_name = $row['size_name'];
                }
            } else {
                $size_id = 0;
                $size_name = '';
            }
            
            if($color_id > 0) {
                $statement = $pdo->prepare("SELECT * FROM tbl_color WHERE color_id=?");
                $statement->execute(array($color_id));
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
                foreach ($result as $row) {
                    $color_name = $row['color_name'];
                }
            } else {
                $color_id = 0;
                $color_name = '';
            }
          

            $_SESSION['cart_p_id'][$new_key] = $_REQUEST['id'];
            $_SESSION['cart_size_id'][$new_key] = $size_id;
            $_SESSION['cart_size_name'][$new_key] = $size_name;
            $_SESSION['cart_color_id'][$new_key] = $color_id;
            $_SESSION['cart_color_name'][$new_key] = $color_name;
            $_SESSION['cart_p_qty'][$new_key] = $requested_qty;
            $_SESSION['cart_p_current_price'][$new_key] = $cart_price_value;
            $_SESSION['cart_p_name'][$new_key] = $_POST['p_name'];
            $_SESSION['cart_p_featured_photo'][$new_key] = $_POST['p_featured_photo'];

            $success_message1 = 'Đã thêm sản phẩm vào giỏ hàng thành công!';
        }
        
    }
    else
    {

        $size_id = $selected_size_id;
        $color_id = $selected_color_id;

        if($size_id > 0) {

            $statement = $pdo->prepare("SELECT * FROM tbl_size WHERE size_id=?");
            $statement->execute(array($size_id));
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
            foreach ($result as $row) {
                $size_name = $row['size_name'];
            }
        } else {
            $size_id = 0;
            $size_name = '';
        }
        
        if($color_id > 0) {
            $statement = $pdo->prepare("SELECT * FROM tbl_color WHERE color_id=?");
            $statement->execute(array($color_id));
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
            foreach ($result as $row) {
                $color_name = $row['color_name'];
            }
        } else {
            $color_id = 0;
            $color_name = '';
        }
        

        $_SESSION['cart_p_id'][1] = $_REQUEST['id'];
        $_SESSION['cart_size_id'][1] = $size_id;
        $_SESSION['cart_size_name'][1] = $size_name;
        $_SESSION['cart_color_id'][1] = $color_id;
        $_SESSION['cart_color_name'][1] = $color_name;
        $_SESSION['cart_p_qty'][1] = $requested_qty;
        $_SESSION['cart_p_current_price'][1] = $cart_price_value;
        $_SESSION['cart_p_name'][1] = $_POST['p_name'];
        $_SESSION['cart_p_featured_photo'][1] = $_POST['p_featured_photo'];

        $success_message1 = 'Đã thêm sản phẩm vào giỏ hàng thành công!';
    }
    	endif;
}
?>

<?php
if($success_message1 != '') {
    $redirect_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    $_SESSION['product_flash_success'] = $success_message1;
    safe_redirect('product.php?id='.$redirect_id);
}
?>


<div class="page">
    <style>
        /* Product detail: dim thumbnails that don't match selected color */
        #prod-pager .prod-pager-thumb.is-dim {
            opacity: 0.25;
            filter: grayscale(70%);
        }
        #prod-pager .prod-pager-thumb.is-related {
            opacity: 1;
            filter: none;
        }
        /* Product detail: size buttons */
        .product-size-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .product-size-btn {
            border: 1px solid #d0d7de;
            background: #fff;
            padding: 8px 12px;
            border-radius: 0;
            min-width: 52px;
            text-align: center;
            font-weight: 600;
            line-height: 1;
        }
        .product-size-btn.is-active {
            border-color: #0d6efd;
            box-shadow: inset 0 0 0 1px #0d6efd;
        }
        .product-size-btn.is-disabled {
            opacity: 0.45;
            pointer-events: none;
        }
        /* Product detail: make Select2 (color) square corners */
        #product-color-select {
            border-radius: 0;
        }
        .product-detail-page .select2-container--default .select2-selection--single {
            border-radius: 0 !important;
        }
        .product-detail-page .select2-container--default .select2-dropdown {
            border-radius: 0 !important;
        }
        /* Product detail: center quantity input text */
        #product-qty-input {
            text-align: center;
        }
    </style>
	<div class="container">
        <?php if($error_message1 != ''): ?>
            <div class="alert alert-danger" role="alert" style="margin-bottom:15px;">
                <?php echo htmlspecialchars($error_message1, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if($product_flash_success != ''): ?>
            <div class="alert alert-success" role="alert" style="margin-bottom:15px;">
                <?php echo htmlspecialchars($product_flash_success, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

		<div class="row">
			<div class="col-md-12">
                <div class="breadcrumb mb_30">
                    <ul>
                        <li><a href="index.php">Trang chủ</a></li>
                        <li>></li>
                        <li><a href="product-category.php?id=<?php echo $tcat_id; ?>&type=top-category"><?php echo $tcat_name; ?></a></li>
                        <li>></li>
                        <li><a href="product-category.php?id=<?php echo $mcat_id; ?>&type=mid-category"><?php echo $mcat_name; ?></a></li>
                        <li>></li>
                        <li><a href="product-category.php?id=<?php echo $ecat_id; ?>&type=end-category"><?php echo $ecat_name; ?></a></li>
                        <li>></li>
                        <li><?php echo $p_name; ?></li>
                    </ul>
                </div>

                <div class="product product-detail-page">
					<div class="row">
						<div class="col-md-5">
							<ul class="prod-slider">
                                <?php foreach ($gallery_items as $gallery_item): ?>
                                    <li data-color-id="<?php echo (int)$gallery_item['color_id']; ?>" style="background-image: url(<?php echo $gallery_item['src']; ?>);">
                                            <a class="popup" href="<?php echo $gallery_item['src']; ?>"></a>
                                    </li>
                                <?php endforeach; ?>
							</ul>
							<div id="prod-pager">
                                <?php $pager_index = 0; ?>
                                <?php foreach ($gallery_items as $gallery_item): ?>
                                    <a data-slide-index="<?php echo $pager_index; ?>" data-color-id="<?php echo (int)$gallery_item['color_id']; ?>" href="javascript:void(0)"><div class="prod-pager-thumb" style="background-image: url(<?php echo $gallery_item['src']; ?>);"></div></a>
                                    <?php $pager_index++; ?>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="col-md-7">
							<div class="p-title"><h2><?php echo $p_name; ?></h2></div>
							<div class="p-review">
								<div class="rating">
                                    <?php
                                    if($avg_rating == 0) {
                                        echo '';
                                    }
                                    elseif($avg_rating == 1.5) {
                                        echo '
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star-half-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        ';
                                    } 
                                    elseif($avg_rating == 2.5) {
                                        echo '
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star-half-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        ';
                                    }
                                    elseif($avg_rating == 3.5) {
                                        echo '
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star-half-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        ';
                                    }
                                    elseif($avg_rating == 4.5) {
                                        echo '
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star-half-o"></i>
                                        ';
                                    }
                                    else {
                                        for($i=1;$i<=5;$i++) {
                                            ?>
                                            <?php if($i>$avg_rating): ?>
                                                <i class="fa fa-star-o"></i>
                                            <?php else: ?>
                                                <i class="fa fa-star"></i>
                                            <?php endif; ?>
                                            <?php
                                        }
                                    }                                    
                                    ?>
                                </div>
							</div>
							<div class="p-short-des">
								<p>
									<?php echo $p_short_description; ?>
								</p>
							</div>
                            <div class="p-meta-grid">
                                <div><span>Tình trạng kho:</span> <?php echo ($p_qty > 0) ? 'Còn hàng' : 'Hết hàng'; ?></div>
                                <div><span>Danh mục:</span> <?php echo $ecat_name; ?></div>
                                <div><span>Lượt xem:</span> <?php echo $p_total_view; ?></div>
                            </div>
                            <form action="" method="post">
                            <div class="p-quantity">
                                <div class="row">
                                    <?php if(count($variant_color_ids) > 0): ?>
                                    <div class="col-md-12">
                                        <strong>Màu sắc</strong> <br>
                                        <select name="color_id" id="product-color-select" class="form-control select2" style="width:auto;">
                                            <option value="">Chọn màu sắc</option>
                                            <?php
                                            $statement = $pdo->prepare("SELECT * FROM tbl_color");
                                            $statement->execute();
                                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($result as $row) {
                                                $show_color = isset($variant_color_ids[(int)$row['color_id']]);
                                                if($show_color) {
                                                    ?>
                                                    <option value="<?php echo $row['color_id']; ?>" <?php if((int)$row['color_id'] === $preview_color_id){echo 'selected';} ?>><?php echo $row['color_name']; ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>

                                    <?php if(count($variant_size_ids) > 0): ?>
                                    <div class="col-md-12 mb_20">
                                        <strong>Kích thước</strong> <br>
                                        <select name="size_id" id="product-size-select" class="form-control select2" style="width:auto;display:none;">
                                            <option value="">Chọn kích thước</option>
                                            <?php
                                            $statement = $pdo->prepare("SELECT * FROM tbl_size");
                                            $statement->execute();
                                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($result as $row) {
                                                $show_size = isset($variant_size_ids[(int)$row['size_id']]);
                                                if($show_size) {
                                                    ?>
                                                    <option value="<?php echo $row['size_id']; ?>" <?php if((int)$row['size_id'] === $preview_size_id){echo 'selected';} ?>><?php echo $row['size_name']; ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                        <div id="product-size-buttons" class="product-size-buttons" aria-label="Chọn kích thước"></div>
                                    </div>
                                    <?php endif; ?>

                                </div>
                                
                            </div>
							<div class="p-price">
                                <span style="font-size:14px;">Giá bán</span><br>
                                <span id="product-price-view">
                                    <?php if($p_old_price!=''): ?>
                                        <del><?php echo format_price_vnd($p_old_price); ?></del>
                                    <?php endif; ?> 
                                        <?php echo format_price_vnd($p_current_price); ?>
                                </span>
                            </div>
                            <input type="hidden" name="p_current_price" id="product-price-hidden" value="<?php echo $p_current_price; ?>">
                            <input type="hidden" name="p_name" value="<?php echo $p_name; ?>">
                            <input type="hidden" name="p_featured_photo" value="<?php echo $p_featured_photo; ?>">
							<div class="p-quantity">
                                <strong>Số lượng</strong> <br>
								<div id="product-stock-note" style="margin-bottom:6px;color:#666;">Tồn kho: <?php echo (int)$p_qty; ?></div>
								<input type="number" id="product-qty-input" class="input-text qty" step="1" min="1" max="" name="p_qty" value="1" title="Số lượng" size="4" pattern="[0-9]*" inputmode="numeric">
							</div>
							<div class="btn-cart btn-cart1">
                                <input type="submit" id="product-add-to-cart-btn" value="Thêm vào giỏ hàng" name="form_add_to_cart">
							</div>
                            </form>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<!-- Nav tabs -->
                            <ul class="nav nav-tabs product-detail-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" href="#description" aria-controls="description" role="tab" data-bs-toggle="tab">Mô tả chi tiết</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" href="#feature" aria-controls="feature" role="tab" data-bs-toggle="tab">Tính năng nổi bật</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" href="#condition" aria-controls="condition" role="tab" data-bs-toggle="tab">Hướng dẫn chọn size</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" href="#return_policy" aria-controls="return_policy" role="tab" data-bs-toggle="tab">Chính sách đổi trả</a>
                                </li>
                               <!-- <li role="presentation"><a href="#review" aria-controls="review" role="tab" data-toggle="tab">Đánh giá</a></li> -->
							</ul>

							<!-- Tab panes -->
                            <div class="tab-content product-detail-tab-content">
                                <div role="tabpanel" class="tab-pane fade show active" id="description">
									<p>
                                        <?php
                                        if($p_description == '') {
                                            echo 'Sản phẩm hiện chưa có mô tả chi tiết.';
                                        } else {
                                            echo $p_description;
                                        }
                                        ?>
									</p>
								</div>
                                <div role="tabpanel" class="tab-pane fade" id="feature">
                                    <p>
                                        <?php
                                        if($p_feature == '') {
                                            echo 'Sản phẩm hiện chưa có thông tin tính năng nổi bật.';
                                        } else {
                                            echo $p_feature;
                                        }
                                        ?>
                                    </p>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="condition">
                                    <p>
                                        <?php
                                        if($p_condition == '') {
                                            echo 'Sản phẩm hiện chưa có hướng dẫn chọn size.';
                                        } else {
                                            echo $p_condition;
                                        }
                                        ?>
                                    </p>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="return_policy">
                                    <p>
                                        <?php
                                        if($p_return_policy == '') {
                                            echo 'Sản phẩm hiện chưa có chính sách đổi trả.';
                                        } else {
                                            echo $p_return_policy;
                                        }
                                        ?>
                                    </p>
                                </div>
								<div role="tabpanel" class="tab-pane" id="review" style="margin-top: -30px;">

                                    <div class="review-form">
                                        <?php
                                        $statement = $pdo->prepare("SELECT * 
                                                            FROM tbl_rating t1 
                                                            JOIN tbl_customer t2 
                                                            ON t1.cust_id = t2.cust_id 
                                                            WHERE t1.p_id=?");
                                        $statement->execute(array($_REQUEST['id']));
                                        $total = $statement->rowCount();
                                        ?>
                                        <h2>Đánh giá (<?php echo $total; ?>)</h2>
                                        <?php
                                        if($total) {
                                            $j=0;
                                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($result as $row) {
                                                $j++;
                                                ?>
                                                <div class="mb_10"><b><u>Đánh giá <?php echo $j; ?></u></b></div>
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th style="width:170px;">Tên khách hàng</th>
                                                        <td><?php echo $row['cust_name']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Bình luận</th>
                                                        <td><?php echo $row['comment']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Điểm đánh giá</th>
                                                        <td>
                                                            <div class="rating">
                                                                <?php
                                                                for($i=1;$i<=5;$i++) {
                                                                    ?>
                                                                    <?php if($i>$row['rating']): ?>
                                                                        <i class="fa fa-star-o"></i>
                                                                    <?php else: ?>
                                                                        <i class="fa fa-star"></i>
                                                                    <?php endif; ?>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <?php
                                            }
                                        } else {
                                            echo 'Chưa có đánh giá';
                                        }
                                        ?>
                                        
                                        <h2>Viết đánh giá</h2>
                                        <?php
                                        if($error_message != '') {
                                            echo "<script>alert('".$error_message."')</script>";
                                        }
                                        if($success_message != '') {
                                            echo "<script>alert('".$success_message."')</script>";
                                        }
                                        ?>
                                        <?php if(isset($_SESSION['customer'])): ?>

                                            <?php
                                            $statement = $pdo->prepare("SELECT * 
                                                                FROM tbl_rating
                                                                WHERE p_id=? AND cust_id=?");
                                            $statement->execute(array($_REQUEST['id'],$_SESSION['customer']['cust_id']));
                                            $total = $statement->rowCount();
                                            ?>
                                            <?php if($total==0): ?>
                                            <form action="" method="post">
                                            <div class="rating-section">
                                                <input type="radio" name="rating" class="rating" value="1" checked>
                                                <input type="radio" name="rating" class="rating" value="2" checked>
                                                <input type="radio" name="rating" class="rating" value="3" checked>
                                                <input type="radio" name="rating" class="rating" value="4" checked>
                                                <input type="radio" name="rating" class="rating" value="5" checked>
                                            </div>                                            
                                            <div class="form-group">
                                                <textarea name="comment" class="form-control" cols="30" rows="10" placeholder="Nhập nhận xét của bạn (không bắt buộc)" style="height:100px;"></textarea>
                                            </div>
                                            <input type="submit" class="btn btn-default" name="form_review" value="Gửi đánh giá">
                                            </form>
                                            <?php else: ?>
                                                <span style="color:red;">Bạn đã đánh giá sản phẩm này rồi!</span>
                                            <?php endif; ?>


                                        <?php else: ?>
                                            <p class="error">
                                                Bạn cần đăng nhập để đánh giá sản phẩm <br>
                                                <a href="login.php" style="color:red;text-decoration: underline;">Đăng nhập</a>
											</p>
                                        <?php endif; ?>                         
                                    </div>

								</div>
							</div>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>
</div>

<script>
(function() {
    var variantMap = <?php echo json_encode($variant_map, JSON_UNESCAPED_UNICODE); ?>;
    var basePrice = <?php echo (int)$p_current_price; ?>;
    var baseStock = <?php echo (int)$p_qty; ?>;
    var productId = <?php echo (int)$_REQUEST['id']; ?>;
    var selectedPreviewColor = <?php echo (int)$preview_color_id; ?>;
    var selectedPreviewSize = <?php echo (int)$preview_size_id; ?>;

    var sizeSelect = document.getElementById('product-size-select');
    var colorSelect = document.getElementById('product-color-select');
    var priceView = document.getElementById('product-price-view');
    var priceHidden = document.getElementById('product-price-hidden');
    var stockNote = document.getElementById('product-stock-note');
    var qtyInput = document.getElementById('product-qty-input');
    var addToCartBtn = document.getElementById('product-add-to-cart-btn');
    var sizeButtonsWrap = document.getElementById('product-size-buttons');
    var hasVariants = variantMap && Object.keys(variantMap).length > 0;
    var sizeCatalog = [];
	var suppressGalleryJumpOnce = false;

    function formatPriceVnd(value) {
        var amount = parseInt(value, 10);
        if(isNaN(amount)) {
            amount = 0;
        }
        return amount.toLocaleString('vi-VN') + ' đ';
    }

    function collectSizeCatalog() {
        if(!sizeSelect) {
            return;
        }
        for(var i = 0; i < sizeSelect.options.length; i++) {
            var option = sizeSelect.options[i];
            var id = parseInt(option.value || '0', 10);
            if(id > 0) {
                sizeCatalog.push({
                    id: id,
                    name: option.textContent
                });
            }
        }
    }

    function getVariantBySelection(sizeId, colorId) {
        var key = String(sizeId) + '_' + String(colorId);
        return variantMap && variantMap[key] ? variantMap[key] : null;
    }

    function getSizeListForColor(colorId) {
        if(!hasVariants || colorId <= 0) {
            return sizeCatalog.slice();
        }

        var filtered = [];
        for(var i = 0; i < sizeCatalog.length; i++) {
            var item = sizeCatalog[i];
            var variant = getVariantBySelection(item.id, colorId);
            if(variant) {
                filtered.push({
                    id: item.id,
                    name: item.name,
                    qty: parseInt(variant.qty, 10)
                });
            }
        }
        return filtered;
    }

    function renderSizeOptionsByColor(colorId, forcePickFirstInStock) {
        if(!sizeSelect || !hasVariants) {
            return sizeSelect ? parseInt(sizeSelect.value || '0', 10) : 0;
        }

        var currentSizeId = parseInt(sizeSelect.value || '0', 10);
        var sizeList = getSizeListForColor(colorId);

        sizeSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Chọn kích thước';
        sizeSelect.appendChild(placeholder);

        var firstInStockId = 0;
        var firstAnyId = 0;
        var hasCurrent = false;

        for(var i = 0; i < sizeList.length; i++) {
            var item = sizeList[i];
            var option = document.createElement('option');
            option.value = String(item.id);

            var qty = typeof item.qty === 'number' && !isNaN(item.qty) ? item.qty : null;
            if(qty !== null && qty <= 0) {
                option.textContent = item.name + ' - Hết hàng';
            } else {
                option.textContent = item.name;
            }

            sizeSelect.appendChild(option);

            if(firstAnyId === 0) {
                firstAnyId = item.id;
            }
            if(firstInStockId === 0 && qty !== null && qty > 0) {
                firstInStockId = item.id;
            }
            if(item.id === currentSizeId) {
                hasCurrent = true;
            }
        }

        if(sizeList.length === 0) {
            return 0;
        }

        var nextSizeId = 0;
        if(forcePickFirstInStock) {
            nextSizeId = firstInStockId || firstAnyId;
        } else if(hasCurrent && currentSizeId > 0) {
            nextSizeId = currentSizeId;
        } else if(selectedPreviewSize > 0) {
            for(var j = 0; j < sizeList.length; j++) {
                if(sizeList[j].id === selectedPreviewSize) {
                    nextSizeId = selectedPreviewSize;
                    break;
                }
            }
            if(nextSizeId === 0) {
                nextSizeId = firstInStockId || firstAnyId;
            }
        } else {
            nextSizeId = firstInStockId || firstAnyId;
        }

        sizeSelect.value = nextSizeId > 0 ? String(nextSizeId) : '';
        return nextSizeId;
    }

    function syncSelect2Display() {
        if(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            if(sizeSelect) {
                window.jQuery(sizeSelect).trigger('change.select2');
            }
            if(colorSelect) {
                window.jQuery(colorSelect).trigger('change.select2');
            }
        }
    }

    function normalizeSizeLabel(text) {
        return String(text || '').replace(/\s*-\s*Hết hàng\s*$/i, '').trim();
    }

    function optionIsOutOfStock(optionEl) {
        if(!optionEl) {
            return false;
        }
        var txt = String(optionEl.textContent || optionEl.innerText || '');
        return /Hết\s*hàng/i.test(txt);
    }

    function renderSizeButtonsFromSelect() {
        if(!sizeButtonsWrap || !sizeSelect) {
            return;
        }

        sizeButtonsWrap.innerHTML = '';
        var current = String(sizeSelect.value || '');
        var options = sizeSelect.querySelectorAll('option');
        for(var i = 0; i < options.length; i++) {
            var opt = options[i];
            var value = String(opt.value || '');
            if(!value) {
                continue; // placeholder
            }

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'product-size-btn';
            btn.setAttribute('data-size-id', value);
            btn.textContent = normalizeSizeLabel(opt.textContent);

            if(value === current) {
                btn.classList.add('is-active');
            }

            if(optionIsOutOfStock(opt)) {
                btn.classList.add('is-disabled');
                btn.setAttribute('aria-disabled', 'true');
            }

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var next = this.getAttribute('data-size-id') || '';
                if(!next) {
                    return;
                }
                sizeSelect.value = next;
                // Fire change so any listeners stay consistent.
                sizeSelect.dispatchEvent(new Event('change', { bubbles: true }));
            });

            sizeButtonsWrap.appendChild(btn);
        }
    }

    function applyColorToGallery(colorId, suppressJump) {
        var pager = document.getElementById('prod-pager');
        if(!pager) {
            return;
        }

        var links = pager.querySelectorAll('a[data-slide-index]');
        if(!links || links.length === 0) {
            return;
        }

        var hasRelated = false;
        if(colorId > 0) {
            for(var i = 0; i < links.length; i++) {
                var linkColor = parseInt(links[i].getAttribute('data-color-id') || '0', 10);
                if(linkColor === colorId) {
                    hasRelated = true;
                    break;
                }
            }
        }

        for(var j = 0; j < links.length; j++) {
            var cid = parseInt(links[j].getAttribute('data-color-id') || '0', 10);
            var thumb = links[j].querySelector('.prod-pager-thumb');
            if(!thumb) {
                continue;
            }
            if(colorId > 0 && hasRelated) {
                if(cid === colorId) {
                    thumb.classList.remove('is-dim');
                    thumb.classList.add('is-related');
                } else {
                    thumb.classList.add('is-dim');
                    thumb.classList.remove('is-related');
                }
            } else {
                thumb.classList.remove('is-dim');
                thumb.classList.remove('is-related');
            }
        }

        // Jump to the first related image (no reload) if available.
        if(!suppressJump && colorId > 0 && hasRelated) {
            for(var k = 0; k < links.length; k++) {
                var c = parseInt(links[k].getAttribute('data-color-id') || '0', 10);
                if(c === colorId) {
                    links[k].dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
                    break;
                }
            }
        }
    }

    function syncColorFromThumbnailClick() {
        var pager = document.getElementById('prod-pager');
        if(!pager || !colorSelect) {
            return;
        }

        pager.addEventListener('click', function(e) {
            var el = e.target;
            while(el && el !== pager) {
                if(el.tagName && el.tagName.toLowerCase() === 'a' && el.hasAttribute('data-slide-index')) {
                    break;
                }
                el = el.parentElement;
            }
            if(!el || el === pager) {
                return;
            }

            var thumbColorId = parseInt(el.getAttribute('data-color-id') || '0', 10);
            var currentColorId = parseInt(colorSelect.value || '0', 10);

            // Only change if the select has that option.
            var nextValue = '';
            if(thumbColorId > 0) {
                nextValue = String(thumbColorId);
                if(!colorSelect.querySelector('option[value="' + nextValue + '"]')) {
                    return;
                }
            } else {
                // Featured/default image -> clear color selection
                nextValue = '';
            }

            if((thumbColorId > 0 && thumbColorId === currentColorId) || (thumbColorId === 0 && !colorSelect.value)) {
                return;
            }

            suppressGalleryJumpOnce = true;
            colorSelect.value = nextValue;
            colorSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }, false);
    }

    function updateVariantDisplay() {
        var sizeId = sizeSelect ? parseInt(sizeSelect.value || '0', 10) : 0;
        var colorId = colorSelect ? parseInt(colorSelect.value || '0', 10) : 0;
        var currentPrice = basePrice;
        var currentStock = baseStock;
        var variant = null;

        if(hasVariants && sizeId > 0 && colorId > 0) {
            variant = getVariantBySelection(sizeId, colorId);
            if(variant) {
                currentStock = parseInt(variant.qty, 10);
            } else {
                currentPrice = basePrice;
                currentStock = 0;
            }
        }

        if(priceHidden) {
            priceHidden.value = currentPrice;
        }
        if(priceView && hasVariants) {
            priceView.innerHTML = formatPriceVnd(currentPrice);
        }
        if(stockNote) {
            if(hasVariants && (!colorId || !sizeId || !variant)) {
                stockNote.textContent = 'Vui lòng chọn màu và kích thước';
            } else {
                var qty = isNaN(currentStock) ? 0 : currentStock;
                stockNote.textContent = qty > 0 ? ('Tồn kho: ' + qty) : 'Hết hàng';
            }
        }

        if(qtyInput) {
            var safeStock = isNaN(currentStock) ? 0 : currentStock;
            if(hasVariants && (!variant || !colorId || !sizeId)) {
                qtyInput.max = '';
            } else {
                qtyInput.max = safeStock > 0 ? String(safeStock) : '1';
                if(parseInt(qtyInput.value || '1', 10) > safeStock && safeStock > 0) {
                    qtyInput.value = String(safeStock);
                }
            }
        }

        if(addToCartBtn) {
            addToCartBtn.title = '';
        }
    }

    if(sizeSelect) {
        sizeSelect.addEventListener('change', function() {
            updateVariantDisplay();
            renderSizeButtonsFromSelect();
        });
    }

    if(colorSelect) {
        colorSelect.addEventListener('change', function() {
            var cid = parseInt(colorSelect.value || '0', 10);
            var pickedSizeId = renderSizeOptionsByColor(cid, true);
            syncSelect2Display();
            updateVariantDisplay();

            // Size options may change by color.
            renderSizeButtonsFromSelect();

			var suppressJump = suppressGalleryJumpOnce;
			suppressGalleryJumpOnce = false;
            applyColorToGallery(cid, suppressJump);
        });
    }

    collectSizeCatalog();
    if(colorSelect && hasVariants) {
        renderSizeOptionsByColor(parseInt(colorSelect.value || '0', 10), false);
        syncSelect2Display();
    }
    updateVariantDisplay();

    renderSizeButtonsFromSelect();
	syncColorFromThumbnailClick();

    // Initial dim/highlight state for thumbnails.
    if(colorSelect) {
		applyColorToGallery(parseInt(colorSelect.value || '0', 10), true);
    }
})();
</script>

<div class="product bg-gray pt_70 pb_70">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="headline">
                    <h2>Sản phẩm liên quan</h2>
                    <h3>Gợi ý thêm sản phẩm phù hợp với lựa chọn của bạn</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">

                <div class="product-carousel">

                    <?php
                    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE ecat_id=? AND p_id!=?");
                    $statement->execute(array($ecat_id,$_REQUEST['id']));
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($result as $row) {
                        ?>
                        <div class="item">
                            <div class="thumb">
                                <a class="product-link" href="product.php?id=<?php echo $row['p_id']; ?>">
                                    <div class="photo" style="background-image:url(../assets/uploads/<?php echo $row['p_featured_photo']; ?>);"></div>
                                    <div class="overlay"></div>
                                </a>
                            </div>
                            <div class="text">
                                <h3><a href="product.php?id=<?php echo $row['p_id']; ?>"><?php echo $row['p_name']; ?></a></h3>
                                <h4>
                                    <?php echo format_price_vnd($row['p_current_price']); ?>
                                    <?php if($row['p_old_price'] != ''): ?>
                                    <del>
                                        <?php echo format_price_vnd($row['p_old_price']); ?>
                                    </del>
                                    <?php endif; ?>
                                </h4>
                                <div class="rating">
                                    <?php
                                    $t_rating = 0;
                                    $statement1 = $pdo->prepare("SELECT * FROM tbl_rating WHERE p_id=?");
                                    $statement1->execute(array($row['p_id']));
                                    $tot_rating = $statement1->rowCount();
                                    if($tot_rating == 0) {
                                        $avg_rating = 0;
                                    } else {
                                        $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($result1 as $row1) {
                                            $t_rating = $t_rating + $row1['rating'];
                                        }
                                        $avg_rating = $t_rating / $tot_rating;
                                    }
                                    ?>
                                    <?php
                                    if($avg_rating == 0) {
                                        echo '';
                                    }
                                    elseif($avg_rating == 1.5) {
                                        echo '
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star-half-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        ';
                                    } 
                                    elseif($avg_rating == 2.5) {
                                        echo '
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star-half-o"></i>
                                            <i class="fa fa-star-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        ';
                                    }
                                    elseif($avg_rating == 3.5) {
                                        echo '
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star-half-o"></i>
                                            <i class="fa fa-star-o"></i>
                                        ';
                                    }
                                    elseif($avg_rating == 4.5) {
                                        echo '
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star-half-o"></i>
                                        ';
                                    }
                                    else {
                                        for($i=1;$i<=5;$i++) {
                                            ?>
                                            <?php if($i>$avg_rating): ?>
                                                <i class="fa fa-star-o"></i>
                                            <?php else: ?>
                                                <i class="fa fa-star"></i>
                                            <?php endif; ?>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <p><a href="product.php?id=<?php echo $row['p_id']; ?>">Xem chi tiết</a></p>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
