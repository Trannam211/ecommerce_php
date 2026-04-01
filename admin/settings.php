<?php require_once('header.php'); ?>

<?php
//Change Logo
if(isset($_POST['form1'])) {
    $valid = 1;

    $path = $_FILES['photo_logo']['name'];
    $path_tmp = $_FILES['photo_logo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $logo = $row['logo'];
            unlink('../assets/uploads/'.$logo);
        }

        // updating the data
        $final_name = 'logo'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET logo=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Logo is updated successfully.';
        
    }
}
// Change Biểu tượng trang
if(isset($_POST['form2'])) {
    $valid = 1;

    $path = $_FILES['photo_favicon']['name'];
    $path_tmp = $_FILES['photo_favicon']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $favicon = $row['favicon'];
            unlink('../assets/uploads/'.$favicon);
        }

        // updating the data
        $final_name = 'favicon'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET favicon=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Biểu tượng trang is updated successfully.';
        
    }
}
//Chân trang và liên hệ us page
if(isset($_POST['form3'])) {
    
    // updating the database
    $statement = $pdo->prepare("UPDATE tbl_settings SET footer_copyright=?, contact_address=?, contact_email=?, contact_phone=?, contact_map_iframe=? WHERE id=1");
    $statement->execute(array($_POST['footer_copyright'],$_POST['contact_address'],$_POST['contact_email'],$_POST['contact_phone'],$_POST['contact_map_iframe']));

    $success_message = 'General content settings is updated successfully.';
    
}
//Email Settings
if(isset($_POST['form4'])) {
    // updating the database
    $statement = $pdo->prepare("UPDATE tbl_settings SET receive_email=?, receive_email_subject=?,receive_email_thank_you_message=?, forget_password_message=? WHERE id=1");
    $statement->execute(array($_POST['receive_email'],$_POST['receive_email_subject'],$_POST['receive_email_thank_you_message'],$_POST['forget_password_message']));

    $success_message = 'Contact form settings information is updated successfully.';
}

//Can not finish this section, leave it
if(isset($_POST['form5'])) {
    // updating the database
    $statement = $pdo->prepare("UPDATE tbl_settings SET total_featured_product_home=?, total_latest_product_home=?, total_popular_product_home=? WHERE id=1");
    $statement->execute(array($_POST['total_featured_product_home'],$_POST['total_latest_product_home'],$_POST['total_popular_product_home']));

    $success_message = 'Sidebar settings is updated successfully.';
}


if(isset($_POST['form6_0'])) {
    // updating the database
    $statement = $pdo->prepare("UPDATE tbl_settings SET home_service_on_off=?, home_welcome_on_off=?, home_featured_product_on_off=?, home_latest_product_on_off=?, home_popular_product_on_off=? WHERE id=1");
    $statement->execute(array($_POST['home_service_on_off'],$_POST['home_welcome_on_off'],$_POST['home_featured_product_on_off'],$_POST['home_latest_product_on_off'],$_POST['home_popular_product_on_off']));

    $success_message = 'Section On-Off Settings is updated successfully.';
}


if(isset($_POST['form6'])) {
    // updating the database
    $statement = $pdo->prepare("UPDATE tbl_settings SET meta_title_home=?, meta_keyword_home=?, meta_description_home=? WHERE id=1");
    $statement->execute(array($_POST['meta_title_home'],$_POST['meta_keyword_home'],$_POST['meta_description_home']));

    $success_message = 'Home Meta settings is updated successfully.';
}

if(isset($_POST['form6_7'])) {

    $valid = 1;

    if(empty($_POST['cta_title'])) {
        $valid = 0;
        $error_message .= 'Call to Action Title can not be empty<br>';
    }

    if(empty($_POST['cta_content'])) {
        $valid = 0;
        $error_message .= 'Call to Action Content can not be empty<br>';
    }

    if(empty($_POST['cta_read_more_text'])) {
        $valid = 0;
        $error_message .= 'Call to Action Read More Text can not be empty<br>';
    }

    if(empty($_POST['cta_read_more_url'])) {
        $valid = 0;
        $error_message .= 'Call to Action Read More URL can not be empty<br>';
    }

    $path = $_FILES['cta_photo']['name'];
    $path_tmp = $_FILES['cta_photo']['tmp_name'];

    if($path != '') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {

        if($path != '') {
            // removing the existing photo
            $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
            foreach ($result as $row) {
                $cta_photo = $row['cta_photo'];
                unlink('../assets/uploads/'.$cta_photo);
            }

            // updating the data
            $final_name = 'cta'.'.'.$ext;
            move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

            // updating the database
            $statement = $pdo->prepare("UPDATE tbl_settings SET cta_title=?,cta_content=?,cta_read_more_text=?,cta_read_more_url=?,cta_photo=? WHERE id=1");
            $statement->execute(array($_POST['cta_title'],$_POST['cta_content'],$_POST['cta_read_more_text'],$_POST['cta_read_more_url'],$final_name));
        } else {
            // updating the database
            $statement = $pdo->prepare("UPDATE tbl_settings SET cta_title=?,cta_content=?,cta_read_more_text=?,cta_read_more_url=? WHERE id=1");
            $statement->execute(array($_POST['cta_title'],$_POST['cta_content'],$_POST['cta_read_more_text'],$_POST['cta_read_more_url']));
        }

        $success_message = 'Call to Action Data is updated successfully.';
        
    }
}

