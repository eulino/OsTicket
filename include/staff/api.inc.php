<?php
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die($trl->translate("TEXT_ACCESS_DENIED"));

global $trl;

$info['phrase']=($errors && $_POST['phrase'])?Format::htmlchars($_POST['phrase']):$cfg->getAPIPassphrase();
$select='SELECT * ';
$from='FROM '.API_KEY_TABLE;
$where='';
$sortOptions=array('date'=>'created','ip'=>'ipaddr');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
//Sorting options...
if($_REQUEST['sort']) {
    $order_column =$sortOptions[$_REQUEST['sort']];
}

if($_REQUEST['order']) {
    $order=$orderWays[$_REQUEST['order']];
}
$order_column=$order_column?$order_column:'ipaddr';
$order=$order?$order:'ASC';
$order_by=" ORDER BY $order_column $order ";

$total=db_count('SELECT count(*) '.$from.' '.$where);
$pagelimit=1000;//No limit.
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,$pagelimit, $trl);
$pageNav->setURL('admin.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
$query="$select $from $where $order_by";
//echo $query;
$result = db_query($query);
$showing=db_num_rows($result)?$pageNav->showing():'';
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting..
$deletable=0;
?>
<div class="msg"><?php te('LABEL_API_KEYS')?></div>
<hr>
<div><b><?=$showing?></b></div>
 <table width="100%" border="0" cellspacing=1 cellpadding=2>
   <form action="admin.php?t=api" method="POST" name="api" onSubmit="return checkbox_checker(document.forms['api'],1,0);">
   <input type=hidden name='t' value='api'>
   <input type=hidden name='do' value='mass_process'>
   <tr><td>
    <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
        <tr>
	        <th width="7px">&nbsp;</th>
	        <th><?php te('LABEL_API_KEY')?></th>
            <th width="10" nowrap><?php te('LABEL_ACTIVE')?></th>
            <th width="100" nowrap>&nbsp;&nbsp;<?php te('LABEL_IP_ADDRESS')?></th>
	        <th width="150" nowrap>&nbsp;&nbsp;
                <a href="admin.php?t=api&sort=date&order=<?=$negorder?><?=$qstr?>" title="<?php te('TEXT_SORT_BY_CREATE_DATE')?> <?=$negorder?>"><?php te('LABEL_CREATED')?></a></th>
        </tr>
        <?
        $class = 'row1';
        $total=0;
        $active=$inactive=0;
        $sids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($result && db_num_rows($result)):
            $dtpl=$cfg->getDefaultTemplateId();
            while ($row = db_fetch_array($result)) {
                $sel=false;
                $disabled='';
                if($row['isactive'])
                    $active++;
                else
                    $inactive++;
                    
                if($sids && in_array($row['id'],$sids)){
                    $class="$class highlight";
                    $sel=true;
                }
                ?>
            <tr class="<?=$class?>" id="<?=$row['id']?>">
                <td width=7px>
                  <input type="checkbox" name="ids[]" value="<?=$row['id']?>" <?=$sel?'checked':''?>
                        onClick="highLight(this.value,this.checked);">
                <td>&nbsp;<?=$row['apikey']?></td>
                <td><?=$row['isactive']?'<b>Yes</b>':'No'?></td>
                <td>&nbsp;<?=$row['ipaddr']?></td>
                <td>&nbsp;<?=Format::db_datetime($row['created'])?></td>
            </tr>
            <?
            $class = ($class =='row2') ?'row1':'row2';
            } //end of while.
        else: //nothin' found!! ?> 
            <tr class="<?=$class?>"><td colspan=5><?php te('TEXT_QUERY_RETURNED_0_RESULTS')?></td></tr>
        <?
        endif; ?>
     
     </table>
    </td></tr>
    <?
    if(db_num_rows($result)>0): //Show options..
     ?>
    <tr>
        <td align="center">
            <?php
            if($inactive) {?>
                <input class="button" type="submit" name="enable" value="<?php te('LABEL_ENABLE')?>"
                     onClick='return confirm("<?php te('ALERT_ENABLE_KEYS')?>");'>
            <?php
            }
            if($active){?>
            &nbsp;&nbsp;
                <input class="button" type="submit" name="disable" value="<?php te('LABEL_DISABLE')?>"
                     onClick='return confirm("<?php te('ALERT_DISABLE_KEYS')?>");'>
            <?}?>
            &nbsp;&nbsp;
            <input class="button" type="submit" name="delete" value="<?php te('LABEL_DELETE')?>" 
                     onClick='return confirm("<?php te('ALERT_DELETE_KEYS')?>");'>
        </td>
    </tr>
    <?
    endif;
    ?>
    </form>
 </table>
 <br/>
 <div class="msg"><?php te('TEXT_ADD_NEW_IP')?></div>
 <hr>
 <div>
   <?php te('TEXT_ADD_NEW_IP_ADDRESS')?>&nbsp;&nbsp;<font class="error"><?=$errors['ip']?></font>
   <form action="admin.php?t=api" method="POST" >
    <input type=hidden name='t' value='api'>
    <input type=hidden name='do' value='add'>
    <?php te('LABEL_NEW_IP')?>
    <input name="ip" size=30 value="<?=($errors['ip'])?Format::htmlchars($_REQUEST['ip']):''?>" />
    <font class="error">*&nbsp;</font>&nbsp;&nbsp;
     &nbsp;&nbsp; <input class="button" type="submit" name="add" value="<?php te('LABEL_ADD')?>">
    </form>
 </div>
 <br/>
 <div class="msg"><?php te('TEXT_API_PASSPHRASE')?></div>
 <hr>
 <div>
   <?php te('TEXT_API_PASSPHRASE_TEXT')?><br/>
   <form action="admin.php?t=api" method="POST" >
    <input type=hidden name='t' value='api'>
    <input type=hidden name='do' value='update_phrase'>
    <?php te('LABEL_PHRASE')?>
    <input name="phrase" size=50 value="<?=Format::htmlchars($info['phrase'])?>" />
    <font class="error">*&nbsp;<?=$errors['phrase']?></font>&nbsp;&nbsp;
     &nbsp;&nbsp; <input class="button" type="submit" name="update" value="<?php te('LABEL_SUBMIT')?>">
    </form>
    <br/><br/>
    <div><i><?php te('TEXT_API_PASSPHRASE_TEXT_NOTE')?></i></div>
 </div>
