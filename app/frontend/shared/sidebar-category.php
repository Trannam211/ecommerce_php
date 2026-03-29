
<h3><?php echo (defined('LANG_VALUE_49') && trim(LANG_VALUE_49) !== 'Categories') ? LANG_VALUE_49 : 'Danh mục'; ?></h3>
    <div id="left" class="span3">

        <ul id="menu-group-1" class="nav menu">
            <?php
                $i=0;
                $statement = $pdo->prepare("SELECT DISTINCT t1.tcat_id, t1.tcat_name
                    FROM tbl_top_category t1
                    JOIN tbl_mid_category t2 ON t2.tcat_id = t1.tcat_id
                    JOIN tbl_end_category t3 ON t3.mcat_id = t2.mcat_id
                    JOIN tbl_product p ON p.ecat_id = t3.ecat_id
                    WHERE t1.show_on_menu=1 AND p.p_is_active=1
                    ORDER BY t1.tcat_id ASC");
                $statement->execute();
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $row) {
                    $i++;
                    ?>
                    <li class="cat-level-1 deeper parent">
                        <a class="" href="product-category.php?id=<?php echo $row['tcat_id']; ?>&type=top-category">
                            <span data-toggle="collapse" data-parent="#menu-group-1" href="#cat-lvl1-id-<?php echo $i; ?>" class="sign"><i class="fa fa-plus"></i></span>
                            <span class="lbl"><?php echo $row['tcat_name']; ?></span>                      
                        </a>
                        <ul class="children nav-child unstyled small collapse" id="cat-lvl1-id-<?php echo $i; ?>">
                            <?php
                            $j=0;
                            $statement1 = $pdo->prepare("SELECT DISTINCT t2.mcat_id, t2.mcat_name
                                FROM tbl_mid_category t2
                                JOIN tbl_end_category t3 ON t3.mcat_id = t2.mcat_id
                                JOIN tbl_product p ON p.ecat_id = t3.ecat_id
                                WHERE t2.tcat_id=? AND p.p_is_active=1
                                ORDER BY t2.mcat_id ASC");
                            $statement1->execute(array($row['tcat_id']));
                            $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result1 as $row1) {
                                $j++;
                                ?>
                                <li class="deeper parent">
                                    <a class="" href="product-category.php?id=<?php echo $row1['mcat_id']; ?>&type=mid-category">
                                        <span data-toggle="collapse" data-parent="#menu-group-1" href="#cat-lvl2-id-<?php echo $i.$j; ?>" class="sign"><i class="fa fa-plus"></i></span>
                                        <span class="lbl lbl1"><?php echo $row1['mcat_name']; ?></span> 
                                    </a>
                                    <ul class="children nav-child unstyled small collapse" id="cat-lvl2-id-<?php echo $i.$j; ?>">
                                        <?php
                                            $k=0;
                                            $statement2 = $pdo->prepare("SELECT DISTINCT t3.ecat_id, t3.ecat_name
                                                FROM tbl_end_category t3
                                                JOIN tbl_product p ON p.ecat_id = t3.ecat_id
                                                WHERE t3.mcat_id=? AND p.p_is_active=1
                                                ORDER BY t3.ecat_id ASC");
                                            $statement2->execute(array($row1['mcat_id']));
                                            $result2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($result2 as $row2) {
                                                $k++;
                                                ?>
                                                <li class="item-<?php echo $i.$j.$k; ?>">
                                                    <a class="" href="product-category.php?id=<?php echo $row2['ecat_id']; ?>&type=end-category">
                                                        <span class="sign"></span>
                                                        <span class="lbl lbl1"><?php echo $row2['ecat_name']; ?></span>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                        ?>
                                    </ul>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </li>
                    <?php
                }
            ?>
        </ul>

    </div>