if(isset($_POST['form6_4'])) {

    $valid = 1;

    if(empty($_POST['featured_product_title'])) {
        $valid = 0;
        $error_message .= 'Featured Product Title can not be empty<br>';
    }

    if(empty($_POST['featured_product_subtitle'])) {
        $valid = 0;
        $error_message .= 'Featured Product SubTitle can not be empty<br>';
    }

    if($valid == 1) {

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET featured_product_title=?,featured_product_subtitle=? WHERE id=1");
        $statement->execute(array($_POST['featured_product_title'],$_POST['featured_product_subtitle']));

        $success_message = 'Featured Product Data is updated successfully.';
        
    }
}

if(isset($_POST['form6_5'])) {

    $valid = 1;

    if(empty($_POST['latest_product_title'])) {
        $valid = 0;
        $error_message .= 'Latest Product Title can not be empty<br>';
    }

    if(empty($_POST['latest_product_subtitle'])) {
        $valid = 0;
        $error_message .= 'Latest Product SubTitle can not be empty<br>';
    }

    if($valid == 1) {

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET latest_product_title=?,latest_product_subtitle=? WHERE id=1");
        $statement->execute(array($_POST['latest_product_title'],$_POST['latest_product_subtitle']));

        $success_message = 'Latest Product Data is updated successfully.';
        
    }
}

if(isset($_POST['form6_6'])) {

    $valid = 1;

    if(empty($_POST['popular_product_title'])) {
        $valid = 0;
        $error_message .= 'Popular Product Title can not be empty<br>';
    }

    if(empty($_POST['popular_product_subtitle'])) {
        $valid = 0;
        $error_message .= 'Popular Product SubTitle can not be empty<br>';
    }

    if($valid == 1) {

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET popular_product_title=?,popular_product_subtitle=? WHERE id=1");
        $statement->execute(array($_POST['popular_product_title'],$_POST['popular_product_subtitle']));

        $success_message = 'Popular Product Data is updated successfully.';
        
    }
}
/*
if(isset($_POST['form6_1'])) {

    $valid = 1;

    if(empty($_POST['testimonial_title'])) {
        $valid = 0;
        $error_message .= 'Testimonial Title can not be empty<br>';
    }

    if(empty($_POST['testimonial_subtitle'])) {
        $valid = 0;
        $error_message .= 'Testimonial SubTitle can not be empty<br>';
    }

    $path = $_FILES['testimonial_photo']['name'];
    $path_tmp = $_FILES['testimonial_photo']['tmp_name'];

    if($path != '') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {


        if($path != '') {
            // removing the existing photo
            $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
            foreach ($result as $row) {
                $testimonial_photo = $row['testimonial_photo'];
                unlink('../assets/uploads/'.$testimonial_photo);
            }

            // updating the data
            $final_name = 'testimonial'.'.'.$ext;
            move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

            // updating the database
            $statement = $pdo->prepare("UPDATE tbl_settings SET testimonial_title=?,testimonial_subtitle=?, testimonial_photo=? WHERE id=1");
            $statement->execute(array($_POST['testimonial_title'],$_POST['testimonial_subtitle'],$final_name));
        } else {
            // updating the database
            $statement = $pdo->prepare("UPDATE tbl_settings SET testimonial_title=?,testimonial_subtitle=? WHERE id=1");
            $statement->execute(array($_POST['testimonial_title'],$_POST['testimonial_subtitle']));
        }

        $success_message = 'Testimonial Data is updated successfully.';
        
    }
}


if(isset($_POST['form6_2'])) {

    $valid = 1;

    if(empty($_POST['blog_title'])) {
        $valid = 0;
        $error_message .= 'Blog Title can not be empty<br>';
    }

    if(empty($_POST['blog_subtitle'])) {
        $valid = 0;
        $error_message .= 'Blog SubTitle can not be empty<br>';
    }

    if($valid == 1) {

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET blog_title=?,blog_subtitle=? WHERE id=1");
        $statement->execute(array($_POST['blog_title'],$_POST['blog_subtitle']));

        $success_message = 'Blog Data is updated successfully.';
        
    }
}
*/

if(isset($_POST['form6_3'])) {

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET newsletter_text=? WHERE id=1");
        $statement->execute(array($_POST['newsletter_text']));
        
        $success_message = 'Newsletter Text is updated successfully.';
 
}

if(isset($_POST['form7_1'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_login = $row['banner_login'];
            unlink('../assets/uploads/'.$banner_login);
        }

        // updating the data
        $final_name = 'banner_login'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_login=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Đăng nhập Page Banner is updated successfully.';
        
    }
}

if(isset($_POST['form7_2'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_registration = $row['banner_registration'];
            unlink('../assets/uploads/'.$banner_registration);
        }

        // updating the data
        $final_name = 'banner_registration'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_registration=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Registration Page Banner is updated successfully.';
        
    }
}

if(isset($_POST['form7_3'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_forget_password = $row['banner_forget_password'];
            unlink('../assets/uploads/'.$banner_forget_password);
        }

        // updating the data
        $final_name = 'banner_forget_password'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_forget_password=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Forget Mật khẩu Page Banner is updated successfully.';
        
    }
}

if(isset($_POST['form7_4'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_reset_password = $row['banner_reset_password'];
            unlink('../assets/uploads/'.$banner_reset_password);
        }

        // updating the data
        $final_name = 'banner_reset_password'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_reset_password=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Reset Mật khẩu Page Banner is updated successfully.';
        
    }
}


