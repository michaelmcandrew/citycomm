<div class="citycomm_directory">
{* make sure there are some fields in the selector *}
{if ! empty( $columnHeaders ) || $isReset }

{if $search}
{include file="$searchTPL"}
{/if}

{* show profile listings criteria ($qill) *}
{if $rows}
    {include file="CRM/common/pager.tpl" location="top"}
    {* Search criteria are passed to tpl in the $qill array *}
    {*
		{if $qill}
     <p>
     <div id="search-status">
        {ts}Displaying contacts where:{/ts}
        {include file="CRM/common/displaySearchCriteria.tpl"}
        {if $mapURL}<a href="{$mapURL}">&raquo; {ts}Map these contacts{/ts}</a>{/if}
    </div>
    </p>
    {/if}
		*}
    {strip}
		{*
      {foreach from=$columnHeaders item=header}
        {if $header.sort} 
          {assign var='key' value=$header.sort} 
          {$sort->_response.$key.link} 
        {else} 
          {$header.name}
        {/if} 
      {/foreach}
    *}
      {counter start=0 skip=1 print=false}
      {foreach from=$rows item=row name=listings}
				<h3><a href="/civicrm/profile/view?reset=1&id={$row.1}&gid=4">{$row.sort_name}</a></h3>
				{$row.2}<br />{foreach from=$profileGroups item=group}
				    <h2>{$group.title}</h2>
				    <div id="profilewrap{$groupID}">
				    	 {$group.content}
				    </div>
				{/foreach}
				<hr />				
      {/foreach}
    {/strip}
    {include file="CRM/common/pager.tpl" location="bottom"}
{elseif ! $isReset}
    {include file="CRM/Contact/Form/Search/EmptyResults.tpl" context="Profile"}
{/if}


{else}
    <div class="messages status">
      <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
        <dd>{ts}No fields in this Profile have been configured to display as columns in the listings (selector) table. Ask the site administrator to check the Profile setup.{/ts}</dd>
      </dl>
    </div>
{/if}
</div>
