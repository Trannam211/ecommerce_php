<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $banner_search = $row['banner_search'];
}

$search_text_raw = isset($_GET['search_text']) ? trim(strip_tags($_GET['search_text'])) : '';
$ecat_id_filter = isset($_GET['ecat_id']) ? (int)$_GET['ecat_id'] : 0;
$price_min_filter = isset($_GET['price_min']) ? preg_replace('/[^0-9]/', '', (string)$_GET['price_min']) : '';
$price_max_filter = isset($_GET['price_max']) ? preg_replace('/[^0-9]/', '', (string)$_GET['price_max']) : '';

if($price_min_filter !== '' && $price_max_filter !== '' && (int)$price_min_filter > (int)$price_max_filter) {
    $tmp_price = $price_min_filter;
    $price_min_filter = $price_max_filter;
    $price_max_filter = $tmp_price;
}

$has_any_filter = ($search_text_raw !== '' || $ecat_id_filter > 0 || $price_min_filter !== '' || $price_max_filter !== '');
if(!$has_any_filter) {
    safe_redirect('index.php');
}

$where_parts = array('p_is_active=1');
$query_params = array();

if($search_text_raw !== '') {
    $where_parts[] = 'p_name LIKE ?';
    $query_params[] = '%'.$search_text_raw.'%';
}

if($ecat_id_filter > 0) {
    $where_parts[] = 'ecat_id=?';
    $query_params[] = $ecat_id_filter;
}

if($price_min_filter !== '') {
    $where_parts[] = 'p_current_price >= ?';
    $query_params[] = (int)$price_min_filter;
}

if($price_max_filter !== '') {
    $where_parts[] = 'p_current_price <= ?';
    $query_params[] = (int)$price_max_filter;
}

$where_sql = implode(' AND ', $where_parts);

/* ===================== Pagination Code Starts ================== */
$adjacents = 5;
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) {
    $page = 1;
}
$start = ($page - 1) * $limit;

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product WHERE {$where_sql}");
$statement->execute($query_params);
$total_pages = (int)$statement->fetchColumn();

$filter_query = array();
if($search_text_raw !== '') {
    $filter_query['search_text'] = $search_text_raw;
}
if($ecat_id_filter > 0) {
    $filter_query['ecat_id'] = $ecat_id_filter;
}
if($price_min_filter !== '') {
    $filter_query['price_min'] = $price_min_filter;
}
if($price_max_filter !== '') {
    $filter_query['price_max'] = $price_max_filter;
}

$targetpage = 'search-result.php';
if(!empty($filter_query)) {
    $targetpage .= '?'.http_build_query($filter_query);
}

$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE {$where_sql} ORDER BY p_id DESC LIMIT {$start}, {$limit}");
$statement->execute($query_params);
$products = $statement->fetchAll(PDO::FETCH_ASSOC);

$lastpage = (int)ceil($total_pages/$limit);
if($lastpage < 1) {
    $lastpage = 1;
}
$prev = $page - 1;
$next = $page + 1;
$lpm1 = $lastpage - 1;
$pagination = "";
if($lastpage > 1)
{
    $pagination .= "<div class=\"pagination\">";
    if ($page > 1)
        $pagination.= "<a href=\"$targetpage&page=$prev\">&#171; Trang trước</a>";
    else
        $pagination.= "<span class=\"disabled\">&#171; Trang trước</span>";
    if ($lastpage < 7 + ($adjacents * 2))
    {
        for ($counter = 1; $counter <= $lastpage; $counter++)
        {
            if ($counter == $page)
                $pagination.= "<span class=\"current\">$counter</span>";
            else
                $pagination.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
        }
    }
    elseif($lastpage > 5 + ($adjacents * 2))
    {
        if($page < 1 + ($adjacents * 2))
        {
            for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
            {
                if ($counter == $page)
                    $pagination.= "<span class=\"current\">$counter</span>";
                else
                    $pagination.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
            }
            $pagination.= "...";
            $pagination.= "<a href=\"$targetpage&page=$lpm1\">$lpm1</a>";
            $pagination.= "<a href=\"$targetpage&page=$lastpage\">$lastpage</a>";
        }
        elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
        {
            $pagination.= "<a href=\"$targetpage&page=1\">1</a>";
            $pagination.= "<a href=\"$targetpage&page=2\">2</a>";
            $pagination.= "...";
            for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
            {
                if ($counter == $page)
                    $pagination.= "<span class=\"current\">$counter</span>";
                else
                    $pagination.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
            }
            $pagination.= "...";
            $pagination.= "<a href=\"$targetpage&page=$lpm1\">$lpm1</a>";
            $pagination.= "<a href=\"$targetpage&page=$lastpage\">$lastpage</a>";
        }
        else
        {
            $pagination.= "<a href=\"$targetpage&page=1\">1</a>";
            $pagination.= "<a href=\"$targetpage&page=2\">2</a>";
            $pagination.= "...";
            for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
            {
                if ($counter == $page)
                    $pagination.= "<span class=\"current\">$counter</span>";
                else
                    $pagination.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
            }
        }
    }
    if ($page < $counter - 1)
        $pagination.= "<a href=\"$targetpage&page=$next\">Trang sau &#187;</a>";
    else
        $pagination.= "<span class=\"disabled\">Trang sau &#187;</span>";
    $pagination.= "</div>\n";
}
/* ===================== Pagination Code Ends ================== */

