<?php
include 'inc/config.php';
if($_POST['id'])
{
	$id = $_POST['id'];
	
	$statement = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE tcat_id=?");
	$statement->execute(array($id));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	?><option value="">Chọn danh mục cấp 2</option><?php						
	foreach ($result as $row) {
		?>
        <option value="<?php echo $row['mcat_id']; ?>"><?php echo $row['mcat_name']; ?></option>
        <?php
	}
}
