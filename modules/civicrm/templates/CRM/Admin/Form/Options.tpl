{* this template is used for adding/editing options *}
<div class="form-item">
<fieldset><legend>{if $action eq 1}{ts}New {$GName} Option{/ts}{elseif $action eq 8}{ts}Delete {$GName} Option{/ts}{else}{ts}Edit {$GName} Option{/ts}{/if}</legend>
	{if $action eq 8}
      <div class="messages status">
        <dl>
          <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
          <dd>    
          {ts}WARNING: Deleting this option will result in the loss of all {$GName} related records which use the option.{/ts} {ts}This may mean the loss of a substantial amount of data, and the action cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
          </dd>
       </dl>
      </div>
    {else}
	<dl>
        {if $gName eq 'custom_search'}
            <dt>{ts}Custom Search Path{/ts}</dt><dd>{$form.label.html}</dd>
            <dt>&nbsp;</dt><dd class="description">{ts}Enter the "class path" for this custom search here.{/ts} {docURL page="Custom Search Components"}</dd>
        {elseif $gName eq 'from_email_address'}
            <dt>{ts}FROM Email Address{/ts} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_option_value' field='label' id=$id}{/if}</dt><dd>{$form.label.html}</dd>
            <dt>&nbsp;</dt><dd class="description">{ts}Include double-quotes (&quot;) around the name and angle-brackets (&lt; &gt;) around the email address.<br />EXAMPLE: <em>&quot;Client Services&quot; &lt;clientservices@example.org&gt;</em>{/ts}</dd>
        {else}
            <dt>{$form.label.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_option_value' field='label' id=$id}{/if}</dt><dd>{$form.label.html}</dd>
            <dt>&nbsp;</dt><dd class="description">{ts}The option label is displayed to users.{/ts}</dd>
        {/if}
        {if $gName eq 'custom_search'}
            <dt>{ts}Search Title{/ts}</dt><dd>{$form.description.html}</dd>
            <dt>&nbsp;</dt><dd class="description">{ts}This title is displayed to users in the Custom Search listings.{/ts}</dd>
        {else}
            <dt>{$form.description.label}</dt><dd>{$form.description.html}</dd>
            {if $gName eq 'activity_type'}
                <dt>&nbsp;</dt><dd class="description">{ts}Description is included at the top of the activity edit and view pages for this type of activity.{/ts}</dd>
            {/if}
        {/if}
        {if $form.filter.html} {* Filter property is only exposed for some option groups. *}
            <dt>{$form.filter.label}</dt><dd>{$form.filter.html}</dd>
        {/if} 
        {if $gName eq 'participant_status'}
            <dt>{$form.visibility_id.label}</dt><dd>{$form.visibility_id.html}</dd>	
        {/if}
        <dt>{$form.weight.label}</dt><dd>{$form.weight.html}</dd>
        {if $form.component_id.html} {* Component ID is exposed for activity types if CiviCase is enabled. *}
            <dt>{$form.component_id.label}</dt><dd>{$form.component_id.html}</dd>
        {/if}
        <dt>{$form.is_active.label}</dt><dd>{$form.is_active.html}</dd>
        {if $showDefault}
            <dt>{$form.is_default.label}</dt><dd>{$form.is_default.html}</dd>
        {/if}
    </dl>
    {/if}
	<dl><dt></dt><dd>{$form.buttons.html}</dd></dl>
</fieldset>
</div>