if(isset($_POST['form7_6'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_search = $row['banner_search'];
            unlink('../assets/uploads/'.$banner_search);
        }

        // updating the data
        $final_name = 'banner_search'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_search=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Tìm kiếm Page Banner is updated successfully.';
        
    }
}

if(isset($_POST['form7_7'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_cart = $row['banner_cart'];
            unlink('../assets/uploads/'.$banner_cart);
        }

        // updating the data
        $final_name = 'banner_cart'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_cart=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Cart Page Banner is updated successfully.';
        
    }
}

if(isset($_POST['form7_8'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_checkout = $row['banner_checkout'];
            unlink('../assets/uploads/'.$banner_checkout);
        }

        // updating the data
        $final_name = 'banner_checkout'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_checkout=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Checkout Page Banner is updated successfully.';
        
    }
}

if(isset($_POST['form7_9'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_product_category = $row['banner_product_category'];
            unlink('../assets/uploads/'.$banner_product_category);
        }

        // updating the data
        $final_name = 'banner_product_category'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_product_category=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Product Category Page Banner is updated successfully.';
        
    }
}

if(isset($_POST['form7_10'])) {
    $valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path == '') {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }
/*
    if($valid == 1) {
        // removing the existing photo
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $banner_blog = $row['banner_blog'];
            unlink('../assets/uploads/'.$banner_blog);
        }

        // updating the data
        $final_name = 'banner_blog'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
        $statement = $pdo->prepare("UPDATE tbl_settings SET banner_blog=? WHERE id=1");
        $statement->execute(array($final_name));

        $success_message = 'Blog Page Banner is updated successfully.';
        
    } */
}

if(isset($_POST['form9'])) {
    $cod_on_off = isset($_POST['cod_on_off']) ? 1 : 0;

    try {
        $statement = $pdo->prepare("UPDATE tbl_settings SET cod_on_off=? WHERE id=1");
        $statement->execute(array($cod_on_off));
        $success_message = 'Đã lưu thay đổi thành công.';
    } catch(PDOException $e) {
        // Backward compatible: auto-add column if old database doesn't have it.
        $message = $e->getMessage();
        if(stripos($message, 'Unknown column') !== false || stripos($message, '42S22') !== false) {
            try {
                $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN cod_on_off TINYINT(1) NOT NULL DEFAULT 1");
                $statement = $pdo->prepare("UPDATE tbl_settings SET cod_on_off=? WHERE id=1");
                $statement->execute(array($cod_on_off));
                $success_message = 'Đã lưu thay đổi thành công.';
            } catch(PDOException $e2) {
                $error_message .= 'Không thể lưu cài đặt thanh toán. Vui lòng thử lại.<br>';
            }
        } else {
            $error_message .= 'Không thể lưu cài đặt thanh toán. Vui lòng thử lại.<br>';
        }
    }
}

/*
if(isset($_POST['form11'])) {
    // updating the database
    $statement = $pdo->prepare("UPDATE tbl_settings 
    						SET 
    						ads_above_welcome_on_off=?, 
    						ads_above_featured_product_on_off=?, 
    						ads_above_latest_product_on_off=?, 
    						ads_above_popular_product_on_off=?, 
    						ads_above_testimonial_on_off=?, 
    						ads_category_sidebar_on_off=? 

    						WHERE id=1");
    $statement->execute(array(
    						$_POST['ads_above_welcome_on_off'],
    						$_POST['ads_above_featured_product_on_off'],
    						$_POST['ads_above_latest_product_on_off'],
    						$_POST['ads_above_popular_product_on_off'],
    						$_POST['ads_above_testimonial_on_off'],
    						$_POST['ads_category_sidebar_on_off']
    					));

    $success_message = 'Advertisement On-Off Section is updated successfully.';
} */
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Cài đặt website</h1>
    </div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
