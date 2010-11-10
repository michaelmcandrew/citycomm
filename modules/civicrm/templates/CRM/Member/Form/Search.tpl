{assign var="showBlock" value="'searchForm'"}
{assign var="hideBlock" value="'searchForm_show'"}

<div id="searchForm_show" class="form-item">
  <a href="#" onclick="hide('searchForm_show'); show('searchForm'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}" /></a>
  <label>
        {ts}Edit Search Criteria{/ts}
  </label>
</div>

<div id="searchForm" class="form-item">
<fieldset><legend>{ts}Search Criteria{/ts}</legend>
{strip}
     <table class="form-layout">
        <tr>
            <td class="font-size12pt" colspan="2">
                {$form.sort_name.label}&nbsp;&nbsp;{$form.sort_name.html|crmReplace:class:'twenty'}&nbsp;&nbsp;&nbsp;{$form.buttons.html}
            </td>       
        </tr>
      
        {include file="CRM/Member/Form/Search/Common.tpl"}
      
        <tr>
            <td colspan="2">{$form.buttons.html}</td>
        </tr>
    </table>
{/strip} 
</fieldset>
</div>

{if $rowsEmpty}
    {include file="CRM/Member/Form/Search/EmptyResults.tpl"}
{/if}

{if $rows}
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
    {assign var="showBlock" value="'searchForm_show'"}
    {assign var="hideBlock" value="'searchForm'"}
    
    {* Search request has returned 1 or more matching rows. *}
    <fieldset>
    
       {* This section handles form elements for action task select and submit *}
       {include file="CRM/common/searchResultTasks.tpl"}

       {* This section displays the rows along and includes the paging controls *}
       <p></p>
       {include file="CRM/Member/Form/Selector.tpl" context="Search"}
       
    </fieldset>
    {* END Actions/Results section *}

{/if}

<script type="text/javascript">
    var showBlock = new Array({$showBlock});
    var hideBlock = new Array({$hideBlock});

{* hide and display the appropriate blocks *}
    on_load_init_blocks( showBlock, hideBlock );
</script>
