<div class="form-item">
 <fieldset><legend>{ts}Find Events{/ts}</legend>
  <table class="form-layout">
    <tr>
        <td class="label">{$form.title.label}</td>
        <td>{$form.title.html|crmReplace:class:twenty}
             <div class="description font-italic">
                    {ts}Complete OR partial Event name.{/ts}
             </div>
             <div style="height: auto; vertical-align: bottom">{$form.eventsByDates.html}</div>
        </td>
        <td rowspan="2"><label>{ts}Event Type{/ts}</label>
            <div class="listing-box">
                {foreach from=$form.event_type_id item="event_val"}
                <div class="{cycle values="odd-row,even-row"}">
                    {$event_val.html}
                </div>
                {/foreach}
            </div>
        </td>
        <td class="right" rowspan="2">&nbsp;{$form.buttons.html}</td>  
    </tr>
  
    <tr>
       <td></td>
       <td colspan="2">
       <table class="form-layout-compressed" id="id_fromToDates">
         <tr>
           <td>{$form.start_date.label}</td>
           <td>&nbsp;{$form.start_date.html}&nbsp;
            &nbsp;{include file="CRM/common/calendar/desc.tpl" trigger=trigger_search_member_1}
            {include file="CRM/common/calendar/body.tpl" dateVar=start_date startDate=startYear endDate=endYear offset=5 trigger=trigger_search_member_1}
            </td>
          </tr>
          <tr>
            <td>{$form.end_date.label}</td>
            <td>&nbsp;{$form.end_date.html}&nbsp;
             &nbsp;{include file="CRM/common/calendar/desc.tpl" trigger=trigger_search_member_2}
             {include file="CRM/common/calendar/body.tpl" dateVar=end_date startDate=startYear endDate=endYear offset=5 trigger=trigger_search_member_2}
            </td> 
          </tr>
      </table> 
    </td></tr>  
  </table>
</fieldset>
</div>

{include file="CRM/common/showHide.tpl"}

{literal} 
<script type="text/javascript">
if ( document.getElementsByName('eventsByDates')[1].checked ) {
   show( 'id_fromToDates', 'block' );
}
</script>
{/literal} 