foreach ($result as $row) {
    $logo                            = $row['logo'];
    $favicon                         = $row['favicon'];
    $footer_about                    = $row['footer_about'];
    $footer_copyright                = $row['footer_copyright'];
    $contact_address                 = $row['contact_address'];
    $contact_email                   = $row['contact_email'];
    $contact_phone                   = $row['contact_phone'];
   // $contact_fax                     = $row['contact_fax'];
    $contact_map_iframe              = $row['contact_map_iframe'];
    $receive_email                   = $row['receive_email'];
    $receive_email_subject           = $row['receive_email_subject'];
    $receive_email_thank_you_message = $row['receive_email_thank_you_message'];
    $forget_password_message         = $row['forget_password_message'];
   // $total_recent_post_footer        = $row['total_recent_post_footer'];
   // $total_popular_post_footer       = $row['total_popular_post_footer'];
  //  $total_recent_post_sidebar       = $row['total_recent_post_sidebar'];
  //  $total_popular_post_sidebar      = $row['total_popular_post_sidebar'];
    $total_featured_product_home     = $row['total_featured_product_home'];
    $total_latest_product_home       = $row['total_latest_product_home'];
    $total_popular_product_home      = $row['total_popular_product_home'];
    $meta_title_home                 = $row['meta_title_home'];
    $meta_keyword_home               = $row['meta_keyword_home'];
    $meta_description_home           = $row['meta_description_home'];
    $banner_login                    = $row['banner_login'];
    $banner_registration             = $row['banner_registration'];
    $banner_forget_password          = $row['banner_forget_password'];
    $banner_reset_password           = $row['banner_reset_password'];
    $banner_search                   = $row['banner_search'];
    $banner_cart                     = $row['banner_cart'];
    $banner_checkout                 = $row['banner_checkout'];
    $banner_product_category         = $row['banner_product_category'];
   // $banner_blog                     = $row['banner_blog'];
   // $cta_title                       = $row['cta_title'];
   // $cta_content                     = $row['cta_content'];
   // $cta_read_more_text              = $row['cta_read_more_text'];
  //  $cta_read_more_url               = $row['cta_read_more_url'];
  //  $cta_photo                       = $row['cta_photo'];
    $featured_product_title          = $row['featured_product_title'];
    $featured_product_subtitle       = $row['featured_product_subtitle'];
    $latest_product_title            = $row['latest_product_title'];
    $latest_product_subtitle         = $row['latest_product_subtitle'];
    $popular_product_title           = $row['popular_product_title'];
    $popular_product_subtitle        = $row['popular_product_subtitle'];
   // $testimonial_title               = $row['testimonial_title'];
   // $testimonial_subtitle            = $row['testimonial_subtitle'];
  //  $testimonial_photo               = $row['testimonial_photo'];
  //  $blog_title                      = $row['blog_title'];
   // $blog_subtitle                   = $row['blog_subtitle'];
    $newsletter_text                 = $row['newsletter_text'];
    if(trim($newsletter_text) === 'Sign-up to our newsletter for latest promotions and discounts.') {
        $newsletter_text = 'Đăng ký nhận bản tin để cập nhật các chương trình khuyến mãi và ưu đãi mới nhất.';
    }
  //  $stripe_public_key               = $row['stripe_public_key'];
 //   $stripe_secret_key               = $row['stripe_secret_key'];
    $bank_detail                     = $row['bank_detail'];
    $cod_on_off                      = isset($row['cod_on_off']) ? (int)$row['cod_on_off'] : 1;
    $home_service_on_off             = $row['home_service_on_off'];
    $home_welcome_on_off             = $row['home_welcome_on_off'];
    $home_featured_product_on_off    = $row['home_featured_product_on_off'];
    $home_latest_product_on_off      = $row['home_latest_product_on_off'];
    $home_popular_product_on_off     = $row['home_popular_product_on_off'];
  //  $home_testimonial_on_off         = $row['home_testimonial_on_off'];
   // $home_blog_on_off                = $row['home_blog_on_off'];

  //  $ads_above_welcome_on_off           = $row['ads_above_welcome_on_off'];
  //  $ads_above_featured_product_on_off  = $row['ads_above_featured_product_on_off'];
  //  $ads_above_latest_product_on_off    = $row['ads_above_latest_product_on_off'];
 //   $ads_above_popular_product_on_off   = $row['ads_above_popular_product_on_off'];
 //   $ads_above_testimonial_on_off       = $row['ads_above_testimonial_on_off'];
  //  $ads_category_sidebar_on_off        = $row['ads_category_sidebar_on_off'];
}
?>

<style>
.nav-tabs-custom > .settings-tabs {
    border-bottom: 1px solid #d8dde6;
}

.nav-tabs-custom > .settings-tabs > li > a {
    color: #2f3a4a;
    font-weight: 500;
    border-top: 3px solid transparent;
    transition: background-color .2s ease, color .2s ease, border-top-color .2s ease;
}

.nav-tabs-custom > .settings-tabs > li > a:hover,
.nav-tabs-custom > .settings-tabs > li > a:focus {
    background: #f5f9ff;
    color: #1e4f91;
}

.nav-tabs-custom > .settings-tabs > li.active > a,
.nav-tabs-custom > .settings-tabs > li.active > a:hover,
.nav-tabs-custom > .settings-tabs > li.active > a:focus {
    background: #1f66d1;
    color: #ffffff;
    border-top-color: #124a9f;
    font-weight: 700;
}

.nav-tabs-custom > .settings-tabs > li.active > a {
    box-shadow: inset 0 -2px 0 rgba(255, 255, 255, 0.28);
}

.nav-tabs-custom > .tab-content > .tab-pane {
    display: none;
}

.nav-tabs-custom > .tab-content > .tab-pane.active {
    display: block;
}

/* Improve form spacing for all settings tabs. */
.nav-tabs-custom .tab-content {
    padding-top: 14px;
}

.nav-tabs-custom .form-horizontal .form-group {
    margin-bottom: 16px;
}

.nav-tabs-custom .form-horizontal .control-label {
    line-height: 1.35;
    padding-top: 8px;
}

.nav-tabs-custom .form-horizontal .form-control {
    min-height: 40px;
}

.nav-tabs-custom .form-horizontal textarea.form-control {
    min-height: 120px;
}

.nav-tabs-custom .form-horizontal input[type="file"] {
    display: block;
    margin-top: 6px;
    margin-bottom: 12px;
}

.nav-tabs-custom .form-horizontal button[type="submit"],
.nav-tabs-custom .form-horizontal input[type="submit"] {
    margin-top: 6px;
}

.nav-tabs-custom .table > tbody > tr > td {
    vertical-align: top;
    padding: 16px;
}

.nav-tabs-custom .table input[type="file"] {
    display: block;
    margin-top: 8px;
    margin-bottom: 12px;
}

.nav-tabs-custom .table input[type="submit"] {
    margin-top: 8px !important;
}

.payment-settings-card {
    border: 1px solid #d9e2ec;
    background: #ffffff;
    padding: 18px 20px 16px;
}

.payment-settings-title {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #1f2d3d;
}

.payment-settings-subtitle {
    margin: 0 0 14px;
    color: #5b6675;
}

.payment-mode-box {
    border: 1px solid #cfd8e3;
    border-left: 4px solid #2e944b;
    background: #f8fbff;
    padding: 12px 14px;
    margin-bottom: 16px;
}

