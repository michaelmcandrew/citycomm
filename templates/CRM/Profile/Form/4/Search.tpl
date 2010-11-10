{$fields.organization_name.groupHelpPre}
{if ! empty( $fields )}

  {if $groupId }
    <div id="id_{$groupId}_show" style="border: 1px solid #ccc;background-color: #f3f3f3; padding: 0px 14px;">
      <p><b><a href="#" onclick="hide('id_{$groupId}_show'); show('id_{$groupId}'); return false;">Show search criteria</a></b></p>
    </div>

    <div id="id_{$groupId}" class="citycomm_directory" style="border: 1px solid #ccc;background-color: #f3f3f3; padding: 0px 14px;">
    <h2>Search Criteria <a href="#" onclick="hide('id_{$groupId}'); show('id_{$groupId}_show'); return false;">(hide)</a></h2>

	{else}
	    <div>
	{/if}

  {foreach from=$fields item=field key=fieldName}
        {assign var=n value=$field.name}
	{if $field.is_search_range}
	   {assign var=from value=$field.name|cat:'_from'}
	   {assign var=to value=$field.name|cat:'_to'}
w	        <tr>
        	    <td class="label">{$form.$from.label}</td>
	            <td class="description">{$form.$from.html}</td>
	            <td class="label">{$form.$to.label}</td>
        	    <td class="description">{$form.$to.html}</td>
	        </tr>
	{elseif $field.options_per_line}
		  <h3>{$form.$n.label}</h3>
	    {assign var="count" value="1"}
        {strip}
        <table class="form-layout-compressed">
        <tr>
          {* sort by fails for option per line. Added a variable to iterate through the element array*}
          {assign var="index" value="1"}
          {foreach name=outer key=key item=item from=$form.$n}
          {if $index < 10} {* Hack to skip QF field properties that aren't checkbox elements. *}
              {assign var="index" value=`$index+1`}
          {else}
              {if $field.html_type EQ 'CheckBox' AND  $smarty.foreach.outer.last EQ 1} {* Put 'match ANY / match ALL' checkbox in separate row. *}
                    </tr>
                    <tr>
                        <td class="and_or" width="50%" colspan="{$field.options_per_line}" style="padding-top: 0px;">{$form.$n.$key.html}</td>
              {else}
                    <td width="50%">{$form.$n.$key.html}</td>
                    {if $count EQ $field.options_per_line}
                        </tr>
                        <tr>
                        {assign var="count" value="1"}
                    {else}
                        {assign var="count" value=`$count+1`}
                    {/if}
                {/if}
          {/if}
          {/foreach}
        </tr>
        </table>
			<p><small>{$field.help_post}</small></p>
	{if $field.html_type eq 'Radio' and $form.formName eq 'Search'}
            &nbsp;&nbsp;(&nbsp;<a href="#" title="unselect" onclick="unselectRadio('{$n}', '{$form.formName}'); return false;">{ts}unselect{/ts}</a>&nbsp;)
	{/if}
        {/strip}
	{else}
  <h3>{$form.$n.label}</h3>
                {if $n eq 'greeting_type'}             
                       <table class="form-layout-compressed">
                         <tr>     
                           <td class="description">{$form.$n.html}</td>
                           <td id="customGreeting">
                             {$form.custom_greeting.label}&nbsp;&nbsp;&nbsp;
                             {$form.custom_greeting.html|crmReplace:class:big}
                           </td>
                         </tr>
                       </table> 
		{elseif $n eq 'group'} 
	 	 <td><table id="selector" class="selector" style="width:auto;">
			<tr><td>{$form.$n.html}{* quickform add closing </td> </tr>*}
		 </table></td>
                {else}
                    <td class="description">{$form.$n.html}
		    	{if ($n eq 'gender') or ($field.html_type eq 'Radio' and $form.formName eq 'Search')}
			    &nbsp;&nbsp;(&nbsp;<a href="#" title="unselect" onclick="unselectRadio('{$n}', '{$form.formName}'); return false;">{ts}unselect{/ts}</a>&nbsp;)
	    	        {/if}
		    </td>
                {/if}
        	</tr>
				  <small><p>{$field.help_post}</p></small>

	{/if}
	<p>{$form.buttons.html}</p><br />
    {/foreach}
</div>

{if $groupId}
<script type="text/javascript">
    {if empty($rows) }
	var showBlocks = new Array("id_{$groupId}");
        var hideBlocks = new Array("id_{$groupId}_show");
    {else}
	var showBlocks = new Array("id_{$groupId}_show");
        var hideBlocks = new Array("id_{$groupId}");
    {/if}
    {* hide and display the appropriate blocks as directed by the php code *}
    on_load_init_blocks( showBlocks, hideBlocks );
</script>
{/if}

{else} {* empty fields *}
    <div class="messages status">
      <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
        <dd>{ts}No fields in this Profile have been configured as searchable. Ask the site administrator to check the Profile setup.{/ts}</dd>
      </dl>
    </div>
{/if}
{$fields.organization_name.groupHelpPost}
{if $form.greeting_type}
  {literal}
    <script type="text/javascript">
      window.onload = function() {
        showGreeting();
      }
    </script>
  {/literal}
{/if}

{literal}
<script type="text/javascript">
   
    function showGreeting() {
        if( document.getElementById("greeting_type").value == 4 ) {
            show('customGreeting');           
        } else {
            hide('customGreeting');  
        }        
    }

cj(document).ready(function(){ 
	cj('#selector tr:even').addClass('odd-row ');
	cj('#selector tr:odd ').addClass('even-row');
});
</script>
{/literal}
