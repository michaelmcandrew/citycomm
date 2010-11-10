<div class="form-item">
<fieldset><legend>{ts}View Contribution{/ts}</legend>
<dl>
	<dt class="font-size12pt">{ts}From{/ts}</dt>
	<dd class="font-size12pt"><strong>{$displayName}</strong>&nbsp;</dd>

	<dt>{ts}Contribution Type{/ts}</dt>
	<dd>{$contribution_type}&nbsp; {if $is_test} {ts}(test){/ts} {/if}</dd>

	<dt>{ts}Total Amount{/ts}</dt>
	<dd class="bold">{$total_amount|crmMoney}&nbsp; 
{if $contribution_recur_id}
  <strong>{ts}Recurring Contribution{/ts}</strong> <br/>
  {ts}Installments{/ts}: {$recur_installments}, {ts}Interval{/ts}: {$recur_frequency_interval} {$recur_frequency_unit}(s)
{/if}
</dd>

	{if $non_deductible_amount}
	<dt>{ts}Non-deductible Amount{/ts}</dt>
	<dd>{$non_deductible_amount|crmMoney}&nbsp;</dd>
	{/if} 

	{if $fee_amount}
	<dt>{ts}Fee Amount{/ts}</dt>
	<dd>{$fee_amount|crmMoney}&nbsp;</dd>
	{/if} 

	{if $net_amount}
	<dt>{ts}Net Amount{/ts}</dt>
	<dd>{$net_amount|crmMoney}&nbsp;</dd>
	{/if}

	<dt>{ts}Received{/ts}</dt>
	<dd>{if $receive_date}{$receive_date|truncate:10:''|crmDate}{else}({ts}pending{/ts}){/if}&nbsp;</dd>

	<dt>{ts}Contribution Status{/ts}</dt>
	<dd {if $contribution_status_id eq 3} class="font-red bold"{/if}>{$contribution_status}
	{if $contribution_status_id eq 2} {if $is_pay_later}: {ts}Pay Later{/ts} {else} : {ts}Incomplete Transaction{/ts} {/if}{/if}</dd>

	{if $cancel_date}
	<dt>{ts}Cancelled Date{/ts}</dt>
	<dd>{$cancel_date|truncate:10:''|crmDate}</dd>

	{if $cancel_reason}
	<dt>{ts}Cancellation Reason{/ts}</dt>
	<dd>{$cancel_reason}</dd>
	{/if} 
	{/if}

	<dt>{ts}Paid By{/ts}</dt>
	<dd>{$payment_instrument}&nbsp;</dd>

	{if $payment_instrument eq 'Check'}
	<dt>{ts}Check Number{/ts}</dt>
	<dd>{$check_number}&nbsp;</dd>
	{/if}

	<dt>{ts}Source{/ts}</dt>
	<dd>{$source}&nbsp;</dd>

	{if $receipt_date}
	<dt>{ts}Receipt Sent{/ts}</dt>
	<dd>{$receipt_date|truncate:10:''|crmDate}</dd>
	{/if} 

	{foreach from=$note item="rec"} 
		{if $rec }
		<dt>{ts}Note:{/ts}</dt>	<dd>{$rec}</dd>
		{/if} 
	{/foreach} 

	{if $trxn_id}
	<dt>{ts}Transaction ID{/ts}</dt>
	<dd>{$trxn_id}&nbsp;</dd>
	{/if} 

	{if $invoice_id}
	<dt>{ts}Invoice ID{/ts}</dt>
	<dd>{$invoice_id}&nbsp;</dd>
	{/if} 

	{if $honor_display}
	<dt>{ts}{$honor_type}{/ts}</dt>
	<dd>{$honor_display}&nbsp;</dd>
	{/if} 

	{if $thankyou_date}
	<dt>{ts}Thank-you Sent{/ts}</dt>
	<dd>{$thankyou_date|truncate:10:''|crmDate}</dd>
	{/if}

</dl>

{if $premium}
<fieldset><legend>{ts}Premium Information{/ts}</legend>
<dl>
	<dt>{ts}Premium{/ts}</dt><dd>{$premium}&nbsp;</dd>
	<dt>{ts}Option{/ts}</dt><dd>{$option}&nbsp;</dd>
	<dt>{ts}Fulfilled{/ts}</dt><dd>{$fulfilled|truncate:10:''|crmDate}&nbsp;</dd>
</dl>
</fieldset>
{/if}
 
{if $softCreditToName}
<dl>
	<dt>{ts}Soft Credit To{/ts}</dt>
    <dd><a href="{crmURL p="civicrm/contact/view" q="reset=1&cid=`$soft_credit_to`"}" id="view_contact" title="{ts}View contact record{/ts}">{$softCreditToName}</a>&nbsp;</dd>
</dl>
{/if}
       
{if $pcp_id}
<fieldset><legend>{ts}Personal Campaign Page{/ts}</legend>
<dl>
	<dt>{ts}Campaign Page{/ts}</dt>
    <dd><a href="{crmURL p="civicrm/contribute/pcp/info" q="reset=1&id=`$pcp_id`"}">{$pcp}</a><br />
        <span class="description">{ts}Contribution was made through this personal campaign page.{/ts}</span>
    </dd>
	<dt>{ts}In Public Honor Roll?{/ts}</dt><dd>{if $pcp_display_in_roll}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</dd>
    {if $pcp_roll_nickname}
        <dt>{ts}Honor Roll Name{/ts}</dt><dd>{$pcp_roll_nickname}</dd>
    {/if}
</dl>
</fieldset>
{/if} 

{include file="CRM/Custom/Page/CustomDataView.tpl"}

{if $billing_address}
<fieldset><legend>{ts}Billing Address{/ts}</legend>
	<div class="form-item">
		{$billing_address|nl2br}
	</div>
</fieldset>
{/if}
<dl>
	<dt></dt>
	<dd>{$form.buttons.html}</dd>
</dl>
</fieldset>
</div>