.payment-mode-label {
    display: block;
    font-weight: 700;
    margin-bottom: 4px;
    color: #17263a;
}

.payment-mode-value {
    margin: 0;
    color: #223247;
}

.payment-badge {
    display: inline-block;
    padding: 3px 8px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: #fff;
    background: #2e944b;
    margin-left: 8px;
}

.payment-feature-list {
    margin: 0 0 14px;
    padding-left: 18px;
    color: #374456;
}

.payment-feature-list li {
    margin-bottom: 6px;
}
</style>


<section class="content" style="min-height:auto;margin-bottom: -30px;">
    <div class="row">
        <div class="col-md-12">
            <?php if($error_message): ?>
            <div class="callout callout-danger">
            
            <p>
            <?php echo $error_message; ?>
            </p>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="callout callout-success">
            
            <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="content">

    <div class="row">
        <div class="col-md-12">
                            
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs settings-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab">Logo</a></li>
                        <li><a href="#tab_2" data-toggle="tab">Biểu tượng trang</a></li>
                        <li><a href="#tab_3" data-toggle="tab">Chân trang và liên hệ</a></li>
                        <li><a href="#tab_4" data-toggle="tab">Cài đặt thông báo</a></li>
                        <li><a href="#tab_5" data-toggle="tab">Sản phẩm</a></li>
                        <li><a href="#tab_6" data-toggle="tab">Cài đặt trang chủ</a></li>
                        <li><a href="#tab_7" data-toggle="tab">Cài đặt banner</a></li>
                        <li><a href="#tab_9" data-toggle="tab">Cài đặt thanh toán</a></li>
                       <!--<li><a href="#tab_11" data-toggle="tab">Ads</a></li>-->
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">


                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Ảnh hiện tại</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <img src="../assets/uploads/<?php echo $logo; ?>" class="existing-photo" style="height:80px;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Ảnh mới</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="photo_logo">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form1">Cập nhật Logo</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>

                            


                        </div>
                        <div class="tab-pane" id="tab_2">

                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Ảnh hiện tại</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <img src="../assets/uploads/<?php echo $favicon; ?>" class="existing-photo" style="height:40px;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Ảnh mới</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="photo_favicon">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form2">Cập nhật Biểu tượng trang</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                        </div>
                        <div class="tab-pane" id="tab_3">

                            <form class="form-horizontal" action="" method="post">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Chân trang - Bản quyền</label>
                                        <div class="col-sm-9">
                                            <input class="form-control" type="text" name="footer_copyright" value="<?php echo $footer_copyright; ?>">
                                        </div>
                                    </div>                              
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Địa chỉ liên hệ</label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control" name="contact_address" style="height:140px;"><?php echo $contact_address; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Email liên hệ </label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="contact_email" value="<?php echo $contact_email; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Số điện thoại liên hệ </label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="contact_phone" value="<?php echo $contact_phone; ?>">
                                        </div>
                                    </div>
                                 <!-- <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Contact Fax Number </label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="contact_fax" value="<?php echo $contact_fax; ?>">
                                        </div>
                                    </div>-->
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Bản đồ liên hệ (iFrame) </label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" name="contact_map_iframe" style="height:200px;"><?php echo $contact_map_iframe; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form3">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                        </div>

                        <div class="tab-pane" id="tab_4">

                            <form class="form-horizontal" action="" method="post">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Địa chỉ email nhận liên hệ</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="receive_email" value="<?php echo $receive_email; ?>">
                                        </div>
                                    </div>                                  
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Tiêu đề email liên hệ</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="receive_email_subject" value="<?php echo $receive_email_subject; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Nội dung cảm ơn sau khi liên hệ</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="receive_email_thank_you_message"><?php echo $receive_email_thank_you_message; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Thông báo quên mật khẩu</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="forget_password_message"><?php echo $forget_password_message; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-5">
                                            <button type="submit" class="btn btn-success pull-left" name="form4">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                        </div>

                        <div class="tab-pane" id="tab_5">

                            <form class="form-horizontal" action="" method="post">
                            <div class="box box-info">
                                <div class="box-body">
                                    <!--<div class="form-group">
                                        <label for="" class="col-sm-4 control-label">Footer (How many recent posts?)<span>*</span></label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="total_recent_post_footer" value="<?php echo $total_recent_post_footer; ?>">
                                        </div>
                                    </div>      
                                    <div class="form-group">
                                        <label for="" class="col-sm-4 control-label">Footer (How many popular posts?)<span>*</span></label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="total_popular_post_footer" value="<?php echo $total_popular_post_footer; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-4 control-label">Sidebar (How many recent posts?)<span>*</span></label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="total_recent_post_sidebar" value="<?php echo $total_recent_post_sidebar; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-4 control-label">Sidebar (How many popular posts?)<span>*</span></label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="total_popular_post_sidebar" value="<?php echo $total_popular_post_sidebar; ?>">
                                        </div>
                                    </div>-->
                                    <div class="form-group">
                                        <label for="" class="col-sm-4 control-label">Trang chủ (Số sản phẩm nổi bật hiển thị)<span>*</span></label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="total_featured_product_home" value="<?php echo $total_featured_product_home; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-4 control-label">Trang chủ (Số sản phẩm mới nhất hiển thị)<span>*</span></label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="total_latest_product_home" value="<?php echo $total_latest_product_home; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-4 control-label">Trang chủ (Số sản phẩm phổ biến hiển thị)<span>*</span></label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="total_popular_product_home" value="<?php echo $total_popular_product_home; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-4 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form5">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                        </div>




                        <div class="tab-pane" id="tab_6">


                            <h3>Bật/Tắt các khu vực</h3>
                            <form class="form-horizontal" action="" method="post">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Khu vực dịch vụ </label>
                                        <div class="col-sm-4">
                                            <select name="home_service_on_off" class="form-control" style="width:auto;">
	                                            <option value="1" <?php if($home_service_on_off == 1) {echo 'selected';} ?>>Bật</option>
	                                            <option value="0" <?php if($home_service_on_off == 0) {echo 'selected';} ?>>Tắt</option>
                                            </select>
                                        </div>
                                    </div>      
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Khu vực chào mừng </label>
                                        <div class="col-sm-4">
                                            <select name="home_welcome_on_off" class="form-control" style="width:auto;">
	                                            <option value="1" <?php if($home_welcome_on_off == 1) {echo 'selected';} ?>>Bật</option>
	                                            <option value="0" <?php if($home_welcome_on_off == 0) {echo 'selected';} ?>>Tắt</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Khu vực sản phẩm nổi bật </label>
                                        <div class="col-sm-4">
                                            <select name="home_featured_product_on_off" class="form-control" style="width:auto;">
	                                            <option value="1" <?php if($home_featured_product_on_off == 1) {echo 'selected';} ?>>Bật</option>
	                                            <option value="0" <?php if($home_featured_product_on_off == 0) {echo 'selected';} ?>>Tắt</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Khu vực sản phẩm mới nhất </label>
                                        <div class="col-sm-4">
                                            <select name="home_latest_product_on_off" class="form-control" style="width:auto;">
	                                            <option value="1" <?php if($home_latest_product_on_off == 1) {echo 'selected';} ?>>Bật</option>
	                                            <option value="0" <?php if($home_latest_product_on_off == 0) {echo 'selected';} ?>>Tắt</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Khu vực sản phẩm phổ biến </label>
                                        <div class="col-sm-4">
                                            <select name="home_popular_product_on_off" class="form-control" style="width:auto;">
	                                            <option value="1" <?php if($home_popular_product_on_off == 1) {echo 'selected';} ?>>Bật</option>
	                                            <option value="0" <?php if($home_popular_product_on_off == 0) {echo 'selected';} ?>>Tắt</option>
                                            </select>
                                        </div>
                                    </div>
                                   <!-- <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Testimonial Section </label>
                                        <div class="col-sm-4">
                                            <select name="home_testimonial_on_off" class="form-control" style="width:auto;">
                                            	<option value="1" <?php if($home_testimonial_on_off == 1) {echo 'selected';} ?>>On</option>
                                            	<option value="0" <?php if($home_testimonial_on_off == 0) {echo 'selected';} ?>>Off</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Blog Section </label>
                                        <div class="col-sm-4">
                                            <select name="home_blog_on_off" class="form-control" style="width:auto;">
                                            	<option value="1" <?php if($home_blog_on_off == 1) {echo 'selected';} ?>>On</option>
                                            	<option value="0" <?php if($home_blog_on_off == 0) {echo 'selected';} ?>>Off</option>
                                            </select>
                                        </div>
                                    </div>-->
                                    
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6_0">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>

                            
                            <h3>Phần Meta</h3>
                            <form class="form-horizontal" action="" method="post">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Tiêu đề Meta </label>
                                        <div class="col-sm-8">
                                            <input type="text" name="meta_title_home" class="form-control" value="<?php echo $meta_title_home ?>">
                                        </div>
                                    </div>      
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Từ khóa Meta </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="meta_keyword_home" style="height:100px;"><?php echo $meta_keyword_home ?></textarea>
                                        </div>
                                    </div>  
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Mô tả Meta </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="meta_description_home" style="height:200px;"><?php echo $meta_description_home ?></textarea>
                                        </div>
                                    </div>  
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>



                           <!-- <h3>Call to Action Section</h3>
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">                                          
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Title<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="cta_title" value="<?php echo $cta_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Content<span>*</span></label>
                                        <div class="col-sm-8">
                                            <textarea name="cta_content" class="form-control" cols="30" rows="10" style="height:120px;"><?php echo $cta_content; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Read More Text<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="cta_read_more_text" value="<?php echo $cta_read_more_text; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Read More URL<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="cta_read_more_url" value="<?php echo $cta_read_more_url; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Existing Call to Action Background</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <img src="../assets/uploads/<?php echo $cta_photo; ?>" class="existing-photo" style="height:80px;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">New Background</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="cta_photo">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6_7">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>-->





                            <h3>Khu vực sản phẩm nổi bật</h3>
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">                                          
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Tiêu đề sản phẩm nổi bật<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="featured_product_title" value="<?php echo $featured_product_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Phụ đề sản phẩm nổi bật<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="featured_product_subtitle" value="<?php echo $featured_product_subtitle; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6_4">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                            <h3>Khu vực sản phẩm mới nhất</h3>
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">                                          
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Tiêu đề sản phẩm mới nhất<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="latest_product_title" value="<?php echo $latest_product_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Phụ đề sản phẩm mới nhất<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="latest_product_subtitle" value="<?php echo $latest_product_subtitle; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6_5">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                            <h3>Khu vực sản phẩm phổ biến</h3>
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">                                          
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Tiêu đề sản phẩm phổ biến<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="popular_product_title" value="<?php echo $popular_product_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Phụ đề sản phẩm phổ biến<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="popular_product_subtitle" value="<?php echo $popular_product_subtitle; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6_6">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                            <!--
                            <h3>Testimonial Section</h3>
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">                                          
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Testimonial Section Title<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="testimonial_title" value="<?php echo $testimonial_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Testimonial Section SubTitle<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="testimonial_subtitle" value="<?php echo $testimonial_subtitle; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Existing Testimonial Background</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <img src="../assets/uploads/<?php echo $testimonial_photo; ?>" class="existing-photo" style="height:80px;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">New Background</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="testimonial_photo">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6_1">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                            <h3>Blog Section</h3>
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">                                          
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Blog Section Title<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="blog_title" value="<?php echo $blog_title; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Blog Section SubTitle<span>*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="blog_subtitle" value="<?php echo $blog_subtitle; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6_2">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>

                                    -->
                            

                            <h3>Khu vực bản tin</h3>
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">                                          
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Nội dung bản tin</label>
                                        <div class="col-sm-8">
                                            <textarea name="newsletter_text" class="form-control" cols="30" rows="10" style="height: 120px;"><?php echo $newsletter_text; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form6_3">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>


                        </div>



                        <div class="tab-pane" id="tab_7">

                            <table class="table table-bordered">
                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Banner trang đăng nhập hiện tại</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_login; ?>" alt="" style="width: 100%;height:auto;"> 
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Cập nhật banner trang đăng nhập</h4>
                                        Chọn ảnh
                                        <input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Cập nhật" style="margin-top:10px;" name="form7_1">
                                    </td>
                                    </form>
                                </tr>
                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Banner trang đăng ký hiện tại</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_registration; ?>" alt="" style="width: 100%;height:auto;">  
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Cập nhật banner trang đăng ký</h4>
                                        Chọn ảnh
                                        <input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Cập nhật" style="margin-top:10px;" name="form7_2">
                                    </td>
                                    </form>
                                </tr>
                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Banner trang quên mật khẩu hiện tại</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_forget_password; ?>" alt="" style="width: 100%;height:auto;">   
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Cập nhật banner trang quên mật khẩu</h4>
                                        Chọn ảnh
                                        <input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Cập nhật" style="margin-top:10px;" name="form7_3">
                                    </td>
                                    </form>
                                </tr>
                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Banner trang đặt lại mật khẩu hiện tại</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_reset_password; ?>" alt="" style="width: 100%;height:auto;">   
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Cập nhật banner trang đặt lại mật khẩu</h4>
                                        Chọn ảnh
                                        <input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Cập nhật" style="margin-top:10px;" name="form7_4">
                                    </td>
                                    </form>
                                </tr>
                                
                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Banner trang tìm kiếm hiện tại</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_search; ?>" alt="" style="width: 100%;height:auto;">  
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Cập nhật banner trang tìm kiếm</h4>
                                        Chọn ảnh
                                        <input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Cập nhật" style="margin-top:10px;" name="form7_6">
                                    </td>
                                    </form>
                                </tr>


                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Banner trang giỏ hàng hiện tại</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_cart; ?>" alt="" style="width: 100%;height:auto;">  
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Cập nhật banner trang giỏ hàng</h4>
                                        Chọn ảnh
                                        <input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Cập nhật" style="margin-top:10px;" name="form7_7">
                                    </td>
                                    </form>
                                </tr>


                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Banner trang thanh toán hiện tại</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_checkout; ?>" alt="" style="width: 100%;height:auto;">  
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Cập nhật banner trang thanh toán</h4>
                                        Chọn ảnh
                                        <input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Cập nhật" style="margin-top:10px;" name="form7_8">
                                    </td>
                                    </form>
                                </tr>

                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Banner trang danh mục sản phẩm hiện tại</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_product_category; ?>" alt="" style="width: 100%;height:auto;">  
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Cập nhật banner trang danh mục sản phẩm</h4>
                                        Chọn ảnh
                                        <input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Cập nhật" style="margin-top:10px;" name="form7_9">
                                    </td>
                                    </form>
                                </tr>

                               <!-- <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                    <td style="width:50%">
                                        <h4>Existing Blog Page Banner</h4>
                                        <p>
                                            <img src="<?php echo '../assets/uploads/'.$banner_blog; ?>" alt="" style="width: 100%;height:auto;">  
                                        </p>                                        
                                    </td>
                                    <td style="width:50%">
                                        <h4>Change Blog Page Banner</h4>
                                        Select Photo<input type="file" name="photo">
                                        <input type="submit" class="btn btn-primary btn-xs" value="Change" style="margin-top:10px;" name="form7_10">
                                    </td>
                                    </form>
                                </tr>-->
                            </table>

                        </div>



                    
