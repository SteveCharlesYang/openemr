<?php
/**
 * destroy lot
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("drugs.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$drug_id = $_REQUEST['drug'];
$lot_id  = $_REQUEST['lot'];
$info_msg = "";

if (!AclMain::aclCheckCore('admin', 'drugs')) {
    die(xlt('Not authorized'));
}

if (!$drug_id) {
    die(xlt('Drug ID missing!'));
}

if (!$lot_id) {
    die(xlt('Lot ID missing!'));
}
?>
<html>
<head>
<title><?php echo xlt('Destroy Lot') ?></title>

<?php Header::setupHeader('datetime-picker'); ?>

<style>
    td {
        font-size: 0.8125rem;
    }
</style>

<script>
    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
</script>

</head>

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    sqlStatement(
        "UPDATE drug_inventory SET " .
        "destroy_date = ?, "  .
        "destroy_method = ?, " .
        "destroy_witness = ?, " .
        "destroy_notes = ? "  .
        "WHERE drug_id = ? AND inventory_id = ?",
        array(
            (empty($_POST['form_date']) ? "NULL" : $_POST['form_date']),
            $_POST['form_method'],
            $_POST['form_witness'],
            $_POST['form_notes'],
            $drug_id,
            $lot_id
        )
    );

  // Close this window and redisplay the updated list of drugs.
  //
    echo "<script>\n";
    if ($info_msg) {
        echo " alert('".addslashes($info_msg)."');\n";
    }

    echo " window.close();\n";
    echo " if (opener.refreshme) opener.refreshme();\n";
    echo "</script></body></html>\n";
    exit();
}

 $row = sqlQuery("SELECT * FROM drug_inventory WHERE drug_id = ? " .
  "AND inventory_id = ?", array($drug_id,$lot_id));
    ?>

<form method='post' name='theform' action='destroy_lot.php?drug=<?php echo attr_url($drug_id); ?>&lot=<?php echo attr_url($lot_id); ?>'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<table class='border-0 w-100'>

 <tr>
  <td valign='top' width='1%' nowrap><strong><?php echo xlt('Lot Number'); ?>:</strong></td>
  <td>
    <?php echo text($row['lot_number']) ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><strong><?php echo xlt('Manufacturer'); ?>:</strong></td>
  <td>
    <?php echo text($row['manufacturer']) ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><strong><?php echo xlt('Quantity On Hand'); ?>:</strong></td>
  <td>
    <?php echo text($row['on_hand']) ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><strong><?php echo xlt('Expiration Date'); ?>:</strong></td>
  <td>
    <?php echo text($row['expiration']) ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><strong><?php echo xlt('Date Destroyed'); ?>:</strong></td>
  <td>
   <input type='text' size='10' class='datepicker' name='form_date' id='form_date' value='<?php echo $row['destroy_date'] ? attr($row['destroy_date']) : date("Y-m-d"); ?>' title='<?php echo xla('yyyy-mm-dd date destroyed'); ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><strong><?php echo xlt('Method of Destruction'); ?>:</strong></td>
  <td>
   <input type='text' class='w-100' size='40' name='form_method' maxlength='250'
    value='<?php echo attr($row['destroy_method']) ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><strong><?php echo xlt('Witness'); ?>:</strong></td>
  <td>
   <input type='text' class='w-100' size='40' name='form_witness' maxlength='250'
    value='<?php echo attr($row['destroy_witness']) ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><strong><?php echo xlt('Notes'); ?>:</strong></td>
  <td>
   <input type='text' class='w-100' size='40' name='form_notes' maxlength='250'
    value='<?php echo attr($row['destroy_notes']) ?>' />
  </td>
 </tr>

</table>

<div class="btn-group">
<input type='submit' class="btn btn-primary" name='form_save' value='<?php echo xla('Submit') ;?>' />
<input type='button' class="btn btn-secondary" value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</div>

</center>
</form>
</body>
</html>
