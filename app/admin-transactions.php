<?php
include_once("admin.php");

$Db = MySQLConnection::GetInstance();
$Transactions = $Db->GetTransactions();

?>

<script type="text/javascript">
$(function()
{
  render_transactions(<?php echo json_encode($Transactions); ?>);
});

  function render_transactions(transactions)
  {
        $("#tbl-previous-transactions").find("tr:gt(0)").remove();
        var html = '';
        for (var i = 0; i < transactions.length; i++)
        {
          html += render_transaction(transactions[i]);
        }
        $("#tbl-previous-transactions").find("tr").after(html);
  }
  
  function render_transaction(trn)
  {
    var html = '<tr><td>' + trn.Id + '</td><td>' + moment(trn.TransactionDateTime).format('DD/MM/YYYY') + '</td><td>' + trn.Hours + '</td><td>' + trn.Giver.Username + '</td><td>' + trn.Receiver.Username + '</td><td>' + trn.Description + '</td><td>' + (trn.GiverApproved == true && trn.ReceiverApproved == true ? "Yes" : "Pending") + '</td></tr>';
    return html;
  }
</script>

<div id="div-categories-list">
    <h2><img class="middle" src="images/32/exchange.png">&nbsp;History of Exchanges</h2>
    <div id="transactions-update-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating...<br/><br/></div>
    <table id="tbl-previous-transactions" class="form-table">
      <tr><th>Id</th><th>Date</th><th>Hours</th><th>From</th><th>To</th><th>For</th><th>Approved?</th></tr>
    </table>
</div>
<?php
include_once("admin-footer.php");
?>