<!-- PAYMENT METHODS TAB -->



                        <div class="tab-pane" id="tab_9">
                            <form class="form-horizontal" action="" method="post">
                                <div class="box box-info">
                                    <div class="box-body">
                                        <div class="payment-settings-card">
                                            <div class="form-group" style="margin-bottom:14px;">
                                                <label style="display:flex;align-items:center;gap:10px;font-weight:700;margin:0;">
                                                    <input type="checkbox" id="cod_on_off" name="cod_on_off" value="1" <?php echo ((int)$cod_on_off === 1) ? 'checked' : ''; ?> style="margin:0;">
                                                    <span>Thanh toán khi nhận hàng (COD)</span>
                                                </label>
                                                <div style="margin-top:8px;">
                                                    <span>Trạng thái:</span>
                                                    <span id="cod_status" class="label <?php echo ((int)$cod_on_off === 1) ? 'label-success' : 'label-default'; ?>">
                                                        <?php echo ((int)$cod_on_off === 1) ? 'Đang bật' : 'Đang tắt'; ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-success" name="form9">Lưu thay đổi</button>

                                            <script>
                                            (function () {
                                                var checkbox = document.getElementById('cod_on_off');
                                                var badge = document.getElementById('cod_status');
                                                if (!checkbox || !badge) return;

                                                function syncBadge() {
                                                    if (checkbox.checked) {
                                                        badge.className = 'label label-success';
                                                        badge.textContent = 'Đang bật';
                                                    } else {
                                                        badge.className = 'label label-default';
                                                        badge.textContent = 'Đang tắt';
                                                    }
                                                }

                                                checkbox.addEventListener('change', syncBadge);
                                                syncBadge();
                                            })();
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

