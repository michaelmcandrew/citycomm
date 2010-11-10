{capture assign=erURL}{crmURL p='civicrm/acl/entityrole' q='reset=1'}{/capture}
{capture assign=rolesURL}{crmURL p='civicrm/admin/options/acl_role' q='group=acl_role&reset=1'}{/capture}
{capture assign=docLink}{docURL page='Access Control' text='Access Control Documentation'}{/capture}
<div id="help">
    <p>{ts 1=$docLink}ACLs allow you control access to CiviCRM data. An ACL consists of an <strong>Operation</strong> (e.g. 'View' or 'Edit'), a <strong>set of data</strong> that the operation can be performed on (e.g. a group of contacts, a profile or a set of custom fields), and a <strong>Role</strong> that has permission to do this operation. Refer to the %1 for more info.{/ts}</p>
    <p>{ts 1=$erURL 2=$rolesURL}You can add or modify your ACLs below. You can create additional ACL Roles <a href='%2'>here</a>... and you can assign Roles to CiviCRM contacts who are users of your site <a href='%1'>here</a>.{/ts}</p>
</div>

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/ACL/Form/ACL.tpl"}
{/if}

{if $rows}
<div id="ltype">
<p></p>
    <div class="form-item">
        {strip}
        <table>
        <tr class="columnheader">
            <th>{ts}Role{/ts}</th>
            <th>{ts}Operation{/ts}</th>
            <th>{ts}Type of Data{/ts}</th>
            <th>{ts}Which Data{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th>{ts}Enabled?{/ts}</th>
            <th></th>
        </tr>
        {foreach from=$rows item=row}
        <tr class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
	        <td>{$row.entity}</td>	
	        <td>{$row.operation}</td>	
	        <td>{$row.object_name}</td>	
	        <td>{$row.object}</td>	
	        <td>{$row.name}</td>	
	        <td>{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{$row.action}</td>
        </tr>
        {/foreach}
        </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
	    <div class="action-link">
    	<a href="{crmURL q="action=add&reset=1"}" id="newACL" class="button"><span>&raquo; {ts}New ACL{/ts}</span></a>
        </div>
        {/if}
    </div>
</div>
{elseif $action ne 1 and $action ne 2 and $action ne 8}
    <div class="messages status">
    <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt>
        {capture assign=crmURL}{crmURL q="action=add&reset=1"}{/capture}
        <dd>{ts 1=$crmURL}There are no ACLs entered. You can <a href='%1'>add one</a>.{/ts}</dd>
        </dl>
    </div>    
{/if}