$page_title = 'Kết quả tìm kiếm nâng cao';
if($search_text_raw !== '') {
    $page_title = 'Kết quả tìm kiếm cho: '.$search_text_raw;
}
?>

<div class="page-banner" style="background-image: url(../assets/uploads/<?php echo $banner_search; ?>);">
    <div class="overlay"></div>
    <div class="inner">
        <h1><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>
</div>

<style>
.search-advanced-box {
    border: 1px solid #e4e8f0;
    background: #fff;
    padding: 14px;
    margin-bottom: 16px;
}

.search-advanced-box .btn {
    border-radius: 0;
}
</style>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <form action="search-result.php" method="get" class="search-advanced-box">
                    <?php $csrf->echoInputField(); ?>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Tên sản phẩm</label>
                            <input type="text" name="search_text" class="form-control" value="<?php echo htmlspecialchars($search_text_raw, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nhập tên sản phẩm">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Danh mục cấp 3</label>
                            <select name="ecat_id" class="form-control select2">
                                <option value="">Tất cả danh mục</option>
                                <?php
                                $statement = $pdo->prepare("SELECT ecat_id, ecat_name FROM tbl_end_category ORDER BY ecat_name ASC");
                                $statement->execute();
                                $end_categories = $statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach($end_categories as $end_category_row):
                                ?>
                                <option value="<?php echo (int)$end_category_row['ecat_id']; ?>" <?php echo ((int)$end_category_row['ecat_id'] === $ecat_id_filter) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($end_category_row['ecat_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Giá từ</label>
                            <input type="text" name="price_min" class="form-control" inputmode="numeric" value="<?php echo htmlspecialchars($price_min_filter, ENT_QUOTES, 'UTF-8'); ?>" placeholder="0">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Giá đến</label>
                            <input type="text" name="price_max" class="form-control" inputmode="numeric" value="<?php echo htmlspecialchars($price_max_filter, ENT_QUOTES, 'UTF-8'); ?>" placeholder="1000000">
                        </div>
                        <div class="col-md-1 form-group" style="padding-top:24px;">
                            <button type="submit" class="btn btn-primary btn-block">Lọc</button>
                        </div>
                    </div>
                </form>

                <div class="product product-cat">
                    <div class="row">
                        <?php
                        if($total_pages <= 0):
                            echo '<span style="color:red;font-size:18px;">Không tìm thấy kết quả phù hợp</span>';
                        else:
                            foreach ($products as $row) {
                                ?>
                                <div class="col-md-3 item item-search-result">
                                    <div class="inner">
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
                                            <?php if($row['p_qty'] == 0): ?>
                                                <div class="out-of-stock">
                                                    <div class="inner">
                                                        Hết hàng
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <p><a href="product.php?id=<?php echo $row['p_id']; ?>"><i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng</a></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="clear"></div>
                            <div class="pagination">
                                <?php echo $pagination; ?>
                            </div>
                            <?php
                        endif;
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>