<!--
                        <div class="tab-pane" id="tab_11">
                            <h3>Advertisements On and Off</h3>
                            <form class="form-horizontal" action="" method="post">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Above Welcome </label>
                                        <div class="col-sm-4">
                                            <select name="ads_above_welcome_on_off" class="form-control" style="width:auto;">
                                            	<option value="1" <?php if($ads_above_welcome_on_off == 1) {echo 'selected';} ?>>On</option>
                                            	<option value="0" <?php if($ads_above_welcome_on_off == 0) {echo 'selected';} ?>>Off</option>
                                            </select>
                                        </div>
                                    </div>      
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Above Featured Product </label>
                                        <div class="col-sm-4">
                                            <select name="ads_above_featured_product_on_off" class="form-control" style="width:auto;">
                                            	<option value="1" <?php if($ads_above_featured_product_on_off == 1) {echo 'selected';} ?>>On</option>
                                            	<option value="0" <?php if($ads_above_featured_product_on_off == 0) {echo 'selected';} ?>>Off</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Above Latest Product </label>
                                        <div class="col-sm-4">
                                            <select name="ads_above_latest_product_on_off" class="form-control" style="width:auto;">
                                            	<option value="1" <?php if($ads_above_latest_product_on_off == 1) {echo 'selected';} ?>>On</option>
                                            	<option value="0" <?php if($ads_above_latest_product_on_off == 0) {echo 'selected';} ?>>Off</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Above Popular Product </label>
                                        <div class="col-sm-4">
                                            <select name="ads_above_popular_product_on_off" class="form-control" style="width:auto;">
                                            	<option value="1" <?php if($ads_above_popular_product_on_off == 1) {echo 'selected';} ?>>On</option>
                                            	<option value="0" <?php if($ads_above_popular_product_on_off == 0) {echo 'selected';} ?>>Off</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Above Testimonial </label>
                                        <div class="col-sm-4">
                                            <select name="ads_above_testimonial_on_off" class="form-control" style="width:auto;">
                                            	<option value="1" <?php if($ads_above_testimonial_on_off == 1) {echo 'selected';} ?>>On</option>
                                            	<option value="0" <?php if($ads_above_testimonial_on_off == 0) {echo 'selected';} ?>>Off</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label">Category Page Sidebar </label>
                                        <div class="col-sm-4">
                                            <select name="ads_category_sidebar_on_off" class="form-control" style="width:auto;">
                                            	<option value="1" <?php if($ads_category_sidebar_on_off == 1) {echo 'selected';} ?>>On</option>
                                            	<option value="0" <?php if($ads_category_sidebar_on_off == 0) {echo 'selected';} ?>>Off</option>
                                            </select>
                                        </div>
                                    </div>                                    
                                    <div class="form-group">
                                        <label for="" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form11">Cập nhật</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>

-->

                    </div>
                </div>

                

            </form>
        </div>
    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabLinks = document.querySelectorAll('.settings-tabs a[href^="#tab_"]');
    var tabItems = document.querySelectorAll('.settings-tabs li');
    var tabPanes = document.querySelectorAll('.nav-tabs-custom > .tab-content > .tab-pane');
    var storageKey = 'adminSettingsActiveTab';
    var hashTab = window.location.hash;
    var savedTab = localStorage.getItem(storageKey);
    var defaultTab = '#tab_1';

    function getLinkByTabId(tabId) {
        return document.querySelector('.settings-tabs a[href="' + tabId + '"]');
    }

    function showTab(tabId) {
        var targetLink = getLinkByTabId(tabId);
        if (!targetLink) {
            tabId = defaultTab;
            targetLink = getLinkByTabId(tabId);
        }

        if (!targetLink) {
            return;
        }

        tabItems.forEach(function(item) {
            item.classList.remove('active');
        });

        tabPanes.forEach(function(pane) {
            pane.classList.remove('active');
        });

        var targetItem = targetLink.parentElement;
        var targetPane = document.querySelector(tabId);

        if (targetItem) {
            targetItem.classList.add('active');
        }
        if (targetPane) {
            targetPane.classList.add('active');
        }

        localStorage.setItem(storageKey, tabId);
        if (window.history && history.replaceState) {
            history.replaceState(null, null, tabId);
        } else {
            window.location.hash = tabId;
        }
    }

    tabLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showTab(this.getAttribute('href'));
        });
    });

    if (hashTab && getLinkByTabId(hashTab)) {
        showTab(hashTab);
    } else if (savedTab && getLinkByTabId(savedTab)) {
        showTab(savedTab);
    } else {
        showTab(defaultTab);
    }
});
</script>

<?php require_once('footer.php'); ?>
